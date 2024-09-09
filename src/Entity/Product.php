<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]

class Product
{
    // Propriétés communes à toutes les versions
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getCollection', 'getItem', 'getCollectionV2', 'getItemV2'])]
    private ?int $id = null;


    #[ORM\Column(length: 255)]
    #[Groups(['getCollection', 'getItem', 'getCollectionV2', 'getItemV2'])]
    private ?string $name = null;


    #[ORM\Column(length: 255)]
    #[Groups(['getCollection', 'getItem'])]
    private ?string $description = null;


    #[ORM\Column(length: 255)]
    #[Groups(['getCollection', 'getItem'])]
    private ?string $brand = null;


    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Groups(['getCollection', 'getItem'])]
    private ?string $model = null;


    #[ORM\Column]
    #[Groups(['getCollection', 'getItem'])]
    private ?int $price = null;


    #[ORM\Column]
    #[Groups(['getCollection', 'getItem'])]
    private ?int $stock = null;


    #[ORM\Column(type: 'datetime')]
    #[Groups(['getCollection', 'getItem'])]
    private ?\DateTimeInterface $dateAdded = null;


    #[ORM\Column(nullable: true)]
    #[Groups(['getCollection', 'getItem'])]
    private ?array $technicalSpecs = null;


    #[ORM\Column(nullable: true)]
    #[Groups(['getCollection', 'getItem'])]
    private ?array $images = null;


    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['getCollection', 'getItem'])]
    private ?string $category = null;


    #[ORM\Column(nullable: true)]
    #[Groups(['getCollection', 'getItem'])]
    private ?array $availableColors = null;


    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['getCollection', 'getItem'])]
    private ?string $state = null;

    // Propriétés spécifiques à la version 2
    /**
     * @Groups({"getCollection", "getItem"})
     *
     * @since 2.0  // Indique la version d'introduction
     */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['v2_getCollection', 'v2_getItem'])]

    private ?string $comment = null;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }
}
