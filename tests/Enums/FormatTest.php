<?php

namespace Tests\Enums;

use App\Enums\Format;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FormatTest extends TestCase
{
    #[Test]
    #[DataProvider('formatProvider')]
    public function it_normalizes_content_formats(string $value, Format $expected): void
    {
        Assert::assertSame($expected, Format::normalize($value));
    }

    public static function formatProvider(): array
    {
        return [
            'html' => ['text/html', Format::Html],
            'markdown' => ['text/markdown', Format::Markdown],
            'markdown shorthand' => ['MD', Format::Markdown],
            'rss' => ['application/rss+xml', Format::Rss],
            'atom' => ['application/atom+xml', Format::Atom],
            'json' => ['application/json', Format::Json],
            'xml' => ['text/xml', Format::Xml],
            'text' => ['text/plain', Format::Text],
            'trimmed case insensitive value' => ['  APPLICATION/JSON  ', Format::Json],
            'unknown' => ['application/pdf', Format::Other],
        ];
    }
}
