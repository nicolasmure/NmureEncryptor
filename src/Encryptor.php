<?php

namespace Nmure\Encryptor;

use Nmure\Encryptor\Formatter\FormatterInterface;
use Nmure\Encryptor\Exception\DecryptException;

final class Encryptor
{
    /**
     * The encryption key.
     *
     * @var string
     */
    private $secret;

    /**
     * The cipher method.
     *
     * @var string
     */
    private $cipher;

    /**
     * The Initialization Vector.
     *
     * @var string
     */
    private $iv;

    /**
     * Indicates if this Encryptor should automatically update
     * its IV after an encryption.
     *
     * @var boolean Default true (recommended)
     */
    private $autoIvUpdate;

    /**
     * The formatter to use when specified.
     *
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * Constructor.
     *
     * @param string $secret The encryption key.
     * @param string $cipher The cipher method
     */
    public function __construct($secret, $cipher)
    {
        $this->secret = $secret;
        $this->cipher = $cipher;
        $this->autoIvUpdate = true;
    }

    /**
     * @param  string $data Data to encrypt.
     *
     * @return string       Encrypted data, or encrypted and formatted data
     *                      when a formatter has been provided.
     */
    public function encrypt($data)
    {
        if (!$this->iv) {
            $this->generateIv();
        }

        $output = openssl_encrypt($data, $this->cipher, $this->secret, OPENSSL_RAW_DATA, $this->iv);

        if ($this->formatter) {
            $output = $this->formatter->format($this->iv, $output);
        }

        if ($this->autoIvUpdate) {
            $this->generateIv();
        }

        return $output;
    }

    /**
     * @param  string $data  When a formatter is set to this encryptor,
     *                       the $data should be the formetted string by the formatter.
     *                       When no formatter is used, the $data should be the raw encrypted
     *                       data returned by this encryptor.
     *
     * @throws DecryptException When not able to decrypt the given data.
     *
     * @return string            Decrypted data.
     */
    public function decrypt($data)
    {
        if ($this->formatter) {
            $parsed = $this->formatter->parse($data, $this->getIvLength());
            $this->iv = $parsed[FormatterInterface::KEY_IV];
            $data = $parsed[FormatterInterface::KEY_DATA];
        }

        if (!$this->iv) {
            throw new DecryptException('No Initialization Vector set to this encryptor : unable to decrypt data');
        }

        return openssl_decrypt($data, $this->cipher, $this->secret, OPENSSL_RAW_DATA, $this->iv);
    }

    /**
     * Generate a random IV and set it to this Encryptor.
     * The IV should be changed at each encryption to assure that
     * two encryptions of the same data won't produce the same encrypted output.
     * Be sure to store the IV along side the encrypted data to be able to
     * decrypt it then (e.g. by using a Nmure\Encryptor\Formatter\FormatterInterface).
     *
     * @return string The gerenated IV.
     */
    public function generateIv()
    {
        $this->iv = openssl_random_pseudo_bytes($this->getIvLength());

        return $this->iv;
    }

    /**
     * Enable the automatic IV update after each encryption.
     */
    public function enableAutoIvUpdate()
    {
        $this->autoIvUpdate = true;
    }

    /**
     * Disable the automatic IV update after each encryption.
     */
    public function disableAutoIvUpdate()
    {
        $this->autoIvUpdate = false;
    }

    /**
     * @return string The Initialization Vector.
     */
    public function getIv()
    {
        return $this->iv;
    }

    /**
     * @param string $iv The Initialization Vector to set.
     */
    public function setIv($iv)
    {
        $this->iv = $iv;
    }

    /**
     * @param FormatterInterface|null $formatter The formatter to use when encrypting / decrypting data.
     */
    public function setFormatter(FormatterInterface $formatter = null)
    {
        $this->formatter = $formatter;
    }

    /**
     * @return int The length of the IV used by the cipher method.
     */
    private function getIvLength()
    {
        return openssl_cipher_iv_length($this->cipher);
    }
}
