<?php

namespace App\Http\Controllers;

use App\Models\SchoolSetting;
use App\Services\ReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private ReportBuilder $reports) {}

    public function index(Request $request): View
    {
        return view('reports.index');
    }

    public function show(Request $request, string $type): View
    {
        $type = $this->type($type);

        return view('reports.show', [
            'type' => $type,
            'report' => $this->reports->build($request->user(), $type, $request),
            'filters' => $request->query(),
            'classes' => \App\Models\SchoolClass::query()
                ->when($request->user()->isTeacher(), fn ($query) => $query->whereIn('id', $request->user()->teacher?->assignments()->pluck('class_id') ?? []))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function csv(Request $request, string $type): StreamedResponse
    {
        $report = $this->reports->build($request->user(), $this->type($type), $request);
        $filename = $type.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $report['columns']);
            foreach ($report['rows'] as $row) {
                fputcsv($handle, array_map(fn ($cell) => $this->csvSafe((string) $cell), $row));
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(Request $request, string $type)
    {
        $report = $this->reports->build($request->user(), $this->type($type), $request);
        $settings = SchoolSetting::query()->first();

        return Pdf::loadView('reports.pdf', [
            'report' => $report,
            'school' => $settings,
            'printedAt' => now()->translatedFormat('d M Y H:i'),
        ])->setPaper('a4', 'landscape')->download($type.'-'.now()->format('Ymd-His').'.pdf');
    }

    private function type(string $type): string
    {
        abort_unless(in_array($type, ['jurnal', 'absensi', 'catatan'], true), 404);

        return $type;
    }

    private function csvSafe(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) === 1 ? "'".$value : $value;
    }
}
