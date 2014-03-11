running=$(ps axho user,comm|grep -E "httpd|apache"|uniq|grep -v "root"|awk 'END {if ($1) print $1}')

if [ "$running" = "www-data" ]; then
	rm -f /var/log/apache2/access.log.*
	rm -f /var/log/apache2/error.log.*
	echo "" > /var/log/apache2/error.log
	
	rm -f /var/www/socialreader/Branches/mobileFramework/src/logs/cron/log*
	rm -f /var/www/socialreader/Branches/mobileFramework/src/logs/misc/log*
	rm -f /var/www/socialreader/Branches/mobileFramework/src/logs/oAuth/log*
	
	rm -f /var/www/socialreader/Branches/mobileFramework/src/elasticSearch/logs/elasticsearch.log.*
fi

if [ "$running" = "apache" ]; then
	rm -f /var/log/httpd/access_log-*
	rm -f /var/log/httpd/error_log-*
	echo "" > /var/log/httpd/error_log
	
	rm -f /var/www/html/socialreader/Branches/mobileFramework/src/logs/cron/log*
	rm -f /var/www/html/socialreader/Branches/mobileFramework/src/logs/misc/log*
	rm -f /var/www/html/socialreader/Branches/mobileFramework/src/logs/oAuth/log*
	
	rm -f /var/www/html/socialreader/Branches/mobileFramework/src/elasticSearch/logs/elasticsearch.log.*
fi