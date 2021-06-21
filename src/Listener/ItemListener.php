<?php

declare(strict_types=1);

namespace App\Listener;

use App\Contract\Encryptor;
use App\Entity\Item;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ItemListener
{
    private $encryptor;

    public function __construct(Encryptor $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    public function prePersist(Item $item, LifecycleEventArgs $event): void
    {
        $item->encrypt($this->encryptor);
    }

    public function preUpdate(Item $item, LifecycleEventArgs $event): void
    {
        $item->encrypt($this->encryptor);
    }

    public function postLoad(Item $item, LifecycleEventArgs $event): void
    {
        $item->decrypt($this->encryptor);
    }
}