<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]

class Customer
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['customer:read'])]

    private ?int $id = null;

    public function __construct($userId)
    {
        $this->user = $userId;
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\Column(length: 255)]
    #[Groups(['customer:read', 'customer:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customer:read', 'customer:write'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['customer:read', 'customer:write'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['customer:read', 'customer:write'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 20)]
    #[Groups(['customer:read', 'customer:write'])]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255)]
    #[Groups(['customer:read', 'customer:write'])]
    private ?string $address = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'customers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["customer:read"])]
    private ?User $user = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
