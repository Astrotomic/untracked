<?php

namespace App\Http\Requests;

use App\Enums\Metric;
use App\Models\Website;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WebsiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('metric_preferences')) {
            $this->merge([
                'metric_preferences' => Website::defaultMetricPreferences(),
            ]);

            return;
        }

        $metricPreferences = $this->input('metric_preferences');

        if (! is_array($metricPreferences)) {
            return;
        }

        unset($metricPreferences[Metric::Path->value]);

        $this->merge([
            'metric_preferences' => $metricPreferences,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Website|null $website */
        $website = $this->route('website');
        $metricPreferences = Website::defaultMetricPreferences();
        $metricRules = [];

        foreach (array_keys($metricPreferences) as $metric) {
            $metricRules["metric_preferences.{$metric}"] = ['required', 'boolean'];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'domain' => [
                'required',
                'string',
                'max:255',
                Rule::unique('websites', 'domain')->ignore($website),
            ],
            'timezone' => ['required', 'timezone'],
            'should_track_bots' => ['required', 'boolean'],
            'metric_preferences' => ['required', 'array:'.implode(',', array_keys($metricPreferences))],
            ...$metricRules,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function websiteAttributes(): array
    {
        /** @var array<string, mixed> $attributes */
        $attributes = $this->validated();
        $attributes['metric_preferences'] = [];

        foreach (array_keys(Website::defaultMetricPreferences()) as $metric) {
            $attributes['metric_preferences'][$metric] = $this->boolean("metric_preferences.{$metric}");
        }

        return $attributes;
    }
}
