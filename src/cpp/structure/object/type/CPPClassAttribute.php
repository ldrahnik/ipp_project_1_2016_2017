<?php

/*
 * This file is part of ldrahnik/ipp_1_project.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>, <xdrahn00@stud.fit.vutbr.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CLS\CPP\Structure\Object\Type;

/**
 * Class CPPClassAttribute.
 */
class CPPClassAttribute
{

    const BOOL = 'bool';
    const CHAR = 'char';
    const CHAR16 = 'char16_t';
    const CHAR32 = 'char32_t';
    const WCHAR = 'wchar_t';
    const SIGNED_CHAR = 'signed char';
    const SHORT_INT = 'short int';
    const INT = 'int';
    const LONG_INT = 'long int';
    const LONG_LONG_INT = 'long long int';
    const UNSIGNED_CHAR = 'unsigned char';
    const UNSIGNED_SHORT_INT = 'unsigned short int';
    const UNSIGNED_LONG_INT = 'unsigned long int';
    const UNSIGNED_LONG_LONG_INT = 'unsigned long long int';
    const FLOAT = 'float';
    const DOUBLE = 'double';
    const LONG_DOUBLE = 'long double';
    const VOID = 'void';

    /**
     * Validate type.
     *
     * @param string $type
     *
     * @return bool
     */
    public static function isValidSimpleType($type)
    {
        return $type == self::BOOL || $type == self::CHAR || $type == self::CHAR16 || $type == self::CHAR32
            || $type == self::WCHAR || $type == self::SIGNED_CHAR || $type == self::SHORT_INT || $type == self::INT ||
            $type == self::LONG_INT || $type == self::LONG_LONG_INT || $type == self::UNSIGNED_CHAR || $type == self::UNSIGNED_SHORT_INT
            || $type == self::UNSIGNED_LONG_INT || $type == self::UNSIGNED_LONG_LONG_INT || $type == self::FLOAT || $type == self::DOUBLE ||
            $type == self::LONG_DOUBLE || $type == self::VOID;
    }

    /**
     * Return true if given string is part of valid type.
     *
     * @param string $type
     * @param int $count
     *
     * @return bool
     */
    public static function partIsValidType($type, $count)
    {
        switch($type) {
            case self::isPartOfTypeEqual($type, self::SHORT_INT, $count):
                return true;
            case self::isPartOfTypeEqual($type, self::LONG_INT, $count):
                return true;
            case self::isPartOfTypeEqual($type, self::LONG_LONG_INT, $count):
                return true;
            case self::isPartOfTypeEqual($type, self::UNSIGNED_CHAR, $count):
                return true;
            case self::isPartOfTypeEqual($type, self::UNSIGNED_SHORT_INT, $count):
                return true;
            case self::isPartOfTypeEqual($type, self::UNSIGNED_LONG_INT, $count):
                return true;
            case self::isPartOfTypeEqual($type, self::UNSIGNED_LONG_LONG_INT, $count):
                return true;
            default:
                return false;
        }
    }

    /**
     * Is part of type equal.
     *
     * @param string $type
     * @param string $equalType
     * @param int $count
     *
     * @return bool
     */
    public static function isPartOfTypeEqual($type, $equalType, $count)
    {
        $trimmedSignedChar = explode(' ', $equalType);
        if(isset($trimmedSignedChar[$count]) && $trimmedSignedChar[$count] == $type) {
            return true;
        }
        return false;
    }

}
