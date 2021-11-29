#!/bin/bash

echo "<INFO> Install folder: $3"
echo "<INFO> Creating symlink for WU catcher site"
ln -s -f "$LBPCONFIG/$3/002-wunderground.conf" "/etc/apache2/sites-available/"
a2ensite 002-wunderground.conf
if [ $0 -eq 0 ]; then
	echo "<OK> New WU catcher site was enabled"
else
	echo "<ERROR> WU catcher site could not be enabled"
fi

echo "<INFO> Reloading Apache configuration"
systemctl apache2 reload
