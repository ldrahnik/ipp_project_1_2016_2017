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

        if (array_key_exists(CLSOption::HELP, $options)) {
            return count($options) == 1 ? $this->displayHelp() : 1;
        }
        foreach ($this->options as $name => $value) {
            if (array_key_exists($name, $options)) {
                if ($name == CLSOption::DETAILS) {
                    $this->options[$name] = $options[$name] != false ? $options[$name] : false;
                } else {
                    if ($name == CLSOption::DETAILS) {
                        $this->options[$name] = $options[$name] != false ? $options[$name] : false;
                    } else {
                        $this->options[$name] = $options[$name];
                    }
                }
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

}