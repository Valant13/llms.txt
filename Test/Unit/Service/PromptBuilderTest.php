<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Test\Unit\Service;

use MageOS\LlmTxt\Data\SectionItem;
use MageOS\LlmTxt\Data\StoreContext;
use MageOS\LlmTxt\Service\PromptBuilder;
use PHPUnit\Framework\TestCase;

final class PromptBuilderTest extends TestCase
{
    private PromptBuilder $promptBuilder;

    protected function setUp(): void
    {
        $this->promptBuilder = new PromptBuilder();
    }

    private function buildStoreContext(array $data = []): StoreContext
    {
        $context = new StoreContext();
        $context->setStoreId($data['store_id'] ?? 1);
        $context->setName($data['name'] ?? 'Test Store');
        $context->setDescription($data['description'] ?? null);
        $context->setUrl($data['url'] ?? 'https://example.com/');
        $context->setLocale($data['locale'] ?? 'en_US');
        $context->setCategories($data['categories'] ?? []);
        $context->setProducts($data['products'] ?? []);
        $context->setCmsPages($data['cms_pages'] ?? []);

        return $context;
    }

    private function makeSectionItem(string $name, string $url, string $description): SectionItem
    {
        $item = new SectionItem();
        $item->setName($name);
        $item->setUrl($url);
        $item->setDescription($description);

        return $item;
    }

    public function test_build_prompt_returns_non_empty_string(): void
    {
        $storeContext = $this->buildStoreContext();

        $result = $this->promptBuilder->buildPrompt($storeContext);

        $this->assertNotEmpty($result);
    }

    public function test_build_prompt_includes_store_name(): void
    {
        $storeContext = $this->buildStoreContext(['name' => 'Acme Shop']);

        $result = $this->promptBuilder->buildPrompt($storeContext);

        $this->assertStringContainsString('Acme Shop', $result);
    }

    public function test_build_prompt_includes_store_url(): void
    {
        $storeContext = $this->buildStoreContext(['url' => 'https://acme.example.com/']);

        $result = $this->promptBuilder->buildPrompt($storeContext);

        $this->assertStringContainsString('https://acme.example.com/', $result);
    }

    public function test_build_prompt_includes_store_locale(): void
    {
        $storeContext = $this->buildStoreContext(['locale' => 'de_DE']);

        $result = $this->promptBuilder->buildPrompt($storeContext);

        $this->assertStringContainsString('de_DE', $result);
    }

    public function test_build_prompt_includes_description_when_provided(): void
    {
        $storeContext = $this->buildStoreContext([
            'description' => 'The best online shop for quality goods',
        ]);

        $result = $this->promptBuilder->buildPrompt($storeContext);

        $this->assertStringContainsString('The best online shop for quality goods', $result);
    }

    public function test_build_prompt_omits_description_line_when_null(): void
    {
        $storeContext = $this->buildStoreContext(['description' => null]);

        $result = $this->promptBuilder->buildPrompt($storeContext);

        $this->assertStringNotContainsString('Store Description:', $result);
    }

    public function test_build_prompt_includes_category_section_when_categories_provided(): void
    {
        $category = $this->makeSectionItem('Electronics', 'https://example.com/electronics', 'All electronics');
        $storeContext = $this->buildStoreContext(['categories' => [$category]]);

        $result = $this->promptBuilder->buildPrompt($storeContext);

        $this->assertStringContainsString('Top Categories', $result);
        $this->assertStringContainsString('Electronics', $result);
        $this->assertStringContainsString('https://example.com/electronics', $result);
    }

    public function test_build_prompt_omits_category_section_when_no_categories(): void
    {
        $storeContext = $this->buildStoreContext(['categories' => []]);

        $result = $this->promptBuilder->buildPrompt($storeContext);

        $this->assertStringNotContainsString('Top Categories', $result);
    }

    public function test_build_prompt_includes_product_section_when_products_provided(): void
    {
        $product = $this->makeSectionItem('Widget Pro', 'https://example.com/widget-pro', 'Best widget');
        $storeContext = $this->buildStoreContext(['products' => [$product]]);

        $result = $this->promptBuilder->buildPrompt($storeContext);

        $this->assertStringContainsString('Sample Products', $result);
        $this->assertStringContainsString('Widget Pro', $result);
    }

    public function test_build_prompt_omits_product_section_when_no_products(): void
    {
        $storeContext = $this->buildStoreContext(['products' => []]);

        $result = $this->promptBuilder->buildPrompt($storeContext);

        $this->assertStringNotContainsString('Sample Products', $result);
    }

    public function test_build_prompt_includes_cms_pages_section_when_pages_provided(): void
    {
        $page = $this->makeSectionItem('About Us', 'https://example.com/about', 'About the company');
        $storeContext = $this->buildStoreContext(['cms_pages' => [$page]]);

        $result = $this->promptBuilder->buildPrompt($storeContext);

        $this->assertStringContainsString('Key Pages', $result);
        $this->assertStringContainsString('About Us', $result);
    }

    public function test_build_prompt_omits_cms_pages_section_when_no_pages(): void
    {
        $storeContext = $this->buildStoreContext(['cms_pages' => []]);

        $result = $this->promptBuilder->buildPrompt($storeContext);

        $this->assertStringNotContainsString('Key Pages', $result);
    }

    public function test_build_prompt_truncates_description_to_max_length(): void
    {
        $longDescription = str_repeat('a', PromptBuilder::MAX_DESCRIPTION_LENGTH + 100);
        $category = $this->makeSectionItem('Cat', 'https://example.com/cat', $longDescription);
        $storeContext = $this->buildStoreContext(['categories' => [$category]]);

        $result = $this->promptBuilder->buildPrompt($storeContext);

        // The description should not appear beyond MAX_DESCRIPTION_LENGTH characters in the item line
        $this->assertStringNotContainsString(str_repeat('a', PromptBuilder::MAX_DESCRIPTION_LENGTH + 1), $result);
    }

    public function test_build_prompt_formats_section_items_as_markdown_links(): void
    {
        $category = $this->makeSectionItem('Shoes', 'https://example.com/shoes', 'Quality footwear');
        $storeContext = $this->buildStoreContext(['categories' => [$category]]);

        $result = $this->promptBuilder->buildPrompt($storeContext);

        $this->assertStringContainsString('- [Shoes](https://example.com/shoes): Quality footwear', $result);
    }

    public function test_build_prompt_includes_multiple_section_items(): void
    {
        $cat1 = $this->makeSectionItem('Men', 'https://example.com/men', 'Menswear');
        $cat2 = $this->makeSectionItem('Women', 'https://example.com/women', 'Womenswear');
        $storeContext = $this->buildStoreContext(['categories' => [$cat1, $cat2]]);

        $result = $this->promptBuilder->buildPrompt($storeContext);

        $this->assertStringContainsString('- [Men](https://example.com/men): Menswear', $result);
        $this->assertStringContainsString('- [Women](https://example.com/women): Womenswear', $result);
    }

    public function test_max_description_length_constant_is_255(): void
    {
        $this->assertSame(255, PromptBuilder::MAX_DESCRIPTION_LENGTH);
    }
}
