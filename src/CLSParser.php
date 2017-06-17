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

use CLS\CPP\CPPParser;
use CLS\CPP\Error\Error;
use CLS\CPP\Structure\Object\CPPClass;
use CLS\CPP\Structure\Object\CPPClassAttribute;
use CLS\CPP\Structure\Object\CPPClassMethod;
use CLS\XML\XMLElement;
use XMLWriter;

/**
 * Class CLSParser.
 */
class CLSParser
{

    /** @var string */
    private $input;

    /** @var string */
    private $output;

    /** @var int */
    private $mode;

    /** @var string */
    private $outputIndent;

    /** @var null|string */
    private $detailsModeClass;

    /** @var bool */
    private $conflicts;

    /**
     * CLS constructor.
     *
     * @param null $input
     * @param null $output
     * @param int $outputIndent
     * @param int $mode
     * @param string|null $detailsModeClass
     * @param boolean $conflicts
     */
    public function __construct(
        $input = null,
        $output = null,
        $outputIndent = 4,
        $mode,
        $detailsModeClass = null,
        $conflicts
    ) {
        $this->input = $input;
        $this->output = $output;
        $this->mode = $mode;
        $this->outputIndent = $this->calcIndent($outputIndent);
        $this->detailsModeClass = $detailsModeClass;
        $this->conflicts = $conflicts;
    }

    /**
     * @return int
     */
    public function run()
    {
        $contentToParse = '';

        if ($this->input == STDIN) {
            while ($line = fgets(STDIN))
                $contentToParse .= $line;
        } else {
            if (!($file = fopen($this->input, "r"))) {
                if (!($file = fopen(__DIR__ . $this->input, "r"))) {
                    return Error::UNEXISTING_INPUT_FILE_OR_ERROR_WHEN_OPENING_INPUT_FILE;
                }
            }
            $contentToParse = filesize($this->input) ? fread($file, filesize($this->input)) : "";
            fclose($file);
            return $this->parse($contentToParse);
        }

        return 1;
    }

    /**
     * @param int $outputIndent
     *
     * @return string
     */
    private function calcIndent($outputIndent)
    {
        $indent = '';
        for ($i = 0; $i < $outputIndent; $i++) {
            $indent .= ' ';
        }
        return $indent;
    }

    /**
     * @param CPPClass $class
     * @param CPPClass[] $parsedClasses
     *
     * @return XMLElement
     */
    private function recursiveGenerateTree(CPPClass $class, $parsedClasses = array())
    {
        $classElement = new XMLElement(
            'class',
            array(
                'name' => $class->getName(),
                'kind' => $class->getKind()
            )
        );

        foreach ($parsedClasses as $name => $cls) {
            if ($name != $class->getName()) {
                foreach ($cls->getInheritances() as $inheritance) {
                    if ($class->getName() == $inheritance->getName()) {
                        $classElement->applyXmlElement($this->recursiveGenerateTree($cls, $parsedClasses));
                        break;
                    }
                }
            }
        }
        return $classElement;
    }

    /**
     * @param CPPClass[] $parsedClasses
     *
     * @return array
     */
    private function generateClassTree($parsedClasses)
    {
        $xmlElements = array();
        $xmlElements[] = $model = new XMLElement('model', array(), array(), true);

        foreach ($parsedClasses as $name => $class) {
            if (!$class->getInheritances()) {
                $classXmlElement = $this->recursiveGenerateTree($class, $parsedClasses);
                if ($classXmlElement->getXmlElements()) {
                    $model->applyXmlElement($classXmlElement);
                }
            }
        }
        return $xmlElements;
    }

