<?php

namespace App\Controller\Admin;

use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/admin/question')]
class QuestionStatsAjaxController extends AbstractController
{
    #[Route('/{id}/stats', name: 'admin_question_stats_ajax')]
    public function stats(int $id, QuestionRepository $questionRepository, ChartBuilderInterface $chartBuilder): Response
    {
        $distribution = $questionRepository->getAnswerDistribution($id);

        $labels = [];
        $data = [];
        $backgroundColors = [];
        $borderColors = [];

        $question = $questionRepository->find($id);

        // Handle case where no answers exist
        if (empty($distribution)) {
            // Return a friendly message or empty state in the modal
             return $this->render('admin/question/_stats_modal.html.twig', [
                'chart' => null,
                'noData' => true,
                'question' => $question
            ]);
        }

        $totalResponses = array_sum(array_column($distribution, 'count'));

        foreach ($distribution as $item) {
            $text = $item['text'] ?? 'Réponse sans texte';
            // Wrap text for multiline labels in Chart.js (approx 50 chars per line)
            $wrappedText = wordwrap($text, 50, "\n", true);
            $labels[] = explode("\n", $wrappedText);
            
            // Calculate percentage
            $percentage = $totalResponses > 0 ? round(($item['count'] / $totalResponses) * 100, 1) : 0;
            $data[] = $percentage;
            
            // Color logic: Green for correct, Red/Grey for others
            if ($item['is_correct']) {
                $backgroundColors[] = 'rgba(75, 192, 192, 0.5)'; // Green
                $borderColors[] = 'rgba(75, 192, 192, 1)';
            } else {
                $backgroundColors[] = 'rgba(255, 99, 132, 0.5)'; // Red
                $borderColors[] = 'rgba(255, 99, 132, 1)';
            }
        }

        $chart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Pourcentage de choix (%)',
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 1,
                    'data' => $data,
                ],
            ],
        ]);

        $chart->setOptions([
            'indexAxis' => 'y', // Horizontal Bar Chart
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => 'function(context) { return context.raw + "%"; }'
                    ]
                ]
            ],
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'ticks' => [
                        'stepSize' => 10,
                        'callback' => 'function(value) { return value + "%" }'
                    ]
                ],
            ],
        ]);

        return $this->render('admin/question/_stats_modal.html.twig', [
            'question' => $question,
            'chart' => $chart,
        ]);
    }
}
