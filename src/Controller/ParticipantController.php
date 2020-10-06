<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class ParticipantController extends AbstractController
{
    /**
     * @Route("/", name="base")
     */
    public function index()
    {
        return $this->render("base.html.twig", []);
    }


    /**
     * @Route("/login", name="login")
     */
    public function login()
    {
        return $this->render("participant/login.html.twig", []);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        $this->addFlash("success", "Vous êtes déconnecté.");
    }
}
