<?php

class DecoratorFactoryTest extends PHPUnit_Framework_TestCase {

    protected $decorator = null;

    public function setUp() {
        $this->decorator = new Decorator\Factory;
    }

    public function tearDown() {
        $this->decorator = null;
    }

    public function testConstructorClosure() {
        $constructor = $this->decorator->getConstructorClosure('foo');
        $foo = $constructor();
        $this->assertTrue($foo instanceof Foo);
        $foo = $this->decorator->call($constructor);
        $this->assertTrue($foo instanceof Foo);
    }

    public function testSetterClosure() {
        $closure = $this->decorator->getSetterClosure();
        $this->assertEquals($closure(2), 2);
        $this->assertEquals($closure(3), 3);
        $this->assertEquals($closure('bar'), 'bar');
        $this->assertEquals($closure(null), null);
    }

    public function testAddingGlobalFunctionDecoratorsAtInstantiation() {
        $decorators = array('foo', 'bar');
        $this->decorator = new Decorator\Factory($decorators);
        $this->assertTrue($this->decorator->hasDecorator('foo'));
        $this->assertTrue($this->decorator->hasDecorator('bar'));
        $this->assertFalse($this->decorator->hasDecorator('unknown'));

        $this->assertEquals($this->decorator->getDecorator('foo'), 'foo');
        $this->assertEquals($this->decorator->getDecorator('bar'), 'bar');

        $decorators = $this->decorator->getDecorators();
        $this->assertEquals(array_values($decorators), array('foo', 'bar'));
        $this->assertEquals(array_keys($decorators), array('foo', 'bar'));
    }

    public function testFunctionDecorators() {
        $this->decorator->addDecorator('returnformat', function ($callback, $format) {
            return function () use ($callback, $format) {
                $return = call_user_func_array($callback, func_get_args());
                return str_replace('%s', $return, $format);
            };
        });

        $result = $this->decorator->invoke('returnvalue2', 'foo');
        $this->assertEquals($result, '<pre>foo</pre>');

        $this->decorator->addArgumentDecorator('barinsteadoffoo', function ($args) {
            $args[0] = 'bar';
            return $args;
        });

        $result = $this->decorator->invoke('returnvalue2', 'foo');
        $this->assertEquals($result, '<pre>bar</pre>');

        $this->decorator->addReturnDecorator('reverseresult', function ($return) {
            return strrev($return);
        });

        $result = $this->decorator->invoke('returnvalue2', 'foo');
        $this->assertEquals($result, '<pre>rab</pre>');
    }

}
