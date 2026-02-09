<?php

namespace App\Controller\Frontend;

use App\Entity\Adherent;
use App\Entity\User;
use App\Repository\AdherentRepository;
use App\Repository\EmpruntRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/adherent')]
#[IsGranted(User::ROLE_ADHERENT)]
class AdherentEspaceController extends AbstractController
{
    #[Route('/espace', name: 'adherent_espace')]
    public function index(AdherentRepository $adherentRepository, EmpruntRepository $empruntRepository, \App\Repository\DemandeEmpruntRepository $demandeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
             return $this->redirectToRoute('app_login');
        }

        $adherent = $adherentRepository->findOneBy(['email' => $user->getEmail()]);

        if (!$adherent) {
            $this->addFlash('error', 'Aucune fiche adhérent associée à votre compte utilisateur.');
            return $this->render('adherent/no_profile.html.twig');
        }

        // Dashboard only shows current loans and summary stats
        $empruntsEnCours = $empruntRepository->findEnCoursByAdherent($adherent);
        $demandes = $demandeRepository->findByAdherent($adherent);
        
        return $this->render('adherent/espace.html.twig', [
            'adherent' => $adherent,
            'emprunts_en_cours' => $empruntsEnCours,
            'demandes' => $demandes,
        ]);
    }

    #[Route('/mes-emprunts', name: 'adherent_emprunts')]
    public function history(AdherentRepository $adherentRepository, EmpruntRepository $empruntRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $adherent = $adherentRepository->findOneBy(['email' => $user->getEmail()]);

        if (!$adherent) {
            return $this->redirectToRoute('adherent_espace');
        }

        // Full history
        $historique = $empruntRepository->findByAdherentWithDocument($adherent);

        return $this->render('adherent/mes-emprunts.html.twig', [
            'adherent' => $adherent,
            'emprunts' => $historique,
        ]);
    }
}
