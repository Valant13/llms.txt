<?php

declare(strict_types=1);

namespace MageOS\LlmTxt\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OpenAiModel implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'gpt-5-mini', 'label' => __('GPT-5 Mini (Fast & Affordable)')],
            ['value' => 'gpt-5.4', 'label' => __('GPT-5.4 (Recommended - Most Capable)')],
            ['value' => 'gpt-4o-mini', 'label' => __('GPT-4o Mini (Previous Generation)')],
            ['value' => 'gpt-4o', 'label' => __('GPT-4o (Previous Generation)')],
            ['value' => 'gpt-4-turbo', 'label' => __('GPT-4 Turbo (Previous Generation)')],
        ];
    }
}
