<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    /**
     * Get available languages
     */
    public function index()
    {
        $languages = [
            'en' => [
                'name' => 'English',
                'nativeName' => 'English',
                'flag' => '🇺🇸'
            ],
            'ms' => [
                'name' => 'Malay',
                'nativeName' => 'Bahasa Melayu',
                'flag' => '🇲🇾'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'languages' => $languages,
                'current' => App::getLocale(),
                'userPreference' => Auth::user()->language ?? 'en'
            ]
        ]);
    }

    /**
     * Switch user language preference
     */
    public function switch(Request $request)
    {
        $request->validate([
            'language' => ['required', Rule::in(['en', 'ms'])]
        ]);

        $user = Auth::user();
        $user->update(['language' => $request->language]);

        // Also set the current session locale
        App::setLocale($request->language);

        return response()->json([
            'success' => true,
            'message' => __('api.language_updated'),
            'data' => [
                'language' => $request->language,
                'languageName' => $request->language === 'ms' ? 'Bahasa Melayu' : 'English'
            ]
        ]);
    }

    /**
     * Get current language
     */
    public function current()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'current' => App::getLocale(),
                'userPreference' => Auth::user()->language ?? 'en'
            ]
        ]);
    }
}