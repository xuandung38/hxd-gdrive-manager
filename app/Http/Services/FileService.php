<?php

namespace App\Http\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Repositories\LocalRepository;

class FileService
{
    private $repository;

    public function __construct(LocalRepository $repository)
    {
        $this->repository = $repository;
    }

    public function detectUser()
    {
        $guards = config('file-manager.guards');
        $user = null;
        $userDirectory = config('file-manager.directory') . '/' . config('file-manager.anonymous_folder');

        foreach ($guards as $guard) {
            if (auth($guard)->check()) {
                $user = auth($guard)->user();
                $userDirectory = $user->folder_id;
                break;
            }
        }

        if ($user === null) {
            abort(403, trans('file-manager::file-manger.you_have_not_logged'));
        }

        return [
            'user' => $user,
            'userDirectory' => $userDirectory
        ];
    }

    public function browser(string $directoryPath)
    {

        $directories = $this->repository->listDirectories($directoryPath);

        $files = array_map(function ($file) use ($directoryPath) {
            return [
                'name' => $file['name'],
                'url' => $this->repository->fileUrl($file['path']),
                'path' => $file['path'],
                'size' => $file['size'],
                'mime' => $file['mimetype'],
                'last_modified' => $file['timestamp'],
            ];
        }, $this->repository->listFiles($directoryPath));

        return [
            'directories' => $directories,
            'files' => $files,
        ];
    }

    public function ckeditorUpload(Request $request, string $destination)
    {
        $file = $request->file('upload'); // Ckeditor using it

        if ($file->isValid()) {

            $filePath = $this->repository->upload($destination, $file);
            $url = $this->repository->fileUrl($filePath);
            $name = $file->hashName();

            return [
                'uploaded' => 1,
                'fileName' => $name,
                'url' => $url,
            ];

        }

        return abort(400, 'Cannot upload file.');
    }

    public function uploadSingle(Request $request, string $destination)
    {
        $file = $request->file('file');

        if ($file->isValid()) {

            $filePath = $this->repository->upload($destination, $file);

            return [
                'name' => $file->getFilename(),
                'url' => $this->repository->fileUrl($filePath),
                'path' => $filePath,
                'size' => $this->repository->fileSize($filePath),
                'mime' => $this->repository->fileMimeType($filePath),
                'last_modified' => $this->repository->lastModifiedDate($filePath),
            ];

        }

        return abort(400, trans('file-manager::file-manger.cannot_upload_file'));
    }

    public function uploadChunk(array $params, string $destination)
    {
        $hashName = empty($params['hash']) ? Str::random(25) : $params['hash'];
        $chunkFilePath = $destination . '/' . $hashName . '.' . $params['offset'] . '.chunk';
        $this->repository->upload($chunkFilePath, $params['data']);

        if ($params['eof']) {
            $chunkFilePaths = $this->buildChunkFilePaths($hashName, $params['offset']);
            $destinationFile = $destination . '/' . $params['name'];

            $this->repository->mergeChunkFiles($destination, $chunkFilePaths, $destinationFile);

            return [
                'name' => $params['name'],
                'url' => $this->repository->fileUrl($destinationFile),
                'path' => $destinationFile,
                'size' => $this->repository->fileSize($destinationFile),
                'mime' => $this->repository->fileMimeType($destinationFile),
                'last_modified' => $this->repository->lastModifiedDate($destinationFile),
                'complete' => true,
            ];
        }

        return [
            'complete' => false,
            'hashName' => $hashName
        ];
    }

    protected function buildChunkFilePaths($hashName, $totalPaths)
    {
        $data = [];

        for ($i = 0; $i <= $totalPaths; $i++) {
            $data[] = $hashName . '.' . $i . '.chunk';
        }

        return $data;
    }

    public function deleteFiles($files)
    {
        $this->repository->deleteFiles($files);
    }

    public function deleteDirectories($directories)
    {
        if (is_array($directories)) {
            foreach ($directories as $directory) {
                $this->repository->deleteDirectory($directory);
            }
        } else {
            $this->repository->deleteDirectory($directories);
        }
    }

    public function makeDirectory(string $destination, string $directoryName)
    {
        $dir =[];

        $directory = $destination . '/' . $directoryName;


        if($this->repository->makeDirectory($directory)){
            $dir = $this->repository->findDirByName($destination, $directoryName);
        }

        return [
            'name' => $directoryName,
            'path' => $dir['path'],
        ];
    }

}
