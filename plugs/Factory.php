<?php namespace Decorator;

class Factory extends Introspector {

    /**
     * Decorator functions.
     */
    protected $_decorators = array();

    /**
     * Setup the decorator factory.
     *
     * @param Array|Object $decorators
     */
    public function __construct($decorators = null) {
        if (null !== $decorators) {
            $this->addDecorators($decorators);
        }
    }

    /**
     * Add a decorator function. Usage:
     *
     *    addDecorator('foo', function(){});
     *    addDecorator('foo', $decorator_class, 'some_method');
     *    addDecorator('foo', $decorator_obj, 'some_property');
     *    addDecorator('foo', $decorator_array, 'some_key');
     *    addDecorator('foo'); function foo(){}
     *
     * @param String $name
     * @param Array|Object|Closure $decorator
     * @param String $key (optional)
     * @return Closure $decorator
     */
    public function addDecorator($name, $decorator = null, $key = null) {
        if (is_object($decorator) && !is_callable($decorator)) {
            if ($decorator instanceof StdClass) {
                $decorator = $decorator->$key;
            } else {
                $decorator = array($decorator, $key);
            }
        } elseif (is_array($decorator)) {
            $decorator = $decorator[$key];
        } elseif (null === $decorator) {
            $decorator = $name;
        }
        return $this->_decorators[$name] = $decorator;
    }

    /**
     * Add a decorator that invokes $decorator before a function.
     *
     * @param String $name
     * @param String|Array|Closure $decorator
     * @return Closure $argument_decorator
     */
    public function addPreDecorator($name, $decorator = null) {
        if (null === $decorator) {
            $decorator = $name;
        }
        $decorator = $this->createPreDecorator($decorator);
        return $this->addDecorator($name, $decorator);
    }

    /**
     * Add a decorator that invokes $decorator after a function.
     *
     * @param String $name
     * @param String|Array|Closure $decorator
     * @return Closure $return_decorator
     */
    public function addPostDecorator($name, $decorator = null) {
        if (null === $decorator) {
            $decorator = $name;
        }
        $decorator = $this->createPostDecorator($decorator);
        return $this->addDecorator($name, $decorator);
    }

    /**
     * Add a decorator that modifies a function's arguments.
     *
     * @param String $name
     * @param String|Array|Closure $decorator
     * @return Closure $argument_decorator
     */
    public function addArgumentDecorator($name, $decorator = null) {
        if (null === $decorator) {
            $decorator = $name;
        }
        $decorator = $this->createArgumentDecorator($decorator);
        return $this->addDecorator($name, $decorator);
    }

    /**
     * Add a decorator that modifies a function's return value.
     *
     * @param String $name
     * @param String|Array|Closure $decorator
     * @return Closure $return_decorator
     */
    public function addReturnDecorator($name, $decorator = null) {
        if (null === $decorator) {
            $decorator = $name;
        }
        $decorator = $this->createReturnDecorator($decorator);
        return $this->addDecorator($name, $decorator);
    }

    /**
     * Extract decorators from an object, class or array.
     *
     * @param Array|Object $decorators
     * @return Array $decorators
     */
    public function extractDecorators($decorators) {
        if ($decorators instanceof StdClass) {
            $decorators = (array)$decorators;
        } elseif (is_object($decorators)) {
            $class = $decorators;
            $decorators = array();
            foreach ($this->getMethods($class) as $method) {
                $decorators[$method] = array($class, $method);
            }
        } elseif (isset($decorators[0])) {
            $functions = array();
            foreach ($decorators as $function) {
                $functions[$function] = $function;
            }
            $decorators = $functions;
        }
        return $decorators;
    }

    /**
     * Add multiple decorators.
     *
     * @param Array|Object $decorators
     */
    public function addDecorators($decorators) {
        foreach ($this->extractDecorators($decorators) as $name => $decorator) {
            $this->_decorators[$name] = $decorator;
        }
    }

    /**
     * Add multiple decorators.
     *
     * @param Array|Object $decorators
     */
    public function addPreDecorators($decorators) {
        foreach ($this->extractDecorators($decorators) as $name => $decorator) {
            $this->addPreDecorator($name, $decorator);
        }
    }

