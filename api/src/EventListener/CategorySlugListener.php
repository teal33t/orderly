<?php

namespace App\EventListener;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: Category::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Category::class)]
class CategorySlugListener
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function prePersist(Category $category, LifecycleEventArgs $event): void
    {
        $this->updateSlug($category);
    }

    public function preUpdate(Category $category, LifecycleEventArgs $event): void
    {
        $this->updateSlug($category);
    }

    private function updateSlug(Category $category): void
    {
        if (empty($category->getSlug()) || $this->isSlugOutdated($category)) {
            $slug = $this->slugger->slug(strtolower($category->getName()))->toString();
            $category->setSlug($slug);
        }
    }

    private function isSlugOutdated(Category $category): bool
    {
        $currentSlug = $category->getSlug();
        $expectedSlug = $this->slugger->slug(strtolower($category->getName()))->toString();
        
        return $currentSlug !== $expectedSlug;
    }
}