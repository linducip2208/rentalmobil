<?php

namespace App\Services;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportExcelService
{
    public function download(string $filename, array $headers, iterable $rows): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'rentalmobil-report-').'.xlsx';
        $writer = new Writer;
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues($headers));
        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues(array_values($row)));
        }
        $writer->close();

        return response()->download($path, $filename.'.xlsx')->deleteFileAfterSend(true);
    }
}
