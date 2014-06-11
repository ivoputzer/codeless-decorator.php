<?php

/**
 * @test arg
 * @another
 */
function foobar() {}

/**
 * @multlplyresultby 2
 */
function returnValue($value) {
    return $value;
}

/**
 * @reverseresult
 * @barinsteadoffoo
 * @returnformat <pre>%s</pre>
 */
function returnValue2($value) {
    return $value;
}

/**
 * @singleton
 */
class Foo {

    /**
     * @foobar
     */
    protected $_foo;

    /**
     * @foo bar
     */
    public function foo() {}

    /**
     * @foo
     * @bar foo
     */
    protected function bar() {}

}

