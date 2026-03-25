# GIA — PHP 8.2 + Apache + extensions SQL Server (pdo_sqlsrv)
# Image de base : Debian Bookworm (compatible packages Microsoft ODBC)

FROM php:8.2-apache-bookworm

# Dépendances système + outils PECL
RUN apt-get update && apt-get install -y --no-install-recommends \
    gnupg2 curl apt-transport-https ca-certificates \
    unixodbc unixodbc-dev libgssapi-krb5-2 \
    $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

# Dépôt Microsoft + pilote ODBC 18
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc \
    | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && echo "deb [arch=amd64 signed-by=/usr/share/keyrings/microsoft-prod.gpg] https://packages.microsoft.com/debian/12/prod bookworm main" \
    > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends msodbcsql18 \
    && rm -rf /var/lib/apt/lists/*

# Extensions PHP Microsoft (sqlsrv)
RUN pecl install sqlsrv pdo_sqlsrv \
    && docker-php-ext-enable sqlsrv pdo_sqlsrv

# Document root Apache = racine du projet
ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Application (sans includes/config.php : fourni par la config Docker)
COPY . /var/www/html/

# Configuration BDD Docker (authentification SQL + certificat de confiance pour le conteneur MSSQL)
RUN cp /var/www/html/includes/config.docker.php /var/www/html/includes/config.php

# Dossier uploads accessible en écriture par Apache
RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html/uploads

COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
