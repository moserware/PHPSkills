.PHONY: build
build:
	docker compose run --rm php composer install

.PHONY: test
test:
	docker compose run --rm php vendor/bin/phpunit --testdox