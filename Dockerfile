# ============================================================
# Vite & Gourmand — image de production (PHP 8.3, serveur intégré + router.php)
#
# Le serveur intégré de PHP avec router.php reproduit exactement
# l'environnement de développement utilisé tout le projet :
# la liste blanche des fichiers servis (protection Phase 9) est
# appliquée par router.php lui-même.
# ============================================================

FROM php:8.3-cli

# --- Extensions PHP requises par l'application -------------------------------
# pdo_mysql : accès à la base relationnelle (requêtes préparées réelles)
# gd        : traitement des images
# mongodb   : base NoSQL des statistiques (extension PECL, compilée ici)
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev libssl-dev pkg-config \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && rm -rf /var/lib/apt/lists/*

# --- PHP en configuration de production (display_errors désactivé…) ----------
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# --- Code applicatif ---------------------------------------------------------
WORKDIR /app
COPY . /app

# L'hébergeur impose le port d'écoute via $PORT.
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-8080} -t /app /app/router.php"]
