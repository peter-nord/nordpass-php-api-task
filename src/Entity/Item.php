<?php

namespace App\Entity;

use App\Contract\EncryptedEntity;
use App\Contract\Encryptor;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ItemRepository;

/**
 * @ORM\Entity(repositoryClass=ItemRepository::class)
 * @ORM\EntityListeners({"App\Listener\ItemListener"})
 * @ORM\HasLifecycleCallbacks
 */
class Item implements EncryptedEntity
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $data;

    private $decryptedData;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="items")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getData(): ?string
    {
        return $this->decryptedData;
    }

    public function setData(string $data): self
    {
        $this->decryptedData = $data;
        $this->data = null;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function updateTimestampsOnPersist(): void
    {
        if (null === $this->getUpdatedAt()) {
            $this->setUpdatedAt(new DateTime('now'));
        }

        if (null === $this->getCreatedAt()) {
            $this->setCreatedAt(new DateTime('now'));
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function updatedTimestampsOnUpdate(): void
    {
        $this->setUpdatedAt(new DateTime('now'));

        if (null === $this->getCreatedAt()) {
            $this->setCreatedAt(new DateTime('now'));
        }
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function encrypt(Encryptor $encryptor): void
    {
        if ($this->decryptedData) {
            $this->data = $encryptor->encrypt($this->decryptedData);
        }
    }

    public function decrypt(Encryptor $encryptor): void
    {
        if ($this->data) {
            $this->decryptedData = $encryptor->decrypt($this->data);
        }
    }
}
