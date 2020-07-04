ASSESSMENT 
==========

4/12b ([assessment report](https://github.com/ldrahnik/ipp_project_1_2016_2017/issues/34))

CLS: C++ Classes
================

## Příklad spuštění:

```
php5.6 ./cls.php --help
Options:
    --help              Display this help message
    --input=file        Input text file which contains classes in language C++. If is parameter missing input is standard.
    --output=file       Output text file in XML format. If is parameter missing output is standard.
    --pretty-xml=k      Output XML formatting. Without parameter is formatting free. Default k is setup to 4. 
    --details=class     Instead of write dependencies to output is write details of class. If there is not defined class details are write about all classes.
    --conflicts         With enabled --details store conflicts between inherited methods and arguments (even private which are not allowed to inherite) and display that.
```
```
php5.6 ./cls.php < ./tests/cls-supplementary-tests/test01.in
<?xml version="1.0" encoding="UTF-8"?>
<model>
    <class name="A" kind="concrete">
        <class name="B" kind="concrete">
            <class name="D" kind="concrete"/>
        </class>
    </class>
    <class name="C" kind="concrete">
        <class name="D" kind="concrete"/>
    </class>
</model>
```

```
php5.6 ./cls.php < ./tests/cls-supplementary-tests/test02.in --details=A
<?xml version="1.0" encoding="UTF-8"?>
<class name="A" kind="abstract">
    <private>
        <methods>
            <method name="f" type="int" scope="instance">
                <virtual pure="yes"/>
                <arguments></arguments>
            </method>
        </methods>
    </private>
</class>
```

## Omezení programu:

## Rozšíření programu:

## Testování programu:

```
make test
# pustí projekt s dodanými testy a výstupy uloží do složky
cd ./tests/cls-supplementary-tests && bash _stud_tests.sh  /home/ldrahnik/projects/ipp_project_1_2016_2017 /home/ldrahnik/projects/ipp_project_1_2016_2017/tests/cls-supplementary-tests/out
# provede porovnání výstupů
cd ./tests/cls-supplementary-tests && bash _stud_tests_diff.sh  /home/ldrahnik/projects/ipp_project_1_2016_2017/jexamxml.jar /home/ldrahnik/projects/ipp_project_1_2016_2017/cls-supplementary-tests/jexamxml_tmp /home/ldrahnik/projects/ipp_project_1_2016_2017/cls-supplementary-tests/cls_options /home/ldrahnik/projects/ipp_project_1_2016_2017/tests/cls-supplementary-tests/out /home/ldrahnik/projects/ipp_project_1_2016_2017/tests/cls-supplementary-tests/ref-out
*******TEST01 PASSED
*******TEST02 PASSED
*******TEST03 PASSED
*******TEST04 PASSED
*******TEST05 PASSED
*******TEST06 PASSED
*******TEST07 PASSED
*******TEST08 PASSED
*******TEST09 PASSED
*******TEST10 PASSED
*******TEST11 PASSED
*******TEST12 PASSED
# úklid
rm -rf /home/ldrahnik/projects/ipp_project_1_2016_2017/cls-supplementary-tests/jexamxml_tmp
```

## Odevzdané soubory:

```
xdrahn00-CLS
├── CLS-doc.pdf
├── cls.php
├── composer.json
├── readme.md
├── rozsireni
├── src
│   ├── CLSArgumentParser.php
│   ├── CLSMode.php
│   ├── CLSOption.php
│   ├── CLSParser.php
│   ├── cpp
│   │   ├── CPPParser.php
│   │   ├── CPPParserState.php
│   │   ├── error
│   │   │   └── Error.php
│   │   ├── exception
│   │   │   ├── ElementConflictDuringInheritance.php
│   │   │   ├── InvalidInputFormat.php
│   │   │   ├── InvalidType.php
│   │   │   ├── StaticCanNotBeVirtual.php
│   │   │   ├── UnknownInheritanceClassName.php
│   │   │   └── UnknownTypeClassName.php
│   │   └── structure
│   │       └── object
│   │           ├── CPPClassAttribute.php
│   │           ├── CPPClassElement.php
│   │           ├── CPPClassMethodArgument.php
│   │           ├── CPPClassMethod.php
│   │           ├── CPPClass.php
│   │           ├── CPPInheritance.php
│   │           └── type
│   │               ├── CPPClassAttribute.php
│   │               ├── CPPClassKind.php
│   │               ├── CPPPrivacy.php
│   │               └── CPPScope.php
│   └── xml
│       └── XMLElement.php
└── vendor
    ├── autoload.php
    └── composer
        ├── autoload_classmap.php
        ├── autoload_namespaces.php
        ├── autoload_psr4.php
        ├── autoload_real.php
        ├── autoload_static.php
        ├── ClassLoader.php
        ├── installed.json
        └── LICENSE

10 directories, 38 files
```
