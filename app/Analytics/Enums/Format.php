<?php

namespace App\Analytics\Enums;

enum Format: string
{
    case Html = 'html';
    case Markdown = 'markdown';
    case Rss = 'rss';
    case Atom = 'atom';
    case Json = 'json';
    case Xml = 'xml';
    case Text = 'text';
    case Other = 'other';

    public static function normalize(string $value): self
    {
        return match (strtolower(trim($value))) {
            'html', 'text/html' => self::Html,
            'markdown', 'md', 'text/markdown' => self::Markdown,
            'rss', 'application/rss+xml' => self::Rss,
            'atom', 'application/atom+xml' => self::Atom,
            'json', 'application/json' => self::Json,
            'xml', 'application/xml', 'text/xml' => self::Xml,
            'text', 'text/plain' => self::Text,
            default => self::Other,
        };
    }
}
