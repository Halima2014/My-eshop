<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccountController extends AbstractController
{ 
    /** 
    *@Route("/mon-compte", name="show_account", methods={"GET"})
    *@return Response
    */
    public function showAccount(CommandeRespository $commandeRespository): Response
    {
        $commands = $commandeRespository->findBy(['user' => $this->getUser()]);

        return $this->render('account/show_account.html.twig');
        
    }
}
