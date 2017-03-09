<?php

/*
 * This file is part of ldrahnik/ipp_1_project.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>, <xdrahn00@stud.fit.vutbr.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CLS\XML;

/**
 * Class XMLElement.
 */
class XMLElement
{

    /** @var string */
    private $name;

    /** @var array */
    private $attributes;

    /** @var array */
    private $xmlElements;

    /** @var bool */
    private $isPaired;

    /**
     * XmlElement constructor.
     *
     * @param string $name
     * @param array $attributes
     * @param array $xmlElements
     * @param bool $isPaired
     */
    public function __construct($name, $attributes = array(), $xmlElements = array(), $isPaired = false)
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->xmlElements = $xmlElements;
        $this->isPaired = $isPaired;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param XMLElement $xmlElement
     */
    public function applyXmlElement(XMLElement $xmlElement)
    {
        $this->xmlElements[] = $xmlElement;
    }

    /**
     * @return bool
     */
    public function isPaired()
    {
        if($this->xmlElements) {
            return true;
        }
        if($this->isPaired) {
            return true;
        }
        return false;
    }

    /**
     * @return XMLElement[]
     */
    public function getXmlElements()
    {
        return $this->xmlElements;
    }

}