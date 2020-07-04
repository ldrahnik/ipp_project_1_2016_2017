ASSESSMENT 
==========

4/12b ([assessment report](https://github.com/ldrahnik/ipp_project_1_2016_2017/issues/34))

CLS: C++ Classes
================

## Příklad spuštění:

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
