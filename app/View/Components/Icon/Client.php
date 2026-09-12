<?php

namespace App\View\Components\Icon;

use App\Values\Client as ClientValue;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Client extends Component
{
    public function __construct(
        public string $client,
    ) {}

    public function render(): View
    {
        return view('components.icon.client', [
            'domain' => (new ClientValue($this->client))->domain(),
        ]);
    }
}
