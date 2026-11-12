<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSession;
use App\Models\SchoolSetting;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Services\ReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherRecordController extends Controller
{
    public function __construct(private ReportBuilder $reports) {}

    public function show(Request $request, Teacher $teacher): View
    {
        $this->dates($request);

        return view('teacher-records.show', [
            'teacher' => $teacher,
            'journals' => $this->journals($teacher, $request)->latest('date')->latest('id')->get(),
            'sessions' => $this->sessions($teacher, $request)->latest('date')->latest('id')->get(),
        ]);
    }

    public function journalsCsv(Request $request, Teacher $teacher): StreamedResponse
    {
        return $this->csv($this->journalReport($request, $teacher), 'jurnal-'.$teacher->id);
    }

    public function journalsPdf(Request $request, Teacher $teacher)
    {
        return $this->pdf($this->journalReport($request, $teacher), 'jurnal-'.$teacher->id);
    }

    public function attendanceCsv(Request $request, Teacher $teacher): StreamedResponse
    {
        $this->dates($request);

        return $this->csv($this->reports->teacherAttendanceHistory($teacher, $request), 'absensi-'.$teacher->id);
    }

    public function attendancePdf(Request $request, Teacher $teacher)
    {
        $this->dates($request);

        return $this->pdf($this->reports->teacherAttendanceHistory($teacher, $request), 'absensi-'.$teacher->id);
    }

    /**
     * @return array<string, mixed>
     */
    private function journalReport(Request $request, Teacher $teacher): array
    {
        $this->dates($request);
        $request->merge(['teacher_id' => $teacher->id]);

        return $this->reports->build($request->user(), 'jurnal', $request);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function csv(array $report, string $name): StreamedResponse
    {
        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $report['columns']);
            foreach ($report['rows'] as $row) {
                fputcsv($handle, array_map(fn ($cell) => $this->csvSafe((string) $cell), $row));
            }
            fclose($handle);
        }, $name.'-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function pdf(array $report, string $name)
    {
        $settings = SchoolSetting::query()->first();

        return Pdf::loadView('reports.pdf', [
            'report' => $report,
            'schoolName' => $settings->school_name ?? 'EduJurnal',
            'placeDate' => $this->placeDate($settings),
        ])->setPaper('a4', 'portrait')->download($name.'-'.now()->format('Ymd-His').'.pdf');
    }

    private function journals(Teacher $teacher, Request $request): Builder
    {
        return TeachingJournal::query()
            ->where('teacher_id', $teacher->id)
            ->with(['schoolClass', 'subject'])
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('date', '<=', $request->date('date_to')));
    }

    private function sessions(Teacher $teacher, Request $request): Builder
    {
        return AttendanceSession::query()
            ->where('teacher_id', $teacher->id)
            ->with(['schoolClass', 'subject'])
            ->withCount('records')
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('date', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('date', '<=', $request->date('date_to')));
    }

    private function dates(Request $request): void
    {
        $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);
    }

    private function placeDate(?SchoolSetting $settings): string
    {
        $date = now()->locale('id')->translatedFormat('d F Y');
        $parts = array_values(array_filter(array_map(
            trim(...),
            explode(',', (string) $settings?->address)
        )));
        $city = count($parts) >= 2 ? $parts[count($parts) - 2] : ($parts[0] ?? '');

        return $city !== '' ? $city.', '.$date : $date;
    }

    private function csvSafe(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) === 1 ? "'".$value : $value;
    }
}
