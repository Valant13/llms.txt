<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Test\Unit\Config\Backend;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\ValidatorException;
use MageOS\LlmTxt\Config\Backend\CommaSeparatedStrings;
use MageOS\LlmTxt\Service\CsvSerializer;
use PHPUnit\Framework\TestCase;

final class CommaSeparatedStringsTest extends TestCase
{
    private CsvSerializer $csvSerializer;

    protected function setUp(): void
    {
        $this->csvSerializer = new CsvSerializer();
    }

    /**
     * Creates a testable instance by skipping the AbstractModel constructor chain
     * and injecting dependencies via reflection.
     */
    private function makeInstance(string $value = ''): CommaSeparatedStrings
    {
        /** @var CommaSeparatedStrings $instance */
        $instance = (new \ReflectionClass(CommaSeparatedStrings::class))
            ->newInstanceWithoutConstructor();

        $property = new \ReflectionProperty(CommaSeparatedStrings::class, 'csvSerializer');
        $property->setValue($instance, $this->csvSerializer);

        $dataProperty = new \ReflectionProperty(\Magento\Framework\DataObject::class, '_data');
        $dataProperty->setValue($instance, ['value' => $value]);

        $eventManager = $this->createMock(EventManagerInterface::class);
        $eventManagerProperty = new \ReflectionProperty(\Magento\Framework\Model\AbstractModel::class, '_eventManager');
        $eventManagerProperty->setValue($instance, $eventManager);

        return $instance;
    }

    public function test_before_save_accepts_single_valid_identifier(): void
    {
        $instance = $this->makeInstance('about-us');

        $this->expectNotToPerformAssertions();
        $instance->beforeSave();
    }

    public function test_before_save_accepts_comma_separated_valid_identifiers(): void
    {
        $instance = $this->makeInstance('about-us, contact, faq');

        $this->expectNotToPerformAssertions();
        $instance->beforeSave();
    }

    public function test_before_save_accepts_alphanumeric_values(): void
    {
        $instance = $this->makeInstance('SKU123, product-name, page_id');

        $this->expectNotToPerformAssertions();
        $instance->beforeSave();
    }

    public function test_before_save_accepts_values_with_dots(): void
    {
        $instance = $this->makeInstance('page.html, sub.page.html');

        $this->expectNotToPerformAssertions();
        $instance->beforeSave();
    }

    public function test_before_save_accepts_empty_value_without_validation(): void
    {
        $instance = $this->makeInstance('');

        $this->expectNotToPerformAssertions();
        $instance->beforeSave();
    }

    public function test_before_save_throws_validator_exception_for_value_with_spaces(): void
    {
        $instance = $this->makeInstance('invalid value');

        $this->expectException(ValidatorException::class);
        $instance->beforeSave();
    }

    public function test_before_save_throws_validator_exception_for_value_with_special_characters(): void
    {
        $instance = $this->makeInstance('invalid@value');

        $this->expectException(ValidatorException::class);
        $instance->beforeSave();
    }

    public function test_before_save_throws_validator_exception_for_value_with_slash(): void
    {
        $instance = $this->makeInstance('path/to/page');

        $this->expectException(ValidatorException::class);
        $instance->beforeSave();
    }

    public function test_before_save_throws_validator_exception_when_mixed_valid_and_invalid(): void
    {
        $instance = $this->makeInstance('valid-slug, invalid slug, another-valid');

        $this->expectException(ValidatorException::class);
        $instance->beforeSave();
    }

    public function test_before_save_normalizes_value_through_csv_serializer(): void
    {
        $instance = $this->makeInstance('about-us, contact, faq');
        $instance->beforeSave();

        $serialized = $instance->getValue();
        $this->assertIsString($serialized);
        $this->assertStringContainsString('about-us', $serialized);
        $this->assertStringContainsString('contact', $serialized);
        $this->assertStringContainsString('faq', $serialized);
    }

    public function test_before_save_returns_self(): void
    {
        $instance = $this->makeInstance('valid-slug');
        $result = $instance->beforeSave();

        $this->assertInstanceOf(CommaSeparatedStrings::class, $result);
    }

    public function test_before_save_accepts_underscores_hyphens_and_dots(): void
    {
        $instance = $this->makeInstance('my_page, my-page, my.page');

        $this->expectNotToPerformAssertions();
        $instance->beforeSave();
    }
}
