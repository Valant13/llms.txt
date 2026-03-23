<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Service;

use MageOS\LlmTxt\Config\Config;

class LlmTxtProvider
{
    public function __construct(private readonly Config $config) {}

    public function get(int $storeId): string
    {
        if ($this->config->shouldUseManualContent($storeId)) {
            return $this->config->getManualContent($storeId);
        }

        return $this->config->getGeneratedContent($storeId);
    }
}
