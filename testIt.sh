#!/bin/bash

echo "---------------------------------------------Tests started-----------------------------------------"

./final_distrib.sh
cp ./xkozak15-DKA.zip ../dka-supplementary-tests/


cd ../dka-supplementary-tests
unzip xkozak15-DKA.zip
./_stud_tests.sh

if [ $? -ne 0 ]; then
	echo "The test script did not execute successfully"
	exit 1
fi 



filesToCompare=`ls ../dka-supplementary-tests/ref-out/`

for file in $filesToCompare; do
	echo "-------------------------$file vs ref-out/$file-------------------------------------------
		"
	
echo '
	echo "------------$file--------------"
	cat $file

	echo "------------ref-out/$file--------------"
	cat ref-out/$file

	echo "--------------------------"

' > /dev/null

	diff $file ref-out/$file

	echo "------------------------------------------------------------------------------------------
		"
done

echo "---------------------------------------------Tests finished-----------------------------------------"
