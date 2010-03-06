# Returns the svn version number of the current directory.
# Works in an svn working copy, or in a git svn clone of an svn repo.
# thanks pete goodliffe (http://goodliffe.blogspot.com/2009/08/code-how-to-spell-svnversion-in-git.html)

alias git_svnversion="git svn find-rev `git log -1 --pretty=format:%H 2>/dev/null` 2>/dev/null"
get_svnversion()
{
	SVNVERSION=`svnversion`
	if [ "X$SVNVERSION" == "Xexported" -o "X$SVNVERSION" == "X" ]; then
		git_svnversion
	else
		echo "`svnversion -c -n . | awk -F: '{print $NF}' | sed -e '1,$s/[a-zA-Z\ ]*//g'`"; 
	fi
}

REV=$(get_svnversion)
echo $REV;

#REV=sed -e "s/build\.number\=\(.*\)/build\.number\=$REV/" src/boot/core.ini > tmp.ini
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

if [ -f src/boot/core.ini ]
then
	sed -e "s/build\.number\=\(.*\)/build\.number\=$REV/" src/boot/core.ini > tmp.ini
	if [ -f tmp.ini ]
	then
		mv -f tmp.ini src/boot/core.ini
	fi
fi
