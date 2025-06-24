#!/bin/bash

echo "Waiting for MySQL to be ready..."
until mysqladmin ping -h db -uappuser -papppass --silent; do
    sleep 2
done

echo "MySQL is ready. Running createTables.php..."
php /scripts/createTables.php

echo "Starting Apache..."
exec apache2-foreground
