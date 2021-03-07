<?php
namespace App\Http\Repositories;

use Illuminate\Support\Facades\Storage;
use App\Http\Repositories\Interfaces\FileRepositoryInterface;

class LocalRepository implements FileRepositoryInterface
{
    const DISK = 'google';

    public function listDirectories(string $path)
    {
        $contents = Storage::disk(self::DISK)->listContents($path, false);
        $dirs = [];
        foreach ($contents as  $dir){
            if ($dir['type'] === 'dir'){
                array_push($dirs, $dir);
            }
        }
        return $dirs;
    }

    public function findDirByName(string $destination, string $name){
        $dirs = collect(Storage::disk(self::DISK)->listContents($destination,false));
        return $dirs
            ->where('type', '=', 'dir')
            ->where('name', '=', pathinfo($name, PATHINFO_FILENAME))
            ->first();
    }

    public function listFiles(string $path)
    {
        $contents = Storage::disk(self::DISK)->listContents($path, false);
        $files = [];
        foreach ($contents as  $file){
            if ($file['type'] === 'file'){
                array_push($files, $file);
            }
        }
        return $files;
    }

    public function fileUrl(string $path)
    {
        return  Storage::disk(self::DISK)->url($path);
    }

    public function fileMimeType(string $path)
    {
        return Storage::disk(self::DISK)->mimeType($path);
    }


    public function fileSize(string $path)
    {
        return Storage::disk(self::DISK)->size($path);
    }

    public function lastModifiedDate(string $path)
    {
        return Storage::disk(self::DISK)->lastModified($path);
    }

    public function upload(string $destination, $fileData)
    {
        return Storage::disk(self::DISK)->put($destination, $fileData);
    }

    public function deleteFiles(array $filePaths)
    {
        return Storage::disk(self::DISK)->delete($filePaths);
    }

    public function deleteDirectory(string $directoryPath)
    {
        return Storage::disk(self::DISK)->deleteDirectory($directoryPath);
    }

    public function makeDirectory(string $directory)
    {
        return Storage::disk(self::DISK)->makeDirectory($directory);
    }

    public function getRealPath($path) {
        return Storage::path($path);
    }

    public function mergeChunkFiles(string $destination, Array $chunkFiles, string $fileName)
    {
        $chunkData = '';
        if (!Storage::disk(self::DISK)->exists(Storage::path($fileName))) {
            for($i = 0; $i < count($chunkFiles); $i++) {

                $contents = collect(Storage::disk(self::DISK)->listContents($destination,false));

                $file = $contents
                    ->where('type', '=', 'file')
                    ->where('filename', '=', pathinfo($chunkFiles[$i], PATHINFO_FILENAME))
                    ->where('extension', '=', pathinfo($chunkFiles[$i], PATHINFO_EXTENSION))
                    ->first();
                $chunkData .= base64_decode(Storage::disk(self::DISK)->get($file['path']));

                Storage::disk(self::DISK)->delete($file['path']);
            }
            $this->upload($destination.'/'.$fileName, $chunkData);
        }

    }
}
