<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function terms()
    {
        $locale = app()->getLocale();
        $content = setting('terms_' . $locale) ?? setting('terms_ar');
        $title = __('admin.settings.terms_' . $locale);
        
        return view('pages.legal', compact('content', 'title'));
    }

    public function privacy()
    {
        $locale = app()->getLocale();
        $content = setting('privacy_' . $locale) ?? setting('privacy_ar');
        $title = __('admin.settings.privacy_' . $locale);
        
        return view('pages.legal', compact('content', 'title'));
    }
}
