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
use CLS\CPP\Structure\Object\Type\CPPPScope;

/**
 * Class ClassAttribute.
 */
class CPPClassAttribute implements CPPClassElement
{

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

    /** @var bool */
    private $using;

    /**
     * CPPClassAttribute constructor.
     *
     * @param string $name
     * @param string $type
     * @param string $scope
     * @param string $privacy
     * @param string $fromInheritanceClassName
     * @param bool $using
     */
    public function __construct($name, $type, $scope, $privacy, $fromInheritanceClassName = null, $using = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->scope = $scope ? $scope : 'instance';
        $this->privacy = $privacy ? $privacy : CPPPrivacy::PRIVATE_TYPE;
        $this->fromInheritanceClassName = $fromInheritanceClassName;
        $this->using = $using;
    }

    /**
     * @return null|string
     */
    public function isInheritance()
    {
        return $this->fromInheritanceClassName;
    }

    /**
     * @return string
     */
    public function getFromInheritanceClassName()
    {
        return $this->fromInheritanceClassName;
    }

    /**
     * @param string $fromInheritanceClassName
     */
    public function setFromInheritanceClassName($fromInheritanceClassName)
    {
        $this->fromInheritanceClassName = $fromInheritanceClassName;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @return string
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }

    /**
     * @param string $privacy
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
    }

    /**
     * @return bool
     */
    public function isUsing()
    {
        return $this->using;
    }

    /**
     * @param bool $using
     */
    public function setUsing($using)
    {
        $this->using = $using;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

}