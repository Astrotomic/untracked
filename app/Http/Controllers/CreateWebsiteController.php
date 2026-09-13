<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebsiteRequest;
use App\Models\Website;
use Illuminate\Http\RedirectResponse;

class CreateWebsiteController
{
    public function __invoke(WebsiteRequest $request): RedirectResponse
    {
        $website = Website::query()->create($request->websiteAttributes());

        return redirect()->route('websites.show', $website);
    }
}
