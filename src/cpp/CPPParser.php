<?php

namespace CLS\CPP;

/*
 * This file is part of ldrahnik/ipp_1_project.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>, <xdrahn00@stud.fit.vutbr.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use CLS\CPP\Error\Error;
use CLS\CPP\Exception\ElementConflictDuringInheritance;
use CLS\CPP\Exception\InvalidInputFormat;
use CLS\CPP\Exception\InvalidType;
use CLS\CPP\Exception\StaticCanNotBeVirtual;
use CLS\CPP\Exception\UnknownInheritanceClassName;
use CLS\CPP\Exception\UnknownTypeClassName;
use CLS\CPP\Structure\Object\CPPClass;
use CLS\CPP\Structure\Object\CPPClassAttribute;
use CLS\CPP\Structure\Object\CPPClassMethod;
use CLS\CPP\Structure\Object\CPPClassMethodArgument;
use CLS\CPP\Structure\Object\CPPInheritance;
use CLS\CPP\Structure\Object\Type\CPPClassAttribute as CPPClassAttributeType;
use CLS\CPP\Structure\Object\Type\CPPClassKind;
use CLS\CPP\Structure\Object\Type\CPPPrivacy;

/**
 * Class CPPParser.
 */
class CPPParser
{

    /** @var array */
    private $tokens;

    /** @var CPPClass|null */
    private $class;

    /** @var CPPClassMethod|null */
    private $method;

    /** @var string|null */
    private $name;

    /** @var CPPClass[] */
    private $parsedClasses;

    /** @var int */
    private $index;

    /** @var CPPPrivacy|null */
    private $privacy;

    /** @var string|null */
    private $scope;

    /** @var string|null */
    private $type;

    /** @var bool */
    private $conflicts;

    /** @var int */
    private $typeWordCount;

    /**
     * CLSParser constructor.
     *
     * @param $input
     * @param boolean $conflicts
     */
    public function __construct($input, $conflicts)
    {
        $this->tokens = $this->tokenize($input);
        $this->index = 0;
        $this->class = null;
        $this->method = null;
        $this->privacy = null;
        $this->scope = null;
        $this->type = null;
        $this->typeWordCount = 0;
        $this->name = null;
        $this->parsedClasses = array();
        $this->conflicts = $conflicts;
    }

    /**
     * @return int
     */
    public function parse()
    {
        try {
            $returnValue = $this->recursiveParser();

            if($returnValue != 0) {
                return $returnValue;
            }
            return 0;
        } catch (InvalidType $invalidType) {
            return Error::INVALID_INPUT_FORMAT;
        } catch (UnknownInheritanceClassName $unknownInheritanceClassName) {
            return Error::INVALID_INPUT_FORMAT;
        } catch (UnknownTypeClassName $unknownTypeClassName) {
            return Error::INVALID_INPUT_FORMAT;
        } catch (StaticCanNotBeVirtual $staticCanNotBeVirtual) {
            return Error::INVALID_INPUT_FORMAT;
        } catch (InvalidInputFormat $invalidInputFormat) {
            return Error::INVALID_INPUT_FORMAT;
        } catch (ElementConflictDuringInheritance $elementConflictDuringInheritance) {
            return Error::ELEMENT_CONFLICT_DURING_INHERITANCE;
        } catch (\Exception $exception) {
            return Error::STANDARD;
        }
    }

    /**
     * @return CPPClass[]
     */
    public function getParsedClasses()
    {
        return $this->parsedClasses;
    }

