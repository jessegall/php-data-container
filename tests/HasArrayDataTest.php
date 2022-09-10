<?php

namespace JesseGall\HasArrayData\Tests;

use JesseGall\HasArrayData\HasArrayData;
use PHPUnit\Framework\TestCase;

class HasArrayDataTest extends TestCase
{

    /**
     * @var HasArrayData
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new class {
            use HasArrayData;

            public function __construct()
            {
                $this->data = [
                    'one' => [
                        'two' => [
                            'three' => 'value'
                        ]
                    ],
                ];
            }

            public function getData(): array
            {
                return $this->data;
            }

            public function setData(array $data): void
            {
                $this->data = $data;
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
        $this->assertEquals(['one' => ['two' => ['three' => 'new value']]], $this->subject->set('one.two.three', 'new value'));
        $this->assertEquals(['one' => ['two' => 'new value']], $this->subject->set('one.two', 'new value'));
        $this->assertEquals(['one' => 'new value'], $this->subject->set('one', 'new value'));
    }

    public function test_set_creates_missing_segments_when_missing()
    {
        $this->subject->setData([]);

        $this->assertEquals(['one' => 'value'], $this->subject->set('one', 'value'));
        $this->assertEquals(['one' => ['two' => 'value']], $this->subject->set('one.two', 'value'));
        $this->assertEquals(['one' => ['two' => ['three' => 'value']]], $this->subject->set('one.two.three', 'value'));
    }

}