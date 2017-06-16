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

use CLS\CPP\Error\Error;

/**
 * Class CLSArgumentParser.
 */
class CLSArgumentParser
{

    /** @var string */
    const HELP_MESSAGE = "Options:
    --help              Display this help message
    --input=file        Input text file which contains classes in language C++. If is parameter missing input is standard.
    --output=file       Output text file in XML format. If is parameter missing output is standard.
    --pretty-xml=k      Output XML formatting. Without parameter is formatting free. Default k is setup to 4. 
    --details=class     Instead of write dependencies to output is write details of class. If there is not defined class details are write about all classes.
    --conflicts         With enabled --details store conflicts between inherited methods and arguments (even private which are not allowed to inherite) and display that.\n";

    /** @var array */
    private $options = array(
        CLSOption::INPUT => STDIN,
        CLSOption::OUTPUT => STDOUT,
        CLSOption::PRETTY_XML => 4,
        CLSOption::DETAILS => null,
        CLSOption::CONFLICTS => null
    );

    /**
     * Start function where are options parsed.
     *
     * @return int
     */
    public function run()
    {
        $longOptions = $this->getLongOptions();
        $options = getopt("", $longOptions);
        $all = getopt(implode(array_merge(range('a', 'z'), range('A', 'Z'), range('0', '9'))));
        $wrong = array_diff(array_keys($all), array_keys($options));

        // wrong short parameters -[a|z],[A|Z],[0|9]
        if (!empty($wrong)) {
            return Error::BAD_FORMAT_OF_INPUT_ARGS_AND_OPTIONS;
        }

        // duplicates
        if($this->checkDuplicates($options) != 0) {
            return Error::BAD_FORMAT_OF_INPUT_ARGS_AND_OPTIONS;
        }

        // help
        if (array_key_exists(CLSOption::HELP, $options)) {
            return count($options) == 1 ? $this->displayHelp() : Error::BAD_FORMAT_OF_INPUT_ARGS_AND_OPTIONS;
        }

        // processing
        foreach ($options as $name => $value) {
            if (array_key_exists($name, $this->options)) {
                $this->options[$name] = $options[$name];
            } else {
                return Error::BAD_FORMAT_OF_INPUT_ARGS_AND_OPTIONS;
            }
        }

        return 0;
    }

    /**
     * @param string $name
     *
     * @return null|mixed
     */
    public function getOptionValue($name)
    {
        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }
        return null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isOptionSet($name)
    {
        if (array_key_exists($name, $this->options) && $this->options[$name] !== null) {
            return true;
        }
        return false;
    }

    /**
     * Display help message.
     *
     * @return int
     */
    private function displayHelp()
    {
        echo self::HELP_MESSAGE;
        return 0;
    }

    /**
     * @return array
     */
    private function getLongOptions()
    {
        return array(
            CLSOption::HELP,
            CLSOption::get(CLSOption::INPUT, '::'),
            CLSOption::get(CLSOption::OUTPUT, '::'),
            CLSOption::get(CLSOption::PRETTY_XML, ':'),
            CLSOption::get(CLSOption::DETAILS, '::'),
            CLSOption::CONFLICTS
        );
    }

    /**
     * @return string
     */
    private function getShortOptions()
    {
        $result  = "";
        $result .= CLSOption::HELP_SHORT;
        $result .= CLSOption::get(CLSOption::INPUT_SHORT, '::');
        $result .= CLSOption::get(CLSOption::OUTPUT_SHORT, '::');
        $result .= CLSOption::get(CLSOption::PRETTY_XML_SHORT, ':');
        $result .= CLSOption::get(CLSOption::DETAILS_SHORT, '::');
        $result .= CLSOption::CONFLICTS_SHORT;
        return $result;
    }

    /**
     * @param $options
     * @return int
     */
    private function checkDuplicates($options) {
        foreach ($options as $name => $value) {
            if (is_array($value)) {
                return 1;
            }
        }
        return 0;
    }

}