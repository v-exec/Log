#!/bin/bash

source ./credentials.sh

p() {
	echo "parsing log"
	ruby log_parse.rb
}

r() {
	echo "updating repo"
	git add --all
	git commit -m "logs update"
	git push -u origin master
}

d() {
	echo "connecting to database"
	mysql -h $h -u $u --password=$p
}

while true
do
	echo -n "log_q>"
	read text

	if [ $text = "parse" ]; then p
	elif [ $text = "repo" ]; then r
	elif [ $text = "database" ]; then d
	elif [ $text = "exit" ]; then exit
	fi
done