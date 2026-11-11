<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 36px 40px 42px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #000; }
        .school { text-align: center; font-size: 16px; font-weight: bold; margin: 0; letter-spacing: 0.2px; }
        .heading { text-align: center; font-size: 12px; font-weight: bold; margin: 3px 0 0; }
        .subtitle { text-align: center; font-size: 10px; font-style: italic; margin: 2px 0 8px; }
        .rule { border: 0; border-top: 2px solid #000; margin: 0 0 12px; }
        table.meta { width: 100%; border-collapse: collapse; margin: 0 0 12px; }
        table.meta td { vertical-align: top; padding: 2px 0; }
        table.meta .label { width: 17%; font-weight: bold; }
        table.meta .colon { width: 2%; }
        table.meta .value { width: 31%; padding-right: 8px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #000; padding: 5px 4px; vertical-align: top; }
        table.data th { text-align: center; font-weight: bold; }
        table.data td.center, table.data th.center { text-align: center; }
        table.signs { width: 100%; margin-top: 28px; border-collapse: collapse; page-break-inside: avoid; }
        table.signs td.half { width: 50%; vertical-align: top; text-align: center; }
        .sign-space { height: 68px; }
        table.nip { width: 78%; margin: 0 auto; border-collapse: collapse; }
        table.nip td { border-top: 1px solid #000; text-align: center; padding-top: 4px; }
    </style>
</head>
<body>
    <p class="school">{{ $schoolName }}</p>
    <p class="heading">{{ $report['heading'] }}</p>
    <p class="subtitle">Buku Catatan Guru Digital — EduJurnal</p>
    <hr class="rule">
    <table class="meta">
        @foreach (array_chunk($report['meta'], 2) as $pair)
            <tr>
                @foreach ($pair as $item)
                    <td class="label">{{ $item[0] }}</td>
                    <td class="colon">:</td>
                    <td class="value">{{ $item[1] }}</td>
                @endforeach
            </tr>
        @endforeach
    </table>
    @php
        $reportLines = function (string $cell): string {
            $lines = preg_split("/\r\n|\n/", $cell) ?: [];
            $html = [];
            foreach ($lines as $index => $line) {
                $text = $line === 'Kegiatan:' ? '<strong>'.e($line).'</strong>' : e($line);
                $html[] = ($index > 0 ? '<br>' : '').$text;
            }

            return implode('', $html);
        };
    @endphp
    <table class="data">
        <thead>
            <tr>
                @foreach ($report['columns'] as $index => $column)
                    <th class="{{ ($report['align'][$index] ?? 'left') === 'center' ? 'center' : '' }}" style="width: {{ $report['widths'][$index] ?? 'auto' }}">{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($report['rows'] as $row)
                <tr>
                    @foreach ($row as $index => $cell)
                        <td class="{{ ($report['align'][$index] ?? 'left') === 'center' ? 'center' : '' }}">{!! $reportLines($cell) !!}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="{{ count($report['columns']) }}">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
    <table class="signs">
        <tr>
            <td class="half">
                Mengetahui,<br>
                Kepala Sekolah
                <div class="sign-space"></div>
                <table class="nip"><tr><td>NIP. ........................</td></tr></table>
            </td>
            <td class="half">
                {{ $placeDate }}<br>
                Guru Mata Pelajaran,
                <div class="sign-space"></div>
                <table class="nip"><tr><td>NIP. ........................</td></tr></table>
            </td>
        </tr>
    </table>
</body>
</html>
