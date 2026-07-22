# ============================================================
# Vite & Gourmand — image de production (PHP 8.3 + Apache)
#
# Apache est retenu pour que le .htaccess de la racine s'applique tel quel :
# c'est lui qui porte la liste blanche des fichiers servis (protection mise
# en place en Phase 9) et les en-têtes de sécurité.
# ============================================================

FROM php:8.3-apache

# --- Extensions PHP requises par l'application -------------------------------
# pdo_mysql : accès à la base relationnelle (requêtes préparées réelles)
# gd        : traitement des images
# mongodb   : base NoSQL des statistiques (extension PECL, pas native)
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libssl-dev pkg-config \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && rm -rf /var/lib/apt/lists/*

# --- Apache ------------------------------------------------------------------
# AllowOverride All : sans cela le .htaccess serait ignoré et les fichiers
# sensibles (.env, database/, docs/) redeviendraient accessibles.
RUN a2enmod rewrite headers \
    && printf '<Directory /var/www/html>\n    AllowOverride All\n    Require all granted\n</Directory>\n' \
         > /etc/apache2/conf-available/vite-et-gourmand.conf \
    && a2enconf vite-et-gourmand \
    && printf 'ServerName localhost\n' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

# --- PHP en configuration de production --------------------------------------
# (display_errors désactivé, entre autres)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# --- Code applicatif ---------------------------------------------------------
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# L'hébergeur impose le port d'écoute via $PORT : il est appliqué au démarrage.
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
