<?php

namespace Tests\Unit;

use App\Support\Cms\SafeHtml;
use App\Support\Cms\SafeUrl;
use PHPUnit\Framework\TestCase;

class CmsContentSafetyTest extends TestCase
{
    public function test_html_sanitizer_removes_scripts_events_and_unsafe_urls(): void
    {
        $html = (new SafeHtml)->clean('<p onclick="alert(1)">Safe <a href="javascript:alert(1)">link</a></p><script>alert(1)</script>');

        $this->assertStringNotContainsStringIgnoringCase('script', $html);
        $this->assertStringNotContainsStringIgnoringCase('onclick', $html);
        $this->assertStringNotContainsStringIgnoringCase('javascript:', $html);
        $this->assertStringContainsString('<p>Safe', $html);
    }

    public function test_url_policy_only_allows_supported_schemes(): void
    {
        $this->assertSame('#', SafeUrl::clean('data:text/html,test'));
        $this->assertSame('#', SafeUrl::clean('javascript:alert(1)'));
        $this->assertSame('/about', SafeUrl::clean('/about'));
        $this->assertSame('https://example.com', SafeUrl::clean('https://example.com'));
    }
}
