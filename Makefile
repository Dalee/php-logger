phpcs := /vendor/bin/phpcs --standard=ruleset.xml

test:
	./vendor/bin/phpunit
	$(phpcs) src/