    /**
     * Add multiple decorators.
     *
     * @param Array|Object $decorators
     */
    public function addPostDecorators($decorators) {
        foreach ($this->extractDecorators($decorators) as $name => $decorator) {
            $this->addPostDecorator($name, $decorator);
        }
    }

    /**
     * Add multiple decorators.
     *
     * @param Array|Object $decorators
     */
    public function addArgumentDecorators($decorators) {
        foreach ($this->extractDecorators($decorators) as $name => $decorator) {
            $this->addArgumentDecorator($name, $decorator);
        }
    }

    /**
     * Add multiple decorators.
     *
     * @param Array|Object $decorators
     */
    public function addReturnDecorators($decorators) {
        foreach ($this->extractDecorators($decorators) as $name => $decorator) {
            $this->addReturnDecorator($name, $decorator);
        }
    }

    /**
     * Get a decorator by name.
     *
     * @param String $name
     * @return Closure $decorator
     */
    public function getDecorator($name) {
        return $this->_decorators[$name];
    }

    /**
     * Get all decorators.
     *
     * @return Array $decorators
     */
    public function getDecorators() {
        return $this->_decorators;
    }

    /**
     * Check whether a decorator has been added.
     *
     * @param String $name
     * @return Boolean $exists
     */
    public function hasDecorator($name) {
        return isset($this->_decorators[$name]);
    }

    /**
     * Call a function with the specified arguments.
     *
     * @param String|Array|Closure $function
     * @return Mixed $result
     */
    public function call($function /*, $arg1, $arg2, $argN */) {
        $args = array_slice(func_get_args(), 1);
        return $this->apply($function, $args);
    }

    /**
     * Call a function with a variable number of arguments.
     *
     * @param String|Array|Closure $function
     * @param Array $args (optional)
     * @return Mixed $result
     */
    public function apply($function, array $args = array()) {
        switch (count($args)) {
            case 0:  return $function();
            case 1:  return $function($args[0]);
            case 2:  return $function($args[0], $args[1]);
            case 3:  return $function($args[0], $args[1], $args[2]);
            case 4:  return $function($args[0], $args[1], $args[2], $args[3]);
            default: return call_user_func_array($function, $args);
        }
    }

    /**
     * Decorate and instantiate a class.
     *
     * @param String|Object $class
     * @return Object $instance
     */
    public function instantiate($class /*, $arg1, $arg2, $argN */) {
        $args = array_slice(func_get_args(), 1);
        $constructor = $this->decorateClass($class);
        return $this->apply($constructor, $args);
    }

    /**
     * Decorate and invoke a function.
     *
     * @param String|Object $function
     * @return Object $instance
     */
    public function invoke($function /*, $arg1, $arg2, $argN */) {
        $args = array_slice(func_get_args(), 1);
        $function = $this->decorateFunction($function);
        return $this->apply($function, $args);
    }

    /**
     * Create an anonymous function that is equivalent to a class method.
     *
     * @param String|Object $class
     * @param String $method
     * @return Closure $closure
     */
    public function getMethodClosure($class, $method) {
        if ($method === '__construct') {
            return $this->getConstructorClosure($class);
        }
        return function () use ($class, $method) {
            return call_user_func_array(
                array($class, $method), func_get_args()
            );
        };
    }

    /**
     * Create an anonymous function that is equivalent to a class constructor.
     *
     * @param String|Object $class
     * @return Closure $closure
     */
    public function getConstructorClosure($class) {
        $reflector = $this->reflectionClass($class);
        return function () use ($reflector) {
            return $reflector->newInstanceArgs(func_get_args());
        };
    }

    /**
     * Create an anonymous function that returns its first argument.
     *
     * @return Closure $closure
     */
    public function getSetterClosure() {
        return function ($value) {
            return $value;
        };
    }

