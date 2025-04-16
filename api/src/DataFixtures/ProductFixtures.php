<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    // Product title components
    private array $categories = ['Phone', 'Laptop', 'TV', 'Camera', 'Headphones', 'Watch', 'Speaker', 'Tablet', 'Gaming Console'];
    private array $brands = ['Samsung', 'Apple', 'Sony', 'LG', 'Bose', 'Dell', 'HP', 'Lenovo', 'Microsoft', 'Asus'];
    private array $adjectives = ['Premium', 'Pro', 'Ultra', 'Lite', 'Max', 'Advanced', 'Smart', 'Elite', 'Super', 'Deluxe'];
    private array $models = ['X1', 'Z10', 'A50', 'G7', 'Neo', 'Plus', 'Pro', 'Air', 'Elite', 'Prime'];
    
    // Product features for descriptions
    private array $features = [
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

    public function load(ObjectManager $manager): void
    {
        // First, we'll create categories
        $categoryNames = [
            'Electronics', 
            'Clothing & Fashion',
            'Home & Kitchen', 
            'Books & Media',
            'Beauty & Personal Care'
        ];
        
        $categoryEntities = [];
        foreach ($categoryNames as $name) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', str_replace('&', 'and', $name)));
            $slug = trim($slug, '-');
            
            $category = new Category();
            $category->setName($name);
            $category->setSlug($slug);
            
            $manager->persist($category);
            $categoryEntities[] = $category;
        }
        
        // Next, we'll create tags
        $tagNames = [
            'New', 'Sale', 'Bestseller', 'Featured', 'Limited Edition',
            'Handmade', 'Vintage', 'Eco-friendly', 'Premium', 'Smart'
        ];
        
        $tagEntities = [];
        foreach ($tagNames as $name) {
            $slug = strtolower(str_replace(' ', '-', $name));
            
            $tag = new Tag();
            $tag->setName($name);
            $tag->setSlug($slug);
            
            $manager->persist($tag);
            $tagEntities[] = $tag;
        }
        
        // Create 15 products with random categories and tags
        for ($i = 0; $i < 15; $i++) {
            $product = new Product();
            
            // Generate a realistic title
            $title = $this->generateProductTitle();
            $product->setTitle($title);
            
            // Generate description and body
            list($description, $body) = $this->generateProductContent($title);
            $product->setDescription($description);
            $product->setBody($body);
            
            // Set price and stock count
            $product->setPrice(rand(999, 999999)); // Price from $9.99 to $9,999.99
            $product->setCount(rand(0, 100));      // Stock from 0 to 100
            
            // Add 1-2 random categories
            $categoryCount = rand(1, 2);
            $randomCategoryKeys = array_rand($categoryEntities, $categoryCount);
            
            if (!is_array($randomCategoryKeys)) {
                $randomCategoryKeys = [$randomCategoryKeys];
            }
            
            foreach ($randomCategoryKeys as $key) {
                $product->addCategory($categoryEntities[$key]);
            }
            
            // Add 2-4 random tags
            $tagCount = rand(2, 4);
            $randomTagKeys = array_rand($tagEntities, $tagCount);
            
            if (!is_array($randomTagKeys)) {
                $randomTagKeys = [$randomTagKeys];
            }
            
            foreach ($randomTagKeys as $key) {
                $product->addTag($tagEntities[$key]);
            }
            
            $manager->persist($product);
        }
        
        $manager->flush();
    }
    
    private function generateProductTitle(): string
    {
        $category = $this->categories[array_rand($this->categories)];
        $brand = $this->brands[array_rand($this->brands)];
        
        // Different product name formats
        $format = rand(1, 3);
        switch ($format) {
            case 1:
                return $brand . ' ' . $category . ' ' . $this->models[array_rand($this->models)];
            case 2:
                return $brand . ' ' . $this->adjectives[array_rand($this->adjectives)] . ' ' . $category;
            case 3:
                return $brand . ' ' . $category . ' ' . $this->adjectives[array_rand($this->adjectives)] . ' ' . $this->models[array_rand($this->models)];
            default:
                return $brand . ' ' . $category;
        }
    }
    
    private function generateProductContent(string $title): array
    {
        // Extract category from title for use in description
        $category = '';
        foreach ($this->categories as $cat) {
            if (stripos($title, $cat) !== false) {
                $category = strtolower($cat);
                break;
            }
        }
        
        if (empty($category)) {
            $category = 'product';
        }
        
        // Pick 3-5 random features for the description
        $featureCount = min(3, count($this->features));
        $selectedFeatures = [];
        $featureKeys = array_rand($this->features, $featureCount);
        
        if (!is_array($featureKeys)) {
            $featureKeys = [$featureKeys];
        }
        
        foreach ($featureKeys as $key) {
            $selectedFeatures[] = $this->features[$key];
        }
        
        // Generate description
        $description = "The $title is a high-quality $category that offers " . 
                      implode(", ", array_slice($selectedFeatures, 0, -1)) . 
                      " and " . end($selectedFeatures) . ".";
        
        // Extract brand from title
        $brand = '';
        foreach ($this->brands as $b) {
            if (stripos($title, $b) !== false) {
                $brand = $b;
                break;
            }
        }
        
        if (empty($brand)) {
            $brand = 'Generic';
        }
        
        // Generate more detailed body text
        $accessory = "";
        $rand = rand(0, 3);
        if ($rand == 0) {
            $accessory = "1 x Charging Cable";
        } elseif ($rand == 1) {
            $accessory = "1 x Power Adapter";
        } elseif ($rand == 2) {
            $accessory = "1 x Protective Case";
        } else {
            $accessory = "Batteries Included";
        }
        
        $body = $description . "\n\n" .
                "Specifications:\n" .
                "- Manufacturer: $brand\n" .
                "- Model: " . $this->models[array_rand($this->models)] . "\n" .
                "- Year: " . rand(2021, 2025) . "\n" .
                "- Warranty: " . rand(1, 3) . " years\n\n" .
                "Package Includes:\n" .
                "- 1 x " . $title . "\n" .
                "- 1 x User Manual\n" .
                "- " . $accessory . "\n\n" .
                "This $category is designed to provide the best experience with its outstanding features and performance. Perfect for both personal and professional use.";
        
        return [$description, $body];
    }
}
