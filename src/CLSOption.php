<?php

/*
 * This file is part of ldrahnik/ipp_1_project.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>, <xdrahn00@stud.fit.vutbr.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CLS;

class CLSOption {

    const HELP = 'help';
    const INPUT = 'input';
    const OUTPUT = 'output';
    const PRETTY_XML = 'pretty-xml';
    const DETAILS = 'details';
    const CONFLICTS = 'conflicts';

    /**
     * @param string $value
     * @param string $valueIndicator
     *
     * @return string
     */
    public static function get($value, $valueIndicator)
    {
        return $value . $valueIndicator;
    }
}