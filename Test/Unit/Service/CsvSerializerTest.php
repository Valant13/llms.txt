<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Test\Unit\Service;

use MageOS\LlmTxt\Service\CsvSerializer;
use PHPUnit\Framework\TestCase;

final class CsvSerializerTest extends TestCase
{
    private CsvSerializer $csvSerializer;

    protected function setUp(): void
    {
        $this->csvSerializer = new CsvSerializer();
    }

    public function test_serialize_returns_comma_space_separated_string(): void
    {
        $result = $this->csvSerializer->serialize(['foo', 'bar', 'baz']);

        $this->assertSame('foo, bar, baz', $result);
    }

    public function test_serialize_returns_empty_string_for_empty_array(): void
    {
        $result = $this->csvSerializer->serialize([]);

        $this->assertSame('', $result);
    }

    public function test_serialize_returns_single_item_without_separator(): void
    {
        $result = $this->csvSerializer->serialize(['only']);

        $this->assertSame('only', $result);
    }

    public function test_unserialize_returns_array_from_comma_separated_string(): void
    {
        $result = $this->csvSerializer->unserialize('foo, bar, baz');

        $this->assertSame(['foo', 'bar', 'baz'], array_values($result));
    }

    public function test_unserialize_trims_whitespace_from_each_item(): void
    {
        $result = $this->csvSerializer->unserialize('  foo  ,  bar  ,  baz  ');

        $this->assertSame(['foo', 'bar', 'baz'], array_values($result));
    }

    public function test_unserialize_returns_empty_array_for_empty_string(): void
    {
        $result = $this->csvSerializer->unserialize('');

        $this->assertSame([], $result);
    }

    public function test_unserialize_returns_empty_array_for_whitespace_only_string(): void
    {
        $result = $this->csvSerializer->unserialize('   ');

        $this->assertSame([], $result);
    }

    public function test_unserialize_filters_out_empty_parts(): void
    {
        $result = $this->csvSerializer->unserialize('foo,,bar');

        $this->assertSame(['foo', 'bar'], array_values($result));
    }

    public function test_unserialize_returns_single_item_array(): void
    {
        $result = $this->csvSerializer->unserialize('only');

        $this->assertSame(['only'], array_values($result));
    }

    public function test_serialize_then_unserialize_roundtrip(): void
    {
        $original = ['alpha', 'beta', 'gamma'];
        $serialized = $this->csvSerializer->serialize($original);
        $result = $this->csvSerializer->unserialize($serialized);

        $this->assertSame($original, array_values($result));
    }
}
