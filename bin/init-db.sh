#!/bin/env bash

if [ $# -ne 3 -a  $# -ne 4 ] || [ "$1" = "help" ] || [ "$1" = "h" ]
then
echo "./init-db.sh LOGIN PASSWORD DATABASE [usa|fr|all]"
exit;
fi 


DATABASE=$3
LOGIN=$1
PASS=$2
MYSQL="mysql --default-character-set=utf8 -u $LOGIN -p$PASS $DATABASE"
PATH_DB=../data/


cd $PATH_DB
echo "create structure     ... 1/2"
${MYSQL} < structure.sql
echo "insert country       ... 2/2"
${MYSQL} < country.sql

COND=$4

if [ "$COND" = "usa" ] || [ "$COND" = "all" ] || [ -z "$COND" ] 
then
	echo "insert division usa  ..."
	${MYSQL} < division-usa.sql
	echo "insert city     usa  ..."
	${MYSQL} < city-usa.sql
fi


if [ "$COND" = "fr" ] || [ "$COND" = "all" ] || [ -z "$COND" ] 
then
	echo "insert division fr  ..."
	${MYSQL} < division-fr.sql
	echo "insert city     fr  ..."
	${MYSQL} < city-fr.sql
fi
