<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function createProduct(string $title, string $description, string $body, int $count): Product
    {
        $product = new Product();
        $product->setTitle($title)
            ->setDescription($description)
            ->setBody($body)
            ->setCount($count);

        $this->productRepository->save($product, true);

        return $product;
    }

    public function updateProduct(Product $product, array $data): Product
    {
        if (isset($data['title'])) {
            $product->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $product->setDescription($data['description']);
        }
        if (isset($data['body'])) {
            $product->setBody($data['body']);
        }
        if (isset($data['count'])) {
            $product->setCount($data['count']);
        }

        $this->productRepository->save($product, true);

        return $product;
    }

    public function deleteProduct(Product $product): void
    {
        $this->productRepository->remove($product, true);
    }

    public function getAvailableProducts(int $minCount = 1): array
    {
        return $this->productRepository->findByAvailability($minCount);
    }

    /**
     * Get products by filters
     * 
     * @param array $filters Available filters:
     *                      - search: string (search in title and description)
     *                      - categories: array of category IDs
     *                      - tags: array of tag IDs
     *                      - minCount: int (minimum available count)
     *                      - orderBy: string (field name to order by)
     *                      - orderDir: string ('ASC' or 'DESC')
     * @return Product[]
     */
    public function getProductsByFilters(array $filters): array
    {
        return $this->productRepository->findByFilters($filters);
    }

    public function addCategory(Product $product, Category $category): Product
    {
        $product->addCategory($category);
        $this->productRepository->save($product, true);
        return $product;
    }

    public function addTag(Product $product, Tag $tag): Product
    {
        $product->addTag($tag);
        $this->productRepository->save($product, true);
        return $product;
    }
}