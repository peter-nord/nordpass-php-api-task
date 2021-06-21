<?php

declare(strict_types=1);

namespace App\Contract;

interface EncryptedEntity
{
    public function encrypt(Encryptor $encryptor): void;

    public function decrypt(Encryptor $encryptor): void;
}
