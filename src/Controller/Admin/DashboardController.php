<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Entity\Question;
use App\Entity\QuizSession;
use App\Entity\QuizTemplate;
use App\Entity\User;
use App\Entity\Company;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Doctrine\ORM\EntityManagerInterface;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private ChartBuilderInterface $chartBuilder,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function index(): Response
    {
        // Fetch data for users and enterprises
        // This is a simplified example. In production, you'd want to group by date properly using SQL or a processed array.
        // Since roles are JSON array, we use LIKE for simple filtering if database is simple, or just PHP filtering for small datasets.
        
        $users = $this->entityManager->getRepository(User::class)->findAll();
        
        $dataUsers = [];
        $dataEnterprises = [];
        $dates = [];

        // Sort users by date to ensure proper timeline
        usort($users, fn($a, $b) => $a->getCreatedAt() <=> $b->getCreatedAt());

        foreach ($users as $user) {
            $date = $user->getCreatedAt() ? $user->getCreatedAt()->format('Y-m') : 'N/A';
            if (!in_array($date, $dates)) {
                $dates[] = $date;
                $dataUsers[$date] = 0;
                $dataEnterprises[$date] = 0;
            }

            if (in_array('ROLE_ENTREPRISE', $user->getRoles())) {
                $dataEnterprises[$date]++;
            } else {
                // Assuming non-enterprise are regular users. Adjust if you want to exclude Admins.
                $dataUsers[$date]++;
            }
        }
        
        // Accumulate counts for "evolution" (cumulative)
        $cumulativeUsers = 0;
        $cumulativeEnterprises = 0;
        $chartDataUsers = [];
        $chartDataEnterprises = [];

        foreach ($dates as $date) {
            $cumulativeUsers += $dataUsers[$date];
            $cumulativeEnterprises += $dataEnterprises[$date];
            $chartDataUsers[] = $cumulativeUsers;
            $chartDataEnterprises[] = $cumulativeEnterprises;
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);

        $chart->setData([
            'labels' => $dates,
            'datasets' => [
                [
                    'label' => 'Utilisateurs',
                    'backgroundColor' => 'rgb(255, 99, 132)',
                    'borderColor' => 'rgb(255, 99, 132)',
                    'data' => $chartDataUsers,
                ],
                [
                    'label' => 'Entreprises',
                    'backgroundColor' => 'rgb(54, 162, 235)',
                    'borderColor' => 'rgb(54, 162, 235)',
                    'data' => $chartDataEnterprises,
                ],
            ],
        ]);

        $chart->setOptions([
            'maintainAspectRatio' => false,
            'scales' => [
                'y' => [
                    'suggestedMin' => 0,
                    'suggestedMax' => 10,
                ],
                'x' => [
                    'display' => true,
                ],
            ],
        ]);

        return $this->render('admin/dashboard/index.html.twig', [
            'chart' => $chart,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Admin dashboard');
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->renderContentMaximized();
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addAssetMapperEntry('admin')
            ->addAssetMapperEntry('app');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Gestion des utilisateurs');
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
        yield MenuItem::linkToCrud('Entreprises', 'fas fa-building', Company::class);
        yield MenuItem::section('Gestion des Quiz');
        yield MenuItem::linkToCrud('Quiz', 'fas fa-list', QuizTemplate::class);
        yield MenuItem::linkToCrud('Sessions', 'fas fa-calendar-check', QuizSession::class);
        yield MenuItem::linkToCrud('Questions', 'fas fa-question', Question::class);

        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}
