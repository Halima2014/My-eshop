<?php

namespace App\Controller;

use DateTime;
use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


/**
 * @Route("/admin")
 */
class CommandeController extends AbstractController
{   

    /**
     * @Route("/voir-les-commandes", name="show_commandes", methods={"GET"})
     */
    public function showCommande(CommandeRepository $commandeRepository): Response
    {
        return $this->render('admin/show_commandes.html.twig', [
            'commandes' => $commandeRepository->findBy(['deletedAt' => null])
        ]);
    }

    
    /**
     * @Route("/archiver-une-commande/{id}", name="soft_delete_commande", methods={"GET"})
     */
    public function softDeleteCommande(Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $commande->setDeletedAt(new DateTime());
        $commande->setState('annulée');

        $entityManager->persist($commande);
        $entityManager->flush();

        $this->addFlash('success', "La commande ".$commande->getId()." a bien été annulée. ");
        return $this->redirectToRoute('show_commandes');
    }

    /**
     * @Route("/voir-une-commande-annulees/", name="show_canceled_commandes", methods={"GET"})
     */
    public function showCanceledCommandes(CommandeRepository $commandeRepository): Response
    {

        $canceledCommandes = $commandeRepository->findByCanceled();

        return $this->render('admin/trash/show_canceled_commandes.html.twig',[
            'canceled_commandes' =>$canceledCommandes
        ]);


        
    }
    /**
         * 
         * @Route("/restaure-une-commande/{id}", name="restore_commande", methods={"GET"})
         */
        public function restoreCommande(Commande $commande, EntityManagerInterface $entityManager): Response
        {
            $commande->setDeletedAt(null);
            $commande->setState("en cours");

            $entityManager->persist($commande);
            $entityManager->flush();

            return $this->redirectToRoute('show_conceled_commandes');


        }

        /**
         * @Route("/supprimer-une-commande/{}id", name="hard_delete_commande", methods="{GET|POST}")
         */
        public function hardDeleteCommande(Commande $commande, EntityManagerInterface $entityManager): Response
        {
            $entityManager->remove($commande);
            $entityManager->flush();

            $this->addFlash('success', 'La commande a bien été supprimée. ');
            return $this->redirectToRoute('show_conceled_commandes');
        }
}
