SRCPATH=$1
USER=$2
echo "Confirm, cp $SRCPATH to `pwd` as user $USER ... "
echo "Ctrl-C to quit, enter to continue"
read confirm
echo "TESTING ..."
tar -c -C $SRCPATH --exclude=".svn" --exclude="*~" --owner="$USER" . | tar -t --owner="$USER"  

echo "That was a test.  Ctrl-C to quit, enter to extract for real"
read confirm2
tar -c -C $SRCPATH --exclude=".svn" --exclude="*~" --owner="$USER" . | tar -xv --owner="$USER"  
