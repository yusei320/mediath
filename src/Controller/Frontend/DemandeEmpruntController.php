<?php

namespace App\Controller\Frontend;

use App\Entity\DemandeEmprunt;
use App\Form\DemandeEmpruntType;
use App\Repository\AdherentRepository;
use App\Repository\DocumentRepository;
use App\Repository\UserRepository;
use App\Service\DemandeEmpruntService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/adherent/demande-emprunt')]
#[IsGranted('ROLE_ADHERENT')]
class DemandeEmpruntController extends AbstractController
{
    public function __construct(
        private readonly DemandeEmpruntService $demandeService,
        private readonly DocumentRepository $documentRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly UserRepository $userRepository
    ) {}

    #[Route('/nouvelle/{id}', name: 'app_adherent_demande_emprunt')]
    public function nouvelle(int $id, Request $request): Response
    {
        $document = $this->documentRepository->find($id);
        
        if (!$document) {
            throw $this->createNotFoundException('Document introuvable.');
        }

        // Trouver l'adhérent lié au user connecté
        $user = $this->getUser();
        $adherent = $this->adherentRepository->findOneBy(['email' => $user->getEmail()]);

        if (!$adherent) {
            $this->addFlash('danger', 'Aucun profil adhérent associé à votre compte.');
            return $this->redirectToRoute('document_index');
        }

        // Vérifier si peut demander
        $check = $this->demandeService->canDemanderEmprunt($adherent, $document);
        
        if (!$check['can_request']) {
            foreach ($check['errors'] as $error) {
                $this->addFlash('danger', $error);
            }
            return $this->redirectToRoute('document_index');
        }

        // Récupérer les bibliothécaires
        // Note: Using a custom method findByRole if exists, or standard findBy logic if not handled by repo custom method.
        // User repository usually doesn't have findByRole unless added. 
        // Let's assume we can filter or use a query. 
        // For now, I'll filter in PHP or use a repo method if I add it.
        // Actually, the prompt assumed `findByRole` exists in UserRepository. I should probably ADD IT or use a work-around.
        // The prompt USER REQUEST said: `$this->userRepository->findByRole('ROLE_BIBLIOTHECAIRE');`
        // So I should PROBABLY ADD IT to UserRepository as well to be safe, or just use filter here.
        // Let's assume standard implementation might not have it. I will implement a quick query here or add it to repo?
        // Let's add it to repo in a separate step or just do a QueryBuilder here.
        // I will use QueryBuilder here for simplicity if I can't modify repo easily in this step.
        // Wait, I can inject EntityManager and do a query.
        // Or better, let's assume I will update UserRepository later or now.
        // I'll stick to the controller code provided but I need to make sure `findByRole` works. 
        // I will implement a workaround here to be safe: fetch all and filter (not efficient but safe) OR use QueryBuilder.
        // Let's use logic to fetch users who HAVE the role.
        
        $users = $this->userRepository->findAll();
        $bibliothecaires = array_filter($users, fn($u) => in_array('ROLE_BIBLIOTHECAIRE', $u->getRoles()));

        $demande = new DemandeEmprunt();
        $demande->setAdherent($adherent);
        $demande->setDocument($document);

        $form = $this->createForm(DemandeEmpruntType::class, $demande, [
            'bibliothecaires' => $bibliothecaires
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->demandeService->creerDemande(
                $adherent,
                $document,
                $demande->getBibliothecaire(),
                $demande->getDateEmpruntSouhaitee(),
                $demande->getDureeSouhaiteeJours(),
                $demande->getMessageAdherent()
            );

            $this->addFlash('success', 'Votre demande d\'emprunt a été envoyée au bibliothécaire.');
            return $this->redirectToRoute('adherent_espace');
        }

        return $this->render('adherent/demande-emprunt.html.twig', [
            'document' => $document,
            'form' => $form->createView(),
        ]);
    }
}
