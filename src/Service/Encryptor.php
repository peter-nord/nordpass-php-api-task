<?php

declare(strict_types=1);

namespace App\Service;

class Encryptor implements \App\Contract\Encryptor
{
    const METHOD_NAME = 'aes-256';

    const ENCRYPT_MODE = 'cbc';

    private $secretKey;

    private $suffix;

    private $encryptMethod;

    public function __construct(string $key, string $suffix)
    {
        $this->secretKey = md5($key);
        $this->suffix = $suffix;
        $this->encryptMethod = sprintf('%s-%s', self::METHOD_NAME, self::ENCRYPT_MODE);
    }

    public function encrypt(string $data): string
    {
        $vector = openssl_random_pseudo_bytes(
            openssl_cipher_iv_length($this->encryptMethod)
        );

        $encrypted = base64_encode(openssl_encrypt(
            $data,
            $this->encryptMethod,
            $this->secretKey,
            0,
            $vector
        ));

        return $this->pack($encrypted, base64_encode($vector));
    }

    public function decrypt(string $data): string
    {
        if (!$this->isEncrypted($data)) {
            return $data;
        }

        list ($encrypted, $vector) = $this->unpack($data);

        return openssl_decrypt(
            base64_decode($encrypted),
            $this->encryptMethod,
            $this->secretKey,
            0,
            base64_decode($vector)
        );
    }

    private function pack(string $encrypted, string $vector): string
    {
        return implode(':', [$encrypted, $vector, $this->suffix]);
    }

    private function isEncrypted(string $data): bool
    {
        return substr($data, -5) === $this->suffix;
    }

    private function unpack(string $encrypted): array
    {
        $parts = explode(':', $encrypted);

        if (count($parts) !== 3 || $parts[2] !== $this->suffix) {
            throw new \RuntimeException('Invalid encrypted string');
        }

        return $parts;
    }
}
