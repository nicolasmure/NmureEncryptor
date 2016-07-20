<?php

namespace Nmure\Encryptor\Tests;

use Nmure\Encryptor\Encryptor;

class EncryptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * 128bit secret hex key generated using the following UNIX command
     * $ head -c16 /dev/urandom | md5sum | awk '{ print toupper($1) }'
     */
    private $secret = '452F93C1A737722D8B4ED8DD58766D99';
    private $cipher = 'AES-256-CBC';
    private $iv;
    private $encryptor;
    private $data = 'data';

    protected function setUp()
    {
        $this->iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $this->encryptor = new Encryptor($this->secret, $this->cipher);
    }

    protected function tearDown()
    {
        unset($this->iv);
        unset($this->encryptor);
    }

    public function testEncryptGeneratesIVWhenNotProvided()
    {
        $this->assertNull($this->encryptor->getIv());
        $this->encryptor->encrypt($this->data);
        $this->assertNotNull($this->encryptor->getIv());
    }

    public function testEncryptUsesProvidedIv()
    {
        $this->encryptor->setIv($this->iv);
        $this->encryptor->disableAutoIvUpdate();
        $this->assertEquals($this->iv, $this->encryptor->getIv());

        $encrypted = $this->encryptor->encrypt($this->data);
        $this->assertEquals($encrypted, $this->encryptor->encrypt($this->data));

        $this->encryptor->generateIv();
        $this->assertNotEquals($encrypted, $this->encryptor->encrypt($this->data));
    }

    public function testEncryptWithAndWithoutFormatter()
    {
        $output = 'encryptedAndFormated';
        $fMock = $this->getFormatterMock();
        $fMock->expects($this->once())
            ->method('format')
            ->with(
                $this->equalTo($this->iv),
                $this->isType('string')
            )
            ->willReturn($output);

        $this->encryptor->disableAutoIvUpdate();
        $this->encryptor->setIv($this->iv);
        $this->encryptor->setFormatter($fMock);
        $this->assertEquals($output, $this->encryptor->encrypt($this->data));

        $this->encryptor->setFormatter(null);
        $this->assertNotEquals($output, $this->encryptor->encrypt($this->data));
    }

    public function testAutoIvUpdate()
    {
        // autoIvUpdate should be enabled by default.
        // during the 1st encryption : the IV is set, then the data is encrypted
        // and the IV is updated.
        $encrypted = $this->encryptor->encrypt($this->data);
        $this->encryptor->disableAutoIvUpdate();

        // during the 2nd encryption : the IV in use is the one updated
        // from the previous encryption. The data is encrypted and
        // the IV is not updated as we disabled the auto update.
        $encrypted2 = $this->encryptor->encrypt($this->data);
        $this->assertNotEquals($encrypted, $encrypted2);

        // during the 3rd encryption : the IV in use is the same than
        // for the 2nd encryption as we haven't updated it.
        $encrypted3 = $this->encryptor->encrypt($this->data);
        $this->assertEquals($encrypted2, $encrypted3);
    }

    /**
     * @expectedException Nmure\Encryptor\Exception\DecryptException
     * @expectedExceptionMessage No Initialization Vector set to this encryptor : unable to decrypt data
     */
    public function testDecryptThrowsDecryptException()
    {
        $this->encryptor->decrypt('');
    }

    public function testEncryptAndDecrypt()
    {
        // keeping the same IV as we decrypt right after the encryption
        $this->encryptor->disableAutoIvUpdate();

        $encrypted = $this->encryptor->encrypt($this->data);
        $this->assertEquals($this->data, $this->encryptor->decrypt($encrypted));
    }

    public function testGenerateIv()
    {
        $iv = $this->encryptor->generateIv();
        $this->assertEquals($iv, $this->encryptor->getIv());
        $this->assertNotEquals($iv, $this->encryptor->generateIv());
    }

    private function getFormatterMock()
    {
        return $this->getMockBuilder('Nmure\Encryptor\Formatter\FormatterInterface')
                ->getMock();
    }
}
