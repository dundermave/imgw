FROM drupal:10.2.4-php8.3-apache

RUN apt-get update \
    && apt-get install -y mariadb-client \
    && rm -rf /var/lib/apt/lists/*
