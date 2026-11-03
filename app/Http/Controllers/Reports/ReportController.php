<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use App\Services\Reports\ReportCatalog;
use App\Services\Reports\ReportExcelExporter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use RuntimeException;

class ReportController extends Controller
{
    public function __construct(
        private ReportCatalog $catalog,
        private ReportExcelExporter $excel,
    ) {}

    public function show(Request $request)
    {
        $report = $this->resolve($request);

        $view = $report['key'] === 'class-attendance' ? 'reports.class-attendance' : 'reports.show';

        return view($view, [
            'report' => $report,
            'reports' => ReportCatalog::menu(),
            'school' => SchoolSetting::current(),
        ]);
    }

    public function print(Request $request)
    {
        $report = $this->resolve($request);

        return view('reports.print', [
            'report' => $report,
            'school' => SchoolSetting::current(),
            'title' => $report['title'],
        ]);
    }

    public function pdf(Request $request)
    {
        $report = $this->resolve($request);
        $school = SchoolSetting::current();

        $pdf = Pdf::loadView('reports.pdf', [
            'report' => $report,
            'school' => $school,
            'title' => $report['title'],
        ])->setPaper('a4', 'landscape');

        return $pdf->download($report['url'].'-'.now()->format('Ymd-His').'.pdf');
    }

    public function excel(Request $request)
    {
        return $this->excel->download($this->resolve($request));
    }

    /**
     * @return array<string, mixed>
     */
    private function resolve(Request $request): array
    {
        $key = $this->keyFromRequest($request);

        try {
            return $this->catalog->build($key, $request);
        } catch (RuntimeException $exception) {
            abort(404, $exception->getMessage());
        }
    }

    private function keyFromRequest(Request $request): string
    {
        $path = $request->path();
        $keys = array_keys(ReportCatalog::KEYS);
        usort($keys, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($keys as $key) {
            $url = ReportCatalog::url($key);
            if ($path === $url || in_array($path, [$url.'-print', $url.'-pdf', $url.'-excel'], true)) {
                return $key;
            }
        }

        return (string) ($request->route('key') ?: '');
    }
}
