<?php

namespace App\Controller\Frontend;

use App\Entity\Document;
use App\Service\DocumentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogueController extends AbstractController
{
    public function __construct(
        private readonly DocumentService $documentService,
        private readonly \App\Repository\EmpruntRepository $empruntRepository,
        private readonly \App\Repository\DemandeEmpruntRepository $demandeRepository,
        private readonly \App\Repository\AdherentRepository $adherentRepository
    ) {}

    #[Route('/documents', name: 'document_index')]
    public function index(Request $request): Response
    {
        $query = $request->query->get('q');
        $type = $request->query->get('type');
        $dispoOnly = $request->query->getBoolean('disponible');

        if ($query) {
            $documents = $this->documentService->search($query);
        } else {
            $documents = $this->documentService->getDisponibles($type);
        }
        
        if ($dispoOnly && $query) {
            $documents = array_filter($documents, fn(Document $d) => $d->isDisponible());
        }

        // Check for button state
        $hasRetards = false;
        $pendingDocIds = [];
        
        if ($this->isGranted('ROLE_ADHERENT')) {
            $user = $this->getUser();
            $adherent = $this->adherentRepository->findOneBy(['email' => $user->getEmail()]);
            
            if ($adherent) {
                $hasRetards = $this->empruntRepository->hasRetards($adherent);
                // Get all pending requests for this adherent
                $demandes = $this->demandeRepository->findBy([
                    'adherent' => $adherent, 
                    'statut' => \App\Entity\DemandeEmprunt::STATUT_EN_ATTENTE
                ]);
                foreach ($demandes as $d) {
                    $pendingDocIds[] = $d->getDocument()->getId();
                }
            }
        }

        return $this->render('document/index.html.twig', [
            'documents' => $documents,
            'current_query' => $query,
            'current_type' => $type,
            'show_dispo' => $dispoOnly,
            'has_retards' => $hasRetards,
            'pending_doc_ids' => $pendingDocIds,
        ]);
    }

    #[Route('/document/{id}', name: 'document_show', requirements: ['id' => '\d+'])]
    public function show(Document $document): Response
    {
        // Check for button state
        $hasRetards = false;
        $isPending = false;

        if ($this->isGranted('ROLE_ADHERENT')) {
            $user = $this->getUser();
            $adherent = $this->adherentRepository->findOneBy(['email' => $user->getEmail()]);
            
            if ($adherent) {
                $hasRetards = $this->empruntRepository->hasRetards($adherent);
                $isPending = $this->demandeRepository->hasDemandeEnAttenteForDocument($adherent, $document);
            }
        }

        return $this->render('document/show.html.twig', [
            'document' => $document,
            'has_retards' => $hasRetards,
            'is_pending' => $isPending,
        ]);
    }
}
