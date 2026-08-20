<?php

namespace Tests\Unit;

use App\Services\ReportExcelService;
use Tests\TestCase;

class ReportExcelServiceTest extends TestCase
{
    public function test_it_generates_a_real_xlsx_download(): void
    {
        $response = app(ReportExcelService::class)->download(
            'laporan-test',
            ['Nomor', 'Jumlah'],
            [['RO-001', 1500000], ['RO-002', 750000]],
        );

        $path = $response->getFile()->getPathname();
        $this->assertFileExists($path);
        $this->assertSame('laporan-test.xlsx', $response->headers->get('content-disposition') !== null ? 'laporan-test.xlsx' : null);
        $this->assertGreaterThan(1000, filesize($path));

        @unlink($path);
    }
}
