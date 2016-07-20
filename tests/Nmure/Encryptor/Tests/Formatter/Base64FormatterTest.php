<?php

namespace Nmure\Encryptor\Tests\Formatter;

use Nmure\Encryptor\Formatter\Base64Formatter;
use Nmure\Encryptor\Formatter\FormatterInterface;
use Nmure\Encryptor\Encryptor;

class Base64FormatterTest extends \PHPUnit_Framework_TestCase
{
    private $iv = 'iv';
    private $data = 'data';
    private $formatter;

    protected function setUp()
    {
        $this->formatter = new Base64Formatter();
    }

    protected function tearDown()
    {
        unset($this->formatter);
    }

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
        $this->formatter->parse('abcdef');
    }

    public function testFormatAndParse()
    {
        $formatted = $this->formatter->format($this->iv, $this->data);
        $parsed = $this->formatter->parse($formatted);
        $this->assertEquals($this->iv, $parsed[FormatterInterface::KEY_IV]);
        $this->assertEquals($this->data, $parsed[FormatterInterface::KEY_DATA]);
    }

    public function testFormatterWithEncryptor()
    {
        $encryptor = new Encryptor('452F93C1A737722D8B4ED8DD58766D99', 'AES-256-CBC');
        $encryptor->setFormatter($this->formatter);

        $output = $encryptor->encrypt($this->data);
        $this->assertEquals($this->data, $encryptor->decrypt($output));
    }
}
