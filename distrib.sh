#!/bin/bash

OUTPUT_FILE='dka.php'
main_file_content=`tail -n+3 main.php`
files_to_include=`grep "include .*\.php" include.php | tr \' ' ' | cut -d' ' -f3`


echo "<?php
/**
* David Kozak
* Prvni projekt do IPP
* Determinizace Konecneho automatu
* rozsireni wsfa,rules,string
*/" > ${OUTPUT_FILE}

for file in ${files_to_include} ; do
	tail -n+2 $file >> ${OUTPUT_FILE}
done

echo "${main_file_content}" >> ${OUTPUT_FILE}
