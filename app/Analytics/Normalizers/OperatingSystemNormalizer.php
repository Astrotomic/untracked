<?php

namespace App\Analytics\Normalizers;

use Illuminate\Support\Str;

class OperatingSystemNormalizer
{
    public function normalize(string $family): string
    {
        $family = trim($family);

        if ($family === '' || strcasecmp($family, 'Other') === 0) {
            return 'Other';
        }

        if (strcasecmp($family, 'Bot') === 0) {
            return 'Bot';
        }

        $family = preg_replace('/(?:\s+|\/)v?\d+(?:[._-]\d+)*$/i', '', $family) ?? $family;
        $family = trim($family);
        $normalized = strtolower($family);

        return match (true) {
            str_starts_with($normalized, 'windows') => 'Windows',
            in_array($normalized, ['mac os x', 'mac os', 'macos', 'os x'], true) => 'macOS',
            str_starts_with($normalized, 'android') => 'Android',
            $normalized === 'ios' => 'iOS',
            in_array($normalized, ['chrome os', 'chromeos'], true) => 'ChromeOS',
            $this->isLinux($normalized) => 'Linux',
            str_starts_with($normalized, 'blackberry') => 'BlackBerry OS',
            in_array($normalized, ['bsd', 'freebsd', 'openbsd', 'netbsd'], true) => 'BSD',
            in_array($normalized, ['sunos', 'solaris'], true) => 'Solaris',
            default => Str::limit($family, 100, ''),
        };
    }

    private function isLinux(string $family): bool
    {
        return preg_match(
            '/(?:linux|ubuntu|debian|fedora|centos|red hat|rhel|suse|gentoo|arch|manjaro|mint|mandriva|mageia|slackware|kubuntu|xubuntu|lubuntu|elementary|pop!_os|alpine|steamos|steam os)/i',
            $family,
        ) === 1;
    }
}
