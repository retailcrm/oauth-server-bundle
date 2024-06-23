PHP=php

vendor: composer.json
	@$(PHP) composer install -o -n --no-ansi
	@touch vendor || true

phpunit: vendor
	@$(PHP) vendor/bin/phpunit --color=always

php-cs: vendor
	@$(PHP) vendor/bin/php-cs-fixer check -vv

phpstan: vendor
	@$(PHP) vendor/bin/phpstan analyse

check: php-cs phpunit
