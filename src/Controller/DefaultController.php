<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default_home", methods={"GET"})
     *@ return Response 
     */
   public function home(ProduitRepository $produitRepository): Response
   {
        $produits = $produitRepository->findBy(['deletedAt' => null]);

       return $this->render('default/home.html.twig', [
           'produits'=>$produits
       ]);
   } 
}
