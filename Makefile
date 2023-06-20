# Выполним по умолчанию, при запуске пустого make
.DEFAULT_GOAL := help

# Подключим файл конфигурации
include app/.env

# И укажем его для docker-compose
ENV = --env-file app/.env

# Добавим красоты и чтоб наши команды было видно в теле скрипта
PURPLE = \033[1;35m $(shell date +"%H:%M:%S") --
RESET = --\033[0m

# Считываем файл, всё что содержит двойную решётку # Это описание к командам
help:
	@grep -E '^[a-zA-Z-]+:.*?## .*$$' Makefile | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-17s\033[0m %s\n", $$1, $$2}'
.PHONY: help

delay: # Задержка, она требуется, чтоб MYSQL успел инициализироваться
	@sleep 3
.PHONY: delay

wait-for-mysql: ## Задержка для MySQL, необходимая для инициализации, работает, только если mysql будет торчать наружу
	@echo "$(PURPLE)Ожидание инициализации MySQL$(RESET)"
	@seconds=0; \
	while ! docker-compose $(ENV) exec php-cli nc -z mysql 3306; do \
		seconds=$$((seconds+1)); \
		sleep 1; \
		echo "Прошло: $$seconds сек."; \
	done
.PHONY: wait-for-mysql

# Если это developer окружение, то подключим debug профиль
PROFILE =
ifeq ($(ENVIRONMENT),developer)
	PROFILE := --profile debug
endif

init: ## Инициализация проекта
init: docker-down docker-pull docker-build docker-up composer-install wait-for-mysql import-db migrate

update: ## Пересобрать контейнер, обновить композер и миграции
update: docker-down docker-pull docker-build docker-up composer-install wait-for-mysql migrate

restart: ## Restart docker containers
restart: docker-down docker-up

php-bash: ## Подключается к контейнеру PHP
	docker-compose $(ENV) exec php-cli bash

composer: ## Подключается к контейнеру PHP и работаем с composer
	docker-compose $(ENV) exec php-cli bash -c "composer -V; bash"

migrate: ## Применить миграции
	@echo "$(PURPLE) Применить миграции $(RESET)"
	docker-compose $(ENV) run --rm php-cli php vendor/bin/phinx migrate --configuration phinx.php

import-db: ## Импорт образа бд
	@echo "$(PURPLE) Импорт БД (если нужен) $(RESET)"
	@#gunzip < ./app/db/dump.sql.gz | docker-compose $(ENV) exec -T mysql mysql -uroot -p${MYSQL_ROOT_PASSWORD} BD

composer-install: ## Поставим пакеты композера
	@echo "$(PURPLE) Поставим пакеты композера $(RESET)"
	@docker-compose $(ENV) run --rm composer

docker-up: ## Поднимем контейнеры
	@echo "$(PURPLE) Поднимем контейнеры $(RESET)"
	docker-compose $(ENV) $(PROFILE) up -d

docker-build: ## Соберём образы
	@echo "$(PURPLE) Соберём образы $(RESET)"
	docker-compose $(ENV) $(PROFILE) build

docker-pull: ## Поучим все контейнеры
	@echo "$(PURPLE) Поучим все контейнеры $(RESET)"
	docker-compose $(ENV) $(PROFILE) pull --include-deps

docker-down: ## Остановим контейнеры
	@echo "$(PURPLE) Остановим контейнеры $(RESET)"
	docker-compose $(ENV) $(PROFILE) down --remove-orphans

