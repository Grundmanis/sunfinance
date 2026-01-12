<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: "payments")]
class Payment
{
    public function __construct()
    {
        $this->id = $id ?? Uuid::uuid4()->toString();
    }

    #[ORM\Id]
    #[ORM\Column(type: "guid", unique: true)]
    #[ORM\GeneratedValue(strategy: "NONE")]
    private ?string $id = null;

    // TODO: relation
    #[ORM\Column(type: "guid")]
    #[Assert\NotBlank]
    private string $loanId;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    private ?string $loanRef = null;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank]
    private string $firstName;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank]
    private string $lastName;

    #[ORM\Column(type: "string", enumType: PaymentState::class, length: 20)]
    #[Assert\NotBlank]
    private PaymentState $state;

    #[ORM\Column(type: "datetime_immutable")]
    #[Assert\NotBlank]
    private \DateTimeImmutable $paymentDate;

    #[ORM\Column(type: "decimal", precision: 10, scale: 2)]
    #[Assert\NotBlank]
    private string $amount;

    // currency

    #[ORM\Column(type: "string", length: 50)]
    #[Assert\NotBlank]
    private string $refId;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "string", length: 11, nullable: true)]
    private ?string $nationalSecurityNumber = null;

    #[ORM\Column(type: "datetime")]
    private \DateTime $createdAt;

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTime();
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

    public function getLoanId(): string
    {
        return $this->loanId;
    }

    public function setLoanId(string $loanId): self
    {
        $this->loanId = $loanId;

        return $this;
    }

    public function getLoanRef(): ?string
    {
        return $this->loanRef;
    }

    public function setLoanRef(?string $loanRef): self
    {
        $this->loanRef = $loanRef;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getState(): PaymentState
    {
        return $this->state;
    }

    public function setState(PaymentState $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getPaymentDate(): \DateTimeImmutable
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(\DateTimeImmutable $paymentDate): self
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getRefId(): string
    {
        return $this->refId;
    }

    public function setRefId(string $refId): self
    {
        $this->refId = $refId;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getNationalSecurityNumber(): ?string
    {
        return $this->nationalSecurityNumber;
    }

    public function setNationalSecurityNumber(?string $nationalSecurityNumber): self
    {
        $this->nationalSecurityNumber = $nationalSecurityNumber;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
