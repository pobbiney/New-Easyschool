<?php

namespace App\Services\Reports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExcelExporter
{
    /**
     * @param  array{title: string, url: string, columns: list<array{key: string, label: string}>, rows: list<array<string, mixed>>, totals: list<array{label: string, value: string}>}  $report
     */
    public function download(array $report): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($report['title'], 0, 31));

        $sheet->setCellValue('A1', $report['title']);
        $last = Coordinate::stringFromColumnIndex(max(1, count($report['columns'])));
        $sheet->mergeCells('A1:'.$last.'1');

        $col = 1;
        foreach ($report['columns'] as $column) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col).'3', $column['label']);
            $col++;
        }

        $rowNum = 4;
        foreach ($report['rows'] as $row) {
            $col = 1;
            foreach ($report['columns'] as $column) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col).$rowNum, $row[$column['key']] ?? '');
                $col++;
            }
            $rowNum++;
        }

        $rowNum += 1;
        foreach ($report['totals'] as $total) {
            $sheet->setCellValue('A'.$rowNum, $total['label']);
            $sheet->setCellValue('B'.$rowNum, $total['value']);
            $rowNum++;
        }

        $filename = $report['url'].'-'.now()->format('Ymd-His').'.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
