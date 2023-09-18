<?php

namespace App\Entity;

use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: IngredientRepository::class)]
#[UniqueEntity('name')]
class Ingredient
{
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->recepes = new ArrayCollection();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank()]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\NotNull()]
    #[Assert\Positive()]
    private ?float $price = null;

    #[ORM\Column]
    #[Assert\NotNull()]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToMany(targetEntity: Recipe::class, mappedBy: 'ingredient')]
    private Collection $recepes;

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

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

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

    /**
     * @return Collection<int, Recipe>
     */
    public function getRecepes(): Collection
    {
        return $this->recepes;
    }

    public function addRecepe(Recipe $recepe): static
    {
        if (!$this->recepes->contains($recepe)) {
            $this->recepes->add($recepe);
            $recepe->addIngredient($this);
        }

        return $this;
    }

    public function removeRecepe(Recipe $recepe): static
    {
        if ($this->recepes->removeElement($recepe)) {
            $recepe->removeIngredient($this);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }


}
