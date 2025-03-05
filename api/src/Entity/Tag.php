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
            securityMessage: 'Only admins can create tags.'
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Only admins can update tags.'
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Only admins can delete tags.'
        )
    ],
    normalizationContext: ['groups' => ['tag:read']],
    denormalizationContext: ['groups' => ['tag:write']]
)]
#[ORM\Entity]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['tag:read', 'product:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['tag:read', 'tag:write', 'product:read'])]
    #[Assert\NotBlank(message: 'Name cannot be blank')]
    #[Assert\Length(min: 2, max: 255, minMessage: 'Name must be at least {{ limit }} characters long', maxMessage: 'Name cannot be longer than {{ limit }} characters')]
    private string $name;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups(['tag:read', 'tag:write'])]
    #[Assert\NotBlank(message: 'Slug cannot be blank')]
    #[Assert\Regex(pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', message: 'Slug must contain only lowercase letters, numbers, and hyphens')]
    private string $slug;

    #[ORM\ManyToMany(targetEntity: Product::class, mappedBy: 'tags')]
    #[Groups(['tag:read'])]
    private Collection $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->addTag($this);
        }
        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->removeElement($product)) {
            $product->removeTag($this);
        }
        return $this;
    }
}