    /**
     * @param string $input
     *
     * @return array
     */
    private function tokenize($input)
    {
        $tokens = preg_split('/([\(\)\s:,;{}&\*=])/iu',
            $input,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $tokens = array_map('trim', $tokens);
        $tokens = array_filter($tokens,
            function($value) {
                if ($value == "" || $value == " ") {
                    return false;
                }
                return true;
            });
        $tokens = array_slice($tokens, 0);
        return array_values($tokens);
    }

    /**
     * @return null|mixed
     */
    private function getToken()
    {
        if (!array_key_exists($this->index, $this->tokens)) {
            return null;
        }
        $token = $this->tokens[$this->index];
        $this->index++;
        return $token;
    }

    /**
     * @return null|mixed
     */
    private function getLatestToken()
    {
        return $this->tokens[$this->index - 1];
    }

    /**
     * @return void
     */
    private function lastTokenWasNotUsed()
    {
        $this->index = $this->index - 1;
    }

    /**
     * @param string $state
     * @return int
     * @throws ElementConflictDuringInheritance
     * @throws InvalidInputFormat
     * @throws InvalidType
     * @throws StaticCanNotBeVirtual
     * @throws UnknownInheritanceClassName
     * @throws UnknownTypeClassName
     */
    private function recursiveParser($state = CPPParserState::START_POINT)
    {
        switch ($state) {
            case CPPParserState::START_POINT:
                $classKeyWord = $this->getToken();
                if ($classKeyWord) {
                    if ($this->recursiveParser(CPPParserState::CLASS_KEY_WORD)) {
                        return 1;
                    }
                } else {
                    return 0;
                }
                break;
            case CPPParserState::CLASS_KEY_WORD:
                if ($this->recursiveParser(CPPParserState::NAME)) {
                    return 1;
                }
                $this->class = new CPPClass($this->name);
                if (!$this->recursiveParser(CPPParserState::COLON)) {
                    $this->recursiveParser(CPPParserState::CLASS_INHERITANCE);
                } else {
                    $this->lastTokenWasNotUsed();
                }
                if ($this->recursiveParser(CPPParserState::CLASS_BODY_DEFINITION)) {
                    return 1;
                }
                $this->recursiveParser(CPPParserState::CLASS_ADD);
                break;
            case CPPParserState::NAME:
                $name = $this->getToken();
                if (!$name) {
                    return 1;
                }
                $this->name = $name;
                break;
            case CPPParserState::ALREADY_DEFINED_CLASS_NAME:
                $className = $this->getToken();
                if (!isset($this->parsedClasses[$className])) {
                    return 1;
                }
                break;
            case CPPParserState::CLASS_INHERITANCE:
                if ($this->recursiveParser(CPPParserState::CLASS_INHERITANCE_PRIVACY)) {
                    $this->lastTokenWasNotUsed();
                }
                $this->recursiveParser(CPPParserState::CLASS_INHERITANCE_NAME);
                if (!$this->recursiveParser(CPPParserState::COMMA)) {
                    $this->recursiveParser(CPPParserState::CLASS_INHERITANCE);
                } else {
                    $this->lastTokenWasNotUsed();
                }
                break;
            case CPPParserState::COMMA:
                if ($this->getToken() != CPPParserState::COMMA) {
                    return 1;
                }
                break;
            case CPPParserState::COLON:
                if ($this->getToken() != ':') {
                    return 1;
                }
                break;
            case CPPParserState::CLASS_INHERITANCE_PRIVACY:
                $privacy = $this->getToken();
                if (!CPPPrivacy::isPossibleClassInheritancePrivacyType($privacy)) {
                    $this->privacy = null;
                    return 1;
                }
                $this->privacy = $privacy;
                break;
            case CPPParserState::CLASS_INHERITANCE_NAME:
                $className = $this->getToken();
                if (!$className) {
                    return 1;
                }
                if (!isset($this->parsedClasses[$className])) {
                    throw new UnknownInheritanceClassName();
                }
                $this->class->addInheritance(new CPPInheritance($className, $this->privacy));
                $this->privacy = null;
                break;
            case CPPParserState::CLASS_BODY_DEFINITION:
                if ($this->recursiveParser(CPPParserState::LEFT_CURLY_BRACKET)) {
                    return 1;
                }
                if ($this->recursiveParser(CPPParserState::CLASS_BODY)) {
                    return 1;
                }
                break;
            case CPPParserState::LEFT_CURLY_BRACKET:
                if ($this->getToken() != CPPParserState::LEFT_CURLY_BRACKET) {
                    return 1;
                }
                break;
            case CPPParserState::RIGHT_CURLY_BRACKET:
                if ($this->getToken() != CPPParserState::RIGHT_CURLY_BRACKET) {
                    return 1;
                }
                break;
            case CPPParserState::LEFT_BRACKET:
                if ($this->getToken() != CPPParserState::LEFT_BRACKET) {
                    return 1;
                }
                break;
            case CPPParserState::RIGHT_BRACKET:
                $token = $this->getToken();
                if ($token != CPPParserState::RIGHT_BRACKET) {
                    return 1;
                }
                break;
            case CPPParserState::CLASS_BODY:
                $token = $this->getToken();
                if ($token == CPPParserState::RIGHT_CURLY_BRACKET) {
                    if ($this->recursiveParser(CPPParserState::SEMICOLON)) {
                        return 1;
                    }
                    return 0;
                }

                if (CPPPrivacy::isStatic($token)) {
                    $this->scope = CPPPrivacy::STATIC_TYPE;
                    if ($this->recursiveParser(CPPParserState::CLASS_STATEMENT)) {
                        return 1;
                    }
                } else {
                    if (CPPPrivacy::isPossibleClassPrivacyType($token)) {
                        if ($this->recursiveParser(CPPParserState::COLON)) {
                            return 1;
                        }

                        $this->privacy = $token;
                    } else {
                        if (CPPPrivacy::isVirtual($token)) {
                            $this->method = new CPPClassMethod(
                                null,
                                null,
                                null,
                                $this->privacy,
                                array(),
                                true);
                            if ($this->recursiveParser(CPPParserState::METHOD)) {
                                return 1;
                            }
                        } else {
                            // TODO: add support using::method
                            if (CPPPrivacy::isUsing($token)) {
                                if ($this->recursiveParser(CPPParserState::ALREADY_DEFINED_CLASS_NAME)) {
                                    return 1;
                                }
                                $className = $this->getLatestToken();
                                if ($this->recursiveParser(CPPParserState::COLON)) {
                                    return 1;
                                }
                                if ($this->recursiveParser(CPPParserState::COLON)) {
                                    return 1;
                                }

                                $attributeName = $this->getToken();
                                if (!$attributeName) {
                                    return 1;
                                }

                                $inheritanceAttribute = $this->parsedClasses[$className]->attributeExist($attributeName);
                                if (!$inheritanceAttribute) {
                                    return 1;
                                }

                                $attribute = clone $inheritanceAttribute;
                                $attribute->setUsing(true);
                                $attribute->setPrivacy($this->privacy);
                                $attribute->setScope($this->privacy ? CPPPrivacy::STATIC_TYPE : null);

                                $this->class->addAttribute($attribute);

                                if ($this->recursiveParser(CPPParserState::SEMICOLON)) {
                                    return 1;
                                }
                            } else {
                                $this->lastTokenWasNotUsed();
                                if ($this->recursiveParser(CPPParserState::CLASS_STATEMENT)) {
                                    return 1;
                                }
                            }
                        }
                    }
                }

                if ($this->recursiveParser(CPPParserState::CLASS_BODY)) {
                    return 1;
                }
                break;
            case CPPParserState::METHOD_ADD:
                $this->class->addMethod($this->method);
                $this->method = null;
                break;
            case CPPParserState::METHOD:
                if ($this->recursiveParser(CPPParserState::TYPE)) {
                    throw new InvalidInputFormat();
                }
                $this->method->setType($this->getLatestToken());

                if ($this->recursiveParser(CPPParserState::NAME)) {
                    return 1;
                }
                $this->method->setName($this->getLatestToken());

                if ($this->recursiveParser(CPPParserState::LEFT_BRACKET)) {
                    return 1;
                }
                if ($this->recursiveParser(CPPParserState::METHOD_ARGUMENTS)) {
                    return 1;
                }
                if ($this->recursiveParser(CPPParserState::RIGHT_BRACKET)) {
                    return 1;
                }
                if ($this->recursiveParser(CPPParserState::METHOD_TAIL)) {
                    return 1;
                }
                if ($this->recursiveParser(CPPParserState::SEMICOLON)) {
                    return 1;
                }

                $this->method->setFromInheritanceClassName($this->class->getName());
                if ($this->recursiveParser(CPPParserState::METHOD_ADD)) {
                    return 1;
                }
                break;
            case CPPParserState::METHOD_ARGUMENTS:
                $token = $this->getToken();
                if ($token == CPPParserState::RIGHT_BRACKET) {
                    return 0;
                }
                if ($token == CPPParserState::VOID) {
                    return 0;
                }

                $this->lastTokenWasNotUsed();
                if ($this->recursiveParser(CPPParserState::TYPE)) {
                    return 1;
                }
                if ($this->recursiveParser(CPPParserState::NAME)) {
                    return 1;
                }
                $argument = new CPPClassMethodArgument($this->name, $this->type);
                $this->method->addArgument($argument);

                if ($this->recursiveParser(CPPParserState::METHOD_ARGUMENTS_NEXT)) {
                    return 1;
                }
                break;
            case CPPParserState::METHOD_ARGUMENTS_NEXT:
                $token = $this->getToken();
                if ($token == CPPParserState::RIGHT_BRACKET) {
                    $this->lastTokenWasNotUsed();
                    return 0;
                }
                $this->lastTokenWasNotUsed();

                if (!$this->recursiveParser(CPPParserState::COMMA)) {
                    if ($this->recursiveParser(CPPParserState::TYPE)) {
                        return 1;
                    }
                    if ($this->recursiveParser(CPPParserState::NAME)) {
                        return 1;
                    }
                    $argument = new CPPClassMethodArgument($this->name, $this->type);
                    $this->method->addArgument($argument);
                    if ($this->recursiveParser(CPPParserState::METHOD_ARGUMENTS_NEXT)) {
                        return 1;
                    }
                } else {
                    throw new InvalidType($this->type);
                }
                break;
            case CPPParserState::EQUAL_SIGN:
                if ($this->getToken() != CPPParserState::EQUAL_SIGN) {
                    return 1;
                }
                break;
            case CPPParserState::ZERO:
                if ($this->getToken() != CPPParserState::ZERO) {
                    return 1;
                }
                break;
            case CPPParserState::METHOD_TAIL:
                if (!$this->recursiveParser(CPPParserState::EQUAL_SIGN)) {
                    if (!$this->recursiveParser(CPPParserState::ZERO)) {
                        $this->method->setPureVirtual(true);
                    }
                    $this->class->setKind(CPPClassKind::ABSTRACT_CLASS);
                } else {
                    if (!$this->recursiveParser(CPPParserState::LEFT_CURLY_BRACKET)) {
                        if (!$this->recursiveParser(CPPParserState::LEFT_CURLY_BRACKET)) {
                            if ($this->recursiveParser(CPPParserState::RIGHT_CURLY_BRACKET)) {
                                return 1;
                            }
                        }
                    }
                }
                break;
            case CPPParserState::SEMICOLON:
                if ($this->getToken() != CPPParserState::SEMICOLON) {
                    return 1;
                }
                break;
            case CPPParserState::CLASS_ADD:
                foreach ($this->class->getInheritances() as $inheritance) {
                    foreach ($this->parsedClasses[$inheritance->getName()]->getAttributes() as $inheritanceAttribute) {
                        $attributeExist = $this->class->attributeExist($inheritanceAttribute->getName());
                        // avoid inheritance conflict by overriding attribute
                        if ($attributeExist && $attributeExist->getFromInheritanceClassName() != $this->class->getName() && !$attributeExist->isUsing()) {
                            throw new ElementConflictDuringInheritance;
                        }
                        // avoid inheritance conflict by using Using::
                        if($attributeExist && $attributeExist->isUsing()) {
                            continue;
                        }
                        if (CPPPrivacy::isAllowedToInheritance(
                            $inheritanceAttribute->getPrivacy(),
                            $inheritance->getPrivacy()
                        )
                        ) {
                            $attribute = clone $inheritanceAttribute;
                            $attribute->setPrivacy(
                                CPPPrivacy::getInheritanceType(
                                    $inheritanceAttribute->getPrivacy(),
                                    $inheritance->getPrivacy()
                                ));
                            $this->class->addAttribute($attribute);
                        } else {
                            $this->class->addHiddenAttribute($inheritanceAttribute);

                            if ($this->conflicts) {
                                $conflictElement = $this->class->attributeForConflictExist($inheritanceAttribute->getName());
                                if ($conflictElement) {
                                    $this->class->addConflict($conflictElement);
                                    $this->class->addConflict($inheritanceAttribute);

                                    $this->class->removeAttribute($inheritanceAttribute->getName());
                                }
                            }
                        }
                    }
                    foreach ($this->parsedClasses[$inheritance->getName()]->getHiddenAttributes() as $inheritanceHiddenAttribute) {
                        $this->class->addHiddenAttribute($inheritanceHiddenAttribute);

                        if ($this->conflicts) {
                            $conflictElement = $this->class->attributeForConflictExist($inheritanceHiddenAttribute->getName());
                            if ($conflictElement) {
                                $this->class->addConflict($conflictElement);
                                $this->class->addConflict($inheritanceHiddenAttribute);

                                $this->class->removeAttribute($inheritanceHiddenAttribute->getName());
                            }
                        } else {
                            $exist = $this->class->attributeForConflictExist($inheritanceHiddenAttribute->getName());
                            if ($exist && $inheritanceHiddenAttribute->getFromInheritanceClassName() != $this->class->getName() && !$exist->isUsing()) {
                                throw new ElementConflictDuringInheritance;
                            }
                        }
                    }
                    foreach ($this->parsedClasses[$inheritance->getName()]->getMethods() as $inheritanceMethod) {
                        $methodExist = $this->class->methodExist($inheritanceMethod);
                        if ($methodExist && !$inheritanceMethod->getPureVirtual() && $methodExist->getFromInheritanceClassName() != $this->class->getName()) {
                            throw new ElementConflictDuringInheritance;
                        }
                        if (CPPPrivacy::isAllowedToInheritance(
                            $inheritanceMethod->getPrivacy(),
                            $inheritance->getPrivacy()
                        )) {
                            $method = clone $inheritanceMethod;
                            $method->setPrivacy(CPPPrivacy::getInheritanceType(
                                $inheritanceMethod->getPrivacy(),
                                $inheritance->getPrivacy()
                            ));
                            $this->class->addMethod($method);
                        } else {
                            if(!$inheritanceMethod->getPureVirtual()) {
                                $this->class->addHiddenMethod($inheritanceMethod);

                                if ($this->conflicts) {
                                    $conflictElement = $this->class->methodForConflictExist($inheritanceMethod);
                                    if ($conflictElement) {
                                        $this->class->addConflict($conflictElement);
                                        $this->class->addConflict($inheritanceMethod);

                                        $this->class->removeMethod($inheritanceMethod);
                                    }
                                }
                            }
                        }
                    }
                    foreach($this->parsedClasses[$inheritance->getName()]->getHiddenMethods() as $inheritanceHiddenMethod) {
                         $this->class->addHiddenMethod($inheritanceHiddenMethod);

                        if ($this->conflicts) {
                            $conflictElement = $this->class->methodForConflictExist($inheritanceHiddenMethod);
                            if($conflictElement) {
                                $this->class->addConflict($conflictElement);
                                $this->class->addConflict($inheritanceHiddenMethod);

                                $this->class->removeMethod($inheritanceHiddenMethod);
                            }
                        } else {
                            $exist = $this->class->methodForConflictExist($inheritanceHiddenMethod);
                            if ($exist && $exist->getFromInheritanceClassName() != $this->class->getName()) {
                                throw new ElementConflictDuringInheritance;
                            }
                        }
                    }
                }

                foreach ($this->class->getInheritances() as $inheritance) {
                    foreach ($this->parsedClasses[$inheritance->getName()]->getMethods() as $inheritanceMethod) {
                        if ($inheritanceMethod->isPureVirtual()) {
                            if (!$this->class->isMethodRewritten($inheritanceMethod)) {
                                $method = clone $inheritanceMethod;
                                $method->setPrivacy(CPPPrivacy::getInheritanceType(
                                    $inheritanceMethod->getPrivacy(),
                                    $inheritance->getPrivacy()
                                ));
                                $method->setFromInheritanceClassName($inheritanceMethod->getFromInheritanceClassName());
                                $this->class->addMethod($method);
                                $this->class->setKind(CPPClassKind::ABSTRACT_CLASS);
                            } else {
                                $this->class->removeMethod($inheritanceMethod);
                            }
                        }
                    }
                }

                $this->parsedClasses[$this->class->getName()] = $this->class;
                $this->class = null;
                $this->privacy = null;
                $this->recursiveParser(CPPParserState::START_POINT);
                break;
            case CPPParserState::SOMETHING_TAIL:
                $token = $this->getToken();

                if ($token == CPPParserState::SEMICOLON) {
                    $attribute = new CPPClassAttribute($this->name,
                        $this->type,
                        $this->scope,
                        $this->privacy,
                        $this->class->getName());
                    $this->class->addAttribute($attribute);
                    if ($this->recursiveParser(CPPParserState::VAR_NEXT)) {
                        return 1;
                    }
                } else if ($token == CPPParserState::COMMA) {
                    $attribute = new CPPClassAttribute($this->name,
                        $this->type,
                        $this->scope,
                        $this->privacy,
                        $this->class->getName());
                    $this->class->addAttribute($attribute);
                        $this->lastTokenWasNotUsed();
                        if ($this->recursiveParser(CPPParserState::VAR_NEXT)) {
                            return 1;
                        }
                    } else {
                        if ($token == CPPParserState::LEFT_BRACKET) {
                            $this->method = new CPPClassMethod(
                                $this->name,
                                $this->type,
                                null,
                                $this->privacy,
                                array(),
                                false,
                                $this->class->getName()
                            );
                            $this->class->addMethod($this->method);

                            if ($this->recursiveParser(CPPParserState::METHOD_ARGUMENTS)) {
                                return 1;
                            }
                            if ($this->recursiveParser(CPPParserState::RIGHT_BRACKET)) {
                                return 1;
                            }
                            if (!$this->recursiveParser(CPPParserState::LEFT_CURLY_BRACKET)) {
                                if ($this->recursiveParser(CPPParserState::RIGHT_CURLY_BRACKET)) {
                                    return 1;
                                }
                            }
                            if ($this->recursiveParser(CPPParserState::SEMICOLON)) {
                                $this->lastTokenWasNotUsed();
                            } else {
                                return 0;
                            }
                        }
                    }
                break;
            case CPPParserState::VAR_NEXT:
                $token = $this->getToken();
                if ($token == CPPParserState::COMMA) {
                    if ($this->recursiveParser(CPPParserState::NAME)) {
                        return 1;
                    }
                    if (!$this->method) {
                        $attribute = new CPPClassAttribute($this->name,
                            $this->type,
                            $this->scope,
                            $this->privacy,
                            $this->class->getName());
                        $this->class->addAttribute($attribute);
                    } else {
                        $argument = new CPPClassMethodArgument($this->name, $this->type);
                        $this->method->addArgument($argument);
                    }
                    if ($this->recursiveParser(CPPParserState::VAR_NEXT)) {
                        return 1;
                    }
                } else {
                    $this->lastTokenWasNotUsed();
                }
                break;

            case CPPParserState::CONSTRUCTOR_DECONSTRUCTOR_TYPE:
                $token = $this->getToken();
                if ($token == $this->class->getName()) {
                    $this->type = CPPClassAttributeType::VOID;
                } else {
                    if ($token == (CPPParserState::TILDE . $this->class->getName())) {
                        $this->type = CPPPrivacy::VOID;
                    } else {
                        return 1;
                    }
                }
                break;
            case CPPParserState::CLASS_STATEMENT:
                if (!$this->recursiveParser(CPPParserState::CONSTRUCTOR_DECONSTRUCTOR_TYPE)) {
                } else {
                    $this->lastTokenWasNotUsed();
                    if ($this->recursiveParser(CPPParserState::TYPE)) {
                        $this->lastTokenWasNotUsed();
                        if (CPPPrivacy::isVirtual($this->getToken()) && $this->scope == CPPPrivacy::STATIC_TYPE) {
                            throw new StaticCanNotBeVirtual();
                        }
                        if ($this->recursiveParser(CPPParserState::RIGHT_CURLY_BRACKET)) {
                            return 0;
                        }
                        throw new UnknownTypeClassName($this->getLatestToken());
                    }
                    if ($this->recursiveParser(CPPParserState::NAME)) {
                        return 1;
                    }
                }
                if ($this->recursiveParser(CPPParserState::SOMETHING_TAIL)) {
                    return 1;
                }

                break;
            case CPPParserState::TYPE:
                $type = $this->getToken();
                if (CPPClassAttributeType::isValidSimpleType($type)) {
                    $this->type = $type;
                    if ($this->recursiveParser(CPPParserState::TYPE_NEXT)) {
                        return 1;
                    } else {
                        $this->lastTokenWasNotUsed();
                    }
                } else {
                    if (CPPClassAttributeType::partIsValidType($type, $this->typeWordCount)) {
                        $this->lastTokenWasNotUsed();
                        if ($this->recursiveParser(CPPParserState::TYPE_NEXT_PART)) {
                            return 1;
                        }
                    } else {
                        $this->lastTokenWasNotUsed();
                        if ($this->recursiveParser(CPPParserState::ALREADY_DEFINED_CLASS_NAME)) {
                            return 1;
                        } else {
                            $this->type = $type;
                        }
                    }
                }

                break;
            case CPPParserState::TYPE_NEXT_PART:
                $partOfType = $this->getToken();
                if (CPPClassAttributeType::partIsValidType($partOfType, $this->typeWordCount)) {
                    if ($this->typeWordCount == 0) {
                        $this->type = $partOfType;
                    } else {
                        $this->type .= " " . $partOfType;
                    }
                    $this->typeWordCount += 1;
                    if ($this->recursiveParser(CPPParserState::TYPE_NEXT_PART)) {
                        return 1;
                    }
                } else {
                    $this->lastTokenWasNotUsed();
                    if ($this->recursiveParser(CPPParserState::TYPE_NEXT)) {
                        return 1;
                    } else {
                        $this->lastTokenWasNotUsed();
                        $this->typeWordCount = 0;
                        return 0;
                    }
                }
                break;
            case CPPParserState::TYPE_NEXT:
                $token = $this->getToken();
                if ($token == "*") {
                    $this->type .= " " . $token;
                    if ($this->recursiveParser(CPPParserState::POINTER_TYPE_NEXT)) {
                        return 0;
                    }
                    return 0;
                } else {
                    if ($token == "&") {
                        $this->type .= " " . $token;
                        if ($this->recursiveParser(CPPParserState::AMPERSAND_TYPE_NEXT)) {
                            return 0;
                        }
                        return 0;
                    }
                }
                break;
            case CPPParserState::POINTER_TYPE_NEXT:
                $type = $this->getToken();
                if ($type == '*') {
                    $this->type .= $type;

                    if ($this->recursiveParser(CPPParserState::POINTER_TYPE_NEXT)) {
                        return 1;
                    }
                    return 0;
                } else {
                    return 0;
                }
                break;
            case CPPParserState::AMPERSAND_TYPE_NEXT:
                $type = $this->getToken();
                if ($type == "&") {
                    $this->type .= $type;

                    if ($this->recursiveParser(CPPParserState::AMPERSAND_TYPE_NEXT)) {
                        return 1;
                    }
                    return 0;
                } else {
                    return 1;
                }
                break;
        }
        return 0;
    }

}
