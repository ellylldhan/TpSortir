<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Participant;
use App\Form\ParticipantType;

/**
 * Class AdminController
 * @package App\Controller
 * @Route("/admin", name="admin")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/addParticipant", name="_add_participant")
     */
    public function addParticipant(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $participant= new Participant();

        $form = $this->createForm(ParticipantType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participant = $form->getData();

            $em->persist($participant);
            $em->flush();

            $this->addFlash('success', 'Participant enregistré !');
            $this->redirectToRoute('admin_add_participant');
        } else {
            $errors = $this->getErrorsFromForm($form);

            foreach ($errors as $error) {
                $this->addFlash('danger', $error[0]);
            }

            $this->redirectToRoute('admin_add_participant');
        }

        return $this->render('admin/addParticipant.html.twig', [
            'title' => 'Ajout participant',
            'form' => $form->createView()
        ]);
    }

    private function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }
}
