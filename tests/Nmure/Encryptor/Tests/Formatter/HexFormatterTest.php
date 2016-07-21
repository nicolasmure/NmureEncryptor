<?php

namespace Nmure\Encryptor\Tests\Formatter;

use Nmure\Encryptor\Formatter\HexFormatter;

class HexFormatterTest extends AbstractFormatterTest
{
    /**
     * {@inheritdoc}
     */
    public function testFormat()
    {
        $expected = bin2hex(sprintf('%s%s', $this->iv, $this->data));
        $this->assertEquals($expected, $this->formatter->format($this->iv, $this->data));
    }

    /**
     * {@inheritdoc}
     *
     * Asserts that the HexFormatter is working with an hex key and with
     * and hex key converted to a binary key.
     */
    public function testFormatterWithEncryptor()
    {
        parent::testFormatterWithEncryptor();

        $this->secret = hex2bin($this->secret);
        parent::testFormatterWithEncryptor();
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormatter()
    {
        return new HexFormatter();
    }
}
