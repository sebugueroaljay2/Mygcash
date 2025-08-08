FROM laravelsail/php82-composer-node

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN npm install && npm run build

RUN php artisan config:cache && php artisan route:cache

RUN chown -R www-data:www-data /var/www

CMD php artisan serve --host=0.0.0.0 --port=${PORT}