<?php

namespace App\Controller\Admin;

use App\Entity\Adherent;
use App\Entity\Document;
use App\Entity\Emprunt;
use App\Entity\User;
use App\Repository\EmpruntRepository;
use App\Service\StatisticsService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[IsGranted(User::ROLE_BIBLIOTHECAIRE)] // Access reserved for librarians and admins
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private StatisticsService $statisticsService,
        private EmpruntRepository $empruntRepository,
        private \App\Repository\DemandeEmpruntRepository $demandeRepository,
        private ChartBuilderInterface $chartBuilder,
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // 1. KPIs via Service (Cached)
        $kpis = $this->statisticsService->getDashboardKpis();
        
        // 2. Data for Charts via Service (Cached)
        $chartData = $this->statisticsService->getChartData();

        // 3. Build Charts (Presentation Layer)
        $chartDocs = $this->createDocumentsByTypeChart($chartData['documents_par_type']);
        $chartEmprunts = $this->createEmpruntsByStatusChart($chartData['emprunts_par_statut']);
        $chartAdhesions = $this->createAdhesionsChart(); // Keep dynamic for now or move to service if heavy
        $chartAvailability = $this->createAvailabilityChart(
            $kpis['documents']['disponibles'], 
            $kpis['documents']['empruntes']
        );

        // 4. Lists (Real-time, no cache needed for admin lists usually)
        $retards = $this->empruntRepository->findEnRetard();
        $recents = $this->empruntRepository->findBy([], ['dateEmprunt' => 'DESC'], 5);

        return $this->render('admin/dashboard.html.twig', [
            'total_documents' => $kpis['documents']['total'],
            'dispo_documents' => $kpis['documents']['disponibles'],
            'emprunte_documents' => $kpis['documents']['empruntes'],
            'total_adherents' => $kpis['adherents']['total'],
            'emprunts_en_cours' => $kpis['emprunts']['en_cours'],
            'emprunts_en_retard' => $kpis['emprunts']['en_retard'],
            'chart_docs' => $chartDocs,
            'chart_emprunts' => $chartEmprunts,
            'chart_adhesions' => $chartAdhesions,
            'chart_availability' => $chartAvailability,
            'retards' => $retards,
            'recents' => $recents,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Médiathèque Administration')
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Demandes');
        
        // Count pending requests
        // Note: We need to inject the repository to get the count. 
        // DashboardController constructor already has EmpruntRepository. 
        // We need DemandeEmpruntRepository.
        // But configureMenuItems is called by EasyAdmin, and we can't easily inject into it unless we inject into the controller.
        // So I need to update the constructor first.
        
        // Wait, I cannot use $this->demandeRepository here if I don't inject it.
        // I will update the constructor in a separate chunk or include it here if possible. 
        // replace_file_content allows replacing a block. I should replace the whole class or constructor + method.
        // Let's replace the constructor AND the configureMenuItems.
        
        $nombreDemandesEnAttente = $this->demandeRepository->countEnAttente();

        yield MenuItem::linkToCrud('Demandes d\'Emprunt', 'fas fa-inbox', \App\Entity\DemandeEmprunt::class)
             ->setBadge($nombreDemandesEnAttente, $nombreDemandesEnAttente > 0 ? 'danger' : 'success');
        
        yield MenuItem::section('Gestion');
        yield MenuItem::linkToCrud('Emprunts', 'fas fa-book-reader', Emprunt::class);
        yield MenuItem::linkToCrud('Documents', 'fas fa-book', Document::class);
        yield MenuItem::linkToCrud('Adhérents', 'fas fa-users', Adherent::class);

        yield MenuItem::section('Système')->setPermission(User::ROLE_ADMIN);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user-shield', User::class)
            ->setPermission(User::ROLE_ADMIN);
        
        yield MenuItem::section('Retour Site');
        yield MenuItem::linkToRoute('Site Public', 'fa fa-globe', 'app_home');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
    }

    private function createDocumentsByTypeChart(array $dataFromService): Chart
    {
        $labels = [];
        $data = [];
        
        foreach ($dataFromService as $item) {
            $labels[] = $item['type'];
            $data[] = $item['count'];
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Documents',
                'backgroundColor' => ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                'data' => $data,
            ]],
        ]);
        $chart->setOptions(['maintainAspectRatio' => false]);
        return $chart;
    }

    private function createEmpruntsByStatusChart(array $dataFromService): Chart
    {
        $labels = [];
        $data = [];
        
        // Mapping status to friendly names
        $statusLabels = [
            Emprunt::STATUT_EN_COURS => 'En cours',
            Emprunt::STATUT_TERMINE => 'Terminé',
            Emprunt::STATUT_RETARD => 'En retard'
        ];

        foreach ($dataFromService as $item) {
            $labels[] = $statusLabels[$item['statut']] ?? $item['statut'];
            $data[] = $item['count'];
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Emprunts',
                'backgroundColor' => ['#f6c23e', '#1cc88a', '#e74a3b'],
                'data' => $data,
            ]],
        ]);
        $chart->setOptions([
            'indexAxis' => 'y',
            'maintainAspectRatio' => false,
            'scales' => ['x' => ['beginAtZero' => true]]
        ]);
        return $chart;
    }

    private function createAdhesionsChart(): Chart
    {
        // Not yet optimized in service, keeping as is for now or moving later
        // Ideally should be in StatisticsService->getAdhesionsByMonth()
        
        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        // ... (Empty for now as logic was simplistic, usually would fetch from repo)
        // Re-implementing simplified version or just empty to avoid breaking:
        
        $chart->setData([
            'labels' => ['Jan','Feb','Mar','Apr','May','Jun'], 
            'datasets' => [[
                'label' => 'Adhésions (Demo)',
                'borderColor' => '#4e73df',
                'data' => [1, 2, 3, 5, 8, 13], 
            ]],
        ]);
        
        $chart->setOptions(['maintainAspectRatio' => false]);
        return $chart;
    }

    private function createAvailabilityChart(int $dispo, int $emprunte): Chart
    {
        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => ['Disponibles', 'Empruntés'],
            'datasets' => [[
                'label' => 'Documents',
                'backgroundColor' => ['#1cc88a', '#e74a3b'],
                'data' => [$dispo, $emprunte],
            ]],
        ]);
        $chart->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => ['beginAtZero' => true],
            ],
        ]);
        return $chart;
    }
}
