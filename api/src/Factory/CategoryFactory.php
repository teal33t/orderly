<?php

namespace App\Factory;

use App\Entity\Category;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Category>
 */
final class CategoryFactory extends PersistentProxyObjectFactory
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
        return Category::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        $name = self::faker()->randomElement([
            'Electronics', 'Clothing', 'Home & Kitchen', 'Books', 'Beauty',
            'Toys & Games', 'Sports', 'Automotive', 'Health', 'Food & Grocery',
            'Office Supplies', 'Pet Supplies', 'Garden & Outdoor', 'Jewelry', 'Baby',
            'Tools & Home Improvement', 'Music & Instruments', 'Furniture', 'Appliances'
        ]);
        
        // Create a slug from the name (lowercase, replace spaces and special chars with hyphens)
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', str_replace('&', 'and', $name)));
        $slug = trim($slug, '-');
        
        return [
            'name' => $name,
            'slug' => $slug,
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Category $category): void {})
        ;
    }
    
    /**
     * Create a category with a specific name
     * 
     * @param string $name The name for the category
     * @return static
     */
    public function withName(string $name): static
    {
        // Create a slug from the name
        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', str_replace('&', 'and', $name)));
        $slug = trim($slug, '-');
        
        return $this->with([
            'name' => $name,
            'slug' => $slug
        ]);
    }
    
    /**
     * Create multiple categories with specified names
     * 
     * @param array $names Array of category names to create
     * @return array
     */
    public static function createCategories(array $names): array
    {
        $categories = [];
        foreach ($names as $name) {
            $categories[] = self::new()->withName($name)->create();
        }
        return $categories;
    }
}
