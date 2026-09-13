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
        if ($this->exists('metric_preferences')) {
            return;
        }

        $this->merge([
            'metric_preferences' => self::defaultMetricPreferences(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Website|null $website */
        $website = $this->route('website');
        $metricPreferences = self::defaultMetricPreferences();
        $metricRules = [];

        foreach (Metric::cases() as $metric) {
            $metricRules["metric_preferences.{$metric->value}"] = ['required', 'boolean'];
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

        foreach (Metric::cases() as $metric) {
            $attributes['metric_preferences'][$metric->value] = $this->boolean("metric_preferences.{$metric->value}");
        }

        return $attributes;
    }

    /**
     * @return array<string, bool>
     */
    private static function defaultMetricPreferences(): array
    {
        $preferences = [];

        foreach (Metric::cases() as $metric) {
            $preferences[$metric->value] = true;
        }

        return $preferences;
    }
}
