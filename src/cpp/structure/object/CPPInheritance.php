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
 * Class CPPInheritance.
 */
class CPPInheritance
{

    /** @var string */
    private $className;

    /** @var string */
    private $privacy;

    /**
     * CPPInheritance constructor.
     *
     * @param string $className
     * @param CPPPrivacy|null $privacy
     */
    public function __construct($className, $privacy)
    {
        $this->className = $className;
        $this->privacy = $privacy ? $privacy : CPPPrivacy::PRIVATE_TYPE;
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
    public function getName()
    {
        return $this->className;
    }

}