<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product; 

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product = new Product;
        $product->setName('Product 1');
        $product->setDescription("Description of Product 1");
        $product->setSize(200);
        
        $manager->persist($product);

         $product = new Product;
          $product->setName('Product 2');
          $product->setDescription("Description of Product 2");
          $product->setSize(300);
          
          $manager->persist($product);

        $manager->flush();
          
    }
}
