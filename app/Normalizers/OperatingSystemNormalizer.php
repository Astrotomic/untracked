<?php

namespace App\Normalizers;

use App\Concerns\Resolvable;
use App\Contracts\Normalizer;
use Illuminate\Support\Str;

final readonly class OperatingSystemNormalizer implements Normalizer
{
    use Resolvable;

    public function normalize(?string $value): string
    {
        $value = trim((string) $value);

        if (empty($value) || strcasecmp($value, 'other') === 0) {
            return 'Other';
        }

        if (strcasecmp($value, 'bot') === 0) {
            return 'Bot';
        }

        $value = preg_replace('/(?:\s+|\/)v?\d+(?:[._-]\d+)*$/i', '', $value) ?? $value;
        $value = trim($value);

        return match (true) {
            Str::contains($value, 'windows', true) => 'Windows',
            Str::contains($value, ['mac os x', 'mac os', 'macos', 'os x'], true) => 'macOS',
            Str::contains($value, 'android', true) => 'Android',
            Str::contains($value, 'kaios', true) => 'KaiOS',
            Str::contains($value, 'ios', true) => 'iOS',
            Str::contains($value, ['chrome os', 'chromeos'], true) => 'ChromeOS',
            Str::contains($value, ['linux', 'ubuntu', 'debian', 'fedora', 'centos', 'red hat', 'rhel', 'suse', 'gentoo', 'arch', 'manjaro', 'mint', 'mandriva', 'mageia', 'slackware', 'kubuntu', 'xubuntu', 'lubuntu', 'elementary', 'pop!_os', 'alpine', 'steamos', 'steam os'], true) => 'Linux',
            Str::contains($value, 'blackberry', true) => 'BlackBerry OS',
            Str::contains($value, ['bsd', 'freebsd', 'openbsd', 'netbsd'], true) => 'BSD',
            Str::contains($value, ['sunos', 'solaris'], true) => 'Solaris',
            default => ValueNormalizer::make()->normalize($value),
        };
    }
}
