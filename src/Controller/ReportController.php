<?php

namespace App\Controller;

use App\DTO\TaskReportFilterDTO;
use App\Service\ReportService;
use App\Enum\TaskStatus;
use App\Enum\TaskPriority;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/reports')]
class ReportController
{
    public function __construct(
        private ReportService $reportService
    ) {}

    #[Route('/tasks', name: 'report_tasks', methods: ['GET'])]
    public function taskReport(Request $request): JsonResponse
    {
        $filters = new TaskReportFilterDTO();

        // fecha de creación desde
        if ($request->query->get('createdFrom')) {
            $filters->createdFrom = (new \DateTimeImmutable($request->query->get('createdFrom')))
                ->setTime(0, 0, 0);
        }

        // fecha de creación hasta
        if ($request->query->get('createdTo')) {
            $filters->createdTo = (new \DateTimeImmutable($request->query->get('createdTo')))
                ->setTime(23, 59, 59);
        }

        // fecha de vencimiento desde
        if ($request->query->get('dueFrom')) {
            $filters->dueFrom = new \DateTimeImmutable($request->query->get('dueFrom'));
        }

        // fecha de vencimiento hasta
        if ($request->query->get('dueTo')) {
            $filters->dueTo = new \DateTimeImmutable($request->query->get('dueTo'));
        }

        // fecha de actualización desde
        if ($request->query->get('updatedFrom')) {
            $filters->updatedFrom = new \DateTimeImmutable($request->query->get('updatedFrom'));
        }

        // fecha de actualización hasta
        if ($request->query->get('updatedTo')) {
            $filters->updatedTo = new \DateTimeImmutable($request->query->get('updatedTo'));
        }

        // estado
        if ($request->query->get('status')) {
            $filters->status = TaskStatus::from($request->query->get('status'));
        }

        // prioridad
        if ($request->query->get('priority')) {
            $filters->priority = TaskPriority::from($request->query->get('priority'));
        }

        // usuario
        if ($request->query->get('userId')) {
            $filters->userId = (int) $request->query->get('userId');
        }

        $data = $this->reportService->getTaskReport($filters);

        return new JsonResponse($data);
    }

    // exportar a CSV
    #[Route('/tasks/export/csv', name: 'report_tasks_csv', methods: ['GET'])]
    public function exportCsv(Request $request): Response
    {
        $filters = new TaskReportFilterDTO();

        // fecha de creación desde
        if ($request->query->get('createdFrom')) {
            $filters->createdFrom = (new \DateTimeImmutable($request->query->get('createdFrom')))
                ->setTime(0, 0, 0);
        }

        // fecha de creación hasta
        if ($request->query->get('createdTo')) {
            $filters->createdTo = (new \DateTimeImmutable($request->query->get('createdTo')))
                ->setTime(23, 59, 59);
        }

        if ($request->query->get('dueFrom')) {
            $filters->dueFrom = new \DateTimeImmutable($request->query->get('dueFrom'));
        }

        if ($request->query->get('dueTo')) {
            $filters->dueTo = new \DateTimeImmutable($request->query->get('dueTo'));
        }

        if ($request->query->get('updatedFrom')) {
            $filters->updatedFrom = new \DateTimeImmutable($request->query->get('updatedFrom'));
        }

        if ($request->query->get('updatedTo')) {
            $filters->updatedTo = new \DateTimeImmutable($request->query->get('updatedTo'));
        }

        if ($request->query->get('status')) {
            $filters->status = TaskStatus::from($request->query->get('status'));
        }

        if ($request->query->get('priority')) {
            $filters->priority = TaskPriority::from($request->query->get('priority'));
        }

        if ($request->query->get('userId')) {
            $filters->userId = (int) $request->query->get('userId');
        }

        $csv = $this->reportService->exportTasksToCsv($filters);

        return new Response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tasks_report.csv"',
        ]);
    }

    //exportar a PDF
    #[Route('/tasks/export/pdf', name: 'report_tasks_pdf', methods: ['GET'])]
    public function exportPdf(Request $request): Response
    {
        $filters = new TaskReportFilterDTO();

        // fecha de creación desde
        if ($request->query->get('createdFrom')) {
            $filters->createdFrom = (new \DateTimeImmutable($request->query->get('createdFrom')))
                ->setTime(0, 0, 0);
        }

        // fecha de creación hasta
        if ($request->query->get('createdTo')) {
            $filters->createdTo = (new \DateTimeImmutable($request->query->get('createdTo')))
                ->setTime(23, 59, 59);
        }

        if ($request->query->get('status')) {
            $filters->status = TaskStatus::from($request->query->get('status'));
        }

        if ($request->query->get('priority')) {
            $filters->priority = TaskPriority::from($request->query->get('priority'));
        }

        if ($request->query->get('userId')) {
            $filters->userId = (int) $request->query->get('userId');
        }

        $pdf = $this->reportService->exportTasksToPdf($filters);

        return new Response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="tasks_report.pdf"',
        ]);
    }
}
