FROM php:8-cli

# Обновляем
RUN apt-get update && \
    apt-get install -y --no-install-recommends

WORKDIR /app

# нужно для проверки оступа к БД
RUN apt-get install -y netcat-openbsd


# Установка расширения PDO_Mysql
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install mysqli


# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Установка часового пояса Москвы
RUN ln -sf /usr/share/zoneinfo/Europe/Moscow /etc/localtime

# Очистка папки log при запуске контейнера
RUN rm -rf log/*
RUN rm -rf /app/log/*

# Такой запук, нам нужен, чтоб писать вывод ошибок в лог
#  && tail -f /dev/null позволяет не завершать докер после выполнения
CMD ["sh", "-c", "php /app/script.php >> /app/log/error.log 2>&1 && tail -f /dev/null"]

