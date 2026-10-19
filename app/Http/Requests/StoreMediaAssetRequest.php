<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMediaAssetRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'source_id' => ['nullable', 'string', 'max:191', 'unique:media_assets,source_id'],
        ];

        if ($this->hasFile('file')) {
            $rules['file'] = ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'];

            return $rules;
        }

        return $rules + [
            'disk' => ['required', 'string', Rule::in(array_keys(config('filesystems.disks', [])))],
            'path' => [
                'required',
                'string',
                'max:255',
                'not_regex:~(^|/)\.\.(/|$)~',
                Rule::unique('media_assets', 'path')->where('disk', $this->input('disk')),
            ],
        ];
    }
}
