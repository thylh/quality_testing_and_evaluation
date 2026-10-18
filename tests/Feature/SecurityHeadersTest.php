<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_storefront_responses_include_security_headers(): void
    {
        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertHeader('Content-Security-Policy')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeaderMissing('X-Powered-By');

        $contentSecurityPolicy = $response->headers->get('Content-Security-Policy');
        $this->assertIsString($contentSecurityPolicy);
        $this->assertStringNotContainsString('unsafe-inline', $contentSecurityPolicy);
        $this->assertStringNotContainsString('fonts.googleapis.com', $contentSecurityPolicy);
        $this->assertStringNotContainsString('fonts.bunny.net', $contentSecurityPolicy);
        $this->assertStringNotContainsString('cdnjs.cloudflare.com', $contentSecurityPolicy);
        $this->assertMatchesRegularExpression("/style-src [^;]*'nonce-([^']+)'/", $contentSecurityPolicy);
    }
}
