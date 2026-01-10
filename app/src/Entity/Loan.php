<?php

namespace App\Entity;

use App\Repository\LoanRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: LoanRepository::class)]
#[ORM\Table(name: "loans")]
class Loan
{
    public function __construct()
    {
        $this->id = $id ?? Uuid::uuid4()->toString();
    }

    #[ORM\Id]
    #[ORM\Column(type: "guid", unique: true)]
    #[ORM\GeneratedValue(strategy: "NONE")]
    private ?string $id = null;

    #[ORM\Column(type: "guid")]
    #[Assert\NotBlank]
    private string $customerId;

    #[ORM\Column(type: "string", length: 20, unique: true)]
    #[Assert\NotBlank]
    private string $reference;

    #[ORM\Column(type: "string", enumType: LoanState::class, length: 20)]
    #[Assert\NotBlank]
    private LoanState $state;

    // TODO: currency

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    #[Assert\NotBlank]
    private string $amountIssued;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    #[Assert\NotBlank]
    private string $amountToPay;

    #[ORM\Column(type: "datetime")]
    private \DateTime $createdAt;

    #[ORM\Column(type: "datetime")]
    private \DateTime $updatedAt;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $customerId): self
    {
        $this->customerId = $customerId;

        return $this;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getState(): LoanState
    {
        return $this->state;
    }

    public function setState(LoanState $state): self
    {
        $this->state = $state;
        if ($this->state === LoanState::PAID) {
            $this->amountToPay = '0';
        }

        return $this;
    }

    public function getAmountIssued(): string
    {
        return $this->amountIssued;
    }

    public function setAmountIssued(string $amountIssued): self
    {
        $this->amountIssued = $amountIssued;

        return $this;
    }

    public function getAmountToPay(): string
    {
        return $this->amountToPay;
    }

    public function setAmountToPay(string $amountToPay): self
    {
        $this->amountToPay = $amountToPay;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }
}
