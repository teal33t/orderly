<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Product;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryTest extends TestCase
{
    private ValidatorInterface $validator;
    
    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAnnotationMapping()
            ->getValidator();
    }
    
    public function testConstructor(): void
    {
        $category = new Category();
        
        $this->assertEmpty($category->getProducts());
        $this->assertNull($category->getId());
    }
    
    public function testGetterAndSetters(): void
    {
        $category = new Category();
        
        $category->setName('Electronics');
        $this->assertEquals('Electronics', $category->getName());
        
        $category->setSlug('electronics');
        $this->assertEquals('electronics', $category->getSlug());
    }
    
    public function testProductRelationship(): void
    {
        $category = new Category();
        $product = $this->createMock(Product::class);
        
        // Test adding a product
        $product->expects($this->once())
            ->method('addCategory')
            ->with($this->equalTo($category));
            
        $category->addProduct($product);
        $this->assertCount(1, $category->getProducts());
        
        // Test removing a product
        $product->expects($this->once())
            ->method('removeCategory')
            ->with($this->equalTo($category));
            
        $category->removeProduct($product);
        $this->assertCount(0, $category->getProducts());
    }
    
    public function testNameValidation(): void
    {
        $category = new Category();
        
        // Test blank name
        $category->setName('');
        $category->setSlug('valid-slug');
        $violations = $this->validator->validate($category);
        $this->assertGreaterThan(0, count($violations));
        
        // Test name too short
        $category->setName('a');
        $violations = $this->validator->validate($category);
        $this->assertGreaterThan(0, count($violations));
        
        // Test valid name
        $category->setName('Electronics');
        $violations = $this->validator->validate($category, null, ['category:write']);
        $this->assertEquals(0, count($violations));
    }
    
    public function testSlugValidation(): void
    {
        $category = new Category();
        $category->setName('Valid Name');
        
        // Test blank slug
        $category->setSlug('');
        $violations = $this->validator->validate($category);
        $this->assertGreaterThan(0, count($violations));
        
        // Test invalid slug format
        $category->setSlug('Invalid Slug');
        $violations = $this->validator->validate($category);
        $this->assertGreaterThan(0, count($violations));
        
        // Test valid slug
        $category->setSlug('valid-slug');
        $violations = $this->validator->validate($category, null, ['category:write']);
        $this->assertEquals(0, count($violations));
    }
}