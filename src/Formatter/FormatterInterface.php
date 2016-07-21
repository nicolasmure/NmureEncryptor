<?php

namespace Nmure\Encryptor\Formatter;

interface FormatterInterface
{
    /**
     * The array key used to store the IV from the parsed string.
     *
     * @var string
     */
    const KEY_IV = 'iv';

    /**
     * The array key used to store the encrypted data from the parsed string.
     *
     * @var string
     */
    const KEY_DATA = 'data';

    /**
     * Format the given IV and the encrypted data to a string.
     * The string should then be stored and be parsed using the
     * parse function of this class.
     *
     * @param  string $iv The Initialization Vector.
     * @param  string $encrypted The encrypted data.
     *
     * @return string The formatted string, containing the IV and the encrypted data.
     */
    public function format($iv, $encrypted);

    /**
     * Parse the data formated by this class and
     * returns an array containing the IV
     * and the encrypted data.
     *
     * @param  string $input The formated data containing the IV and the encrypted data.
     * @param  int    $ivLength The length of the IV used by the cipher method.
     *
     * @throws Nmure\Encryptor\Exception\ParsingException When not able to parse the given data.
     *
     * @return array An array indexed with self::KEY_IV and self::KEY_DATA keys.
     */
    public function parse($input, $ivLength);
}
