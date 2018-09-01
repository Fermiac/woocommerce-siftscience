set -e
export DEBIAN_FRONTEND=noninteractive

# Install nginx, php, mariadb, nodejs, yarn
apt-get update -y
apt-get install -y --no-install-recommends apt-utils curl ca-certificates apt-transport-https wget

echo "Installing additional libs..."
echo "deb http://packages.dotdeb.org jessie all" | tee /etc/apt/sources.list.d/dotdeb.list
echo "deb-src http://packages.dotdeb.org jessie all" | tee /etc/apt/sources.list.d/dotdeb-src.list
wget -q https://packages.sury.org/php/apt.gpg -O- | apt-key add -
echo "deb https://packages.sury.org/php/ jessie main" | tee /etc/apt/sources.list.d/php.list
curl -sSkL https://www.dotdeb.org/dotdeb.gpg -o dotdeb.gpg
apt-key add dotdeb.gpg

echo "Installing packages..."
curl -sSkL https://deb.nodesource.com/setup_8.x | bash -
apt-get update -y
apt-get install -y --no-install-recommends unzip supervisor php7.2 php7.2-fpm php7.2-imagick \
  php7.2-curl php7.2-mysql php7.2-xml php7.2-memcached php7.2-mbstring php7.2-zip \
  nginx mariadb-server nodejs less build-essential sqlite3 libsqlite3-dev git ruby ruby-dev
npm install -g grunt eslint node-gyp sass check-node-version

echo "Install mailcatcher..."
gem uninstall mail
gem install mail -v 2.6.6
gem install mailcatcher
sed -i 's/.*sendmail_path.*/sendmail_path = \/usr\/local\/bin\/catchmail -f admin@woocommerce\.dev/g' /etc/php/7.2/fpm/php.ini
sed -i 's/.*sendmail_path.*/sendmail_path = \/usr\/local\/bin\/catchmail -f admin@woocommerce\.dev/g' /etc/php/7.2/cli/php.ini
mkdir /run/php

echo "Installing wordpress..."
curl -sSkL https://wordpress.org/latest.tar.gz | tar -xzvC /var/www
rm -rf /var/www/wordpress/wp-content

echo "Installing wp-cli..."
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp
alias wp="wp --allow-root"

echo "Setting up database..."
service mysql start
mysql -e "CREATE DATABASE wordpress;"
mysql -e "GRANT ALL PRIVILEGES ON wordpress.* TO wordpress@localhost IDENTIFIED BY 'wordpress';"
apt-get install -y phpmyadmin
service mysql stop
ln -s /usr/share/phpmyadmin /var/www/wordpress/

mkdir -p /var/log/supervisor

echo "Setting up configs..."
mv docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
mv docker/nginx.conf /etc/nginx/conf.d/nginx.conf
mv docker/wp-config.php /var/www/wordpress/wp-config.php

echo "Cleaning up..."
rm -rf docker
apt-get autoremove -y
