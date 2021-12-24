<?php

namespace App\Entity;

use App\Repository\SeasonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SeasonRepository::class)
 */
class Season
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * 
     * @Assert\NotBlank(message="Vous devez saisir chiffre")
     */
    private $number;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $releasedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isOnProduction;

    /**
     * @ORM\ManyToOne(targetEntity=Show::class, inversedBy="seasons")
     * 
     * On rajoute une contrainte de suppression en cascade côté base de données
     * 
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $seasonShow;

    /**
     * @ORM\OneToMany(targetEntity=Episode::class, mappedBy="season", orphanRemoval=true, cascade={"persist"})
     */
    private $episodes;

    public function __construct()
    {
        $this->episodes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getReleasedAt(): ?\DateTimeInterface
    {
        return $this->releasedAt;
    }

    public function setReleasedAt(?\DateTimeInterface $releasedAt): self
    {
        $this->releasedAt = $releasedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getIsOnProduction(): ?bool
    {
        return $this->isOnProduction;
    }

    public function setIsOnProduction(?bool $isOnProduction): self
    {
        $this->isOnProduction = $isOnProduction;

        return $this;
    }

    public function getSeasonShow(): ?Show
    {
        return $this->seasonShow;
    }

    public function setSeasonShow(?Show $seasonShow): self
    {
        $this->seasonShow = $seasonShow;

        return $this;
    }

    /**
     * @return Collection|Episode[]
     */
    public function getEpisodes(): Collection
    {
        return $this->episodes;
    }

    public function addEpisode(Episode $episode): self
    {
        if (!$this->episodes->contains($episode)) {
            $this->episodes[] = $episode;
            $episode->setSeason($this);
        }

        return $this;
    }

    public function removeEpisode(Episode $episode): self
    {
        if ($this->episodes->removeElement($episode)) {
            // set the owning side to null (unless already changed)
            if ($episode->getSeason() === $this) {
                $episode->setSeason(null);
            }
        }

        return $this;
    }
}
