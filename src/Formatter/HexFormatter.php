<?php

namespace Nmure\Encryptor\Formatter;

use Nmure\Encryptor\Exception\ParsingException;

class HexFormatter implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function format($iv, $encrypted)
    {
        return bin2hex(sprintf('%s%s', $iv, $encrypted));
    }

    /**
     * {@inheritdoc}
     */
    public function parse($input, $ivLength)
    {
        $input = hex2bin($input);
        
        return array(
            self::KEY_IV => substr($input, 0, $ivLength),
            self::KEY_DATA => substr($input, $ivLength, strlen($input)),
        );
    }
}
