<?php

declare(strict_types=1);

namespace App\Contract;

interface Encryptor
{
    public function encrypt(string $data): string;

    public function decrypt(string $data): string;
}
