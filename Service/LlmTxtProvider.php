<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Service;

use Magento\Store\Model\StoreManagerInterface;
use MageOS\LlmTxt\Config\Config;

class LlmTxtProvider
{
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly Config $config,
    ) {}

    public function get(int $storeId): string
    {
        if ($this->config->shouldUseManualContent($storeId)) {
            return $this->config->getManualContent($storeId);
        }

        $generatedContent = $this->config->getGeneratedContent($storeId);

        if (!empty($generatedContent)) {
            return $generatedContent;
        }

        return $this->getFallbackContent($storeId);
    }

    private function getFallbackContent(int $storeId): string
    {
        $siteName = $this->config->getSiteName($storeId);
        if (empty($siteName)) {
            $siteName = (string) $this->storeManager->getStore($storeId)->getName();
        }

        $siteDescription = $this->config->getSiteDescription($storeId);

        $content = "# {$siteName}\n\n";

        if (!empty($siteDescription)) {
            $content .= "> {$siteDescription}\n\n";
        }

        $content .= "Please configure your LLMs.txt content in the admin panel.\n";
        $content .= "Use the 'Generate with AI' button to automatically create content from your store data.";

        return $content;
    }
}
