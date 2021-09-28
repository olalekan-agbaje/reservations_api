<?php

namespace App\Models\Validators;

use App\Models\Office;
use Illuminate\Validation\Rule;

class OfficeValidator
{
    public function validate(Office $office, array $attributes): array
    {
        return validator(
            $attributes,
            [
                'lat' => [Rule::when($office->exists, 'sometimes'), 'required', 'numeric'],
                'lng' => [Rule::when($office->exists, 'sometimes'), 'required', 'numeric'],
                'title' => [Rule::when($office->exists, 'sometimes'), 'required', 'string'],
                'description' => [Rule::when($office->exists, 'sometimes'), 'required', 'string'],
                'address_line1' => [Rule::when($office->exists, 'sometimes'), 'required', 'string'],
                'price_per_day' => [Rule::when($office->exists, 'sometimes'), 'required', 'integer', 'min:100'],

                'hidden' => ['bool'],
                'monthly_discount' => ['integer', 'min:0'],

                'tags' => ['array'],
                'tags.*' => ['integer', Rule::exists('tags', 'id')],
            ]
        )->validate();
    }
}
