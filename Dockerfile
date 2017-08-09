FROM php:7.0-apache
MAINTAINER James Rascoe <jr@edataquest.com>

# Install dependencies
RUN apt-get update -y
RUN apt-get install -y sqlite3
RUN apt-get install -y libsqlite3-dev
RUN apt-get install -y postgresql-client
RUN apt-get install -y libpq-dev

RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install pdo_sqlite

# PECL extensions
RUN pecl install raphf
RUN docker-php-ext-enable raphf
RUN pecl install propro
RUN docker-php-ext-enable propro
RUN pecl install pecl_http
RUN docker-php-ext-enable http

RUN a2enmod rewrite
RUN a2enmod headers
RUN a2enmod expires

RUN rm -rf /var/www/html && mkdir /var/www/html && mkdir /tmp/.opcache && chown www-data /tmp/.opcache
COPY ./public /var/www/public
COPY ./private /var/www/private
COPY ./etc/000-default.conf /etc/apache2/sites-available/
COPY ./etc/php.ini /usr/local/etc/php/

COPY ./start.sh /
RUN chmod 755 /start.sh

RUN chown www-data /var/www/private/dbs
RUN chown www-data /var/www/private/dbs/test-website.db


EXPOSE 80
CMD ["/start.sh"]

## To build this environment
## docker build -t edataquest/simpleweb .

## To run this container (adjust --link, -v and -e as needed.)
## docker run --rm --name simpleweb -v ~/projects/simpleweb:/var/www/ -p 80:80 -t -i edataquest/simpleweb

## Run without local volume mount
## docker run --rm --name simpleweb -p 80:80 -t -i edataquest/simpleweb
