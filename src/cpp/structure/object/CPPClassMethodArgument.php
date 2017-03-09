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

/**
 * Class ClassMethodArgument.
 */
class CPPClassMethodArgument
{

    /** @var string */
    private $name;

    /** @var string */
    private $type;

    /**
     * CPPClassMethodArgument constructor.
     *
     * @param string $name
     * @param string $type
     */
    public function __construct(
        $name,
        $type
    ) {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

}