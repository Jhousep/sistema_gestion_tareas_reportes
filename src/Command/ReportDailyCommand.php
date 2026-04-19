<?php

namespace App\Command;

use App\DTO\TaskReportFilterDTO;
use App\Service\ReportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:report:daily',
    description: 'Genera el reporte diario de tareas',
)]
class ReportDailyCommand extends Command
{
    public function __construct(
        private ReportService $reportService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // creamos el DTO de filtros para el reporte
        $filters = new TaskReportFilterDTO();

        // definimos el rango del día actual (desde inicio hasta fin del día)
        $todayStart = new \DateTimeImmutable('today 00:00:00');
        $todayEnd = new \DateTimeImmutable('today 23:59:59');

        // asignamos el rango de fechas de creación
        $filters->createdFrom = $todayStart;
        $filters->createdTo = $todayEnd;

        // generamos el contenido del CSV usando el servicio
        $csv = $this->reportService->exportTasksToCsv($filters);

        // generamos el nombre del archivo con la fecha actual
        $fileName = 'report_' . $todayStart->format('Y-m-d') . '.csv';

        // definimos la ruta donde se guardará el archivo
        $path = __DIR__ . '/../../var/reports/' . $fileName;

        // si la carpeta no existe, la creamos
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        // guardamos el archivo en disco
        file_put_contents($path, $csv);

        // mostramos mensaje en consola
        $output->writeln('Reporte generado correctamente en: ' . $path);

        return Command::SUCCESS;
    }
}
