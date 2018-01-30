FROM php:7.2.1-cli

RUN docker-php-ext-install pcntl
RUN docker-php-ext-install sockets

WORKDIR /var/application

ENTRYPOINT [ "./bin/server.php"]
