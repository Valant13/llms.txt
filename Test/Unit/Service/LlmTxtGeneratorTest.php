<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Test\Unit\Service;

use MageOS\LlmTxt\Client\OpenAi\Client as OpenAiClient;
use MageOS\LlmTxt\Client\OpenAi\ResponsesParams;
use MageOS\LlmTxt\Client\OpenAi\ResponsesParamsFactory;
use MageOS\LlmTxt\Config\Config;
use MageOS\LlmTxt\Data\StoreContext;
use MageOS\LlmTxt\Service\LlmTxtGenerator;
use MageOS\LlmTxt\Service\PromptBuilder;
use MageOS\LlmTxt\Service\StoreDataCollector;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class LlmTxtGeneratorTest extends TestCase
{
    private StoreDataCollector&MockObject $storeDataCollector;
    private OpenAiClient&MockObject $openAiClient;
    private Config&MockObject $config;
    private PromptBuilder&MockObject $promptBuilder;
    private LoggerInterface&MockObject $logger;
    private ResponsesParamsFactory&MockObject $responsesParamsFactory;
    private ResponsesParams&MockObject $responsesParams;
    private LlmTxtGenerator $generator;

    protected function setUp(): void
    {
        $this->storeDataCollector = $this->createMock(StoreDataCollector::class);
        $this->openAiClient = $this->createMock(OpenAiClient::class);
        $this->config = $this->createMock(Config::class);
        $this->promptBuilder = $this->createMock(PromptBuilder::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->responsesParamsFactory = $this->createMock(ResponsesParamsFactory::class);
        $this->responsesParams = $this->createMock(ResponsesParams::class);

        // ResponsesParams uses fluent interface - chain methods return self
        $this->responsesParams->method('setModel')->willReturnSelf();
        $this->responsesParams->method('setPrompt')->willReturnSelf();
        $this->responsesParams->method('setInstructions')->willReturnSelf();
        $this->responsesParams->method('setMaxOutputTokens')->willReturnSelf();
        $this->responsesParams->method('setTemperature')->willReturnSelf();

        $this->responsesParamsFactory
            ->method('create')
            ->willReturn($this->responsesParams);

        $this->generator = new LlmTxtGenerator(
            $this->storeDataCollector,
            $this->openAiClient,
            $this->config,
            $this->promptBuilder,
            $this->logger,
            $this->responsesParamsFactory,
        );
    }

    private function configureDefaultBehavior(
        string $model = 'gpt-4o',
        string $prompt = 'Test prompt',
        string $apiResponse = '# Store\n> Description',
        string $additionalContent = '',
        bool $logEnabled = false,
    ): StoreContext {
        $storeContext = new StoreContext();

        $this->storeDataCollector->method('collect')->willReturn($storeContext);
        $this->config->method('getOpenAiModel')->willReturn($model);
        $this->config->method('isLogPromptEnabled')->willReturn($logEnabled);
        $this->config->method('getAdditionalContent')->willReturn($additionalContent);
        $this->promptBuilder->method('buildPrompt')->willReturn($prompt);
        $this->openAiClient->method('postResponses')->willReturn($apiResponse);

        return $storeContext;
    }

    public function test_generate_llm_txt_returns_content_from_openai_client(): void
    {
        $this->configureDefaultBehavior(apiResponse: '# My Store\n> A great store');

        $result = $this->generator->generateLlmTxt(1);

        $this->assertSame('# My Store\n> A great store', $result);
    }

    public function test_generate_llm_txt_appends_additional_content_when_present(): void
    {
        $this->configureDefaultBehavior(
            apiResponse: '# My Store',
            additionalContent: 'Extra footer content',
        );

        $result = $this->generator->generateLlmTxt(1);

        $this->assertStringContainsString('# My Store', $result);
        $this->assertStringContainsString('Extra footer content', $result);
    }

    public function test_generate_llm_txt_separates_additional_content_with_newlines(): void
    {
        $this->configureDefaultBehavior(
            apiResponse: '# My Store',
            additionalContent: 'Extra footer',
        );

        $result = $this->generator->generateLlmTxt(1);

        $this->assertSame("# My Store\n\nExtra footer", $result);
    }

    public function test_generate_llm_txt_does_not_append_when_additional_content_is_empty(): void
    {
        $this->configureDefaultBehavior(
            apiResponse: '# My Store',
            additionalContent: '',
        );

        $result = $this->generator->generateLlmTxt(1);

        $this->assertSame('# My Store', $result);
    }

    public function test_generate_llm_txt_collects_store_data_for_given_store_id(): void
    {
        $storeContext = new StoreContext();

        $this->storeDataCollector
            ->method('collect')
            ->with(7)
            ->willReturn($storeContext);

        $this->config->method('getOpenAiModel')->willReturn('gpt-4o');
        $this->config->method('isLogPromptEnabled')->willReturn(false);
        $this->config->method('getAdditionalContent')->willReturn('');
        $this->promptBuilder->method('buildPrompt')->willReturn('prompt');
        $this->openAiClient->method('postResponses')->willReturn('content');

        $result = $this->generator->generateLlmTxt(7);

        $this->assertSame('content', $result);
    }

    public function test_generate_llm_txt_logs_prompt_when_logging_is_enabled(): void
    {
        $this->configureDefaultBehavior(
            model: 'gpt-4o',
            prompt: 'The prompt text',
            logEnabled: true,
        );

        $this->logger
            ->expects($this->atLeastOnce())
            ->method('info')
            ->with(
                'LlmTxt prompt',
                $this->arrayHasKey('prompt'),
            );

        $this->generator->generateLlmTxt(1);
    }

    public function test_generate_llm_txt_does_not_log_when_logging_is_disabled(): void
    {
        $this->configureDefaultBehavior(logEnabled: false);

        $this->logger
            ->expects($this->never())
            ->method('info');

        $this->generator->generateLlmTxt(1);
    }

    public function test_generate_llm_txt_passes_model_to_responses_params(): void
    {
        $this->configureDefaultBehavior(model: 'gpt-4-turbo');

        $this->responsesParams
            ->expects($this->atLeastOnce())
            ->method('setModel')
            ->with('gpt-4-turbo')
            ->willReturnSelf();

        $this->generator->generateLlmTxt(1);
    }

    public function test_generate_llm_txt_passes_instructions_constant_to_responses_params(): void
    {
        $this->configureDefaultBehavior();

        $this->responsesParams
            ->expects($this->atLeastOnce())
            ->method('setInstructions')
            ->with(LlmTxtGenerator::INSTRUCTIONS)
            ->willReturnSelf();

        $this->generator->generateLlmTxt(1);
    }

    public function test_generate_llm_txt_passes_max_output_tokens_constant_to_responses_params(): void
    {
        $this->configureDefaultBehavior();

        $this->responsesParams
            ->expects($this->atLeastOnce())
            ->method('setMaxOutputTokens')
            ->with(LlmTxtGenerator::MAX_OUTPUT_TOKENS)
            ->willReturnSelf();

        $this->generator->generateLlmTxt(1);
    }

    public function test_estimate_token_count_returns_integer(): void
    {
        $result = $this->generator->estimateTokenCount('Hello world');

        $this->assertIsInt($result);
    }

    public function test_estimate_token_count_returns_zero_for_empty_string(): void
    {
        $result = $this->generator->estimateTokenCount('');

        $this->assertSame(0, $result);
    }

    public function test_estimate_token_count_is_proportional_to_word_count(): void
    {
        $short = $this->generator->estimateTokenCount('one two three');
        $long = $this->generator->estimateTokenCount('one two three four five six seven eight nine ten');

        $this->assertGreaterThan($short, $long);
    }

    public function test_estimate_token_count_uses_word_based_estimation(): void
    {
        // 2 words * 1.3 = 2.6, ceil = 3
        $result = $this->generator->estimateTokenCount('hello world');

        $this->assertSame(3, $result);
    }

    public function test_constants_have_expected_values(): void
    {
        $this->assertSame(2000, LlmTxtGenerator::MAX_OUTPUT_TOKENS);
        $this->assertSame(0.7, LlmTxtGenerator::TEMPERATURE);
        $this->assertNotEmpty(LlmTxtGenerator::INSTRUCTIONS);
    }
}
