<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // create 20 products! Bam!
        $category = $manager->getRepository(Category::class)->find(1);
        if ($category === null) {
            print_r("no category found\n");
            die("\n");
        }

        for ($i = 0; $i < 2000; $i++) {
            $product = new Product();
            $product->setActive(true);
            $product->setCategory($category);

            $product->name = 'pFake ' . $i;
            $product->article_number = 'FAKEART-1964-' . $i;
            $product->price = (float)$i + 0.45;
            $manager->persist($product);
        }
        $manager->flush();
    }
}
