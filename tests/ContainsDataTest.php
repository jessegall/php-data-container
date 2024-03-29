<?php

namespace JesseGall\Tests;

use ArrayObject;
use JesseGall\Data\Reference;
use PHPUnit\Framework\TestCase;

class ContainsDataTest extends TestCase
{

    /**
     * ----------------------------------------
     * setData method
     * ----------------------------------------
     */

    public function testSetData()
    {
        $container = container(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $container->get());
    }

    public function testSetDataUsingReference()
    {
        $container = container();

        $data = ['foo' => 'bar'];

        $container->setData(new Reference($data));

        $this->assertEquals(['foo' => 'bar'], $container->get());

        $data['foo'] = 'baz';

        $this->assertEquals(['foo' => 'baz'], $container->get());
    }

    public function testSetDataUsingArrayAccess()
    {
        $container = container();

        $data = new ArrayObject(['foo' => 'bar']);

        $container->setData($data);

        $this->assertEquals(['foo' => 'bar'], $container->get()->getArrayCopy());

        $data['foo'] = 'baz';

        $this->assertEquals(['foo' => 'baz'], $container->get()->getArrayCopy());
    }

    /**
     * ----------------------------------------
     * set method
     * ----------------------------------------
     */

    public function testSetKeyValuePair()
    {
        $container = container();

        $container->set('foo.bar', 'baz');

        $this->assertEquals('baz', $container->get('foo.bar'));
    }

    public function testSetKeyValuePairOnExistingPath()
    {
        $container = container();

        $container->set('foo.bar', 'baz');

        $container->set('foo.bar.baz', 'qux');

        $this->assertEquals(['foo' => ['bar' => ['baz' => 'qux']]], $container->get());
    }

    public function testSetKeyValueUsingReferenceValue()
    {
        $container = container();

        $value = 'foo';

        $container->set('bar', new Reference($value));

        $this->assertEquals('foo', $container->get('bar'));

        $value = 'baz';

        $this->assertEquals('baz', $container->get('bar'));
    }

    /**
     * ----------------------------------------
     * get method
     * ----------------------------------------
     */

    public function testGetValueFromKey()
    {
        $container = container(['foo' => ['bar' => 'baz']]);

        $this->assertEquals('baz', $container->get('foo.bar'));
    }

    public function testGetValueFromMissingKey()
    {
        $container = container();

        $this->assertNull($container->get('foo.bar'));
    }

    public function testGetValueFromMissingKeyWithDefault()
    {
        $container = container();

        $this->assertEquals('baz', $container->get('foo.bar', 'baz'));
    }

    public function testGetValueFromNullKey()
    {
        $container = container(['foo' => ['bar' => 'baz']]);

        $this->assertEquals(['foo' => ['bar' => 'baz']], $container->get());
    }

    public function testGetValueFromKeyAsReference()
    {
        $container = container(['foo' => ['bar' => 'baz']]);

        $value = &$container->get('foo.bar');

        $value = 'qux';

        $this->assertEquals('qux', $container->get('foo.bar'));
    }

    public function testGetValueFromKeyWhenDataIsArrayAccess()
    {
        $container = container(new ArrayObject(['foo' => ['bar' => 'baz']]));

        $this->assertEquals('baz', $container->get('foo.bar'));
    }

    /**
     * ----------------------------------------
     * has method
     * ----------------------------------------
     */

    public function testHasKey()
    {
        $container = container();

        $this->assertFalse($container->has('foo.bar'));

        $container->set('foo.bar', 'baz');

        $this->assertTrue($container->has('foo.bar'));
    }

    public function testHasKeyWhenDataIsArrayAccess()
    {
        $container = container(new ArrayObject(['foo' => ['bar' => 'baz']]));

        $this->assertTrue($container->has('foo.bar'));
    }

    /**
     * ----------------------------------------
     * forget method
     * ----------------------------------------
     */

    public function testForgetKey()
    {
        $container = container(['foo' => ['bar' => 'baz']]);

        $container->forget('foo.bar');

        $this->assertFalse($container->has('foo.bar'));
    }

    public function testForgetMissingKey()
    {
        $container = container(['foo' => ['bar' => 'baz']]);

        $container->forget('foo.bar.baz.qux');

        $container->forget('bar.baz.qux');

        $this->assertTrue($container->has('foo.bar'));
    }

    public function testForgetKeyWhenDataIsArrayAccess()
    {
        $container = container(new ArrayObject(['foo' => ['bar' => 'baz']]));

        $container->forget('foo.bar');

        $this->assertFalse($container->has('foo.bar'));
    }

    /**
     * ----------------------------------------
     * flatten method
     * ----------------------------------------
     */

    public function testFlatten()
    {
        $container = container([
            'foo' => 'bar',
            'baz' => ['qux' => 'quux'],
            'corge' => ['grault' => ['garply' => 'waldo', 'fred' => 'plugh']],
        ]);

        $this->assertEquals([
            'foo' => 'bar',
            'baz.qux' => 'quux',
            'corge.grault.garply' => 'waldo',
            'corge.grault.fred' => 'plugh',
        ], $container->flatten());
    }

    public function testFlattenWhenDataIsArrayAccess()
    {
        $container = container(new ArrayObject([
            'foo' => 'bar',
            'baz' => ['qux' => 'quux'],
            'corge' => ['grault' => ['garply' => 'waldo', 'fred' => 'plugh']],
        ]));

        $this->assertEquals([
            'foo' => 'bar',
            'baz.qux' => 'quux',
            'corge.grault.garply' => 'waldo',
            'corge.grault.fred' => 'plugh',
        ], $container->flatten());
    }

    /**
     * ----------------------------------------
     * merge method
     * ----------------------------------------
     */

