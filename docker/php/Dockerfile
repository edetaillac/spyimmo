FROM jprjr/php-fpm:latest

USER root

RUN sed -i "s#;date.timezone =.*#date.timezone = \"Europe/Paris\"#g" /etc/php/php.ini
RUN usermod -u 1000 http
