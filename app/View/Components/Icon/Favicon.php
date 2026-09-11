<?php

namespace App\View\Components\Icon;

use App\Managers\FaviconManager;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Favicon extends Component
{
    public function __construct(
        public ?string $domain,
    ) {}

    public function render(): View
    {
        return view('components.icon.favicon', [
            'url' => $this->domain === null
                ? null
                : FaviconManager::make()->driver()->url($this->domain),
        ]);
    }
}
