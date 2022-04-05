<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        // throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/inscription", name="security_register", methods={"GET|POST"})
     */ 
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterType::class, $user)->handleRequest($request);

        if($form->isSubmitted() && $form->isvalid()) {

            // Set des proprietés qui ne sont pas dans le formulaire
            $user->setCreatedAt(new DateTime());
            $user->setUpdatedAt( new DateTime());
            $user->setRoles(['ROLE_USER']);

            // Nous devons setter manuallement le hash du password grace au $passwordHasher et sa methode hashPassword()
             # => cette méthode prend 2 paramètres :$user, $plainPassword
            $user->setPassword(
                $passwordHasher->hashPassword(
                    $user, $form->get('password')->getData()
                )
            );

            //
             $entityManager->persist($user);
             $entityManager->flush();

             $this->addFlash('success', 'Votre inscription est validé, Connectez-vous à présent !');
             return $this->redirectToRoute('app_login');
           
        }
        return $this->render('security/form_register.html.twig', [
            'form' => $form->createView(),
         ]);
 

    }


}
