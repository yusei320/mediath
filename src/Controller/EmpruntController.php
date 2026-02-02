<?php

namespace App\Controller;

use App\Entity\Emprunt;
use App\Repository\EmpruntRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EmpruntController extends AbstractController
{
    #[Route('/emprunts', name: 'emprunt_index')]
    public function index(EmpruntRepository $empruntRepository): Response
    {
        $emprunts = $empruntRepository->findAll();

        return $this->render('emprunt/index.html.twig', [
            'emprunts' => $emprunts,
        ]);
    }

    #[Route('/emprunt/{id}', name: 'emprunt_show', requirements: ['id' => '\d+'])]
    public function show(Emprunt $emprunt): Response
    {
        return $this->render('emprunt/show.html.twig', [
            'emprunt' => $emprunt,
        ]);
    }
}