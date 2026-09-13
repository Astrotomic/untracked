<?php

namespace App\Http\Controllers;

use App\Models\Website;
use Illuminate\View\View;

class ShowWebsiteEditFormController
{
    public function __invoke(Website $website): View
    {
        return view('websites.edit', compact('website'));
    }
}