    /**
     * Decorate a function, method or closure.
     *
     * @param String|Array|Closure $function
     * @param Array $annotations - in the form of array($decorator => $arg);
     * @return Closure $function
     */
    protected function decorate($function, $annotations) {
        foreach ($annotations as $name => $arg) {
            if ($this->hasDecorator($name)) {
                $decorator = $this->getDecorator($name);
                $function = call_user_func($decorator, $function, $arg);
            }
        }
        return $function;
    }

    /**
     * Decorate a constructor based on class annotations.
     *
     * @param String|Object $class
     * @return Closure $constructor
     */
    public function decorateConstructor($class) {
        $constructor = $this->getConstructorClosure($class);
        $annotations = $this->getClassAnnotations($class);
        return $this->decorate($constructor, $annotations);
    }

    /**
     * Decorate a function.
     *
     * @param String|Array|Closure $function
     * @return Closure $decorated_function
     */
    public function decorateFunction($function) {
        $annotations = $this->getFunctionAnnotations($function);
        return $this->decorate($function, $annotations);
    }

    /**
     * Decorate a class method.
     *
     * @param String|Object $class
     * @param String $method
     * @return Closure $decorated_method
     */
    public function decorateMethod($class, $method) {
        $annotations = $this->getMethodAnnotations($class, $method);
        $function = $this->getMethodClosure($class, $method);
        return $this->decorate($function, $annotations);
    }

    /**
     * Decorate an array of functions.
     *
     * @param Array $functions
     * @return Array $decorated_functions
     */
    public function decorateFunctions(array $functions) {
        $decorated = array();
        foreach ($functions as $function) {
            $decorated[] = $this->decorateFunction($function);
        }
        return $decorated;
    }

    /**
     * Decorate all class methods. Note that this will return a closure
     * representing each method in the class, even if it has no annotations.
     * Also see `decorateAnnotatedMethods()`.
     *
     * @param String|Object $class
     * @return Array $decorated_methods
     */
    public function decorateMethods($class) {
        $decorated = array();
        foreach ($this->getMethods($class) as $method) {
            $decorated[$method] = $this->decorateMethod($class, $method);
        }
        return $decorated;
    }

