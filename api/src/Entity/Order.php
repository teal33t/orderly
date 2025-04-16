<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
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
        // new GetCollection(
        //     security: "is_granted('ROLE_ADMIN')",
        //     securityMessage: 'Only admins can view all orders.',
        //     description: 'Retrieves the collection of Order resources.'
        // ),
        // new Get(
        //     security: "is_granted('ROLE_ADMIN') or object.getUser() == user",
        //     securityMessage: 'You can only view your own orders.',
        //     description: 'Retrieves a Order resource.'
        // ),
        new GetCollection(),
        new Get(),
        new Post(
            security: "is_granted('ROLE_USER')",
            securityMessage: 'You need to be logged in to create orders.',
            description: 'Creates a Order resource.',
            processor: 'App\State\OrderProcessor'
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Only admins can update orders.',
            description: 'Updates a Order resource.'
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Only admins can delete orders.',
            description: 'Deletes a Order resource.'
        )
    ],
    normalizationContext: ['groups' => ['order:read']],
    denormalizationContext: ['groups' => ['order:write']],
    shortName: 'Order',
    description: 'An order placed by a customer'
)]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'updatedAt', 'total'])]
#[ApiFilter(SearchFilter::class, properties: [
    'status' => 'exact',
    'user' => 'exact',
    'user.email' => 'partial'
])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'updatedAt'])]
#[ORM\Entity]
#[ORM\Table(name: '`order`')]
#[ORM\HasLifecycleCallbacks]
class Order
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['order:read'])]
    #[ApiProperty(description: 'The unique identifier of the order.', example: 1)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['order:read'])]
    #[Assert\NotNull(message: 'An order must belong to a user')]
    #[ApiProperty(description: 'The customer who placed the order.', types: ['https://schema.org/Person'])]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['order:read', 'order:write'])]
    #[Assert\Valid]
    #[Assert\Count(min: 1, minMessage: 'An order must have at least one item')]
    #[ApiProperty(description: 'The items in the order.')]
    private Collection $items;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['order:read', 'order:write'])]
    #[Assert\NotBlank(message: 'Status cannot be blank')]
    #[Assert\Choice(
        choices: [self::STATUS_PENDING, self::STATUS_PROCESSING, self::STATUS_COMPLETED, self::STATUS_CANCELLED], 
        message: 'Invalid order status'
    )]
    #[ApiProperty(
        description: 'The status of the order.',
        openapiContext: [
            'type' => 'string',
            'enum' => [self::STATUS_PENDING, self::STATUS_PROCESSING, self::STATUS_COMPLETED, self::STATUS_CANCELLED],
            'example' => self::STATUS_PENDING
        ]
    )]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['order:read'])]
    #[ApiProperty(description: 'The date and time when the order was created.')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['order:read'])]
    #[ApiProperty(description: 'The date and time when the order was last updated.')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'integer')]
    #[Groups(['order:read'])]
    #[ApiProperty(
        description: 'The total price of the order in cents.',
        openapiContext: [
            'type' => 'integer',
            'format' => 'int32',
            'example' => 1990
        ]
    )]
    private int $total = 0;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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
        // You could add validation here to enforce proper status transitions
        // For example, you can't go from cancelled to processing
        $this->status = $status;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
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

    /**
     * Checks if the order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    /**
     * Cancels the order if possible
     */
    public function cancel(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }
        
        $this->status = self::STATUS_CANCELLED;
        return true;
    }

    /**
     * Validates that the order is in a valid state
     */
    public function validate(): bool
    {
        // Make sure we have items
        if ($this->items->isEmpty()) {
            return false;
        }
        
        // Make sure we have a user
        if ($this->user === null) {
            return false;
        }
        
        // Verify all items have valid products and quantities
        foreach ($this->items as $item) {
            if ($item->getProduct() === null || $item->getQuantity() <= 0) {
                return false;
            }
        }
        
        return true;
    }
}
