<?php

namespace App\Service;

use App\DTO\TaskReportFilterDTO;
use App\Repository\TaskRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportService
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {}

    // asegura que el contenido sea seguro al mostrarse en interfaces o exportaciones
    private function safe(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    // =====================
    // FORMATTERS (solo para exportaciones)
    // =====================
    private function formatStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendiente',
            'in_progress' => 'En progreso',
            'completed' => 'Completada',
            default => $status
        };
    }

    private function formatPriority(string $priority): string
    {
        return match ($priority) {
            'low' => 'Baja',
            'medium' => 'Media',
            'high' => 'Alta',
            default => $priority
        };
    }


    public function getTaskReport(TaskReportFilterDTO $filters): array
    {
        // obtenemos las tareas aplicando los filtros de reportes
        $tasks = $this->taskRepository->findByReportFilters($filters);

        // transformamos las entidades a un formato simple (array)
        return array_map(function ($task) {
            return [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'status' => $task->getStatus()->value,
                'priority' => $task->getPriority()->value,
                'dueDate' => $task->getDueDate()?->format('Y-m-d'),
                'assignedToEmail' => $task->getAssignedTo()->getEmail(),
                'createdAt' => $task->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $task->getUpdatedAt()?->format('Y-m-d H:i:s'),
                'categories' => array_map(
                    fn($c) => $c->getName(),
                    $task->getCategories()->toArray()
                )
            ];
        }, $tasks);
    }

    public function exportTasksToCsv(TaskReportFilterDTO $filters): string
    {
        $data = $this->getTaskReport($filters);

        // abrimos un stream en memoria
        $handle = fopen('php://temp', 'r+');

        // encabezados del CSV
        fputcsv($handle, [
            'ID',
            'Título',
            'Estado',
            'Prioridad',
            'Fecha límite',
            'Usuario',
            'Creado',
            'Actualizado',
            'Categorías'
        ]);

        // filas
        foreach ($data as $row) {
            fputcsv($handle, [
                $row['id'],
                $row['title'],
                $this->formatStatus($row['status']),
                $this->formatPriority($row['priority']),
                $row['dueDate'] ?? '',
                $row['assignedToEmail'],
                $row['createdAt'] ?? '',
                $row['updatedAt'] ?? '',
                implode(' | ', $row['categories'])
            ]);
        }

        // volvemos al inicio del stream para leerlo
        rewind($handle);

        // obtenemos el contenido del CSV generado
        $csv = stream_get_contents($handle);

        // agregamos BOM para que Excel reconozca bien las tildes (UTF-8)
        return "\xEF\xBB\xBF" . $csv;
    }

    public function exportTasksToPdf(TaskReportFilterDTO $filters): string
    {
        $data = $this->getTaskReport($filters);

        $html = '
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: sans-serif; font-size: 12px; }
                table { border-collapse: collapse; width: 100%; }
                th { background: #eee; }
                td, th { border: 1px solid #000; padding: 5px; }
            </style>
        </head>
        <body>
        <h1>Reporte de tareas</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Estado</th>
                    <th>Prioridad</th>
                    <th>Usuario</th>
                    <th>Creado</th>
                </tr>
            </thead>
            <tbody>
        ';

        foreach ($data as $row) {
            $html .= '<tr>
                <td>'.$row['id'].'</td>
                <td>'.$this->safe($row['title']).'</td>
                <td>'.$this->safe($this->formatStatus($row['status'])).'</td>
                <td>'.$this->safe($this->formatPriority($row['priority'])).'</td>
                <td>'.$this->safe($row['assignedToEmail']).'</td>
                <td>'.$this->safe($row['createdAt']).'</td>
            </tr>';
        }

        $html .= '</tbody></table></body></html>';

        // configuración moderna
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return $dompdf->output();
    }
}
