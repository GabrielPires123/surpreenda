<?php

namespace App\Entity\Trait;

use App\Security\Privacy\DataAnonymizer;
use Doctrine\ORM\Mapping as ORM;

trait AnonymizableTrait
{
    #[ORM\Column(nullable: true)]
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

    public function anonymize(DataAnonymizer $anonymizer): static
    {
        if ($this->isAnonymized()) {
            return $this;
        }
        $this->setAnonymizedAt(new \DateTimeImmutable());
        return $this;
    }
}
