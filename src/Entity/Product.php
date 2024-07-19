<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)] 
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $brand = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 2, scale: 2)]
    private ?string $model = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column]
    private ?int $stock = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateAdded = null;

    #[ORM\Column(nullable: true)]
    private ?array $technicalSpecs = null;

    #[ORM\Column(nullable: true)]
    private ?array $images = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(nullable: true)]
    private ?array $availableColors = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $State = null;

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
        return $this->State;
    }

    public function setState(?string $State): static
    {
        $this->State = $State;

        return $this;
    }
}
