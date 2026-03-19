<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Model;

use MageOS\LlmTxt\Model\OpenAi\Client as OpenAiClient;

class LlmsTxtGenerator
{
    public const INSTRUCTIONS = 'You are an expert at creating concise, well-structured llms.txt files that help AI systems understand website content. You follow the llmstxt.org standard precisely.';
    public const MAX_OUTPUT_TOKENS = 2000;
    public const TEMPERATURE = 0.7;

    public function __construct(
        private readonly StoreDataCollector $storeDataCollector,
        private readonly OpenAiClient $openAiClient,
        private readonly Config $config,
        private readonly PromptBuilder $promptBuilder,
    ) {}

    public function generateLlmsTxt(int $storeId): string
    {
        $storeData = $this->storeDataCollector->collect($storeId);

        $model = $this->config->getOpenAiModel();
        $prompt = $this->promptBuilder->buildPrompt($storeData);

        return $this->openAiClient->postResponses(
            $model,
            $prompt,
            self::INSTRUCTIONS,
            self::MAX_OUTPUT_TOKENS,
            self::TEMPERATURE
        );
    }

    public function estimateTokenCount(string $content): int
    {
        // Rough estimation: 1 token ≈ 0.75 words
        $wordCount = str_word_count($content);
        return (int) ceil($wordCount * 1.3);
    }
}
