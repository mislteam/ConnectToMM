<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class JoyUpdateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|in:0,1',
            'old_photos' => 'nullable|array',
            'old_photos.*' => 'string',
            'removed_photos' => 'nullable|array',
            'removed_photos.*' => 'string',
            'files' => 'nullable|array',
            'files.*' => 'nullable',
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $uploadedFiles = $this->file('files', []);

            if (!empty($uploadedFiles) && is_array($uploadedFiles)) {
                foreach ($uploadedFiles as $index => $file) {

                    if ($file === null) {
                        continue;
                    }

                    if (!is_object($file) || !method_exists($file, 'getClientOriginalName')) {
                        continue;
                    }

                    $single = Validator::make(
                        ['f' => $file],
                        ['f' => 'image|mimes:jpg,jpeg,png|max:2048'],
                        [],
                        ['f' => "files.{$index}"]
                    );

                    if ($single->fails()) {
                        foreach ($single->errors()->get('f') as $msg) {
                            $validator->errors()->add("files.{$index}", $msg);
                        }
                    }
                }
            }
        });
    }
}
