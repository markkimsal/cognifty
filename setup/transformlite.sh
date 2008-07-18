for x in `ls db_install/*.sql`
do
	echo $x
	sed -e "s/auto_increment//" $x >  sqlite_install/`basename $x`
	sed -e "s/^.*COLLATE.*$//"  sqlite_install/`basename $x` > tmp.sqlite
	mv -f tmp.sqlite sqlite_install/`basename $x`
	sed -e "s/unsigned//"  sqlite_install/`basename $x` > tmp.sqlite
	mv -f tmp.sqlite sqlite_install/`basename $x`
	sed -e "s/\(.*\)\(ENGINE.*\)$/\1;/"  sqlite_install/`basename $x` > tmp.sqlite
	mv -f tmp.sqlite sqlite_install/`basename $x`

#ALTER TABLE `cgn_account` COLLATE utf8_general_ci;
done
