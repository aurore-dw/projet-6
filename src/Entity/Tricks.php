<?php

namespace App\Entity;

use App\Repository\TricksRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: TricksRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[UniqueEntity(fields: ['name'], message: 'Il existe déjà une figure avec ce nom !')]
class Tricks
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $create_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $update_at = null;

    #[ORM\ManyToOne(targetEntity:"App\Entity\User", inversedBy: 'tricks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type:"json", nullable:true)]
    private ?array $pictures = [];

    #[ORM\Column(type:"json", nullable:true)]
    private ?array $videos = [];

    #[ORM\Column(length: 255)]
    private ?string $category = null;

    #[ORM\OneToMany(mappedBy: 'trick', targetEntity: Comment::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Collection $comments;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        //$this->pictures = new ArrayCollection();
        $this->pictures = [];
        $this->videos = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCreateAt(): ?\DateTimeInterface
    {
        return $this->create_at;
    }

    public function setCreateAt(\DateTimeInterface $create_at): self
    {
        $this->create_at = $create_at;

        return $this;
    }

    public function getUpdateAt(): ?\DateTimeInterface
    {
        return $this->update_at;
    }

    /**
     * @ORM\PreUpdate
     */
    public function setUpdateAt(?\DateTimeInterface $update_at): self
    {
        $this->update_at = $update_at;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }


    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getPictures(): array
    {
        return $this->pictures ?? [];
    }

    public function setPictures(array $pictures): self
    {
        $this->pictures = $pictures;
        return $this;
    }

    public function addPicture(string $picture): self
    {
        if (!in_array($picture, $this->pictures ?? [], true)) {
            $this->pictures[] = $picture;
        }
        return $this;
    }

    public function removePicture(string $picture): self
    {
        $index = array_search($picture, $this->pictures ?? [], true);
        if ($index !== false) {
            unset($this->pictures[$index]);
        }
        return $this;
    }

    public function getVideos(): array
    {
        return $this->videos ?? [];
    }

    public function setVideos($videos): self
    {
        if (is_string($videos)) {
            $videos = explode(',', $videos);
        }

        $this->videos = $videos;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setTrick($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTrick() === $this) {
                $comment->setTrick(null);
            }
        }

        return $this;
    }
}
