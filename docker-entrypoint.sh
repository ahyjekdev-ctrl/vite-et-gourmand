#!/bin/sh
# Apache écoute par défaut sur le port 80, mais l'hébergeur impose le sien
# via la variable $PORT. On l'applique avant de démarrer le serveur.
set -e

PORT="${PORT:-80}"

sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s#<VirtualHost \*:[0-9]*>#<VirtualHost *:${PORT}>#" /etc/apache2/sites-available/000-default.conf

exec "$@"
