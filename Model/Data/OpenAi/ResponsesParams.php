<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Model\Data\OpenAi;

use Magento\Framework\Model\AbstractExtensibleModel;

class ResponsesParams extends AbstractExtensibleModel
{
    public const MODEL = 'model';
    public const PROMPT = 'prompt';
    public const INSTRUCTIONS = 'instructions';
    public const MAX_OUTPUT_TOKENS = 'max_output_tokens';
    public const TEMPERATURE = 'temperature';

    public function getModel(): string
    {
        return (string) $this->getData(self::MODEL);
    }

    public function setModel(string $model): self
    {
        return $this->setData(self::MODEL, $model);
    }

    public function getPrompt(): string
    {
        return (string) $this->getData(self::PROMPT);
    }

    public function setPrompt(string $prompt): self
    {
        return $this->setData(self::PROMPT, $prompt);
    }

    public function getInstructions(): string
    {
        return (string) $this->getData(self::INSTRUCTIONS);
    }

    public function setInstructions(string $instructions): self
    {
        return $this->setData(self::INSTRUCTIONS, $instructions);
    }

    public function getMaxOutputTokens(): int
    {
        return (int) $this->getData(self::MAX_OUTPUT_TOKENS);
    }

    public function setMaxOutputTokens(int $maxOutputTokens): self
    {
        return $this->setData(self::MAX_OUTPUT_TOKENS, $maxOutputTokens);
    }

    public function getTemperature(): float
    {
        return (float) ($this->getData(self::TEMPERATURE) ?? 0.7);
    }

    public function setTemperature(float $temperature): self
    {
        return $this->setData(self::TEMPERATURE, $temperature);
    }

    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    public function setExtensionAttributes($extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
