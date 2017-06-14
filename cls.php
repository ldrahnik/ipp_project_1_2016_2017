<?php

/*
 * This file is part of ldrahnik/ipp_1_project.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>, <xdrahn00@stud.fit.vutbr.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use CLS\CLSArgumentParser;
use CLS\CLSMode;
use CLS\CLSOption;
use CLS\CLSParser;

require(__DIR__ . "/vendor/autoload.php");

$clsArgumentParser = new CLSArgumentParser();
$result = $clsArgumentParser->run();

if($result != 0) {
    exit($result);
}

$clsParser = new CLSParser(
    $clsArgumentParser->getOptionValue(CLSOption::INPUT),
    $clsArgumentParser->getOptionValue(CLSOption::OUTPUT),
    $clsArgumentParser->getOptionValue(CLSOption::PRETTY_XML),
    $clsArgumentParser->isOptionSet(CLSOption::DETAILS) ? CLSMode::DETAILS : CLSMode::STANDARD,
    $clsArgumentParser->getOptionValue(CLSOption::DETAILS),
    $clsArgumentParser->isOptionSet(CLSOption::DETAILS) && $clsArgumentParser->isOptionSet(CLSOption::CONFLICTS)
);

$result = $clsParser->run();

if($result != 0) {
    exit($result);
}
