<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Test\Unit\Config\Source;

use MageOS\LlmTxt\Config\Source\OpenAiModel;
use PHPUnit\Framework\TestCase;

final class OpenAiModelTest extends TestCase
{
    private OpenAiModel $openAiModel;

    protected function setUp(): void
    {
        $this->openAiModel = new OpenAiModel();
    }

    public function test_to_option_array_returns_array(): void
    {
        $result = $this->openAiModel->toOptionArray();

        $this->assertIsArray($result);
    }

    public function test_to_option_array_returns_non_empty_list(): void
    {
        $result = $this->openAiModel->toOptionArray();

        $this->assertNotEmpty($result);
    }

    public function test_to_option_array_every_item_has_value_key(): void
    {
        $result = $this->openAiModel->toOptionArray();

        foreach ($result as $option) {
            $this->assertArrayHasKey('value', $option);
        }
    }

    public function test_to_option_array_every_item_has_label_key(): void
    {
        $result = $this->openAiModel->toOptionArray();

        foreach ($result as $option) {
            $this->assertArrayHasKey('label', $option);
        }
    }

    public function test_to_option_array_contains_gpt_4o_mini(): void
    {
        $result = $this->openAiModel->toOptionArray();
        $values = array_column($result, 'value');

        $this->assertContains('gpt-4o-mini', $values);
    }

    public function test_to_option_array_contains_gpt_4o(): void
    {
        $result = $this->openAiModel->toOptionArray();
        $values = array_column($result, 'value');

        $this->assertContains('gpt-4o', $values);
    }

    public function test_to_option_array_contains_gpt_4_turbo(): void
    {
        $result = $this->openAiModel->toOptionArray();
        $values = array_column($result, 'value');

        $this->assertContains('gpt-4-turbo', $values);
    }

    public function test_to_option_array_values_are_non_empty_strings(): void
    {
        $result = $this->openAiModel->toOptionArray();

        foreach ($result as $option) {
            $this->assertIsString($option['value']);
            $this->assertNotEmpty($option['value']);
        }
    }

    public function test_to_option_array_has_exactly_five_options(): void
    {
        $result = $this->openAiModel->toOptionArray();

        $this->assertCount(5, $result);
    }
}
