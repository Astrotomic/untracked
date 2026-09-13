<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebsiteRequest;
use App\Models\Website;
use Illuminate\Http\RedirectResponse;

class UpdateWebsiteController
{
    public function __invoke(WebsiteRequest $request, Website $website): RedirectResponse
    {
        $website->update($request->websiteAttributes());

        return redirect()->route('websites.show', $website);
    }
}
