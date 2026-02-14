<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);
        
        if (in_array($locale, ['en', 'ms'])) {
            App::setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Determine the appropriate locale from multiple sources
     */
    private function determineLocale(Request $request): string
    {
        // 1. Check if user is authenticated and has language preference
        if (Auth::check() && Auth::user()->language) {
            return Auth::user()->language;
        }

        // 2. Check for explicit locale parameter in request
        if ($request->has('locale') && in_array($request->locale, ['en', 'ms'])) {
            return $request->locale;
        }

        // 3. Check Accept-Language header
        $acceptLanguage = $request->header('Accept-Language');
        if ($acceptLanguage) {
            // Parse Accept-Language header and find best match
            $languages = $this->parseAcceptLanguage($acceptLanguage);
            foreach ($languages as $lang) {
                if (in_array($lang, ['en', 'ms'])) {
                    return $lang;
                }
                // Handle language variants (e.g., ms-MY -> ms)
                $primaryLang = substr($lang, 0, 2);
                if (in_array($primaryLang, ['en', 'ms'])) {
                    return $primaryLang;
                }
            }
        }

        // 4. Default to English
        return 'en';
    }

    /**
     * Parse Accept-Language header
     */
    private function parseAcceptLanguage(string $acceptLanguage): array
    {
        $languages = [];
        $parts = explode(',', $acceptLanguage);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (strpos($part, ';') !== false) {
                [$lang] = explode(';', $part);
                $languages[] = trim($lang);
            } else {
                $languages[] = $part;
            }
        }
        
        return $languages;
    }
}