    /**
     * Determines whether any class properties or methods have decoraters.
     *
     * @param String|Object $class
     * @return Boolean $has_decorated
     */
    public function hasDecoratedMembers($class) {
        foreach ($this->getMethods($class) as $method) {
            $annotations = $this->getMethodAnnotations($class, $method);
            foreach ($annotations as $decorator => $arg) {
                if ($this->hasDecorator($decorator)) {
                    return true;
                }
            }
        }
        foreach ($this->getProperties($class) as $property) {
            $annotations = $this->getPropertyAnnotations($class, $property);
            foreach ($annotations as $decorator => $arg) {
                if ($this->hasDecorator($decorator)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Decorate all class methods that have one or more decorators.
     *
     * @param String|Object $class
     * @return Array $decorated_methods
     */
    public function decorateAnnotatedMethods($class) {
        $methods = array();
        foreach ($this->getMethods($class) as $method) {
            $annotations = $this->getMethodAnnotations($class, $method);
            if (!empty($annotations)) {
                $function = $this->getMethodClosure($class, $method);
                $methods[$method] = $this->decorate($function, $annotations);
            }
        }
        return $methods;
    }

    /**
     * Decorate all class properties (setters) that have one or more
     * decorators.
     *
     * @param String|Object $class
     * @return Array $decorated_properties
     */
    public function decorateAnnotatedProperties($class) {
        $properties = array();
        foreach ($this->getProperties($class) as $property) {
            $annotations = $this->getPropertyAnnotations($class, $property);
            if (!empty($annotations)) {
                $function = $this->getSetterClosure();
                $properties[$property] = $this->decorate(
                    $function, $annotations
                );
            }
        }
        return $properties;
    }

    /**
     * Decorate a class, its methods and its properties.
     *
     * @param String|Object $class
     */
    public function decorateClass($class) {
        $constructor = $this->decorateConstructor($class);

        //If there are no decorated methods or properties, we can just
        //decorate the constructor and return
        if (!$this->hasDecoratedMembers($class)) {
            return $constructor;
        }

        $self = $this;

        //Otherwise, decorate properties and methods at instantiation
        return function () use ($self, $class, $constructor) {
            $instance = $self->apply($constructor, func_get_args());

            //Decorate annotated class methods and properties
            $methods = $self->decorateAnnotatedMethods($instance);
            $properties = $self->decorateAnnotatedProperties($instance);

            //Wrap the instance in a Decorator\Wrapper
            $instance = new Wrapper($instance);

            //The instance will behave exactly like the original class
            //through duck-typing, but certain method calls and property
            //sets will be intercepted and overriden
            $instance->__setMethodOverrides($methods);
            $instance->__setPropertyOverrides($properties);

            return $instance;
        };
    }

    /**
     * Create a decorator that modifies a function's arguments.
     *
     * @param String|Array|Closure $decorator
     * @return Closure $argument_decorator
     */
    public function createArgumentDecorator($decorator) {
        return function ($callback, $decorator_arg) use ($decorator) {
            return function () use ($decorator, $callback, $decorator_arg) {
                $args = func_get_args();
                $args = call_user_func($decorator, $args);
                return call_user_func_array($callback, $args);
            };
        };
    }

    /**
     * Create a decorator that modifies a function's return value.
     *
     * @param String|Array|Closure $decorator
     * @return Closure $return_decorator
     */
    public function createReturnDecorator($decorator) {
        return function ($callback, $decorator_arg) use ($decorator) {
            return function () use ($decorator, $callback, $decorator_arg) {
                $args = func_get_args();
                $return = call_user_func_array($callback, $args);
                return call_user_func($decorator, $return);
            };
        };
    }

    /**
     * Create a decorator that invokes $decorator before a function.
     *
     * @param String|Array|Closure $decorator
     * @return Closure $pre_decorator
     */
    public function createPreDecorator($decorator) {
        return function ($callback, $decorator_arg) use ($decorator) {
            return function () use ($decorator, $callback, $decorator_arg) {
                $args = func_get_args();
                $continue = call_user_func($decorator, $args);
                if (false !== $continue) {
                    return call_user_func_array($callback, $args);
                }
            };
        };
    }

    /**
     * Create a decorator that invokes $decorator after a function.
     *
     * @param String|Array|Closure $decorator
     * @return Closure $pre_decorator
     */
    public function createPostDecorator($decorator) {
        return function ($callback, $decorator_arg) use ($decorator) {
            return function () use ($decorator, $callback, $decorator_arg) {
                $args = func_get_args();
                $return = call_user_func_array($callback, $args);
                $continue = call_user_func($decorator, $return);
                if (false !== $continue) {
                    return $return;
                }
            };
        };
    }

    /**
     * Get all user-defined functions that have decorators defined. Note that
     * this method does not actually decorate the functions.
     *
     * @return Array $decorated_functions
     */
    public function getFunctionsWithDecorators() {
        $decorated = array();
        foreach ($this->getFunctions() as $function) {
            $annotations = $this->getFunctionAnnotations($function);
            foreach ($annotations as $decorator => $arg) {
                if ($this->hasDecorator($decorator)) {
                    $decorated[] = $function;
                    break;
                }
            }
        }
        return $decorated;
    }

    /**
     * Decorate and invoke functions with the specified annotation.
     *
     * @param String $decorator
     * @return Integer $invoked
     */
    public function invokeFunctionsWithDecorator($decorator /*, $args */) {
        $args = array_slice(func_get_args(), 1);
        $functions = $this->getFunctionsWithAnnotation($decorator);
        $functions = $this->decorateFunctions($functions);
        $invoked = 0;
        foreach ($functions as $function) {
            $this->apply($function, $args);
            $invoked++;
        }
        return $invoked;
    }

    /**
     * Invoke all decorated functions.
     *
     * @return Integer $invoked
     */
    public function invokeDecoratedFunctions(/* arg1, $arg2, $argN */) {
        $args = func_get_args();
        $invoked = 0;
        foreach ($this->getFunctionsWithDecorators() as $function) {
            $function = $this->decorateFunction($function);
            $this->apply($function, $args);
            $invoked++;
        }
        return $invoked;
    }

}

