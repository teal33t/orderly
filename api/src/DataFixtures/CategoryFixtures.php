<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Note: This fixture class is no longer used.
 * The ProductFixtures class now creates its own categories and tags directly.
 * This file is kept for reference purposes only.
 */
class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // This method does nothing now - all categories are created in ProductFixtures
    }
}
