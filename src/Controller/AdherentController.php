<?php

namespace App\Controller;

use App\Entity\Adherent;
use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdherentController extends AbstractController
{
    #[Route('/adherents', name: 'adherent_index')]
    public function index(AdherentRepository $adherentRepository): Response
    {
        $adherents = $adherentRepository->findAll();

        return $this->render('adherent/index.html.twig', [
            'adherents' => $adherents,
        ]);
    }

    #[Route('/adherent/{id}', name: 'adherent_show', requirements: ['id' => '\d+'])]
    public function show(Adherent $adherent): Response
    {
        return $this->render('adherent/show.html.twig', [
            'adherent' => $adherent,
        ]);
    }
}