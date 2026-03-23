<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Test\Unit\Config\Backend;

use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\ValidatorException;
use MageOS\LlmTxt\Config\Backend\CommaSeparatedIntegers;
use MageOS\LlmTxt\Service\CsvSerializer;
use PHPUnit\Framework\TestCase;

final class CommaSeparatedIntegersTest extends TestCase
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
    private function makeInstance(string $value = ''): CommaSeparatedIntegers
    {
        /** @var CommaSeparatedIntegers $instance */
        $instance = (new \ReflectionClass(CommaSeparatedIntegers::class))
            ->newInstanceWithoutConstructor();

        // Inject the readonly csvSerializer property
        $property = new \ReflectionProperty(CommaSeparatedIntegers::class, 'csvSerializer');
        $property->setValue($instance, $this->csvSerializer);

        // Initialise the DataObject _data store and set the value
        $dataProperty = new \ReflectionProperty(\Magento\Framework\DataObject::class, '_data');
        $dataProperty->setValue($instance, ['value' => $value]);

        // Inject the event manager required by AbstractModel::beforeSave()
        $eventManager = $this->createMock(EventManagerInterface::class);
        $eventManagerProperty = new \ReflectionProperty(\Magento\Framework\Model\AbstractModel::class, '_eventManager');
        $eventManagerProperty->setValue($instance, $eventManager);

        return $instance;
    }

    public function test_before_save_accepts_single_positive_integer(): void
    {
        $instance = $this->makeInstance('42');

        $this->expectNotToPerformAssertions();
        $instance->beforeSave();
    }

    public function test_before_save_accepts_comma_separated_positive_integers(): void
    {
        $instance = $this->makeInstance('1, 2, 3, 100');

        $this->expectNotToPerformAssertions();
        $instance->beforeSave();
    }

    public function test_before_save_accepts_empty_value_without_validation(): void
    {
        $instance = $this->makeInstance('');

        $this->expectNotToPerformAssertions();
        $instance->beforeSave();
    }

    public function test_before_save_throws_validator_exception_for_non_integer_value(): void
    {
        $instance = $this->makeInstance('abc');

        $this->expectException(ValidatorException::class);
        $instance->beforeSave();
    }

    public function test_before_save_throws_validator_exception_for_negative_integer(): void
    {
        $instance = $this->makeInstance('-5');

        $this->expectException(ValidatorException::class);
        $instance->beforeSave();
    }

    public function test_before_save_skips_validation_for_zero_because_empty_check(): void
    {
        // PHP's empty('0') === true, so '0' alone bypasses the validation block
        $instance = $this->makeInstance('0');

        $this->expectNotToPerformAssertions();
        $instance->beforeSave();
    }

    public function test_before_save_throws_validator_exception_for_zero_in_list(): void
    {
        $instance = $this->makeInstance('1, 0, 3');

        $this->expectException(ValidatorException::class);
        $instance->beforeSave();
    }

    public function test_before_save_throws_validator_exception_for_float_value(): void
    {
        $instance = $this->makeInstance('3.14');

        $this->expectException(ValidatorException::class);
        $instance->beforeSave();
    }

    public function test_before_save_throws_validator_exception_when_mixed_valid_and_invalid(): void
    {
        $instance = $this->makeInstance('1, 2, abc, 4');

        $this->expectException(ValidatorException::class);
        $instance->beforeSave();
    }

    public function test_before_save_normalizes_value_through_csv_serializer(): void
    {
        $instance = $this->makeInstance('10, 20, 30');
        $instance->beforeSave();

        $serialized = $instance->getValue();
        $this->assertIsString($serialized);
        $this->assertStringContainsString('10', $serialized);
        $this->assertStringContainsString('20', $serialized);
        $this->assertStringContainsString('30', $serialized);
    }

    public function test_before_save_returns_self(): void
    {
        $instance = $this->makeInstance('5');
        $result = $instance->beforeSave();

        $this->assertInstanceOf(CommaSeparatedIntegers::class, $result);
    }
}