    /**
     * @param CPPClass $class
     *
     * @return XMLElement
     */
    private function generateClassDetail(CPPClass $class)
    {
        $classElement = new XMLElement(
            'class',
            array(
                'name' => $class->getName(),
                'kind' => $class->getKind()
            ),
            array(),
            true
        );

        $inheritanceElement = new XMLElement('inheritance');

        foreach ($class->getInheritances() as $inheritance) {
            $inheritanceElement->applyXmlElement(
                new XMLElement(
                    'from',
                    array(
                        'name' => $inheritance->getName(),
                        'privacy' => $inheritance->getPrivacy()
                    )
                )
            );
        }

        if ($class->getInheritances()) {
            $classElement->applyXmlElement($inheritanceElement);
        }

        if ($this->conflicts) {
            $conflictsElement = new XMLElement('conflicts', array(), array(), true);

            $conflictSourcesElements = array();
            $conflictName = null;
            foreach ($class->getConflicts() as $conflict) {
                $conflictMainElement = null;
                $conflictName = $conflict->getName();
                if ($conflict instanceof CPPClassAttribute) {
                    $conflictMainElement = new XMLElement(
                        'attribute',
                        array(
                            'name' => $conflict->getName(),
                            'type' => $conflict->getType(),
                            'scope' => $conflict->getScope()
                        ));
                } else {
                    if ($conflict instanceof CPPClassMethod) {
                        $conflictMainElement = new XMLElement(
                            'method',
                            array(
                                'name' => $conflict->getName(),
                                'type' => $conflict->getType(),
                                'scope' => $conflict->getScope()
                            )
                        );
                        if ($conflict->getFromInheritanceClassName() != $class->getName()) {
                            $conflictMainElement->applyXmlElement(
                                new XMLElement(
                                    'from',
                                    array(
                                        'name' => $conflict->getFromInheritanceClassName()
                                    )
                                )
                            );
                        }
                        $conflictMainElement->applyXmlElement(
                            new XMLElement(
                                'virtual',
                                array(
                                    'pure' => $conflict->isPureVirtual() ? 'yes' : 'no'
                                )
                            )
                        );
                        $arguments = new XMLElement(
                            'arguments',
                            array(),
                            array(),
                            true
                        );
                        foreach ($conflict->getArguments() as $argument) {
                            $arguments->applyXmlElement(
                                new XMLElement(
                                    'argument',
                                    array(
                                        'name' => $argument->getName(),
                                        'type' => $argument->getType()
                                    )
                                )
                            );
                        }
                        $conflictMainElement->applyXmlElement($arguments);
                    }
                }

                $conflictSourcesElements[] = new XMLElement('class',
                    array(
                        'name' => $conflict->getFromInheritanceClassName()
                    ),
                    array(
                        new XMLElement(
                            $conflict->getPrivacy(),
                            array(),
                            array($conflictMainElement)
                        )
                    )
                );
            }
            $conflictsMainElement = new XMLElement(
                'member',
                array(
                    'name' => $conflictName
                ),
                $conflictSourcesElements
            );

            $conflictsElement->applyXmlElement($conflictsMainElement);


            if ($class->getConflicts()) {
                $classElement->applyXmlElement($conflictsElement);
            }
        }

        foreach (array("public", "protected", "private") as $privacy) {
            $privacyElement = new XMLElement($privacy);

            $attributesElements = new XMLElement('attributes');
            foreach ($class->getAttributesWithNoConflicts() as $attribute) {

                if ($attribute->getPrivacy() == $privacy) {
                    $attributeElement = new XMLElement(
                        'attribute',
                        array(
                            'name' => $attribute->getName(),
                            'type' => $attribute->getType(),
                            'scope' => $attribute->getScope()
                        ));
                    if ($attribute->getFromInheritanceClassName() != $class->getName()) {
                        $attributeElement->applyXmlElement(
                            new XMLElement(
                                'from',
                                array(
                                    'name' => $attribute->getFromInheritanceClassName()
                                )
                            )
                        );
                    }
                    $attributesElements->applyXmlElement($attributeElement);
                }
            }
            if ($attributesElements->getXmlElements()) {
                $privacyElement->applyXmlElement($attributesElements);
            }

            $methodsElement = new XMLElement('methods');
            foreach ($class->getMethodsWithNoConflicts() as $method) {
                if ($method->getPrivacy() == $privacy) {
                    $methodElement = new XMLElement(
                        'method',
                        array(
                            'name' => $method->getName(),
                            'type' => $method->getType(),
                            'scope' => $method->getScope()
                        )
                    );
                    if ($method->getFromInheritanceClassName() != $class->getName()) {
                        $methodElement->applyXmlElement(
                            new XMLElement(
                                'from',
                                array(
                                    'name' => $method->getFromInheritanceClassName()
                                )
                            )
                        );
                    }
                    if($method->isVirtual()) {
                        $methodElement->applyXmlElement(
                            new XMLElement(
                                'virtual',
                                array(
                                    'pure' => $method->isPureVirtual() ? 'yes' : 'no'
                                )
                            )
                        );
                    }
                    $arguments = new XMLElement(
                        'arguments',
                        array(),
                        array(),
                        true
                    );
                    foreach ($method->getArguments() as $argument) {
                        $arguments->applyXmlElement(
                            new XMLElement(
                                'argument',
                                array(
                                    'name' => $argument->getName(),
                                    'type' => $argument->getType()
                                )
                            )
                        );
                    }
                    $methodElement->applyXmlElement($arguments);
                    $methodsElement->applyXmlElement($methodElement);
                }
            }

            if ($methodsElement->getXmlElements()) {
                $privacyElement->applyXmlElement($methodsElement);
            }

            if ($privacyElement->getXmlElements()) {
                $classElement->applyXmlElement($privacyElement);
            }
        }

        return $classElement;
    }

