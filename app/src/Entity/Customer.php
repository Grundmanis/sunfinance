<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: "customers")]
class Customer
{
    public function __construct()
    {
        $this->id = $id ?? Uuid::uuid4()->toString();
    }

    #[ORM\Id]
    #[ORM\Column(type: "guid", unique: true)]
    #[ORM\GeneratedValue(strategy: "NONE")]
    private ?string $id = null;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank]
    private string $firstname;

    #[ORM\Column(type: "string", length: 100)]
    #[Assert\NotBlank]
    private string $lastname;

    #[ORM\Column(type: "string", length: 180, unique: true, nullable: true)]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: "string", length: 25, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: "string", length: 11, nullable: true)]
    private ?string $ssn = null;

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

    public function setId(string $id): void
    {
        if (!Uuid::isValid($id)) {
            throw new InvalidArgumentException("Invalid UUID: $id");
        }

        $this->id = $id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getSsn(): ?string
    {
        return $this->ssn;
    }

    public function setSsn(?string $ssn): self
    {
        $this->ssn = $ssn;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
