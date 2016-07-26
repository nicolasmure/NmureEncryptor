# NmureEncryptor

[![Build Status](https://travis-ci.org/nicolasmure/NmureEncryptor.svg?branch=master)](https://travis-ci.org/nicolasmure/NmureEncryptor)
[![Coverage Status](https://coveralls.io/repos/github/nicolasmure/NmureEncryptor/badge.svg?branch=master)](https://coveralls.io/github/nicolasmure/NmureEncryptor?branch=master)

PHP data encryptor using open_ssl

## Table of contents
- [Installation](#installation)
- [Usage](#usage)
    - [Basic usage](#basic-usage)
        - [Encrypt](#encrypt)
        - [Decrypt](#decrypt)
    - [Advanced usage](#advanced-usage)
- [Formatters](#formatters)
    - [Built-in formatters](#built-in-formatters)
        - [Base64Formatter](#base64formatter)
        - [HexFormatter](#hexformatter)
    - [Make your own formatter](#make-your-own-formatter)
- [API](#api)
- [Troubleshooting](#troubleshooting)
    - [Using the HexFormatter with a C# app](#using-the-hexformatter-with-a-c-app)
- [Integration](#integration)
- [Development / contributing](#development--contributing)
    - [Installing ecosystem](#installing-ecosystem)
    - [Testing](#testing)
    - [PHP CS Fixer](#php-cs-fixer)
- [License](#license)

## Installation
Use composer to install the lib :

```bash
composer require nmure/encryptor "dev-master"
```

## Usage

### Basic usage

#### Encrypt

The simpliest way to use this library is to create a instance of the Encryptor
by passing it a secret key and a cipher method to use during encryption.
To see all the cipher methods supported by your php installation, use the
[openssl_get_cipher_methods](http://php.net/manual/en/function.openssl-get-cipher-methods.php "Gets available cipher methods") function.

Then you can encrypt your plain text data, for instance :
```php
use Nmure\Encryptor\Encryptor;

$encryptor = new Encyptor('452F93C1A737722D8B4ED8DD58766D99', 'AES-256-CBC');
$encrypted = $encryptor->encrypt('plain text data');
```

The encryptor uses an Initialization Vector (IV) in addition to the secret key to encrypt data.
This IV is randomly generated to be sure that 2 encryptions of the same data with the
same key won't produce the same encrypted output.

Thereby, you should store the IV used to encrypt your data along side to the encrypted data
to be able to decrypt it later.

For instance, you could store it in a database :
```
| id |  iv  | encrypted |
-----------------------
| 1  | 945a | oifd4867h |
| 2  | 894d | 62vbyibd6 |
```

##### Decrypt

Then, to decrypt your data, initialize the encryptor and call the `decrypt` function :

```php
use Nmure\Encryptor\Encryptor;

$encryptor = new Encyptor('452F93C1A737722D8B4ED8DD58766D99', 'AES-256-CBC');
// retrieve the IV ($iv) and the encryped data ($encrypted) from your DB
// ...
$encryptor->setIv($iv);
$plainText = $encryptor->decrypt($encrypted);
```

### Advanced usage

If you don't want to deal with how to store the encrypted data and the IV,
you can use the [formatters](#formatters "Formatters documentation").
The formatters combine the IV and the encrypted data into one string to make it
easier to store and to share with an other app.

For instance, if you want to store an encrypted data to a file, you could use
the [Base64Formatter](#base64formatter "Base64Formatter documentation") :

```php
use Nmure\Encryptor\Encryptor;
use Nmure\Encryptor\Formatter\Base64Formatter;

$encryptor = new Encyptor('452F93C1A737722D8B4ED8DD58766D99', 'AES-256-CBC');
$encryptor->setFormatter(new Base64Formatter());

// will produce a string containg the IV and the encrypted data
$encrypted = $encryptor->encrypt('plain text data');
// store $encrypted to a file
```

Then, to decrypt your data, use the same encryptor / formatter couple and simply call
the `decrypt` function with the combined string :

```php
use Nmure\Encryptor\Encryptor;
use Nmure\Encryptor\Formatter\Base64Formatter;

$encryptor = new Encyptor('452F93C1A737722D8B4ED8DD58766D99', 'AES-256-CBC');
$encryptor->setFormatter(new Base64Formatter());

// get the encrypted data from a file, or whatever
// ... $encrypted

// the encryptor uses the formatter to get the IV used for the encryption from
// the given $encrypted string
$plainText = $encryptor->decrypt($encrypted);
```

If you don't want to use the formatter anymore, simply set it to `null` on the encryptor :
```php
$encryptor->setFormatter(null);
```

Using formatters is more convenient as all the work to store the IV along side
the encrypted data is done by the formatters, and not by you anymore.

## Formatters

The formatters are used to combine the IV and the encrypted data into one string
(usually a non binary string), to make it easier to store and to share accross systems.
They all implement the [`FormatterInterface`](/src/Formatter/FormatterInterface.php "Nmure\Encryptor\Formatter\FormatterInterface").

### Built-in formatters

#### Base64Formatter

The string returned by the [`Base64Formatter`](/src/Formatter/Base64Formatter.php "Nmure\Encryptor\Formatter\Base64Formatter")
during the encryption process contains the base64 encoded IV, concatened to a colon (`:`),
concatened to the base64 encoded encrypted data.

As the colon is not a char from the base64 chars, we can easily split this string
in two parts from the colon, and get back the IV and the encrypted data during the decryption process.

#### HexFormatter

The string returned by the [`HexFormatter`](/src/Formatter/HexFormatter.php "Nmure\Encryptor\Formatter\HexFormatter")
during the encryption process contains the hex representation of the concatened
IV and encrypted data binary string.

During the decryption process, this string is splitted to get back the IV
and the encrypted data. We use the cipher method's IV length to determine
where to split this string.

### Make your own formatter

You can of course make your own formatter to suit your needs,
it must just implement the [`FormatterInterface`](/src/Formatter/FormatterInterface.php "Nmure\Encryptor\Formatter\FormatterInterface").

## API

- **Nmure\Encryptor\Encryptor** :
    - `public string encrypt($data)` : encrypts the given plain text string and
    returns it. When a formatter is set to the encyryptor, the returned value of
    this function is the formatted string composed of the IV and the ecrypted data.
    - `public string decrypt($data)` : decrypts the given encrypted data and returns it.
    When a formatter is set to the encryptor, the given data must be the string formatted
    by this formatter. The IV will be determined from the formatted string.
    When no formatter is set, the IV must be set to this encryptor to be able
    to decrypt the given data.
    - `public string generateIv()` : generate a new ramdom IV according to the cipher method,
    set it to the encryptor and returns it.
    - `public void enableAutoIvUpdate()` : enable the automatic IV update before each
    encryption process to be sure that two encryptions of the same data won't produce
    the same output. The automatic IV update is enabled by default.
    - `public void disableAutoIvUpdate()` : disable the automatic IV update before each
    encryption process. The encryption will use the last set IV or generate one if
    no IV was set.
    - `public void turnHexKeyToBin()` : turns the hex secret key into a binary key.
    - `public string getIv()` : returns the IV of the encryptor.
    - `public void setIv($iv)` : set the given IV to the encryptor.
    - `public void setFormatter(FormatterInterface $formatter = null)` : sets the given
    FormatterInterface to the encryptor. To unset the formatter, pass `null` to this function.
- **Nmure\Encryptor\Formatter\FormatterInterface** :
    - `public string format($iv, $encrypted)` : formats the given IV and encrypted data to a string
    and returns it.
    - `public array parse($input, $ivLength)` : parse the given `$input` string and return an array
    containing the IV and the encrypted data. The `$ivLength` parameter can be used to parse
    the `$input` string.

## Troubleshooting

### Using the HexFormatter with a C# app

If you use this formatter with the encryptor to share crypted data with a C# app,
you'll probably have to turn your secret key into a binary key :

```php
use Nmure\Encryptor\Encryptor;
use Nmure\Encryptor\Formatter\HexFormatter;

$encryptor = new Encyptor('452F93C1A737722D8B4ED8DD58766D99', 'AES-256-CBC');
$encryptor->turnHexKeyToBin(); // turning the hex key to a binary key
$encryptor->setFormatter(new HexFormatter());

$encrypted = $encryptor->encrypt('plain text data');
```

## Integration

You can use this library as standalone, or if you're using Symfony,
it is wrapped inside the [`NmureEncryptorBundle`](https://github.com/nicolasmure/NmureEncryptorBundle "A Symfony Bundle for the nmure/encryptor library").

## Development / Contributing

### Installing ecosystem
```bash
docker-compose run --rm composer install
```

### Testing
``` bash
docker-compose run --rm phpunit -c /app
```

The formatters test classes should extend the [`AbstractFormatterTest`](/tests/Nmure/Encryptor/Tests/Formatter/AbstractFormatterTest.php "Nmure\Encryptor\Tests\Formatter\AbstractFormatterTest") class
to be sure that the formatters fulfill the minimum requirements.

### PHP CS Fixer
```bash
docker-compose run --rm phpcs phpcbf --standard=PSR2 /scripts/
```

## License

This library is licensed under the MIT License.
More informations in the [LICENSE](/LICENSE) file.
