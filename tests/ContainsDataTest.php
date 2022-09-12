<?php

namespace Test;

use JesseGall\ContainsData\ContainsData;
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

            public function getData(): array
            {
                return $this->__container;
            }

            public function setData(array $data): void
            {
                $this->__container = $data;
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
        $this->subject->setData([]);

        $this->assertFalse($this->subject->has('one'));
        $this->assertFalse($this->subject->has('one.two'));
        $this->assertFalse($this->subject->has('one.two.three'));
    }

    public function test_get_returns_root_data_when_key_is_null()
    {
        $this->assertEquals($this->subject->getData(), $this->subject->get());
    }

    public function test_get_value_with_dot_notation_returns_expected_value()
    {
        $this->assertEquals(['two' => ['three' => 'value']], $this->subject->get('one'));
        $this->assertEquals(['three' => 'value'], $this->subject->get('one.two'));
        $this->assertEquals('value', $this->subject->get('one.two.three'));
    }

    public function test_get_returns_null_when_value_does_not_exist()
    {
        $this->subject->setData([]);

        $this->assertNull($this->subject->get('one'));
        $this->assertNull($this->subject->get('one.two'));
        $this->assertNull($this->subject->get('one.two.three'));
    }

    public function test_get_returns_default_when_value_does_not_exist_and_default_is_given()
    {
        $this->subject->setData([]);

        $this->assertEquals('default', $this->subject->get('one', 'default'));
        $this->assertEquals('default', $this->subject->get('one.two', 'default'));
        $this->assertEquals('default', $this->subject->get('one.two.three', 'default'));
    }

    public function test_set_replaces_data_when_first_parameter_is_an_array()
    {
        $this->assertEquals(['some' => ['new' => 'array']], $this->subject->set(['some' => ['new' => 'array']]));
    }

    public function test_set_overwrites_existing_value_when_exists()
    {
        $expected = 'new value';

        $this->subject->set('one.two.three', $expected);
        $this->assertEquals($expected, $this->subject->getData()['one']['two']['three']);

        $this->subject->set('one.two', $expected);
        $this->assertEquals($expected, $this->subject->getData()['one']['two']);

        $this->subject->set('one', $expected);
        $this->assertEquals($expected, $this->subject->getData()['one']);
    }

    public function test_set_creates_missing_segments_when_missing()
    {
        $this->subject->setData([]);

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

        $this->assertEquals([1, 2, 3], $this->subject->getData()['list']);
    }

    public function test_map_replaces_item_when_replace_is_set_to_true()
    {
        $this->subject->map('list', fn($item) => $item * ($item - 1), true);

        $this->assertEquals([0, 2, 6], $this->subject->getData()['list']);
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



}