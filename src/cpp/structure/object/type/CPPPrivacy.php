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
 * Class CPPPrivacy.
 */
class CPPPrivacy
{

    const PRIVATE_TYPE = 'private';
    const PUBLIC_TYPE = 'public';
    const PROTECTED_TYPE = 'protected';
    const STATIC_TYPE = 'static';
    const VOID = 'void';

    const VIRTUAL = 'virtual';
    const USING = 'using';
    const NONE_TYPE = null;

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isPossibleClassPrivacyType($type)
    {
        return $type == self::PRIVATE_TYPE || $type == self::PUBLIC_TYPE || $type == self::PROTECTED_TYPE;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isPossibleClassInheritancePrivacyType($type)
    {
        return $type == self::PRIVATE_TYPE || $type == self::PUBLIC_TYPE || $type == self::PROTECTED_TYPE;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isUsing($type)
    {
        return $type == self::USING;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isVirtual($type)
    {
        return $type == self::VIRTUAL;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public static function isStatic($type)
    {
        return $type == self::STATIC_TYPE;
    }

    /**
     * Function that return attribute or method privacy type when is inherited via class reference.
     * <type>:
     *     int var;
     *
     * class B: <inheritanceClassType> C, D ...
     *
     * type:                         private          protected      public
     *                          -----------------   ------------   -----------
     * private                  is not accessible |   private    |   private
     * protected                is not accessible |   protected  |   protected
     * public                   is not accessible |   protected  |   public

     *
     *
     * @param string $type
     * @param string $inheritanceClassType
     *
     * @return string|null
     */
    public static function getInheritanceType($type, $inheritanceClassType)
    {
        if($inheritanceClassType == self::PRIVATE_TYPE) {
            if($type == self::PRIVATE_TYPE) {
                return self::NONE_TYPE;
            }
            if($type == self::PROTECTED_TYPE) {
                return self::PRIVATE_TYPE;
            }
            if($type == self::PUBLIC_TYPE) {
                return self::PRIVATE_TYPE;
            }
        } else if ($inheritanceClassType == self::PROTECTED_TYPE) {
            if($type == self::PRIVATE_TYPE) {
                return self::NONE_TYPE;
            }
            if($type == self::PROTECTED_TYPE) {
                return self::PROTECTED_TYPE;
            }
            if($type == self::PUBLIC_TYPE) {
                return self::PROTECTED_TYPE;
            }
        } else if ($inheritanceClassType == self::PUBLIC_TYPE) {
            if($type == self::PRIVATE_TYPE) {
                return self::NONE_TYPE;
            }
            if($type == self::PROTECTED_TYPE) {
                return self::PROTECTED_TYPE;
            }
            if($type == self::PUBLIC_TYPE) {
                return self::PUBLIC_TYPE;
            }
        }
        return self::NONE_TYPE;
    }

    /**
     * Function that check if is attribute or method allowed to be inherited via class reference.
     * <type>:
     *     int var;
     *
     * class B: <inheritanceClassType> C, D ...
     *
     * @param string $type
     * @param string $inheritanceClassType
     *
     * @return null|string
     */
    public static function isAllowedToInheritance($type, $inheritanceClassType)
    {
        return self::getInheritanceType($type, $inheritanceClassType) ? true : false;
    }

}
