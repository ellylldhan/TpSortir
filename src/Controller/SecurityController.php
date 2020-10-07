<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\User;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder) {
        $user = new Participant();


        $registerForm = $this->createForm(RegisterType::class, $user);

        $registerForm->handleRequest($request);
        if ($registerForm->isSubmitted() && $registerForm->isValid()) {
            // Hacher mot de passe
            $hashed = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashed);


            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash("success", "User created");

            return $this->redirectToRoute("user_list");
        }

        return $this->render('security/register.html.twig', [
            'registerForm' => $registerForm->createView(),
        ]);
    }

    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // focus sur mdp si last user name
        $autofocusPswd  = ($lastUsername) ? "autofocus" : "";
        $autofocusLogin = ($lastUsername) ? "" : "autofocus";

        return $this->render('security/login.html.twig', array(
            'last_username'  => $lastUsername,
            'error'          => $error,
            'autofocusPswd'  => $autofocusPswd,
            'autofocusLogin' => $autofocusLogin,
        ));
    }


    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        throw new \LogicException(
            'This method can be blank - it will be intercepted by the logout key on your firewall.'
        );
    }

    /**
     * Redirection après login
     * @Route("/login/redirect", name="login_redirect")
     * @param Request $request
     * @return RedirectResponse
     */
    public function loginRedirect(Request $request)
    {
        if (!$this->isGranted('ROLE_CANDIDAT') && !$this->isGranted('ROLE_CLIENT')) //Si l'utilisateur ne possède pas le rôle le plus bas de la hierarchie (i.e. il possède le ROLE_GUEST)
        {
            $this->addFlash('danger', 'Votre inscription est en attente de validation.');

            return $this->redirectToRoute('login');
        } else {
            return $this->redirectToRoute('base');
        }
    }

    /**
     * @Route("/user/list", name="user_list")
     */
    public function list()
    {
        $userRepo = $this->getDoctrine()->getRepository(Participant::class);

        $listUsers = $userRepo->findBy([], ["pseudo" => "ASC"], 30, 0);
        dump($listUsers);

        return $this->render('security/list.html.twig', [
            'listUsers' => $listUsers,
        ]);
    }

}
