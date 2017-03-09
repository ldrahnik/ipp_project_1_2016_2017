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

use CLS\CPP\Structure\Object\Type\CPPPrivacy;

/**
 * Class ClassMethod.
 */
class CPPClassMethod implements CPPClassElement
{

    /** @var CPPClassMethod[] */
    private $arguments;

    /** @var boolean */
    private $virtual;

    /** @var string */
    private $name;

    /** @var string */
    private $type;

    /** @var string */
    private $scope;

    /** @var string */
    private $privacy;

    /** @var string */
    private $fromInheritanceClassName;

    /** @var boolean */
    private $pureVirtual;

    /**
     * CPPClassMethod constructor.
     *
     * @param string $name
     * @param string $type
     * @param string $scope
     * @param string $privacy
     * @param array $arguments
     * @param boolean $virtual
     * @param string $fromInheritanceClassName
     * @param boolean $pureVirtual
     */
    public function __construct($name, $type = null, $scope = null, $privacy = CPPPrivacy::PRIVATE_TYPE, $arguments = array(), $virtual = false, $fromInheritanceClassName = null, $pureVirtual = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->scope = $scope;
        $this->privacy = $privacy ? $privacy : CPPPrivacy::PRIVATE_TYPE;
        $this->arguments = $arguments;
        $this->virtual = $virtual;
        $this->fromInheritanceClassName = $fromInheritanceClassName;
        $this->pureVirtual = $pureVirtual;
    }

    /**
     * @param string $className
     */
    public function setFromInheritanceClassName($className)
    {
        $this->fromInheritanceClassName = $className;
    }

    /**
     * @return string
     */
    public function getFromInheritanceClassName()
    {
        return $this->fromInheritanceClassName;
    }

    /**
     * @return bool
     */
    public function isVirtual()
    {
        return $this->virtual;
    }

    /**
     * @param int $index
     *
     * @return CPPClassMethodArgument
     */
    public function getArgument($index)
    {
        return $this->arguments[$index];
    }

    /**
     * @return CPPClassMethodArgument[]
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @return string
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param bool $virtual
     */
    public function setVirtual($virtual)
    {
        $this->virtual = $virtual;
    }

    /**
     * @param CPPClassMethodArgument $argument
     */
    public function addArgument(CPPClassMethodArgument $argument)
    {
        $this->arguments[$argument->getName()] = $argument;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return boolean
     */
    public function getPureVirtual()
    {
        return $this->pureVirtual;
    }

    /**
     * @param bool $pureVirtual
     */
    public function setPureVirtual($pureVirtual)
    {
        $this->pureVirtual = $pureVirtual;
    }

    /**
     * @return bool
     */
    public function isPureVirtual()
    {
        return $this->pureVirtual;
    }

    /**
     * @param string $privacy
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
    }

}