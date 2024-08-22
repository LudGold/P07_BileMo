<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['product:read']]),
        new GetCollection(normalizationContext: ['groups' => ['product:read']])
    ],
    order: ['dateAdded' => 'DESC'],
    paginationEnabled: true,
    paginationItemsPerPage: 3
)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read'])]
    private ?string $brand = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['product:read'])]
    private ?string $model = null;

    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?int $price = null;

    #[ORM\Column]
    #[Groups(['product:read'])]
    private ?int $stock = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['product:read'])]
    private ?\DateTimeInterface $dateAdded = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['product:read'])]
    private ?array $technicalSpecs = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['product:read'])]
    private ?array $images = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['product:read'])]
    private ?string $category = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['product:read'])]
    private ?array $availableColors = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['product:read'])]
    private ?string $state = null;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getDateAdded(): ?\DateTimeInterface
    {
        return $this->dateAdded;
    }

    public function setDateAdded(\DateTimeInterface $dateAdded): static
    {
        $this->dateAdded = $dateAdded;

        return $this;
    }

    public function getTechnicalSpecs(): ?array
    {
        return $this->technicalSpecs;
    }

    public function setTechnicalSpecs(?array $technicalSpecs): static
    {
        $this->technicalSpecs = $technicalSpecs;

        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(?array $images): static
    {
        $this->images = $images;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getAvailableColors(): ?array
    {
        return $this->availableColors;
    }

    public function setAvailableColors(?array $availableColors): static
    {
        $this->availableColors = $availableColors;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

        return $this;
    }
}
