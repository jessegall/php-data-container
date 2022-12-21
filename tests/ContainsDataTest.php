<?php

namespace Test;

use JesseGall\ContainsData\ContainsData;
use JesseGall\ContainsData\ReferenceMissingException;
use PHPUnit\Framework\TestCase;

class ContainsDataTest extends TestCase
{

    /**
     * @var ContainsData
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new class {
            use ContainsData;

            public function __construct()
            {
                $this->__container = [
                    'one' => [
                        'two' => [
                            'three' => 'value'
                        ]
                    ],
                    'list' => [1, 2, 3],
                    'associative' => [
                        'one' => 1,
                        'two' => 2,
                        'three' => 3
                    ]
                ];
            }

            public function getContainer(): array
            {
                return $this->__container;
            }

            public function setContainer(array $container): void
            {
                $this->__container = $container;
            }
        };
    }

    public function test_has_returns_true_when_value_exists()
    {
        $this->assertTrue($this->subject->has('one'));
        $this->assertTrue($this->subject->has('one.two'));
        $this->assertTrue($this->subject->has('one.two.three'));
    }

    public function test_has_returns_false_when_first_segments_exist_but_final_segments_do_not_exist()
    {
        $this->assertFalse($this->subject->has('one.two.three.four.five'));
    }

    public function test_has_returns_false_when_value_does_not_exists()
    {
        $this->subject->setContainer([]);

        $this->assertFalse($this->subject->has('one'));
        $this->assertFalse($this->subject->has('one.two'));
        $this->assertFalse($this->subject->has('one.two.three'));
    }

    public function test_get_value_with_dot_notation_returns_expected_value()
    {
        $this->assertEquals(['two' => ['three' => 'value']], $this->subject->get('one'));
        $this->assertEquals(['three' => 'value'], $this->subject->get('one.two'));
        $this->assertEquals('value', $this->subject->get('one.two.three'));
    }

    public function test_get_returns_null_when_value_does_not_exist()
    {
        $this->subject->setContainer([]);

        $this->assertNull($this->subject->get('one'));
        $this->assertNull($this->subject->get('one.two'));
        $this->assertNull($this->subject->get('one.two.three'));
    }

    public function test_get_returns_default_when_value_does_not_exist_and_default_is_given()
    {
        $this->subject->setContainer([]);

        $this->assertEquals('default', $this->subject->get('one', 'default'));
        $this->assertEquals('default', $this->subject->get('one.two', 'default'));
        $this->assertEquals('default', $this->subject->get('one.two.three', 'default'));
    }

    public function test_set_overwrites_existing_value_when_exists()
    {
        $expected = 'new value';

        $this->subject->set('one.two.three', $expected);
        $this->assertEquals($expected, $this->subject->getContainer()['one']['two']['three']);

        $this->subject->set('one.two', $expected);
        $this->assertEquals($expected, $this->subject->getContainer()['one']['two']);

        $this->subject->set('one', $expected);
        $this->assertEquals($expected, $this->subject->getContainer()['one']);
    }

    public function test_set_creates_missing_segments_when_missing()
    {
        $this->subject->setContainer([]);

        $this->assertEquals(['one' => 'value'], $this->subject->set('one', 'value'));
        $this->assertEquals(['one' => ['two' => 'value']], $this->subject->set('one.two', 'value'));
        $this->assertEquals(['one' => ['two' => ['three' => 'value']]], $this->subject->set('one.two.three', 'value'));
    }

    public function test_map_returns_single_mapped_value_when_key_points_to_a_single_item()
    {
        $actual = $this->subject->map('one.two.three', fn($item) => $item);

        $this->assertEquals('value', $actual);
    }

    public function test_map_returns_array_of_mapped_values_when_key_points_to_a_list()
    {
        $actual = $this->subject->map('list', fn($item) => $item * ($item - 1));

        $this->assertEquals([0, 2, 6], $actual);
    }

    public function test_map_preserves_keys_when_key_points_to_an_associative_array()
    {
        $actual = $this->subject->map('associative', fn($item) => $item * ($item - 1));

        $this->assertEquals(['one' => 0, 'two' => 2, 'three' => 6], $actual);
    }

    public function test_map_returns_key_as_second_argument_in_the_callback_when_key_points_to_an_array()
    {
        $keys = [];

        $this->subject->map('associative', function ($item, $key) use (&$keys) {
            $keys[] = $key;
        });

        $this->assertEquals(['one', 'two', 'three'], $keys);
    }

    public function test_map_does_not_replace_item_when_replace_is_set_to_false()
    {
        $this->subject->map('list', fn($item) => $item * ($item - 1));

        $this->assertEquals([1, 2, 3], $this->subject->getContainer()['list']);
    }

    public function test_map_replaces_item_when_replace_is_set_to_true()
    {
        $this->subject->map('list', fn($item) => $item * ($item - 1), true);

        $this->assertEquals([0, 2, 6], $this->subject->getContainer()['list']);
    }

    public function test_container_can_be_overridden_to_point_to_a_different_array()
    {
        $localSubject = new class($this->subject) {
            use ContainsData;

            private object $target;

            public function __construct(object $target)
            {
                $this->target = $target;
            }

            public function &container(array &$container = null): array
            {
                return $this->target->container();
            }
        };

        $this->assertEquals($this->subject->container(), $localSubject->container());

        $this->subject->container()['this_should_be_synced'] = 'synced value';
        $this->assertEquals('synced value', $localSubject->container()['this_should_be_synced']);

        $localSubject->container()['this_should_be_synced'] = 'reversed';
        $this->assertEquals('reversed', $this->subject->container()['this_should_be_synced']);

        unset($this->subject->container()['this_should_be_synced']);
        $this->assertNotContains('this_should_be_synced', $localSubject->container());
    }

    public function test_container_reference_can_be_replaced_by_passing_an_argument()
    {
        $localSubject = new class() {
            use ContainsData;
        };

        $this->assertEquals([], $localSubject->container());

        $localSubject->container($this->subject->container());

        $this->assertEquals($this->subject->container(), $localSubject->container());

        $this->subject->container()['this_should_be_synced'] = 'synced value';
        $this->assertEquals('synced value', $localSubject->container()['this_should_be_synced']);

        $localSubject->container()['this_should_be_synced'] = 'reversed';
        $this->assertEquals('reversed', $this->subject->container()['this_should_be_synced']);

        unset($this->subject->container()['this_should_be_synced']);
        $this->assertNotContains('this_should_be_synced', $localSubject->container());
    }

    public function test_container_reference_can_created_with_empty_array()
    {
        $container = [];

        $this->subject->container($container);

        $container['value'] = 'expected';

        $this->assertEquals('expected', $this->subject->container()['value']);
    }

    public function test_merge_merges_data_as_expected()
    {
        $this->subject->merge([
            'one' => [
                'two' => [
                    'three' => 'new value',
                ],
                '_two' => [
                    'property' => 'value'
                ]
            ],
            'merged' => 'property',
        ], true);

        $this->assertEquals([
            'one' => [
                'two' => [
                    'three' => 'new value'
                ],
                '_two' => [
                    'property' => 'value'
                ]
            ],
            'merged' => 'property',
            'list' => [1, 2, 3],
            'associative' => [
                'one' => 1,
                'two' => 2,
                'three' => 3
            ]
        ], $this->subject->getContainer());
    }

    public function test_given_overwrite_false_when_merge_then_existing_data_is_preserved()
    {
        $this->subject->merge([
            'one' => [
                'two' => [
                    'three' => 'new value',
                ],
            ],
            'merged' => 'merged value',
        ], false);

        $this->assertEquals('value', $this->subject->get('one.two.three'));

        $this->assertEquals('merged value', $this->subject->get('merged'));
    }

    public function test_get_as_reference_returns_a_reference()
    {
        $value = &$this->subject->getAsReference('associative.one');

        $value = 3;

        $this->assertEquals($value, $this->subject->getContainer()['associative']['one']);
    }

    public function test_get_as_reference_throws_an_exception_when_key_is_missing()
    {
        $this->expectException(ReferenceMissingException::class);

        $this->subject->getAsReference('associative.missing');
    }

    public function test_set_as_reference_sets_value_as_reference()
    {
        $value = 'initial value';

        $this->subject->setAsReference('one.two.three', $value);

        $this->assertEquals('initial value', $this->subject->getContainer()['one']['two']['three']);

        $value = 'new value';

        $this->assertEquals('new value', $this->subject->getContainer()['one']['two']['three']);
    }

    public function test_filter_return_correct_values_when_filtering_array()
    {
        $data = ['foo' => ['a', 'b', 'c'], 'bar' => 2];

        $container = new class { use ContainsData; };
        $container->container($data);

        $result = $container->filter('foo', function ($item) {
            return $item !== 'b';
        });

        $this->assertEquals(['a', 'c'], $result);
    }

    public function test_filter_return_correct_empty_array_when_all_items_in_array_are_filtered()
    {
        $data = ['foo' => ['a', 'b', 'c'], 'bar' => 2];

        $container = new class { use ContainsData; };
        $container->container($data);

        $result = $container->filter('foo', function ($item) {
            return is_numeric($item);
        });

        $this->assertEquals([], $result);
    }

    public function test_filter_returns_correct_value_when_filtering_single_item()
    {
        $data = ['foo' => ['a', 'b', 'c'], 'bar' => 2];

        $container = new class { use ContainsData; };
        $container->container($data);

        $result = $container->filter('bar', function ($item) {
            return $item > 1;
        });

        $this->assertEquals(2, $result);
    }

    public function test_filter_returns_null_when_filtering_single_item_is_filtered()
    {
        $data = ['foo' => ['a', 'b', 'c'], 'bar' => 2];

        $container = new class { use ContainsData; };
        $container->container($data);

        $result = $container->filter('bar', function ($item) {
            return $item > 2;
        });

        $this->assertNull($result);
    }

    public function test_when_clear_container_it_is_empty()
    {
        $this->subject->clear();

        $this->assertEmpty($this->subject->container());
    }

    public function test_when_clear_then_reference_is_empty()
    {
        $reference = &$this->subject->container();

        $this->subject->clear();

        $this->assertEmpty($reference);
    }

}