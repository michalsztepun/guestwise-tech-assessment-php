<?php

declare(strict_types=1);

namespace App\Http\Requests\Campaigns;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'brand' => [
                'numeric',
                Rule::exists('brands', 'id'),
            ],
            'start_date' => [
                'date',
            ],
            'end_date' => [
                'date',
            ],
            'page' => [
                'numeric',
                'min:1'
            ],
        ];
    }
}
