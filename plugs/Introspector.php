<?php namespace Decorator;

class Introspector {

    /**
     * An annotation from a docblock => "@annotation [arg]"
     */
    const ANNOTATION = '/\* @(\S++)(?: (.++))?/';

    /**
     * Reflection constants used by `getMethods()` and `getProperties()`.
     */
    const IS_STATIC = 1;
    const IS_ABSTRACT = 2;
    const IS_FINAL = 4;
    const IS_PUBLIC = 256;
    const IS_PROTECTED = 512;
    const IS_PRIVATE = 1024;

    /**
     * The list of user-defined classes and functions.
     */
    protected $_userClasses = null;
    protected $_userFunctions = null;

    /**
     * Reflector singletons.
     */
    protected $_reflectionFunctions = array();
    protected $_reflectionClasses = array();
    protected $_reflectionMethods = array();
    protected $_reflectionProperties = array();

    /**
     * Function/class/method/property annotations.
     */
    protected $_annotations = array();

    /**
     * Get a class reflector.
     *
     * @param String|Object $class
     * @return ReflectionClass $reflector
     */
    public function reflectionClass($class) {
        if (is_object($class)) {
            $class = get_class($class);
        }
        if (!isset($this->_reflectionClasses[$class])) {
            $this->_reflectionClasses[$class]
                = new \ReflectionClass($class);
        }
        return $this->_reflectionClasses[$class];
    }

    /**
     * Get a function reflector.
     *
     * @param String|Array|Closure $function
     * @return ReflectionFunction $reflector
     */
    public function reflectionFunction($function) {
        if (!isset($this->_reflectionFunctions[$function])) {
            $this->_reflectionFunctions[$function]
                = new \ReflectionFunction($function);
        }
        return $this->_reflectionFunctions[$function];
    }

    /**
     * Get a property reflector.
     *
     * @param String|Object $class
     * @param String $property
     * @return ReflectionProperty $reflector
     */
    public function reflectionProperty($class, $property) {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $id = "$class->$property";
        if (!isset($this->_reflectionProperties[$id])) {
            $this->_reflectionProperties[$id]
                = new \ReflectionProperty($class, $property);
        }
        return $this->_reflectionProperties[$id];
    }

    /**
     * Get a method reflector.
     *
     * @param String|Object $class
     * @param String $method
     * @return ReflectionMethod $reflector
     */
    public function reflectionMethod($class, $method) {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $id = "$class->$method";
        if (!isset($this->_reflectionMethods[$id])) {
            $this->_reflectionMethods[$id]
                = new \ReflectionMethod($class, $method);
        }
        return $this->_reflectionMethods[$id];
    }

    /**
     * Gets an array of user-defined functions.
     *
     * @return Array $function_list
     */
    public function getFunctions() {
        if (null === $this->_userFunctions) {
            $functions = get_defined_functions();
            $this->_userFunctions = $functions['user'];
        }
        return $this->_userFunctions;
    }

    /**
     * Gets an array of user-defined classes.
     *
     * @return Array $class_list
     */
    public function getClasses() {
        if (null === $this->_userClasses) {
            $this->_userClasses = array();
            foreach (get_declared_classes() as $class) {
                $reflector = $this->reflectionClass($class);
                if ($reflector->isUserDefined()) {
                    $this->_userClasses[] = $class;
                }
            }
        }
        return $this->_userClasses;
    }

    /**
     * Gets an array of class methods.
     *
     * @param String|Object $class
     * @param Integer $filter - see the Reflection constants
     * @return Array $method_list
     */
    public function getMethods($class, $filter = null) {
        $methods = array();
        $class = $this->reflectionClass($class);
        $classMethods = null !== $filter
                         ? $class->getMethods($filter)
                         : $class->getMethods();
        foreach ($classMethods as $method) {
            $methods[] = $method->getName();
        }
        return $methods;
    }

    /**
     * Gets an array of class properties.
     *
     * @param String|Object $class
     * @param Integer $filter - see the Reflection constants
     * @return Array $property_list
     */
    public function getProperties($class, $filter = null) {
        $properties = array();
        $class = $this->reflectionClass($class);
        $classProperties = null !== $filter
                         ? $class->getProperties($filter)
                         : $class->getProperties();
        foreach ($classProperties as $property) {
            $properties[] = $property->getName();
        }
        return $properties;
    }

