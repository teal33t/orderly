<?php

namespace App\Tests\EventListener;

use App\Entity\Category;
use App\EventListener\CategorySlugListener;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PHPUnit\Framework\TestCase;
use Symfony\Component\String\Slugger\AsciiSlugger;

class CategorySlugListenerTest extends TestCase
{
    private CategorySlugListener $listener;
    
    protected function setUp(): void
    {
        $slugger = new AsciiSlugger();
        $this->listener = new CategorySlugListener($slugger);
    }
    
    public function testPrePersistGeneratesSlug(): void
    {
        // Create a category with a name but no slug
        $category = new Category();
        $category->setName('Test Category');
        
        // Mock the lifecycle event args
        $lifecycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        
        // Call the prePersist method
        $this->listener->prePersist($category, $lifecycleEventArgs);
        
        // Assert that the slug was generated correctly
        $this->assertEquals('test-category', $category->getSlug());
    }
    
    public function testPreUpdateGeneratesSlugWhenNameChanges(): void
    {
        // Create a category with a name and existing slug
        $category = new Category();
        $category->setName('Old Name');
        $category->setSlug('old-name');
        
        // Mock the lifecycle event args
        $lifecycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        
        // Change the name
        $category->setName('New Name');
        
        // Call the preUpdate method
        $this->listener->preUpdate($category, $lifecycleEventArgs);
        
        // Assert that the slug was updated
        $this->assertEquals('new-name', $category->getSlug());
    }
    
    public function testPreUpdateDoesNotChangeSlugWhenNameUnchanged(): void
    {
        // Create a category with a custom slug that doesn't match the name
        $category = new Category();
        $category->setName('Test Category');
        $category->setSlug('custom-slug');
        
        // Clone the category to simulate the state before changes
        $originalSlug = $category->getSlug();
        
        // Mock the lifecycle event args
        $lifecycleEventArgs = $this->createMock(LifecycleEventArgs::class);
        
        // Call the preUpdate method without changing the name
        $this->listener->preUpdate($category, $lifecycleEventArgs);
        
        // Assert that the slug remains unchanged
        $this->assertEquals($originalSlug, $category->getSlug());
    }
}