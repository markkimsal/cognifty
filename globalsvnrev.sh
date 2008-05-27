REV="`svnversion -c -n . | awk -F: '{print $NF}' | sed -e '1,$s/[a-zA-Z\ ]*//g'`"; 
sed -e "s/build\.number\=\(.*\)/build\.number\=$REV/" src/boot/core.ini > tmp.ini
if [ -f tmp.ini ]
then
	mv -f tmp.ini src/boot/core.ini
fi

if [ -f src/boot/local/core.ini ]
then
	sed -e "s/build\.number\=\(.*\)/build\.number\=$REV/" src/boot/local/core.ini > tmp.ini
	if [ -f tmp.ini ]
	then
		mv -f tmp.ini src/boot/local/core.ini
	fi
fi
