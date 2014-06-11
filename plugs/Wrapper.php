<?php namespace Decorator;

class Wrapper {

    protected $__class = null;
    protected $__methods = array();
    protected $__properties = array();

    public function __construct($class) {
        $this->__class = $class;
    }

    public function __setMethodOverrides($methods) {
        $this->__methods = $methods;
    }

    public function __setPropertyOverrides($properties) {
        $this->__properties = $properties;
    }

    public function __call($method, $args) {
        if (isset($this->__methods[$method])) {
            $method = $this->__methods[$method];
        } else {
            $method = array($this->__class, $method);
        }
        return call_user_func_array($method, $args);
    }

    public function __get($property) {
        return $this->__class->$property;
    }

    public function __set($property, $value) {
        if (isset($this->__properties[$property])) {
            $setter = $this->__properties[$property];
            $value = $setter($value);
        }
        return $this->__class->$property = $value;
    }

    public function __isset($property) {
        return isset($this->__class->$property);
    }

    public function __unset($property) {
        unset($this->__class->$property);
    }

    public function __toString() {
        return $this->__class->__toString();
    }

    public function __invoke($obj) {
        return $this->__class->__invoke($obj);
    }

    public static function __callStatic($method, $args) {
        return forward_static_call_array($method, $args);
    }

    public static function __set_state($array) {
        return $this->__class->__set_state($array);
    }

}

