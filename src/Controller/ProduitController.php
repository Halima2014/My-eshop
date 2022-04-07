<?php

namespace App\Controller;

use DateTime;
use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

/**
 * @Route("/admin")
 */
class ProduitController extends AbstractController
{
    /**
     * @Route("/voir-les-produits", name="show_produit", methods={"GET"})
     */
    public function showProduit(ProduitRepository $produitRepository): Response
    {
        return $this->render("admin/show_produit.html.twig",[
            'produits' => $produitRepository->findBy(['deletedAt' => null]),
        ]);
    }


    /**
     * @Route("/creer-un-produit", name="create_produit", methods={"GET|POST"})
     */
    public function createProduit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $produit = new Produit() ;
        
        $form = $this->createForm(ProduitType::class, $produit)
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $produit->setCreatedAt(new DateTime());
            $produit->setUpdatedAt(new DateTime());

            $photo = $form->get('photo')->getData();

            if($photo){
                //méthode créée par nous meme pour réutiliser cette partie de code
                $this-> handleFile($produit, $photo, $slugger);
               
            } //end if($photo)

            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Le nouveau produit est en ligne avec succès!');
            return $this->redirectToRoute('show_produit');
        }// end if($form)

        return $this->render('admin/form/form_produit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/modifier-un-produit_{id}", name="update_produit", methods={"GET|POST"})
     */
    public function updateProduit(Produit $produit, EntityManagerInterface $entityManager, Request $request, SluggerInterface $slugger): Response
    {
        $originalPhoto = $produit->getPhoto();

        $form = $this->createForm(ProduitType::class, $produit, [
            'photo' => $originalPhoto
        ])->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $produit->setUpdatedAt(new DateTime());

            $photo = $form->get('photo')->getData();


            if($photo){
                $this->handleFile($produit, $photo, $slugger);


            }else {
                $produit->setPhoto($originalPhoto);
                
            }

            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Vous avez modifié le produit avec succè !');
            return $this->redirectToRoute('show_produit');
        }
        return $this->render('admin/form/form_produit.html.twig', [
            'form' =>$form->createView(),
            'produit' => $produit,
        ]);
    }



    /////////////////////////////////////////////Private Actio////////////////////////

    private function handleFile(Produit $produit, UploadedFile $photo, SluggerInterface $slugger): void
    {
         # guessExtension() devine l'extension du fichier à partir du MineType du fichier
                    # =>rappel: ne pas confondre extension et MimeType !!
        $extension = '.' . $photo->guessExtension();
        $safeFilename = $slugger->slug($produit->getTitle());

        $newFilename = $safeFilename . '_' . uniqid() . $extension;

        try {
            $photo->move($this->getParameter('uploads_dir'), $newFilename);
            $produit->setPhoto($newFilename);
        } catch (FileException $exception) {
            $this->addFlash('warning', 'La photo du produit ne s\'est pas importée avec succès. Veuillez réessayer en modifiant le produit.');
//                    return $this->redirectToRoute('show_produit');
        } // end catch()

//        return $produit;
    }

    /**
     * @Route("/archiver-un-produit/{id}", name="soft_delete_produit", methods={"GET"})
     */
    public function softDeleteProduit(Produit $produit, EntityManagerInterface $entityManager): Response
    {

        // setDeletedAT() nous permet de créer une bascule (on/off) sur le produit pour afficher en ligne ou le mettre dans la poubelle
         # Cependant ! En BDD la ligne existe toujours, l'objet Produit n'est pas supprimé
        $produit->setDeletedAt(new DateTime());

        $entityManager->persist($produit);
        $entityManager->flush();

        $this->addFlash('success', "Le produit".$produit->getTitle()." a bien été archivé.");
        return $this->redirectToRoute('show_produit');
    }

    /**
     * @Route("/restaurer-un-produit/{id}", name="restore_produit", methods={"GET"})
     */
    public function restoreProduit(Produi $produit, EntityManagerInterface $entityManager): Response
    {

        $produit->setDeletedAt(null);

        $entityManager->persist($produit);
        $entityManager->flush();

        $this->addFlash('success', "Le produit". $produit->getTitle()." a bien été restauré");
        return $this->redirectToRoute('show_produit');

    }

}
