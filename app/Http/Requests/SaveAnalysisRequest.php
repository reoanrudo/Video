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
            'analysis.keyframes' => ['present', 'array'],
            'analysis.extensions' => ['present', 'array'],
        ];
    }
}
