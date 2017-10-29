<?php

namespace Nmure\Encryptor\Tests\Formatter;

use Nmure\Encryptor\Encryptor;
use Nmure\Encryptor\Formatter\FormatterInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class asserting that a FormatterInterface fulfills the minimum
 * requirements of what it should accomplish.
 * The formatter test classes should extend this class.
 */
abstract class AbstractFormatterTest extends TestCase
{
    protected $iv;
    protected $data = 'data';
    protected $secret = '452F93C1A737722D8B4ED8DD58766D99';
    protected $cipher = 'AES-256-CBC';
    protected $formatter;

    /**
     * Initialises the IV and the concrete FormatterInterface to test.
     */
    protected function setUp()
    {
        $this->iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $this->formatter = $this->getFormatter();
    }

    /**
     * Free the concrete FormatterInterface to test.
     */
    protected function tearDown()
    {
        unset($this->formatter);
    }

    /**
     * Assert that the concrete FormatterInterface can produce a correct
     * output and read it back as expected.
     */
    public function testFormatAndParse()
    {
        $formatted = $this->formatter->format($this->iv, $this->data);
        $parsed = $this->formatter->parse($formatted, openssl_cipher_iv_length($this->cipher));
        $this->assertEquals($this->iv, $parsed[FormatterInterface::KEY_IV]);
        $this->assertEquals($this->data, $parsed[FormatterInterface::KEY_DATA]);
    }

    /**
     * Asserts that the concrete FormatterInterface is working
     * with the Encryptor.
     */
    public function testFormatterWithEncryptor()
    {
        $encryptor = new Encryptor($this->secret, $this->cipher);
        $encryptor->setFormatter($this->formatter);

        $output = $encryptor->encrypt($this->data);
        $this->assertEquals($this->data, $encryptor->decrypt($output));
    }

    /**
     * Assert that the concrete FormatterInterface is producing
     * expected output.
     */
    abstract public function testFormat();

    /**
     * @return Nmure\Encryptor\Formatter\FormatterInterface The concrete FormatterInterface to test.
     */
    abstract protected function getFormatter();
}