    /**
     * @param array $parsedClasses
     *
     * @return array
     */
    private function generateDetail($parsedClasses)
    {
        $xmlElements = array();

        if ($this->detailsModeClass) {
            // append only if details class exists
            if(array_key_exists($this->detailsModeClass, $parsedClasses)) {
                $xmlElements[] = $this->generateClassDetail($parsedClasses[$this->detailsModeClass]);
            }
        } else {
            $xmlElements[] = $model = new XMLElement('model');
            foreach ($parsedClasses as $name => $class) {
                $model->applyXmlElement($this->generateClassDetail($class));
            }
        }

        return $xmlElements;
    }

    /**
     * @param string $content
     *
     * @return int
     */
    private function parse($content)
    {
        $xmlElements = array();

        $clsParser = new CPPParser($content, $this->conflicts);
        if ($ret = $clsParser->parse()) {
            return $ret;
        }

        $parsedClasses = $clsParser->getParsedClasses();

        switch ($this->mode) {
            case CLSMode::STANDARD:
                $xmlElements = $this->generateClassTree($parsedClasses);
                break;
            case CLSMode::DETAILS:
                $xmlElements = $this->generateDetail($parsedClasses);
                break;
        }

        return $this->export($xmlElements);
    }

    /**
     * @param XMLWriter $xmlWriter
     * @param array $xmlElements
     */
    private function writeXmlElements(XMLWriter $xmlWriter, $xmlElements = array())
    {
        foreach ($xmlElements as $element) {
            /** @var $element XMLElement */
            if (!$element->getXmlElements() && $element->isPaired()) {
                $xmlWriter->startElement($element->getName());
                foreach ($element->getAttributes() as $name => $value) {
                    $xmlWriter->writeAttribute($name, $value);
                    $xmlWriter->endAttribute();
                }
                $xmlWriter->text('');
                $xmlWriter->endElement();
            } else {
                $xmlWriter->startElement($element->getName());
                foreach ($element->getAttributes() as $name => $value) {
                    $xmlWriter->writeAttribute($name, $value);
                    $xmlWriter->endAttribute();
                }
                if ($element->getXmlElements()) {
                    $this->writeXmlElements($xmlWriter, $element->getXmlElements());
                }
                $xmlWriter->endElement();
            }
        }
    }

    /**
     * @param array $xmlElements
     *
     * @return int
     */
    private function export($xmlElements = array())
    {
        $xmlWriter = new XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);
        $xmlWriter->setIndentString($this->outputIndent);
        $xmlWriter->startDocument('1.0', 'UTF-8');

        $this->writeXmlElements($xmlWriter, $xmlElements);

        $xmlWriter->endDocument();
        $result = $xmlWriter->outputMemory();

        if ($this->output == STDOUT) {
            echo $result;
        } else {
            if (!($file = fopen($this->output, "w+"))) {
                return Error::ERROR_WHEN_OPENING_OR_WRITING_OUTPUT_FILE;
            }
            if (fwrite($file, $result) === FALSE) {
                return Error::ERROR_WHEN_OPENING_OR_WRITING_OUTPUT_FILE;
            }
            fclose($file);
        }

        return 0;
    }
}