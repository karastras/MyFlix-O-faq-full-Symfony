<?php

namespace App\Entity;

use App\Repository\ShowRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ShowRepository::class)
 * @ORM\Table(name="`show`")
 */
class Show
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * 
     * @Groups("show_list")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Assert\NotBlank(message="Vous devez saisir un nom de série")
     * 
     * @Groups("show_list")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @Assert\Url(message="Vous devez saisir une URL valide")
     * @Assert\NotBlank(message="Vous devez saisir une URL")
     * 
     * @Groups("show_list")
     */
    private $trailer;

    /**
     * @ORM\Column(type="text", nullable=true)
     * 
     * @Assert\NotBlank(message="Vous devez saisir un synopsis")
     * 
     * @Groups("show_list")
     */
    private $synopsis;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * 
     * @Assert\NotBlank(message="Vous devez faire un choix")
     */
    private $minimum_age;

    /**
     * @ORM\OneToMany(targetEntity=Season::class, mappedBy="seasonShow", orphanRemoval=true)
     */
    private $seasons;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $totalViews;

    /**
     * @ORM\ManyToMany(targetEntity=Category::class, inversedBy="shows")
     * 
     * @Groups("show_list")
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity=Character::class, inversedBy="shows")
     */
    private $characters;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $releasedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $rating;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $poster;

    public function __construct()
    {
        $this->seasons = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->characters = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getTrailer(): ?string
    {
        return $this->trailer;
    }

    public function setTrailer(?string $trailer): self
    {
        $this->trailer = $trailer;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(string $synopsis): self
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getMinimumAge(): ?int
    {
        return $this->minimum_age;
    }

    public function setMinimumAge(?int $minimum_age): self
    {
        $this->minimum_age = $minimum_age;

        return $this;
    }

    /**
     * @return Collection|Season[]
     */
    public function getSeasons(): Collection
    {
        return $this->seasons;
    }

    public function addSeason(Season $season): self
    {
        if (!$this->seasons->contains($season)) {
            $this->seasons[] = $season;
            $season->setSeasonShow($this);
        }

        return $this;
    }

    public function removeSeason(Season $season): self
    {
        if ($this->seasons->removeElement($season)) {
            // set the owning side to null (unless already changed)
            if ($season->getSeasonShow() === $this) {
                $season->setSeasonShow(null);
            }
        }

        return $this;
    }

    public function getTotalViews(): ?string
    {
        return $this->totalViews;
    }

    public function setTotalViews(?string $totalViews): self
    {
        $this->totalViews = $totalViews;

        return $this;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    /**
     * @return Collection|Character[]
     */
    public function getCharacters(): Collection
    {
        return $this->characters;
    }

    public function addCharacter(Character $character): self
    {
        if (!$this->characters->contains($character)) {
            $this->characters[] = $character;
        }

        return $this;
    }

    public function removeCharacter(Character $character): self
    {
        $this->characters->removeElement($character);

        return $this;
    }

    public function getReleasedAt(): ?\DateTimeInterface
    {
        return $this->releasedAt;
    }

    public function setReleasedAt(\DateTimeInterface $releasedAt): self
    {
        $this->releasedAt = $releasedAt;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): self
    {
        $this->rating = $rating;

        return $this;
    }

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setPoster(?string $poster): self
    {
        $this->poster = $poster;

        return $this;
    }

}
