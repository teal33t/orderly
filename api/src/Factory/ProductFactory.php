<?php

namespace App\Factory;

use App\Entity\Product;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Product>
 */
final class ProductFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Product::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        // Generate realistic product titles
        $categories = ['Phone', 'Laptop', 'TV', 'Camera', 'Headphones', 'Watch', 'Speaker', 'Tablet', 'Gaming Console'];
        $brands = ['Samsung', 'Apple', 'Sony', 'LG', 'Bose', 'Dell', 'HP', 'Lenovo', 'Microsoft', 'Asus'];
        $adjectives = ['Premium', 'Pro', 'Ultra', 'Lite', 'Max', 'Advanced', 'Smart', 'Elite', 'Super', 'Deluxe'];
        $models = ['X1', 'Z10', 'A50', 'G7', 'Neo', 'Plus', 'Pro', 'Air', 'Elite', 'Prime'];
        
        $category = self::faker()->randomElement($categories);
        $brand = self::faker()->randomElement($brands);
        $title = '';
        
        // Different product name formats
        $format = self::faker()->numberBetween(1, 3);
        switch ($format) {
            case 1:
                $title = $brand . ' ' . $category . ' ' . self::faker()->randomElement($models);
                break;
            case 2:
                $title = $brand . ' ' . self::faker()->randomElement($adjectives) . ' ' . $category;
                break;
            case 3:
                $title = $brand . ' ' . $category . ' ' . self::faker()->randomElement($adjectives) . ' ' . self::faker()->randomElement($models);
                break;
        }
        
        // Generate realistic descriptions
        $features = [
            'High-resolution display',
            'Long battery life',
            'Fast processing speed',
            'Water resistant',
            'Bluetooth connectivity',
            'Sleek design',
            'Energy efficient',
            'Enhanced security features',
            'Wireless charging',
            'Voice control'
        ];
        
        // Pick 3-5 random features for the description
        $selectedFeatures = self::faker()->randomElements(
            $features, 
            self::faker()->numberBetween(3, 5)
        );
        
        $description = "The $title is a high-quality " . strtolower($category) . " that offers " . 
                      implode(", ", array_slice($selectedFeatures, 0, -1)) . 
                      " and " . end($selectedFeatures) . ".";
        
        // Generate more detailed body text
        $body = $description . "\n\n" .
                "Specifications:\n" .
                "- Manufacturer: $brand\n" .
                "- Model: " . self::faker()->randomElement($models) . "\n" .
                "- Year: " . self::faker()->numberBetween(2021, 2025) . "\n" .
                "- Warranty: " . self::faker()->randomElement(['1 year', '2 years', '3 years']) . "\n\n" .
                "Package Includes:\n" .
                "- 1 x " . $title . "\n" .
                "- 1 x User Manual\n" .
                "- " . self::faker()->randomElement(['1 x Charging Cable', '1 x Power Adapter', '1 x Protective Case', 'Batteries Included']) . "\n\n" .
                self::faker()->paragraph(3);
        
        return [
            'title' => $title,
            'description' => $description,
            'body' => $body,
            'price' => self::faker()->numberBetween(999, 999999), // Price in cents (9.99 to 9,999.99)
            'count' => self::faker()->numberBetween(0, 100),
            'createdAt' => self::faker()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Product $product): void {})
        ;
    }
    
    /**
     * Create a product with a specific title
     * 
     * @param string $title The title for the product
     * @return static
     */
    public function withTitle(string $title): static
    {
        return $this->with(['title' => $title]);
    }
    
    /**
     * Set product price (in cents)
     * 
     * @param int $price The price in cents
     * @return static
     */
    public function withPrice(int $price): static
    {
        return $this->with(['price' => $price]);
    }
    
    /**
     * Set product stock count
     * 
     * @param int $count The stock count
     * @return static
     */
    public function withCount(int $count): static
    {
        return $this->with(['count' => $count]);
    }
    
    /**
     * Associate product with categories
     * 
     * @param array $categories Array of category objects
     * @return static
     */
    public function withCategories(array $categories): static
    {
        return $this->with(['categories' => $categories]);
    }
    
    /**
     * Associate product with tags
     * 
     * @param array $tags Array of tag objects
     * @return static
     */
    public function withTags(array $tags): static
    {
        return $this->with(['tags' => $tags]);
    }
}
