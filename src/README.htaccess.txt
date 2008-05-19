RewriteEngine On

#uncomment the next two lines if you want to control and remove the "www." prefix from your domains
#RewriteCond %{HTTP_HOST} ^www.domain.com$ [NC]
#RewriteRule ^(.*)$ http://domain.com/$1 [R=301,L]

#fill this out if you're not running from a top level url
#RewriteBase /~user/sub/dir/

# Deny access to .htaccess
RewriteRule ^\.htaccess$ - [F]
# Do not worry about missing favico
RewriteRule ^favicon\.ico - [L]
RewriteRule ^favico\.ico - [L]
RewriteRule ^robots\.txt - [L]
# needed for plesk based hosting
RewriteRule ^webstat - [L]


# these are the actual conditions and rules that work on the index.php file
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^(media|icons|templates)/
#choose the top one if you're running from a top level url
#RewriteRule ^(.*)$ /index.php/$1 [L]
RewriteRule ^(.*)$ index.php/$1 [L]


