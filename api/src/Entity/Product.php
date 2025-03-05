<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Only admins can create products.'
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Only admins can update products.'
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Only admins can delete products.'
        )
    ],
    normalizationContext: ['groups' => ['product:read']],
    denormalizationContext: ['groups' => ['product:write']]
)]
#[ORM\Entity]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['product:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotBlank(message: 'Title cannot be blank')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Title must be at least {{ limit }} characters long', maxMessage: 'Title cannot be longer than {{ limit }} characters')]
    private string $title;

    #[ORM\Column(type: 'text')]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotBlank(message: 'Description cannot be blank')]
    private string $description;

    #[ORM\Column(type: 'text')]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotBlank(message: 'Body cannot be blank')]
    private string $body;

    #[ORM\Column(type: 'integer')]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotBlank(message: 'Price cannot be blank')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Price cannot be negative')]
    private int $price = 0;

    #[ORM\Column(type: 'integer')]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\NotBlank(message: 'Stock count cannot be blank')]
    #[Assert\GreaterThanOrEqual(value: 0, message: 'Stock count cannot be negative')]
    private int $count;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['product:read'])]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToMany(targetEntity: Category::class, inversedBy: 'products')]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\Valid]
    private Collection $categories;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'products')]
    #[Groups(['product:read', 'product:write'])]
    #[Assert\Valid]
    private Collection $tags;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

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

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }
        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);
        return $this;
    }
}