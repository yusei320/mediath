<?php

namespace App\Controller\Admin;

use App\Entity\DemandeEmprunt;
use App\Entity\User;
use App\Service\DemandeEmpruntService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_BIBLIOTHECAIRE)]
class DemandeEmpruntCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly DemandeEmpruntService $demandeService,
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {}

    public static function getEntityFqcn(): string
    {
        return DemandeEmprunt::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Demande d\'Emprunt')
            ->setEntityLabelInPlural('Demandes d\'Emprunt')
            ->setDefaultSort(['dateDemande' => 'DESC'])
            ->setSearchFields(['adherent.nom', 'adherent.prenom', 'document.titre']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateTimeField::new('dateDemande', 'Date Demande');
        yield AssociationField::new('adherent', 'Adhérent');
        yield AssociationField::new('document', 'Document');
        yield AssociationField::new('bibliothecaire', 'Bibliothécaire Assigné');
        yield TextField::new('statut', 'Statut')
            ->formatValue(function ($value) {
                return match($value) {
                    'en_attente' => '<span class="badge badge-warning">En Attente</span>',
                    'acceptee' => '<span class="badge badge-success">Acceptée</span>',
                    'refusee' => '<span class="badge badge-danger">Refusée</span>',
                    default => $value
                };
            });
        
        if ($pageName === Crud::PAGE_DETAIL) {
            yield TextareaField::new('messageAdherent', 'Message Adhérent');
            yield TextareaField::new('motifRefus', 'Motif Refus');
        }
    }

    public function configureActions(Actions $actions): Actions
    {
        $accepter = Action::new('accepter', 'Accepter', 'fa fa-check')
            ->linkToRoute('admin_demande_accepter', fn (DemandeEmprunt $entity) => ['id' => $entity->getId()])
            ->displayIf(fn (DemandeEmprunt $entity) => $entity->getStatut() === DemandeEmprunt::STATUT_EN_ATTENTE)
            ->setCssClass('btn btn-success');

        $refuser = Action::new('refuser', 'Refuser', 'fa fa-times')
            ->linkToRoute('admin_demande_refuser', fn (DemandeEmprunt $entity) => ['id' => $entity->getId()])
            ->displayIf(fn (DemandeEmprunt $entity) => $entity->getStatut() === DemandeEmprunt::STATUT_EN_ATTENTE)
            ->setCssClass('btn btn-danger');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $accepter)
            ->add(Crud::PAGE_INDEX, $refuser)
            ->add(Crud::PAGE_DETAIL, $accepter)
            ->add(Crud::PAGE_DETAIL, $refuser);
    }

    #[Route('/admin/demande/{id}/accepter', name: 'admin_demande_accepter')]
    #[IsGranted(User::ROLE_BIBLIOTHECAIRE)]
    public function accepter(int $id): RedirectResponse
    {
        $demande = $this->demandeService->findById($id);
        
        try {
            $this->demandeService->accepterDemande($demande);
            $this->addFlash('success', 'Demande acceptée ! L\'emprunt a été créé automatiquement.');
        } catch (\Exception $e) {
            $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl()
        );
    }

    #[Route('/admin/demande/{id}/refuser', name: 'admin_demande_refuser')]
    #[IsGranted(User::ROLE_BIBLIOTHECAIRE)]
    public function refuser(int $id, Request $request): Response
    {
        $demande = $this->demandeService->findById($id);

        if ($request->isMethod('POST')) {
            $motifRefus = $request->request->get('motif_refus');

            try {
                $this->demandeService->refuserDemande($demande, $motifRefus);
                $this->addFlash('success', 'Demande refusée. L\'adhérent sera notifié.');
                
                return $this->redirect($this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction(Action::INDEX)
                    ->generateUrl()
                );
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            }
        }

        return $this->render('admin/demande_refuser.html.twig', [
            'demande' => $demande,
        ]);
    }
}
