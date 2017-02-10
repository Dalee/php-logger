phpcs := ./vendor/bin/phpcs --standard=ruleset.xml

test:
	$(phpcs) src/
	./vendor/bin/phpunit
