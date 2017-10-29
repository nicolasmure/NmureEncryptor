<?php

namespace Nmure\Encryptor\Tests;

use Nmure\Encryptor\Encryptor;
use PHPUnit\Framework\TestCase;

class EncryptorTest extends TestCase
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
        $encrypted = $this->encryptor->encrypt($this->data);

        // during the 2nd encryption : the IV will be automatically
        // updated before the encryption process.
        // Then, the data is encrypted.
        $encrypted2 = $this->encryptor->encrypt($this->data);
        $this->assertNotEquals($encrypted, $encrypted2);

        // disabling autoIvUpdate : the next encryption should
        // use the same IV as the previous encryption
        $this->encryptor->disableAutoIvUpdate();
        $encrypted3 = $this->encryptor->encrypt($this->data);
        $this->assertEquals($encrypted2, $encrypted3);

        // enable autoIvUpdate : the next encryption should
        // generate a new IV before the encryption
        $this->encryptor->enableAutoIvUpdate();
        $iv = $this->encryptor->getIv();

        // during the 4th encryption, the IV will be automatically updated
        // before the encryption process
        $encrypted4 = $this->encryptor->encrypt($this->data);
        $this->assertNotEquals($encrypted3, $encrypted4);
        $this->assertNotEquals($iv, $this->encryptor->getIv());
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

    /**
     * @expectedException Nmure\Encryptor\Exception\InvalidSecretKeyException
     * @expectedExceptionMessage The secret key "UVWXYZ" is not a hex key
     */
    public function testTurnHexKeyToBinThrowsException()
    {
        $enc = new Encryptor('UVWXYZ', $this->cipher);
        $enc->turnHexKeyToBin();
    }

    public function testTurnKexKeyToBin()
    {
        $enc = new Encryptor(hex2bin($this->secret), $this->cipher);
        $this->encryptor->turnHexKeyToBin();
        $this->encryptor->disableAutoIvUpdate();
        $enc->disableAutoIvUpdate();
        $enc->setIv($this->encryptor->generateIv());
        $this->assertEquals($enc->encrypt($this->data), $this->encryptor->encrypt($this->data));
    }

    private function getFormatterMock()
    {
        return $this->getMockBuilder('Nmure\Encryptor\Formatter\FormatterInterface')
                ->getMock();
    }
}
