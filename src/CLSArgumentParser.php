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
        CLSOption::HELP => null,
        CLSOption::INPUT => STDIN,
        CLSOption::OUTPUT => STDOUT,
        CLSOption::PRETTY_XML => 4,
        CLSOption::DETAILS => null,
        CLSOption::CONFLICTS => null
    );

    /** @var array */
    private $argv;

    /**
     * CLSArgumentParser constructor.
     *
     * @param array $argv
     */
    public function __construct(array $argv)
    {
        unset($argv[0]);
        $this->argv = $argv;
    }


    /**
     * Start function where are options parsed.
     *
     * @return int
     */
    public function run()
    {
        $longOptions = $this->getLongOptions();
        $shortOptions = $this->getShortOptions();
        $options = getopt($shortOptions, $longOptions);

        // wrong options, parameters
        if(count($this->argv) != count($options)) {
            return Error::BAD_FORMAT_OF_INPUT_ARGS_AND_OPTIONS;
        }

        // duplicates
        if($this->checkDuplicates($options) != 0) {
            return Error::BAD_FORMAT_OF_INPUT_ARGS_AND_OPTIONS;
        }

        // help
        if($this->isHelpOptionSet($options)) {
            $this->options[CLSOption::HELP] = true;

            if(count($options) != 1) {
                return Error::BAD_FORMAT_OF_INPUT_ARGS_AND_OPTIONS;
            }

            $this->displayHelp();

            return 0;
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
     */
    private function displayHelp()
    {
        echo self::HELP_MESSAGE;
    }

    /**
     * @return bool
     */
    private function isHelpOptionSet($options)
    {
        if (array_key_exists(CLSOption::HELP, $options) || array_key_exists(CLSOption::HELP_SHORT, $options)) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    private function getLongOptions()
    {
        return array(
            CLSOption::HELP,
            CLSOption::get(CLSOption::INPUT, ':'),
            CLSOption::get(CLSOption::OUTPUT, ':'),
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
     *
     * @return int
     */
    private function checkDuplicates($options) {
        // check together
        if(array_key_exists(CLSOption::HELP, $options) && array_key_exists(CLSOption::HELP_SHORT, $options)) {
            return 1;
        }
        if(array_key_exists(CLSOption::INPUT, $options) && array_key_exists(CLSOption::INPUT_SHORT, $options)) {
            return 1;
        }
        if(array_key_exists(CLSOption::OUTPUT, $options) && array_key_exists(CLSOption::OUTPUT_SHORT, $options)) {
            return 1;
        }
        if(array_key_exists(CLSOption::DETAILS, $options) && array_key_exists(CLSOption::DETAILS_SHORT, $options)) {
            return 1;
        }
        if(array_key_exists(CLSOption::PRETTY_XML, $options) && array_key_exists(CLSOption::PRETTY_XML_SHORT, $options)) {
            return 1;
        }
        if(array_key_exists(CLSOption::CONFLICTS, $options) && array_key_exists(CLSOption::CONFLICTS_SHORT, $options)) {
            return 1;
        }

        // check separately
        foreach ($options as $name => $value) {
            if (is_array($value)) {
                return 1;
            }
        }
        return 0;
    }

}
