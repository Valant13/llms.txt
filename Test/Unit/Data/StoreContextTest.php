<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Test\Unit\Data;

use MageOS\LlmTxt\Data\SectionItem;
use MageOS\LlmTxt\Data\StoreContext;
use PHPUnit\Framework\TestCase;

final class StoreContextTest extends TestCase
{
    private StoreContext $storeContext;

    protected function setUp(): void
    {
        $this->storeContext = new StoreContext();
    }

    public function test_get_store_id_returns_null_when_not_set(): void
    {
        $this->assertNull($this->storeContext->getStoreId());
    }

    public function test_set_store_id_stores_value_and_get_store_id_returns_it(): void
    {
        $this->storeContext->setStoreId(1);

        $this->assertSame(1, $this->storeContext->getStoreId());
    }

    public function test_set_store_id_with_null_stores_null(): void
    {
        $this->storeContext->setStoreId(1);
        $this->storeContext->setStoreId(null);

        $this->assertNull($this->storeContext->getStoreId());
    }

    public function test_get_name_returns_null_when_not_set(): void
    {
        $this->assertNull($this->storeContext->getName());
    }

    public function test_set_name_stores_value_and_get_name_returns_it(): void
    {
        $this->storeContext->setName('My Store');

        $this->assertSame('My Store', $this->storeContext->getName());
    }

    public function test_get_description_returns_null_when_not_set(): void
    {
        $this->assertNull($this->storeContext->getDescription());
    }

    public function test_set_description_stores_value(): void
    {
        $this->storeContext->setDescription('An online store for quality products');

        $this->assertSame('An online store for quality products', $this->storeContext->getDescription());
    }

    public function test_get_url_returns_null_when_not_set(): void
    {
        $this->assertNull($this->storeContext->getUrl());
    }

    public function test_set_url_stores_value(): void
    {
        $this->storeContext->setUrl('https://example.com/');

        $this->assertSame('https://example.com/', $this->storeContext->getUrl());
    }

    public function test_get_locale_returns_null_when_not_set(): void
    {
        $this->assertNull($this->storeContext->getLocale());
    }

    public function test_set_locale_stores_value(): void
    {
        $this->storeContext->setLocale('en_US');

        $this->assertSame('en_US', $this->storeContext->getLocale());
    }

    public function test_get_categories_returns_null_when_not_set(): void
    {
        $this->assertNull($this->storeContext->getCategories());
    }

    public function test_set_categories_stores_array_of_section_items(): void
    {
        $item1 = new SectionItem(['name' => 'Men', 'url' => 'https://example.com/men']);
        $item2 = new SectionItem(['name' => 'Women', 'url' => 'https://example.com/women']);

        $this->storeContext->setCategories([$item1, $item2]);

        $this->assertSame([$item1, $item2], $this->storeContext->getCategories());
    }

    public function test_set_categories_with_null_stores_null(): void
    {
        $this->storeContext->setCategories([new SectionItem()]);
        $this->storeContext->setCategories(null);

        $this->assertNull($this->storeContext->getCategories());
    }

    public function test_get_products_returns_null_when_not_set(): void
    {
        $this->assertNull($this->storeContext->getProducts());
    }

    public function test_set_products_stores_array_of_section_items(): void
    {
        $product = new SectionItem(['name' => 'Widget', 'url' => 'https://example.com/widget']);

        $this->storeContext->setProducts([$product]);

        $this->assertSame([$product], $this->storeContext->getProducts());
    }

    public function test_get_cms_pages_returns_null_when_not_set(): void
    {
        $this->assertNull($this->storeContext->getCmsPages());
    }

    public function test_set_cms_pages_stores_array_of_section_items(): void
    {
        $page = new SectionItem(['name' => 'About Us', 'url' => 'https://example.com/about']);

        $this->storeContext->setCmsPages([$page]);

        $this->assertSame([$page], $this->storeContext->getCmsPages());
    }

    public function test_set_store_id_returns_self_for_chaining(): void
    {
        $result = $this->storeContext->setStoreId(1);

        $this->assertSame($this->storeContext, $result);
    }

    public function test_constants_have_expected_values(): void
    {
        $this->assertSame('store_id', StoreContext::KEY_STORE_ID);
        $this->assertSame('name', StoreContext::KEY_NAME);
        $this->assertSame('description', StoreContext::KEY_DESCRIPTION);
        $this->assertSame('url', StoreContext::KEY_URL);
        $this->assertSame('locale', StoreContext::KEY_LOCALE);
        $this->assertSame('categories', StoreContext::KEY_CATEGORIES);
        $this->assertSame('products', StoreContext::KEY_PRODUCTS);
        $this->assertSame('cms_pages', StoreContext::KEY_CMS_PAGES);
    }

    public function test_method_chaining_stores_all_values(): void
    {
        $this->storeContext
            ->setStoreId(2)
            ->setName('Store Two')
            ->setDescription('Second store')
            ->setUrl('https://store2.example.com/')
            ->setLocale('fr_FR')
            ->setCategories([])
            ->setProducts([])
            ->setCmsPages([]);

        $this->assertSame(2, $this->storeContext->getStoreId());
        $this->assertSame('Store Two', $this->storeContext->getName());
        $this->assertSame('Second store', $this->storeContext->getDescription());
        $this->assertSame('https://store2.example.com/', $this->storeContext->getUrl());
        $this->assertSame('fr_FR', $this->storeContext->getLocale());
        $this->assertSame([], $this->storeContext->getCategories());
        $this->assertSame([], $this->storeContext->getProducts());
        $this->assertSame([], $this->storeContext->getCmsPages());
    }
}
