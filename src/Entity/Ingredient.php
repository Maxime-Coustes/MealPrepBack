<?php

namespace App\Entity;

use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IngredientRepository::class)]
class Ingredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(length: 10)]
    private ?string $unit = null;

    #[ORM\Column]
    private ?float $proteins = null;

    #[ORM\Column]
    private ?float $fat = null;

    #[ORM\Column]
    private ?float $carbs = null;

    #[ORM\Column]
    private ?float $calories = null;

    /**
     * @var Collection<int, RecipeIngredient>
     */
    private Collection $recipeIngredients;

    public function __construct()
    {
        $this->recipeIngredients = new ArrayCollection();
    }

    /**
     * @return Collection<int, RecipeIngredient>
     */
    public function getRecipeIngredients(): Collection
    {
        return $this->recipeIngredients;
    }

    /**
     *
     * @param RecipeIngredient $recipeIngredient
     * @return self
     */
    public function addRecipeIngredient(RecipeIngredient $recipeIngredient): self
    {
        if (!$this->recipeIngredients->contains($recipeIngredient)) {
            $this->recipeIngredients->add($recipeIngredient);
            $recipeIngredient->setIngredient($this);
        }
        return $this;
    }

    /**
     * @param RecipeIngredient $recipeIngredient
     * @return self
     */
    public function removeRecipeIngredient(RecipeIngredient $recipeIngredient): self
    {
        if (
            $this->recipeIngredients->removeElement($recipeIngredient)
            && $recipeIngredient->getIngredient() === $this
        ) {
            $recipeIngredient->setIngredient(null);
        }
        return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    public function getProteins(): ?float
    {
        return $this->proteins;
    }

    public function setProteins(?float $proteins): static
    {
        $this->proteins = $proteins;

        return $this;
    }

    public function getFat(): ?float
    {
        return $this->fat;
    }

    public function setFat(?float $fat): static
    {
        $this->fat = $fat;

        return $this;
    }

    public function getCarbs(): ?float
    {
        return $this->carbs;
    }

    public function setCarbs(?float $carbs): static
    {
        $this->carbs = $carbs;

        return $this;
    }

    public function getCalories(): ?float
    {
        return $this->calories;
    }

    public function setCalories(?float $calories): static
    {
        $this->calories = $calories;

        return $this;
    }
}
