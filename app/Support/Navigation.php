<?php

namespace App\Support;

use App\Models\User;

class Navigation
{
    /**
     * @return list<array{label:string, route:string, icon:string, match:string, bottom:bool}>
     */
    public static function items(User $user): array
    {
        $items = [
            ['label' => 'Dasbor', 'route' => 'dashboard', 'icon' => 'layout-dashboard', 'match' => 'dashboard', 'roles' => ['admin', 'teacher'], 'bottom' => true],
            ['label' => 'Guru', 'route' => 'teachers.index', 'icon' => 'users', 'match' => 'teachers.*', 'roles' => ['admin'], 'bottom' => false],
            ['label' => 'Kelas', 'route' => 'classes.index', 'icon' => 'school', 'match' => 'classes.*', 'roles' => ['admin'], 'bottom' => false],
            ['label' => 'Siswa', 'route' => 'students.index', 'icon' => 'graduation-cap', 'match' => 'students.*', 'roles' => ['admin', 'teacher'], 'bottom' => false],
            ['label' => 'Mapel', 'route' => 'subjects.index', 'icon' => 'book-open', 'match' => 'subjects.*', 'roles' => ['admin'], 'bottom' => false],
            ['label' => 'Jadwal', 'route' => 'schedules.index', 'icon' => 'calendar-days', 'match' => 'schedules.*', 'roles' => ['admin', 'teacher'], 'bottom' => false],
            ['label' => 'Tahun Ajaran', 'route' => 'academic-years.index', 'icon' => 'calendar-range', 'match' => 'academic-years.*', 'roles' => ['admin'], 'bottom' => false],
            ['label' => 'Jurnal', 'route' => 'journals.index', 'icon' => 'notebook-pen', 'match' => 'journals.*', 'roles' => ['admin', 'teacher'], 'bottom' => true],
            ['label' => 'Absensi', 'route' => 'attendance.index', 'icon' => 'clipboard-check', 'match' => 'attendance.*', 'roles' => ['admin', 'teacher'], 'bottom' => true],
            ['label' => 'Catatan', 'route' => 'notes.index', 'icon' => 'sticky-note', 'match' => 'notes.*', 'roles' => ['admin', 'teacher'], 'bottom' => false],
            ['label' => 'Laporan', 'route' => 'reports.index', 'icon' => 'chart-column', 'match' => 'reports.*', 'roles' => ['admin', 'teacher'], 'bottom' => false],
            ['label' => 'Pengaturan', 'route' => 'settings.edit', 'icon' => 'settings', 'match' => 'settings.*', 'roles' => ['admin'], 'bottom' => false],
        ];

        return array_values(array_map(
            fn (array $item) => [
                'label' => $item['label'],
                'route' => $item['route'],
                'icon' => $item['icon'],
                'match' => $item['match'],
                'bottom' => $item['bottom'],
            ],
            array_filter($items, fn (array $item) => in_array($user->role->value, $item['roles'], true))
        ));
    }
}
