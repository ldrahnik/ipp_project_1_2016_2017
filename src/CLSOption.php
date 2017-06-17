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
    const HELP_SHORT = 'h';
    const INPUT = 'input';
    const INPUT_SHORT = 'i';
    const OUTPUT = 'output';
    const OUTPUT_SHORT = 'o';
    const PRETTY_XML = 'pretty-xml';
    const PRETTY_XML_SHORT = 'p';
    const DETAILS = 'details';
    const DETAILS_SHORT = 'd';
    const CONFLICTS = 'conflicts';
    const CONFLICTS_SHORT = 'c';

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