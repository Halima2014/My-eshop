<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\User;
use App\Entity\Produit;
use App\Entity\Commande;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Provider\Base;

class CommandeFixture extends Fixture
{
    private EntityManagerInterface $entityManager;



    public function __construct(EntityManagerInterface $entityManager)
    {
        // On assigne l'objet EntityManagerInterface à notre propriété $entityManager
        $this->entityManager = $entityManager;
      
    }

    /**
     * Cette fonction load() sera exécutée par la ligne de commande[php bin/console doctrine:fixtures:load --append]
     * 
     */
    public function load(ObjectManager $manager): void
    {
            $faker = Base::class;

        $user = $this->entityManager->getRepository(User::class)->find(2);
        $produit = $this->entityManager->getRepository(Produit::class)->find(4);
        
      
        
        for($i=0; $i< 5; ++$i) {

            $commande = new Commande() ;

            $commande->setQuantity($faker::randomDigit());
            $commande->setTotal($faker::randomNumber(3));
            $commande->setState('en cours');
    
            $commande->setUser($user);
            $commande->addProduct($produit);
    
            $commande->setCreatedAt(new DateTime());
            $commande->setUpdatedAt(new DateTime());
    
            $manager->persist($commande);
        } //end for()

        $manager->flush();
    } //end load()

} //end class
