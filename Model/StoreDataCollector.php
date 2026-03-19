<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Model;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class StoreDataCollector
{
    public function __construct(
        private readonly StoreManagerInterface $storeManager,
        private readonly CategoryCollectionFactory $categoryCollectionFactory,
        private readonly ProductCollectionFactory $productCollectionFactory,
        private readonly PageCollectionFactory $pageCollectionFactory,
        private readonly Config $config,
    ) {}

    public function collect(int $storeId): array
    {
        $baseUrl = $this->getBaseUrl($storeId);

        return [
            'store_name' => $this->storeManager->getStore($storeId)->getName(),
            'store_url' => $baseUrl,
            'categories' => $this->collectCategories($storeId, $baseUrl),
            'products' => $this->collectProducts($storeId, $baseUrl),
            'cms_pages' => $this->collectCmsPages($storeId, $baseUrl),
        ];
    }

    private function collectCategories(int $storeId, string $baseUrl): array
    {
        $categoryIds = $this->config->getCategoryIds($storeId);
        if (!$categoryIds) {
            return [];
        }

        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'url_key', 'description'])
            ->addAttributeToFilter('is_active', 1)
            ->addAttributeToFilter('entity_id', ['in' => $categoryIds])
            ->setStoreId($storeId)
            ->setOrder('position', 'ASC');

        $categories = [];
        foreach ($collection as $category) {
            $categories[] = [
                'name' => (string) $category->getName(),
                'url' => $baseUrl . $category->getUrlKey() . '.html',
                'description' => strip_tags((string) $category->getDescription()),
            ];
        }

        return $categories;
    }

    private function collectProducts(int $storeId, string $baseUrl): array
    {
        $productSkus = $this->config->getProductSkus($storeId);
        if (!$productSkus) {
            return [];
        }

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'url_key', 'short_description', 'sku'])
            ->addAttributeToFilter('sku', ['in' => $productSkus])
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)
            ->setOrder('created_at', 'DESC');

        $products = [];
        foreach ($collection as $product) {
            $products[] = [
                'name' => (string) $product->getName(),
                'url' => $baseUrl . $product->getUrlKey() . '.html',
                'description' => strip_tags((string) $product->getShortDescription()),
            ];
        }

        return $products;
    }

    private function collectCmsPages(int $storeId, string $baseUrl): array
    {
        $pageIdentifiers = $this->config->getCmsPageIdentifiers($storeId);
        if (!$pageIdentifiers) {
            return [];
        }

        $collection = $this->pageCollectionFactory->create();
        $collection->addFieldToSelect(['identifier', 'title'])
            ->addFieldToFilter('identifier', ['in' => $pageIdentifiers])
            ->addStoreFilter($storeId)
            ->setOrder('created_at', 'DESC');

        $pages = [];
        foreach ($collection as $page) {
            $identifier = (string) $page->getIdentifier();

            $cmsPages[] = [
                'title' => (string) $page->getTitle(),
                'url' => $baseUrl . $identifier,
                'identifier' => $identifier,
            ];
        }

        return $cmsPages;
    }

    private function getBaseUrl(int $storeId): string
    {
        try {
            return (string) $this->storeManager->getStore($storeId)->getBaseUrl();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }
}