    public function testMergeKey()
    {
        $container = container(['foo' => ['bar' => 'baz']]);

        $container->merge('foo', ['bar' => ['baz' => 'qux']]);

        $this->assertEquals(['foo' => ['bar' => ['baz' => 'qux']]], $container->get());
    }

    public function testMergeMissingKey()
    {
        $container = container();

        $container->merge('foo', ['bar' => ['baz' => 'qux']]);

        $this->assertEquals(['foo' => ['bar' => ['baz' => 'qux']]], $container->get());
    }

    public function testMergeNullKey()
    {
        $container = container(['foo' => ['bar' => 'baz']]);

        $container->merge(null, ['foo' => ['bar' => ['baz' => 'qux']]]);

        $this->assertEquals(['foo' => ['bar' => ['baz' => 'qux']]], $container->get());
    }

    public function testMergeArrayKey()
    {
        $container = container(['foo' => ['bar' => 'baz']]);

        $container->merge(['foo' => ['bar' => ['baz' => 'qux']]]);

        $this->assertEquals(['foo' => ['bar' => ['baz' => 'qux']]], $container->get());
    }

    public function testMergeWhenDataIsArrayAccess()
    {
        $container = container(new ArrayObject(['foo' => ['bar' => 'baz']]));

        $container->merge('foo', ['bar' => ['baz' => 'qux']]);

        $this->assertEquals(['foo' => ['bar' => ['baz' => 'qux']]], $container->get()->getArrayCopy());
    }

    /**
     * ----------------------------------------
     * mergeDistinct method
     * ----------------------------------------
     */

    public function testMergeDistinct()
    {
        $container = container([
            'foo' => ['bar' => 'baz'],
            'baz' => ['qux' => 'quux'],
        ]);

        $container->mergeDistinct([
            'foo' => ['bar' => 'qux'],
            'corge' => ['grault' => 'garply'],
        ]);

        $this->assertEquals([
            'foo' => ['bar' => 'baz'],
            'baz' => ['qux' => 'quux'],
            'corge' => ['grault' => 'garply'],
        ], $container->get());
    }

    public function testMergeDistinctWhenDataIsArrayAccess()
    {
        $container = container(new ArrayObject([
            'foo' => ['bar' => 'baz'],
            'baz' => ['qux' => 'quux'],
        ]));

        $container->mergeDistinct([
            'foo' => ['bar' => 'qux'],
            'corge' => ['grault' => 'garply'],
        ]);

        $this->assertEquals([
            'foo' => ['bar' => 'baz'],
            'baz' => ['qux' => 'quux'],
            'corge' => ['grault' => 'garply'],
        ], $container->get()->getArrayCopy());
    }

    /**
     * ----------------------------------------
     * clear method
     * ----------------------------------------
     */

    public function testClear()
    {
        $container = container(['foo' => ['bar' => 'baz']]);

        $container->clear();

        $this->assertEquals([], $container->get());
    }

    public function testClearAlsoClearsReference()
    {
        $data = ['foo' => ['bar' => 'baz']];

        $container = container(new Reference($data));

        $container->clear();

        $this->assertEquals([], $data);
    }

    public function testClearWhenDataIsArrayAccess()
    {
        $container = container(new ArrayObject(['foo' => ['bar' => 'baz']]));

        $container->clear();

        $this->assertEquals([], $container->get()->getArrayCopy());
    }

    /**
     * ----------------------------------------
     * Custom delimiter
     * ----------------------------------------
     */

    public function testCustomDelimiter()
    {
        $container = container();

        $container->setDelimiter('_');

        $container->setData(['foo' => ['bar' => 'baz']]);

        $this->assertEquals('baz', $container->get('foo_bar'));

        $this->assertTrue($container->has('foo_bar'));

        $container->set('foo_bar', 'qux');

        $this->assertEquals('qux', $container->get('foo_bar'));

        $this->assertEquals(['foo_bar' => 'qux'], $container->flatten());

        $container->forget('foo_bar');

        $this->assertFalse($container->has('foo_bar'));
    }

    public function testCustomDelimiterWhenDataIsArrayAccess()
    {
        $container = container();

        $container->setDelimiter('_');

        $container->setData(new ArrayObject(['foo' => ['bar' => 'baz']]));

        $this->assertEquals('baz', $container->get('foo_bar'));

        $this->assertTrue($container->has('foo_bar'));

        $container->set('foo_bar', 'qux');

        $this->assertEquals('qux', $container->get('foo_bar'));

        $this->assertEquals(['foo_bar' => 'qux'], $container->flatten());

        $container->forget('foo_bar');

        $this->assertFalse($container->has('foo_bar'));
    }

    /**
     * ----------------------------------------
     * Map
     * ----------------------------------------
     */

    public function testMapKey()
    {
        $container = container(['foo' => ['bar' => 'baz']]);

        $actual = $container->map('foo', function ($value) {
            return $value . 'bar';
        });

        $this->assertEquals(['bar' => 'bazbar'], $actual);
    }

    public function testMapMissingKey()
    {
        $container = container(['foo' => ['bar' => 'baz']]);

        $actual = $container->map('baz', function ($value) {
            return 'bar';
        });

        $this->assertEquals('bar', $actual);
    }

    public function testMap()
    {
        $container = container(['foo' => ['bar' => 'baz']]);

        $actual = $container->map(function ($value) {
            return ['bar' => $value['bar'] . 'bar'];
        });

        $this->assertEquals(['foo' => ['bar' => 'bazbar']], $actual);
    }

    public function testMapWhenDataIsArrayAccess()
    {
        $container = container(new ArrayObject(['foo' => ['bar' => 'baz']]));

        $actual = $container->map('foo', function ($value) {
            return $value . 'bar';
        });

        $this->assertEquals(['bar' => 'bazbar'], $actual);
    }
}