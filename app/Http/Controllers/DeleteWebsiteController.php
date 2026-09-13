<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\Http\RedirectResponse;

class DeleteWebsiteController
{
    public function __invoke(Website $website): RedirectResponse
    {
        $website->delete();

        return redirect()->route('websites.index');
    }
}
