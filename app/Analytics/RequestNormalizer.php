<?php

namespace App\Analytics;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RequestNormalizer
{
    /**
     * @return array{path: string, country: string, browser: string, os: string, device: string, format: string, is_bot: bool}
     */
    public function normalize(Request $request): array
    {
        $path = $this->path((string) $request->input('path', '/'));
        $hasNormalizedClient = $request->filled('browser') || $request->filled('os') || $request->filled('device');
        $userAgent = (string) $request->userAgent();

        $isBot = $request->boolean('bot')
            || Str::lower((string) $request->input('device')) === 'bot'
            || (! $hasNormalizedClient && $this->isBot($userAgent));

        if ($isBot) {
            $browser = 'Bot';
            $os = 'Bot';
            $device = 'bot';
        } elseif ($hasNormalizedClient) {
            $browser = $this->browser((string) $request->input('browser'));
            $os = $this->operatingSystem((string) $request->input('os'));
            $device = $this->device((string) $request->input('device'));
        } else {
            $browser = $this->detectBrowser($userAgent);
            $os = $this->detectOperatingSystem($userAgent);
            $device = $this->detectDevice($userAgent);
        }

        return [
            'path' => $path,
            'country' => $this->country($request),
            'browser' => $browser,
            'os' => $os,
            'device' => $device,
            'format' => $this->format((string) $request->input('format', 'html')),
            'is_bot' => $isBot,
        ];
    }

    private function path(string $value): string
    {
        $path = parse_url($value, PHP_URL_PATH);
        $path = is_string($path) && $path !== '' ? $path : '/';
        $path = '/'.ltrim($path, '/');

        return Str::limit($path, 500, '');
    }

    private function country(Request $request): string
    {
        $country = Str::upper((string) ($request->input('country')
            ?: $request->header('CF-IPCountry')
            ?: $request->header('X-Vercel-IP-Country')
            ?: $request->header('CloudFront-Viewer-Country')));

        return preg_match('/^[A-Z]{2}$/', $country) === 1 ? $country : 'XX';
    }

    private function browser(string $value): string
    {
        return match (Str::lower(trim($value))) {
            'chrome', 'chromium' => 'Chrome',
            'firefox' => 'Firefox',
            'safari' => 'Safari',
            'edge', 'microsoft edge' => 'Edge',
            'opera' => 'Opera',
            'samsung internet' => 'Samsung Internet',
            'bot' => 'Bot',
            'unknown', '' => 'Unknown',
            default => 'Other',
        };
    }

    private function operatingSystem(string $value): string
    {
        return match (Str::lower(trim($value))) {
            'windows' => 'Windows',
            'macos', 'mac os', 'mac os x', 'os x' => 'macOS',
            'linux' => 'Linux',
            'android' => 'Android',
            'ios', 'iphone os', 'ipad os' => 'iOS',
            'chromeos', 'chrome os' => 'ChromeOS',
            'bot' => 'Bot',
            'unknown', '' => 'Unknown',
            default => 'Other',
        };
    }

    private function device(string $value): string
    {
        return match (Str::lower(trim($value))) {
            'desktop' => 'desktop',
            'mobile', 'phone' => 'mobile',
            'tablet' => 'tablet',
            'bot' => 'bot',
            'unknown', '' => 'unknown',
            default => 'other',
        };
    }

    private function format(string $value): string
    {
        return match (Str::lower(trim($value))) {
            'html', 'text/html' => 'html',
            'markdown', 'md', 'text/markdown' => 'markdown',
            'rss', 'application/rss+xml' => 'rss',
            'atom', 'application/atom+xml' => 'atom',
            'json', 'application/json' => 'json',
            'xml', 'application/xml', 'text/xml' => 'xml',
            'text', 'text/plain' => 'text',
            default => 'other',
        };
    }

    private function isBot(string $userAgent): bool
    {
        return preg_match('/bot|crawler|spider|slurp|bingpreview|facebookexternalhit|pinterest|telegrambot|discordbot|slackbot/i', $userAgent) === 1;
    }

    private function detectBrowser(string $userAgent): string
    {
        return match (true) {
            $userAgent === '' => 'Unknown',
            preg_match('/Edg\//i', $userAgent) === 1 => 'Edge',
            preg_match('/OPR\//i', $userAgent) === 1 => 'Opera',
            preg_match('/SamsungBrowser\//i', $userAgent) === 1 => 'Samsung Internet',
            preg_match('/Chrome\//i', $userAgent) === 1 => 'Chrome',
            preg_match('/Firefox\//i', $userAgent) === 1 => 'Firefox',
            preg_match('/Safari\//i', $userAgent) === 1 => 'Safari',
            default => 'Other',
        };
    }

    private function detectOperatingSystem(string $userAgent): string
    {
        return match (true) {
            $userAgent === '' => 'Unknown',
            preg_match('/Android/i', $userAgent) === 1 => 'Android',
            preg_match('/iPhone|iPad|iPod/i', $userAgent) === 1 => 'iOS',
            preg_match('/Windows/i', $userAgent) === 1 => 'Windows',
            preg_match('/CrOS/i', $userAgent) === 1 => 'ChromeOS',
            preg_match('/Macintosh|Mac OS X/i', $userAgent) === 1 => 'macOS',
            preg_match('/Linux/i', $userAgent) === 1 => 'Linux',
            default => 'Other',
        };
    }

    private function detectDevice(string $userAgent): string
    {
        return match (true) {
            $userAgent === '' => 'unknown',
            preg_match('/iPad|Tablet/i', $userAgent) === 1 => 'tablet',
            preg_match('/Mobile|iPhone|iPod/i', $userAgent) === 1 => 'mobile',
            preg_match('/Android/i', $userAgent) === 1 && preg_match('/Mobile/i', $userAgent) !== 1 => 'tablet',
            default => 'desktop',
        };
    }
}
