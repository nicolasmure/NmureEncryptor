<?php

namespace Nmure\Encryptor\Tests\Formatter;

use Nmure\Encryptor\Formatter\Base64Formatter;

class Base64FormatterTest extends AbstractFormatterTest
{
    /**
     * {@inheritdoc}
     */
    public function testFormat()
    {
        $expected = sprintf('%s:%s', base64_encode($this->iv), base64_encode($this->data));
        $this->assertEquals($expected, $this->formatter->format($this->iv, $this->data));
    }

    /**
     * @expectedException Nmure\Encryptor\Exception\ParsingException
     * @expectedExceptionMessage Unable to parse the given data with the "Nmure\Encryptor\Formatter\Base64Formatter" formatter
     */
    public function testParseThrowsParsingException()
    {
        $this->formatter->parse('abcdef', 0);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFormatter()
    {
        return new Base64Formatter();
    }
}
