<?php

/*
 * This file is part of ldrahnik/ipp_1_project.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>, <xdrahn00@stud.fit.vutbr.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CLS\CPP\Structure\Object;

use CLS\CPP\Structure\Object\Type\CPPClassKind;

/**
 * Class CPPClass.
 */
class CPPClass
{

    /** @var string */
    private $name;

    /** @var string */
    private $kind;

    /** @var []CPPInheritance */
    private $inheritances;

    /** @var []CPPClassAttribute */
    private $attributes;

    /** @var []CPPClassAttribute */
    private $hiddenAttributes = array();

    /** @var []CPPClassMethod */
    private $methods;

    /** @var []CPPClassMethod */
    private $hiddenMethods = array();

    /** @var []CPPClassElement */
    private $conflicts;

    /**
     * CPPClass constructor.
     *
     * @param string $name
     * @param string $kind
     * @param array $inheritances
     * @param array $attributes
     * @param array $methods
     * @param array $conflicts
     */
    function __construct(
        $name,
        $kind = null,
        $inheritances = array(),
        $attributes = array(),
        $methods = array(),
        $conflicts = array()
    ) {
        $this->name = $name;
        $this->kind = $kind ? $kind : CPPClassKind::CONCRETE_CLASS;
        $this->attributes = $attributes;
        $this->methods = $methods;
        $this->inheritances = $inheritances;
        $this->conflicts = $conflicts;
    }

    /**
     * @param CPPClassMethod $m
     *
     * @return bool|CPPClassMethod
     */
    public function methodExist(CPPClassMethod $m)
    {
        if (!array_key_exists($m->getName(), $this->methods)) {
            return false;
        }

        foreach ($this->getMethods() as $key => $method) {
            if($this->isMethodTheSame($m, $method)) {
                return $method;
            }
        }
        return false;
    }

    /**
     * @param CPPClassMethod $m
     *
     * @return bool|CPPClassMethod
     */
    public function methodForConflictExist(CPPClassMethod $m)
    {
        if (!array_key_exists($m->getName(), $this->methods) && !array_key_exists($m->getName(), $this->hiddenMethods)) {
            return false;
        }

        foreach ($this->getMethods() as $key => $method) {
            if($this->isMethodTheSame($m, $method)) {
                return $method;
            }
        }
        foreach ($this->getHiddenMethods() as $key => $method) {
            if($this->isMethodTheSame($m, $method)) {
                return $method;
            }
        }
        return false;
    }

