#!/usr/bin/env zsh
if [ -z "$TRANSPHPILE_PATH" ]; then
	echo "Need to install Transphpile and set TRANSPHPILE_PATH = path/to/bin/transphpile"
	exit 1
fi
	
for foo in src/**/*.php; do
    if php -d memory_limit=500M $TRANSPHPILE_PATH transpile "$foo" ; then
		cp "php5/$foo" "$foo"
	else
		echo "Failed to transpile $foo";
	fi
done
