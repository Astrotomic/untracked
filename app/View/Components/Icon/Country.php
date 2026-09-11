<?php

namespace App\View\Components\Icon;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Spatie\Emoji\Emoji;

class Country extends Component
{
    public function __construct(
        public string $country,
    ) {}

    public function render(): View
    {
        return view('components.icon.country', [
            'flag' => Emoji::countryFlag($this->country),
        ]);
    }
}
