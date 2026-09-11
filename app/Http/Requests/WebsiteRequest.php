<?php

namespace App\Http\Requests;

use App\Models\Website;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WebsiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Website|null $website */
        $website = $this->route('website');

        return [
            'name' => ['required', 'string', 'max:255'],
            'domain' => [
                'required',
                'string',
                'max:255',
                Rule::unique('websites', 'domain')->ignore($website?->getKey()),
            ],
            'timezone' => ['required', 'timezone'],
            'should_track_bots' => ['required', 'boolean'],
        ];
    }
}
