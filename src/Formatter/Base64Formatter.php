<?php

namespace Nmure\Encryptor\Formatter;

use Nmure\Encryptor\Exception\ParsingException;

class Base64Formatter implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function format($iv, $data)
    {
        return sprintf('%s:%s', base64_encode($iv), base64_encode($data));
    }

    /**
     * {@inheritdoc}
     */
    public function parse($data)
    {
        $parts = explode(':', $data);

        if (2 !== sizeof($parts)) {
            throw new ParsingException(sprintf('Unable to parse the given data with the "%s" formatter', get_class($this)));
        }

        return array(
            self::KEY_IV => base64_decode($parts[0]),
            self::KEY_DATA => base64_decode($parts[1]),
        );
    }
}
