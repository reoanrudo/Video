<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveAnalysisRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'analysis' => ['required', 'array'],
            'analysis.schema' => ['present', 'array:name,version'],
            'analysis.schema.name' => ['required', 'string', 'in:videocoach.analysis'],
            'analysis.schema.version' => ['required', 'string', 'in:1.0.0'],
            'analysis.drawings' => ['present', 'array'],
            'analysis.drawings.*.id' => ['nullable', 'string'],
            'analysis.drawings.*.type' => ['required', 'string'],
            'analysis.drawings.*.geometry' => ['required', 'array'],
            'analysis.drawings.*.style' => ['nullable', 'array'],
            'analysis.drawings.*.variant' => ['nullable', 'string'],
            'analysis.keyframes' => ['present', 'array'],
            'analysis.keyframes.*.time' => ['required', 'numeric', 'min:0'],
            'analysis.keyframes.*.label' => ['nullable', 'string', 'max:255'],
            'analysis.extensions' => ['present', 'array'],
            'analysis.origin' => ['sometimes', 'array:x,y'],
            'analysis.meta' => ['nullable', 'array'],
        ];
    }
}
