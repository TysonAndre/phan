#!/usr/bin/env zsh
if [ -z "$TRANSPHPILE_PATH" ]; then
	echo "Need to install Transphpile and set TRANSPHPILE_PATH = path/to/bin/transphpile"
	exit 1
fi
	
# Note: Add -v after the transpile command to show stack traces if this fails
for filepath in src/**/*.php tests/Phan/**/*.php; do
	if grep -q 'This code has been transpiled via TransPHPile\. For more information' "$filepath"; then
		echo "Skipping '$filepath', already transpiled"
		continue
	fi
	if php -d memory_limit=500M $TRANSPHPILE_PATH transpile "$filepath" ; then
		cp "php5/$filepath" $filepath
		echo -n "."
	else 
		echo
		echo "Failed to transpile '$filepath'";
	fi
done
echo
echo "Created Transphpiled php files in php5/"
