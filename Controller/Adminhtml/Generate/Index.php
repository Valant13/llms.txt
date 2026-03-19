<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Controller\Adminhtml\Generate;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use MageOS\LlmTxt\Model\LlmsTxtGenerator;

class Index implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'MageOS_LlmTxt::config';

    public function __construct(
        private readonly RequestInterface $request,
        private readonly JsonFactory $resultJsonFactory,
        private readonly StoreManagerInterface $storeManager,
        private readonly LlmsTxtGenerator $llmsTxtGenerator,
    ) {}

    public function execute(): Json
    {
        $result = $this->resultJsonFactory->create();

        try {
            $storeId = (int) $this->request->getParam('store', 0);
            if ($storeId === 0) {
                $storeId = (int) $this->storeManager->getDefaultStoreView()->getId();
            }

            $generatedContent = $this->llmsTxtGenerator->generateLlmsTxt($storeId);
            $tokenCount = $this->llmsTxtGenerator->estimateTokenCount($generatedContent);

            return $result->setData([
                'success' => true,
                'content' => $generatedContent,
                'tokens' => $tokenCount,
                'message' => __('Content generated successfully! Token count: %1', $tokenCount)
            ]);

        } catch (\Exception $e) {
            return $result->setData([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => __('Generation failed: %1', $e->getMessage())
            ]);
        }
    }
}
