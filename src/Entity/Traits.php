<?php

namespace App\Entity;

trait UuidTrait
{
    #[\Doctrine\ORM\Mapping\Id]
    #[\Doctrine\ORM\Mapping\Column(type: 'guid', unique: true)]
    private string $id;

    #[\Doctrine\ORM\Mapping\Column]
    private \DateTimeImmutable $createdAt;

    #[\Doctrine\ORM\Mapping\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    protected function initUuid(): void
    {
        $this->id = $this->generateUuid();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function touch(): static
    {
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }
}

trait AnonymizableTrait
{
    #[\Doctrine\ORM\Mapping\Column(nullable: true)]
    private ?\DateTimeImmutable $anonymizedAt = null;

    public function getAnonymizedAt(): ?\DateTimeImmutable
    {
        return $this->anonymizedAt;
    }

    public function setAnonymizedAt(?\DateTimeImmutable $anonymizedAt): static
    {
        $this->anonymizedAt = $anonymizedAt;
        return $this;
    }

    public function isAnonymized(): bool
    {
        return $this->anonymizedAt !== null;
    }
}