    /**
     * Parse all annotations from a docblock.
     *
     * @param String $docblock
     * @return Array $annotations
     */
    public function parseAnnotations($docblock) {
        $annotations = array();
        preg_match_all(self::ANNOTATION, $docblock, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $annotations[$match[1]] = isset($match[2]) ? $match[2] : null;
        }
        return $annotations;
    }

    /**
     * Parse annotations from a function's docblock.
     *
     * @param String|Array|Closure $function
     * @return Array $annotations
     */
    public function getFunctionAnnotations($function) {
        if (!isset($this->_annotations[$function])) {
            $reflector = $this->reflectionFunction($function);
            $docblock = $reflector->getDocComment();
            $annotations = $this->parseAnnotations($docblock);
            $this->_annotations[$function] = $annotations;
        }
        return $this->_annotations[$function];
    }

    /**
     * Parse annotations from a class's docblock.
     *
     * @param String|Object $class
     * @return Array $annotations
     */
    public function getClassAnnotations($class) {
        if (is_object($class)) {
            $class = get_class($class);
        }
        if (!isset($this->_annotations[$class])) {
            $reflector = $this->reflectionClass($class);
            $docblock = $reflector->getDocComment();
            $annotations = $this->parseAnnotations($docblock);
            $this->_annotations[$class] = $annotations;
        }
        return $this->_annotations[$class];
    }

    /**
     * Parse annotations from a class method's docblock.
     *
     * @param String|Object $class
     * @param String $method
     * @return Array $annotations
     */
    public function getMethodAnnotations($class, $method) {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $id = "$class->$method";
        if (!isset($this->_annotations[$id])) {
            $reflector = $this->reflectionMethod($class, $method);
            $docblock = $reflector->getDocComment();
            $annotations = $this->parseAnnotations($docblock);
            $this->_annotations[$id] = $annotations;
        }
        return $this->_annotations[$id];
    }

    /**
     * Parse annotations from a class property's docblock.
     *
     * @param String|Object $class
     * @param String $property
     * @return Array $annotations
     */
    public function getPropertyAnnotations($class, $property) {
        if (is_object($class)) {
            $class = get_class($class);
        }
        $id = "$class->$property";
        if (!isset($this->_annotations[$id])) {
            $reflector = $this->reflectionProperty($class, $property);
            $docblock = $reflector->getDocComment();
            $annotations = $this->parseAnnotations($docblock);
            $this->_annotations[$id] = $annotations;
        }
        return $this->_annotations[$id];
    }

    /**
     * Find user-defined functions with the specified annotation.
     *
     * @param String $annotation
     * @return Array $functions
     */
    public function getFunctionsWithAnnotation($annotation) {
        $result = array();
        foreach ($this->getFunctions() as $function) {
            $annotations = $this->getFunctionAnnotations($function);
            if (array_key_exists($annotation, $annotations)) {
                $result[] = $function;
            }
        }
        return $result;
    }

    /**
     * Find user-defined classes with the specified annotation.
     *
     * @param String $annotation
     * @return Array $functions
     */
    public function getClassesWithAnnotation($annotation) {
        $result = array();
        foreach ($this->getClasses() as $class) {
            $annotations = $this->getClassAnnotations($class);
            if (array_key_exists($annotation, $annotations)) {
                $result[] = $class;
            }
        }
        return $result;
    }

    /**
     * Find class methods with the specified annotation.
     *
     * @param String|Object $class
     * @param String $annotation
     * @return Array $methods
     */
    public function getMethodsWithAnnotation($class, $annotation) {
        $result = array();
        foreach ($this->getMethods($class) as $method) {
            $annotations = $this->getMethodAnnotations($class, $method);
            if (array_key_exists($annotation, $annotations)) {
                $result[] = $method;
            }
        }
        return $result;
    }

    /**
     * Find class properties with the specified annotation.
     *
     * @param String|Object $class
     * @param String $annotation
     * @return Array $functions
     */
    public function getPropertiesWithAnnotation($class, $annotation) {
        $result = array();
        foreach ($this->getProperties($class) as $property) {
            $annotations = $this->getPropertyAnnotations($class, $property);
            if (array_key_exists($annotation, $annotations)) {
                $result[] = $property;
            }
        }
        return $result;
    }

}

