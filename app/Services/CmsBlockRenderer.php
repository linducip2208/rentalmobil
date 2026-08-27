<?php

namespace App\Services;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class CmsBlockRenderer
{
    private HtmlSanitizer $sanitizer;

    public function __construct()
    {
        $config = (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->withMaxInputLength(200_000);

        $this->sanitizer = new HtmlSanitizer($config);
    }

    public function sanitizeHtml(string $html): string
    {
        return $this->sanitizer->sanitize($html);
    }
}
