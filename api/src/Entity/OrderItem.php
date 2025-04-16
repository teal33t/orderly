<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        // new GetCollection(
        //     security: "is_granted('ROLE_ADMIN')",
        //     securityMessage: 'Only admins can view all order items.'
        // ),
        // new Get(
        //     security: "is_granted('ROLE_ADMIN') or object.getOrder().getUser() == user",
        //     securityMessage: 'You can only view your own order items.'
        // ),
        new GetCollection(),
        new Get(),
        new Post(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Only admins can create order items.'
        ),
        new Put(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Only admins can update order items.'
        ),
        new Delete(
            security: "is_granted('ROLE_ADMIN')",
            securityMessage: 'Only admins can delete order items.'
        )
    ],
    normalizationContext: ['groups' => ['order_item:read']],
    denormalizationContext: ['groups' => ['order_item:write']]
)]
#[ORM\Entity]
class OrderItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    #[Groups(['order_item:read', 'order:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['order_item:read'])]
    #[Assert\NotNull(message: 'An order item must belong to an order')]
    private ?Order $order = null;

    #[ORM\ManyToOne(targetEntity: Product::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['order_item:read', 'order:read', 'order_item:write', 'order:write'])]
    #[Assert\NotNull(message: 'An order item must have a product')]
    private ?Product $product = null;

    #[ORM\Column(type: 'integer')]
    #[Groups(['order_item:read', 'order:read', 'order_item:write', 'order:write'])]
    #[Assert\NotBlank(message: 'Quantity cannot be blank')]
    #[Assert\GreaterThan(value: 0, message: 'Quantity must be greater than 0')]
    private int $quantity = 1;

    #[ORM\Column(type: 'integer')]
    #[Groups(['order_item:read', 'order:read'])]
    private int $price = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;
        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;
        if ($product) {
            $this->price = $product->getPrice();
        }
        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function getTotal(): int
    {
        return $this->price * $this->quantity;
    }
}
