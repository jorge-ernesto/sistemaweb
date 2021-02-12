#!/bin/bash
FILES=$(pcregrep -rnM --include='^.*\.php$' '^\?\>(?=([\s\n]+)?$(?!\n))' .);
 
for MATCH in $FILES;
do
   FILE=`echo $MATCH | awk -F ':' '{print $1}'`;
   TARGET=`echo $MATCH | awk -F ':' '{print $2}'`;
   LINE_COUNT=`wc -l $FILE | awk -F " " '{print $1}'`;
   echo "Removing lines ${TARGET} through ${LINE_COUNT} from file $FILE...";
   sed -i "${TARGET},${LINE_COUNT}d" $FILE;
done;
