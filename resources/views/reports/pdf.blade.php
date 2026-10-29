<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 18px; margin: 0 0 6px; }
        p { margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #E5E7EB; padding: 6px; text-align: left; vertical-align: top; }
        th { background: #F9FAFB; }
    </style>
</head>
<body>
    <p>{{ $school->school_name ?? 'EduJurnal' }}{{ $school?->npsn ? ' · NPSN '.$school->npsn : '' }}</p>
    <h1>{{ $report['title'] }}</h1>
    <p>Periode: {{ $report['period'] }}</p>
    <p>Dibuat: {{ $printedAt }}</p>
    <table>
        <thead>
            <tr>
                @foreach ($report['columns'] as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($report['rows'] as $row)
                <tr>
                    @foreach ($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($report['columns']) }}">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
