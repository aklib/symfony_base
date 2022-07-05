<?php

namespace App\DataFixtures;

use App\Entity\Attributable\Product;
use App\Entity\Attributable\ProductCategory;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    /** @noinspection PhpUndefinedFieldInspection
     * @noinspection PhpUndefinedMethodInspection
     */
    public function load(ObjectManager $manager): void
    {
        // create 20 products! Bam!
        $category = $manager->getRepository(ProductCategory::class)->find(1);
        if ($category === null) {
            print_r("no category found\n");
            die("\n");
        }
        $user = $manager->getRepository(User::class)->findOneByEmail('alexej.kisselev@gmail.com');
        for ($i = 0; $i < 5000; $i++) {
            $product = new Product();
            $product->setActive(true);
            $product->setCategory($category);

            $product->name = 'pFake ' . $i;
            $product->article_number = 'FAKE_ART-1964-' . $i;
            $product->price = (float)$i + 0.45;

            $product->setCreatedAt(new DateTime('now'));
            if ($user instanceof User) {
                $product->setCreatedBy($user);
            }
            $manager->persist($product);
        }
        $manager->flush();
    }
}
