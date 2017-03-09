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

    /** @var []CPPClassMethod */
    private $methods;

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
        $kind = CPPClassKind::CONCRETE_CLASS,
        $inheritances = array(),
        $attributes = array(),
        $methods = array(),
        $conflicts = array()
    ) {
        $this->name = $name;
        $this->kind = $kind;
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
                if ($arg->getType() != $method->getArgument($key)->getType()) {
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

        $keyToRemove = null;
        foreach ($this->getMethods() as $key => $method) {
            if(!$m->isPureVirtual()) {
                continue;
            }

            if(!$method->isPureVirtual() && $method->isVirtual()) {
                continue;
            }

            if($this->isMethodTheSame($m, $method)) {
                $keyToRemove = $key;
                break;
            }
        }
        if($keyToRemove) {
            unset($this->methods[$keyToRemove]);
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
     * @param CPPClassMethod $method
     */
    public function addMethod(CPPClassMethod $method)
    {
        $this->methods[$method->getName()] = $method;
    }

    /**
     * @return CPPClassMethod[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @return CPPClassAttribute[]
     */
    public function getAttributes()
    {
        return $this->attributes;
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
                    if ($arg->getType() != $conflict->getArgument($key)->getType()) {
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
}