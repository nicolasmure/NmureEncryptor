<?php

namespace Nmure\Encryptor\Tests\Formatter;

use Nmure\Encryptor\Formatter\HexFormatter;

class HexFormatterTest extends AbstractFormatterTest
{
    /**
     * {@inheritdoc}
     *
     * Also converts the secret key from hex to binary.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->secret = hex2bin($this->secret);
    }

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
     */
    protected function getFormatter()
    {
        return new HexFormatter();
    }
}
