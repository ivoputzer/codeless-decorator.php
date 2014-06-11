<?php

require_once 'tests/include.php';

class DecoratorIntrospectorTest extends PHPUnit_Framework_TestCase {

    protected $introspector = null;

    public function setUp() {
        $this->introspector = new Decorator\Introspector;
    }

    public function tearDown() {
        $this->introspector = null;
    }

    public function testReflectors() {
        $reflector = $this->introspector->reflectionClass('Foo');
        $this->assertTrue($reflector instanceof ReflectionClass);

        $reflector = $this->introspector->reflectionMethod('Foo', 'bar');
        $this->assertTrue($reflector instanceof ReflectionMethod);

        $reflector = $this->introspector->reflectionProperty('Foo', '_foo');
        $this->assertTrue($reflector instanceof ReflectionProperty);

        $reflector = $this->introspector->reflectionFunction('foobar');
        $this->assertTrue($reflector instanceof ReflectionFunction);
    }

    public function testGetFunctions() {
        $functions = $this->introspector->getFunctions();
        $this->assertTrue(in_array('foobar', $functions));
    }

    public function testFunctionsAreUserDefined() {
        $functions = $this->introspector->getFunctions();
        $this->assertFalse(in_array('mysql_connect', $functions));
    }

    public function testGetClasses() {
        $classes = $this->introspector->getClasses();
        $this->assertTrue(in_array('Foo', $classes));
        $this->assertTrue(count($classes) < count(get_declared_classes()));
    }

    public function testClassesAreUserDefined() {
        $classes = $this->introspector->getClasses();
        $this->assertTrue(count($classes) < count(get_declared_classes()));
    }

    public function testGetMethods() {
        $methods = $this->introspector->getMethods('Foo');
        $this->assertEquals($methods, array('foo', 'bar'));
    }

    public function testGetProperties() {
        $properties = $this->introspector->getProperties('Foo');
        $this->assertEquals($properties, array('_foo'));
    }

    public function testParseAnnotations() {
        $foobar = new ReflectionFunction('foobar');
        $annotations = $this->introspector->parseAnnotations($foobar->getDocComment());
        $this->assertEquals($annotations, array('test' => 'arg', 'another' => null));
    }

    public function testGetFunctionAnnotations() {
        $annotations = $this->introspector->getFunctionAnnotations('foobar');
        $this->assertEquals($annotations, array('test' => 'arg', 'another' => null));
    }

    public function testGetFunctionsWithAnnotation() {
        $functions = $this->introspector->getFunctionsWithAnnotation('test');
        $this->assertEquals($functions, array('foobar'));

        $functions = $this->introspector->getFunctionsWithAnnotation('na');
        $this->assertEmpty($functions);
    }

    public function testGetClassAnnotations() {
        $annotations = $this->introspector->getClassAnnotations('Foo');
        $this->assertEquals($annotations, array('singleton' => null));

        $foo = new Foo;
        $annotations = $this->introspector->getClassAnnotations($foo);
        $this->assertEquals($annotations, array('singleton' => null));
    }

    public function testGetClassesWithAnnotation() {
        $classes = $this->introspector->getClassesWithAnnotation('singleton');
        $this->assertEquals($classes, array('Foo'));

        $classes = $this->introspector->getClassesWithAnnotation('na');
        $this->assertEmpty($classes);
    }

    public function testGetMethodAnnotations() {
        $annotations = $this->introspector->getMethodAnnotations('Foo', 'foo');
        $this->assertEquals($annotations, array('foo' => 'bar'));
    }

    public function testGetMethodsWithAnnotation() {
        $methods = $this->introspector->getMethodsWithAnnotation('Foo', 'foo');
        $this->assertEquals($methods, array('foo', 'bar'));

        $methods = $this->introspector->getMethodsWithAnnotation('foo', 'na');
        $this->assertEmpty($methods);
    }

    public function testGetPropertyAnnotations() {
        $annotations = $this->introspector->getPropertyAnnotations('Foo', '_foo');
        $this->assertEquals($annotations, array('foobar' => null));
    }

    public function testGetPropertiesWithAnnotation() {
        $properties = $this->introspector->getPropertiesWithAnnotation('Foo', 'foobar');
        $this->assertEquals($properties, array('_foo'));

        $properties = $this->introspector->getPropertiesWithAnnotation('foo', 'na');
        $this->assertEmpty($properties);
    }
}

