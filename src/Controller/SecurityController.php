<?php

namespace App\Controller;

use App\Entity\Participant;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public const ROUTE_BASE = 'base';

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute(self::ROUTE_BASE);
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // focus sur mdp si last user name
        $autofocusPswd  = ($lastUsername) ? "autofocus" : "";
        $autofocusLogin = ($lastUsername) ? "" : "autofocus";

        return $this->render('security/login.html.twig', [
            'last_username'  => $lastUsername,
            'error'          => $error,
            'autofocusPswd'  => $autofocusPswd,
            'autofocusLogin' => $autofocusLogin,
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
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

    /**
     * @Route("/", name="base")
     */
    public function index()
    {
        return $this->render("base.html.twig", []);
    }
}
