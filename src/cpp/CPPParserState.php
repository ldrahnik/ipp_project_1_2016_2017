<?php

/*
 * This file is part of ldrahnik/ipp_1_project.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>, <xdrahn00@stud.fit.vutbr.cz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CLS\CPP;

/**
 * Class CPPParserState
 */
class CPPParserState {

    const START_POINT = 'start_point';
    const CLASS_KEY_WORD = 'class_key_word';
    const CLASS_INHERITANCE = 'class_inheritance';
    const CLASS_INHERITANCE_PRIVACY = 'class_inheritance_privacy';
    const CLASS_INHERITANCE_NAME = 'class_inheritance_name';
    const CLASS_INHERITANCE_NEXT = 'class_inheritance_next';
    const COLON = ':';
    const COMMA = ',';
    const SEMICOLON = ';';
    const CLASS_BODY_DEFINITION = 'class_body_definition';
    const CLASS_BODY = 'class_body';
    const LEFT_CURLY_BRACKET = '{';
    const RIGHT_CURLY_BRACKET = '}';
    const CLASS_PRIVACY = 'class_privacy';
    const CLASS_ADD = 'class_add';
    const CLASS_STATEMENT = 'class_statement';
    const TYPE = 'attribute_type';
    const TYPE_NEXT = 'attribute_type_next';
    const AMPERSAND_TYPE_NEXT = '&_type_next';
    const POINTER_TYPE_NEXT = '*_type_next';
    const ALREADY_DEFINED_CLASS_NAME = 'already_defined_class_name';
    const STATIC_TYPE = 'static_type';
    const METHOD = 'method';
    const NAME = 'name';
    const LEFT_BRACKET = '(';
    const RIGHT_BRACKET = ')';
    const METHOD_ARGUMENTS = 'method_arguments';
    const METHOD_ARGUMENTS_NEXT = 'method_arguments_next';
    const METHOD_TAIL = 'method_tail';
    const ZERO = '0';
    const EQUAL_SIGN = '=';
    const VOID = 'void';
    const SOMETHING_TAIL = 'something_tail';
    const VAR_NEXT = 'var_next';
    const METHOD_ADD = 'method_add';
    const CONSTRUCTOR_DECONSTRUCTOR_TYPE = 'con_decon_type';
    const TILDE = '~';
    const TYPE_NEXT_PART = 'type_next_part';

}
