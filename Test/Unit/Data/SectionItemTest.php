<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Test\Unit\Data;

use MageOS\LlmTxt\Data\SectionItem;
use PHPUnit\Framework\TestCase;

final class SectionItemTest extends TestCase
{
    private SectionItem $sectionItem;

    protected function setUp(): void
    {
        $this->sectionItem = new SectionItem();
    }

    public function test_get_name_returns_null_when_not_set(): void
    {
        $this->assertNull($this->sectionItem->getName());
    }

    public function test_set_name_stores_value_and_get_name_returns_it(): void
    {
        $this->sectionItem->setName('Test Category');

        $this->assertSame('Test Category', $this->sectionItem->getName());
    }

    public function test_set_name_with_null_stores_null(): void
    {
        $this->sectionItem->setName('First');
        $this->sectionItem->setName(null);

        $this->assertNull($this->sectionItem->getName());
    }

    public function test_get_url_returns_null_when_not_set(): void
    {
        $this->assertNull($this->sectionItem->getUrl());
    }

    public function test_set_url_stores_value_and_get_url_returns_it(): void
    {
        $this->sectionItem->setUrl('https://example.com/category');

        $this->assertSame('https://example.com/category', $this->sectionItem->getUrl());
    }

    public function test_set_url_with_null_stores_null(): void
    {
        $this->sectionItem->setUrl('https://example.com');
        $this->sectionItem->setUrl(null);

        $this->assertNull($this->sectionItem->getUrl());
    }

    public function test_get_description_returns_null_when_not_set(): void
    {
        $this->assertNull($this->sectionItem->getDescription());
    }

    public function test_set_description_stores_value_and_get_description_returns_it(): void
    {
        $this->sectionItem->setDescription('A sample description');

        $this->assertSame('A sample description', $this->sectionItem->getDescription());
    }

    public function test_set_description_with_null_stores_null(): void
    {
        $this->sectionItem->setDescription('Some description');
        $this->sectionItem->setDescription(null);

        $this->assertNull($this->sectionItem->getDescription());
    }

    public function test_set_name_returns_self_for_chaining(): void
    {
        $result = $this->sectionItem->setName('Name');

        $this->assertSame($this->sectionItem, $result);
    }

    public function test_set_url_returns_self_for_chaining(): void
    {
        $result = $this->sectionItem->setUrl('https://example.com');

        $this->assertSame($this->sectionItem, $result);
    }

    public function test_set_description_returns_self_for_chaining(): void
    {
        $result = $this->sectionItem->setDescription('A description');

        $this->assertSame($this->sectionItem, $result);
    }

    public function test_method_chaining_stores_all_values(): void
    {
        $this->sectionItem
            ->setName('Category Name')
            ->setUrl('https://example.com/cat')
            ->setDescription('Category description');

        $this->assertSame('Category Name', $this->sectionItem->getName());
        $this->assertSame('https://example.com/cat', $this->sectionItem->getUrl());
        $this->assertSame('Category description', $this->sectionItem->getDescription());
    }

    public function test_constants_have_expected_values(): void
    {
        $this->assertSame('name', SectionItem::KEY_NAME);
        $this->assertSame('url', SectionItem::KEY_URL);
        $this->assertSame('description', SectionItem::KEY_DESCRIPTION);
    }
}