    /**
     * @param CPPClassMethod $m
     * @param CPPClassMethod $method
     *
     * @return bool
     */
    public function isMethodTheSame($m, $method)
    {
        /** @var $method CPPClassMethod */
        if ($m->getName() == $method->getName()) {
            if (count($m->getArguments()) != count($method->getArguments())) {
                return false;
            }
            $argumentsAreTheSame = true;
            foreach ($m->getArguments() as $key => $arg) {
                if ($method->getArgument($key) && $arg->getType() != $method->getArgument($key)->getType()) {
                    $argumentsAreTheSame = false;
                }
            }
            if($argumentsAreTheSame) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param CPPClassMethod $m
     *
     * @return boolean|CPPClassMethod
     */
    public function isMethodRewritten($m)
    {
        if (!array_key_exists($m->getName(), $this->methods)) {
            return false;
        }

        foreach ($this->getMethods() as $key => $method) {
            if($method->getFromInheritanceClassName() == $m->getFromInheritanceClassName()) {
                continue;
            }
            if($this->isMethodTheSame($m, $method)) {
                return $method;
            }
        }
        return false;
    }

    /**
     * @param string $attributeName
     */
    public function removeAttribute($attributeName)
    {
        foreach($this->attributes as $index => $attribute) {
            if($attribute->getName() == $attributeName) {
                unset($this->attributes[$index]);
            }
        }
    }

    /**
     * @param CPPClassMethod $m
     *
     * @return boolean|CPPClassMethod
     */
    public function removeMethod($m)
    {
        if (!array_key_exists($m->getName(), $this->methods)) {
            return false;
        }

        $methodToRemove = null;
        foreach ($this->getMethods() as $key => $method) {
            if(!$m->isPureVirtual()) {
                continue;
            }

            if(!$method->isPureVirtual() && $method->isVirtual()) {
                continue;
            }

            if($this->isMethodTheSame($m, $method)) {
                $methodToRemove = $method;
                break;
            }
        }
        if($methodToRemove) {
            unset($this->methods[$methodToRemove->getName()][count($methodToRemove->getArguments())]);
            return true;
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @return bool|CPPClassAttribute
     */
    public function attributeExist($name)
    {
        foreach($this->getAttributes() as $attribute) {
            if($attribute->getName() == $name) {
                return $attribute;
            }
        }
        return false;
    }

    /**
     * @param string $name
     *
     * @return bool|CPPClassAttribute
     */
    public function attributeForConflictExist($name)
    {
        foreach($this->getAttributes() as $attribute) {
            if($attribute->getName() == $name) {
                return $attribute;
            }
        }
        foreach($this->getHiddenAttributes() as $attribute) {
            if($attribute->getName() == $name) {
                return $attribute;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isVirtual()
    {
        foreach($this->getMethods() as $method) {
            if($method->isVirtual()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param CPPInheritance $inheritance
     */
    public function addInheritance(CPPInheritance $inheritance)
    {
        $this->inheritances[] = $inheritance;
    }

    /**
     * @param CPPClassAttribute $attribute
     */
    public function addAttribute(CPPClassAttribute $attribute)
    {
        $this->attributes[] = $attribute;
    }

    /**
     * @param CPPClassAttribute $attribute
     */
    public function addHiddenAttribute(CPPClassAttribute $attribute)
    {
        $this->hiddenAttributes[] = $attribute;
    }

    /**
     * @param CPPClassMethod $method
     */
    public function addMethod(CPPClassMethod $method)
    {
        $this->methods[$method->getName()][count($method->getArguments())] = $method;
    }

    /**
     * @param CPPClassMethod $method
     */
    public function addHiddenMethod(CPPClassMethod $method)
    {
        $this->hiddenMethods[$method->getName()][count($method->getArguments())] = $method;
    }

    /**
     * @return CPPClassMethod[]
     */
    public function getMethods()
    {
        $result = array();
        foreach($this->methods as $name => $methods) {
            $result = array_merge($result, $methods);
        }
        return $result;
    }

    /**
     * @return CPPClassMethod[]
     */
    public function getHiddenMethods()
    {
        $result = array();
        foreach($this->hiddenMethods as $name => $methods) {
            $result = array_merge($result, $methods);
        }
        return $result;
    }

    /**
     * @return CPPClassAttribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return CPPClassAttribute[]
     */
    public function getHiddenAttributes()
    {
        return $this->hiddenAttributes;
    }

    /**
     * @return CPPInheritance[]
     */
    public function getInheritances()
    {
        return $this->inheritances;
    }

    /**
     * @return string
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $kind
     */
    public function setKind($kind)
    {
        $this->kind = $kind;
    }

    /**
     * @param CPPClassElement $conflict
     */
    public function addConflict(CPPClassElement $conflict)
    {
        if(!$this->existConflict($conflict)) {
            $this->conflicts[] = $conflict;
        }
    }

    /**
     *
     * Compare two conflicts, if are the same, is added only one of them. (for example diamond problem with inheritance)
     *
     * @param CPPClassElement $c
     *
     * @return bool
     */
    public function existConflict(CPPClassElement $c)
    {
        foreach($this->conflicts as $conflict) {
            if($c->getFromInheritanceClassName() != $conflict->getFromInheritanceClassName()) {
                continue;
            }
            if($c->getName() != $conflict->getName()) {
                continue;
            }
            if($conflict instanceof CPPClassMethod && $c instanceof CPPClassMethod) {
                /** @var $c CPPClassMethod */
                /** @var $conflict CPPClassMethod */
                if (count($c->getArguments()) != count($conflict->getArguments())) {
                    continue;
                }
                $argumentsAreTheSame = true;
                foreach ($c->getArguments() as $key => $arg) {
                    if ($conflict->getArgument($key) && $arg->getType() != $conflict->getArgument($key)->getType()) {
                        continue;
                    }
                }
                if($argumentsAreTheSame) {
                    return true;
                }
            } else if ($conflict instanceof CPPClassAttribute && $c instanceof CPPClassAttribute) {
                /** @var $c CPPClassAttribute */
                /** @var $conflict CPPClassAttribute */
                return true;
            }
        }
        return false;
    }

    /**
     * @return CPPClassElement[]
     */
    public function getConflicts()
    {
        return $this->conflicts;
    }

    /**
     * @return CPPClassAttribute[]
     */
    public function getAttributesWithNoConflicts()
    {
        $result = array();
        foreach($this->getAttributes() as $attribute) {
            if(!$this->existConflict($attribute)) {
                $result[$attribute->getName()] = $attribute;
            }
        }
        return $result;
    }

    /**
     * @return CPPClassMethod[]
     */
    public function getMethodsWithNoConflicts()
    {
        $result = array();
        foreach($this->getMethods() as $method) {
            if(!$this->existConflict($method)) {
                $result[] = $method;
            }
        }
        return $result;
    }

}