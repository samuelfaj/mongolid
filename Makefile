sniff:
	vendor/bin/phpcs ./src --standard='./coding_standard.xml'

phpunit:
	vendor/bin/phpunit

coverage:
	vendor/bin/phpunit --coverage-html ./.coverage
