<?php

namespace App\Http\Requests;

class ChunkUploadRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'dir' => 'required',
            'data' => 'required',
            'name' => 'required',
            'type' => 'required',
            'offset' => 'required',
            'eof' => 'required'
        ];
    }

    /**
     * Prepare parameters from Form Request.
     *
     * @return array
     */
    public function parameters()
    {
        return [
            'dir' => $this->input('dir'),
            'hash' => $this->input('hash', ''),
            'data' => $this->input('data'),
            'name' => $this->input('name'),
            'type' => $this->input('type'),
            'offset' => $this->input('offset'),
            'eof' => $this->input('eof'),
        ];
    }
}
