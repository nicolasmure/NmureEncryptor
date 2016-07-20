# NmureEncryptor

[![Build Status](https://travis-ci.org/nicolasmure/NmureEncryptor.svg?branch=master)](https://travis-ci.org/nicolasmure/NmureEncryptor)
[![Coverage Status](https://coveralls.io/repos/github/nicolasmure/NmureEncryptor/badge.svg?branch=master)](https://coveralls.io/github/nicolasmure/NmureEncryptor?branch=master)

PHP data encryptor using open_ssl

```php
$enc = new Encyptor($secret, $cypher);
$enc->setFormatter(new FormatterInterface());
```

## Development

### Installing dependencies
```bash
docker-compose run --rm composer install
```

### Testing
``` bash
docker-compose run --rm phpunit -c /app
```
