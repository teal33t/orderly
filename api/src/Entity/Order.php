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
        new GetCollection(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Only admins can view all orders.'
        ),
        new Get(
            security: "is_granted('ROLE_ADMIN') or object.getUser() == user",
            securityMessage: 'You can only view your own orders.'
        ),
        new Post(
            security: "is_granted('ROLE_USER')",
            securityMessage: 'You need to be logged in to create orders.'
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Only admins can update orders.'
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Only admins can delete orders.'
        )
    ],
    normalizationContext: ['groups' => ['order:read']],
    denormalizationContext: ['groups' => ['order:write']]
)]
#[ORM\Entity]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['order:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['order:read'])]
    #[Assert\NotNull(message: 'An order must belong to a user')]
    private User $user;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'])]
    #[Groups(['order:read', 'order:write'])]
    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: 'An order must have at least one item')]
    private Collection $items;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['order:read', 'order:write'])]
    #[Assert\NotBlank(message: 'Status cannot be blank')]
    #[Assert\Choice(choices: ['pending', 'processing', 'completed', 'cancelled'], message: 'Invalid order status')]
    private string $status = 'pending';

    #[ORM\Column(type: 'datetime')]
    #[Groups(['order:read'])]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'integer')]
    #[Groups(['order:read'])]
    private int $total = 0;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setOrder($this);
            $this->updateTotal();
        }
        return $this;
    }

    public function removeItem(OrderItem $item): self
    {
        if ($this->items->removeElement($item)) {
            if ($item->getOrder() === $this) {
                $item->setOrder(null);
            }
            $this->updateTotal();
        }
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    private function updateTotal(): void
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getTotal();
        }
        $this->total = $total;
    }
}