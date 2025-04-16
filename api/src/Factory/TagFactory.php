<?php

namespace App\Factory;

use App\Entity\Tag;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Tag>
 */
final class TagFactory extends PersistentProxyObjectFactory
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
        return Tag::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        $name = self::faker()->randomElement([
            'Electronics', 'Fashion', 'Home', 'Kitchen', 'Books', 
            'Sports', 'Outdoor', 'Beauty', 'Health', 'Toys',
            'Games', 'Furniture', 'Accessories', 'Footwear', 'Art',
            'Handmade', 'Vintage', 'Seasonal', 'Sale', 'New',
            'Featured', 'Bestseller', 'Limited Edition', 'Eco-friendly', 'Premium'
        ]);
        
        // Create a slug from the name (lowercase, replace spaces with hyphens)
        $slug = strtolower(str_replace(' ', '-', $name));
        
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
            // ->afterInstantiate(function(Tag $tag): void {})
        ;
    }

    /**
     * Create a tag with a specific name
     * 
     * @param string $name The name for the tag
     * @return static
     */
    public function withName(string $name): static
    {
        return $this->with([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name))
        ]);
    }

    /**
     * Create multiple tags with specified names
     * 
     * @param array $names Array of tag names to create
     * @return array
     */
    public static function createTags(array $names): array
    {
        $tags = [];
        foreach ($names as $name) {
            $tags[] = self::new()->withName($name)->create();
        }
        return $tags;
    }
}
