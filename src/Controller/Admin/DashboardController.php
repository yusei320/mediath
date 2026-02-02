<?php

namespace App\Controller\Admin;

use App\Entity\Adherent;
use App\Entity\Document;
use App\Entity\Emprunt;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private \App\Repository\DocumentRepository $documentRepository,
        private \App\Repository\EmpruntRepository $empruntRepository,
        private \App\Repository\AdherentRepository $adherentRepository,
        private ChartBuilderInterface $chartBuilder,
    ) {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // 1. KPIs
        $totalDocuments = $this->documentRepository->count([]);
        $totalAdherents = $this->adherentRepository->count([]);
        $totalEmprunts = $this->empruntRepository->count([]);
        
        // 2. Charts Data
        
        // A. Pie Chart: Documents by Type
        $types = ['Livre', 'DVD', 'CD', 'Magazine', 'BD'];
        $docsData = [];
        foreach ($types as $type) {
            $docsData[] = $this->documentRepository->count(['type' => $type]);
        }
        $chartDocs = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chartDocs->setData([
            'labels' => $types,
            'datasets' => [[
                'label' => 'Documents par Type',
                'backgroundColor' => ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                'data' => $docsData,
            ]],
        ]);
        $chartDocs->setOptions(['maintainAspectRatio' => false]);

        // B. Bar Chart (Horizontal): Loans by Status
        $statuses = ['en_cours', 'termine', 'retard'];
        $empruntsData = [];
        foreach ($statuses as $status) {
            $empruntsData[] = $this->empruntRepository->count(['statut' => $status]);
        }
        $chartLoans = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chartLoans->setData([
            'labels' => $statuses,
            'datasets' => [[
                'label' => 'Statut des Emprunts',
                'backgroundColor' => ['#f6c23e', '#1cc88a', '#e74a3b'],
                'data' => $empruntsData,
            ]],
        ]);
        // indexAxis 'y' makes it a horizontal bar chart
        $chartLoans->setOptions([
            'indexAxis' => 'y', 
            'maintainAspectRatio' => false,
            'scales' => ['x' => ['beginAtZero' => true]]
        ]);

        // C. Line Chart: Adhesions per Month (Last 12 months)
        // Processing in PHP for simplicity
        $adherents = $this->adherentRepository->findAll();
        $adhesionsPerMonth = [];
        foreach ($adherents as $adherent) {
            if ($adherent->getDateInscription()) {
                $month = $adherent->getDateInscription()->format('Y-m');
            } else {
                continue;
            }
            $adhesionsPerMonth[$month] = ($adhesionsPerMonth[$month] ?? 0) + 1;
        }
        ksort($adhesionsPerMonth);
        // Slice to last 12 months if needed, or take all
        $chartAdhesions = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chartAdhesions->setData([
            'labels' => array_keys($adhesionsPerMonth),
            'datasets' => [[
                'label' => 'Nouvelles Adhésions',
                'borderColor' => '#4e73df',
                'tension' => 0.3,
                'fill' => false,
                'data' => array_values($adhesionsPerMonth),
            ]],
        ]);
        $chartAdhesions->setOptions([
            'maintainAspectRatio' => false,
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['stepSize' => 1]]]
        ]);

        // D. Column Chart: Document Availability
        $available = $this->documentRepository->count(['disponible' => true]);
        $unavailable = $this->documentRepository->count(['disponible' => false]);
        
        $chartAvailability = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chartAvailability->setData([
            'labels' => ['Disponible', 'Emprunté'],
            'datasets' => [[
                'label' => 'Disponibilité',
                'backgroundColor' => ['#1cc88a', '#e74a3b'],
                'data' => [$available, $unavailable],
            ]],
        ]);
        $chartAvailability->setOptions([
            'maintainAspectRatio' => false,
            'scales' => ['y' => ['beginAtZero' => true]]
        ]);

        return $this->render('admin/dashboard.html.twig', [
            'totalDocuments' => $totalDocuments,
            'totalAdherents' => $totalAdherents,
            'totalEmprunts' => $totalEmprunts,
            'chartDocs' => $chartDocs,
            'chartLoans' => $chartLoans, // Now Horizontal
            'chartAdhesions' => $chartAdhesions,
            'chartAvailability' => $chartAvailability,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Médiathèque');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Adhérents', 'fas fa-users', Adherent::class);
        yield MenuItem::linkToCrud('Documents', 'fas fa-book', Document::class);
        yield MenuItem::linkToCrud('Emprunts', 'fas fa-exchange-alt', Emprunt::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user-shield', User::class);
    }
}
