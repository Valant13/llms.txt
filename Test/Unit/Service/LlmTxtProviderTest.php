<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Test\Unit\Service;

use MageOS\LlmTxt\Config\Config;
use MageOS\LlmTxt\Service\LlmTxtProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LlmTxtProviderTest extends TestCase
{
    private Config&MockObject $config;
    private LlmTxtProvider $provider;

    protected function setUp(): void
    {
        $this->config = $this->createMock(Config::class);
        $this->provider = new LlmTxtProvider($this->config);
    }

    public function test_get_returns_manual_content_when_use_manual_is_enabled(): void
    {
        $this->config
            ->method('shouldUseManualContent')
            ->with(1)
            ->willReturn(true);

        $this->config
            ->method('getManualContent')
            ->with(1)
            ->willReturn('# Manual Content\n> Manually written');

        $result = $this->provider->get(1);

        $this->assertSame('# Manual Content\n> Manually written', $result);
    }

    public function test_get_returns_generated_content_when_use_manual_is_disabled(): void
    {
        $this->config
            ->method('shouldUseManualContent')
            ->with(1)
            ->willReturn(false);

        $this->config
            ->method('getGeneratedContent')
            ->with(1)
            ->willReturn('# Generated Content\n> AI written');

        $result = $this->provider->get(1);

        $this->assertSame('# Generated Content\n> AI written', $result);
    }

    public function test_get_does_not_call_get_generated_content_when_manual_is_enabled(): void
    {
        $this->config
            ->method('shouldUseManualContent')
            ->willReturn(true);

        $this->config
            ->method('getManualContent')
            ->willReturn('Manual');

        $this->config
            ->expects($this->never())
            ->method('getGeneratedContent');

        $this->provider->get(1);
    }

    public function test_get_does_not_call_get_manual_content_when_manual_is_disabled(): void
    {
        $this->config
            ->method('shouldUseManualContent')
            ->willReturn(false);

        $this->config
            ->method('getGeneratedContent')
            ->willReturn('Generated');

        $this->config
            ->expects($this->never())
            ->method('getManualContent');

        $this->provider->get(1);
    }

    public function test_get_returns_empty_string_when_generated_content_is_empty(): void
    {
        $this->config
            ->method('shouldUseManualContent')
            ->willReturn(false);

        $this->config
            ->method('getGeneratedContent')
            ->willReturn('');

        $result = $this->provider->get(1);

        $this->assertSame('', $result);
    }

    public function test_get_passes_store_id_to_config_methods(): void
    {
        $this->config
            ->method('shouldUseManualContent')
            ->with(42)
            ->willReturn(false);

        $this->config
            ->method('getGeneratedContent')
            ->with(42)
            ->willReturn('content');

        $result = $this->provider->get(42);

        $this->assertSame('content', $result);
    }
}
