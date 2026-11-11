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
            ['label' => 'Dasbor', 'short' => 'Dasbor', 'route' => 'dashboard', 'icon' => 'layout-dashboard', 'match' => 'dashboard', 'roles' => ['admin', 'teacher'], 'bottom' => true, 'section' => 'main'],
            ['label' => 'Catatan Mengajar', 'short' => 'Catatan', 'route' => 'journals.index', 'icon' => 'notebook-pen', 'match' => ['journals.*', 'attendance.*'], 'roles' => ['teacher'], 'bottom' => true, 'section' => 'main'],
            ['label' => 'Jadwal Pelajaran', 'short' => 'Jadwal', 'route' => 'schedules.index', 'icon' => 'calendar-days', 'match' => 'schedules.*', 'roles' => ['admin', 'teacher'], 'bottom' => true, 'section' => 'main'],
            ['label' => 'Data Kelas', 'short' => 'Kelas', 'route' => 'classes.index', 'icon' => 'school', 'match' => 'classes.*', 'roles' => ['admin'], 'bottom' => false, 'section' => 'main'],
            ['label' => 'Data Siswa', 'short' => 'Siswa', 'route' => 'students.index', 'icon' => 'graduation-cap', 'match' => 'students.*', 'roles' => ['admin', 'teacher'], 'bottom' => true, 'section' => 'main'],
            ['label' => 'Guru', 'short' => 'Guru', 'route' => 'teachers.index', 'icon' => 'users', 'match' => 'teachers.*', 'roles' => ['admin'], 'bottom' => false, 'section' => 'main'],
            ['label' => 'Mata Pelajaran', 'short' => 'Mapel', 'route' => 'subjects.index', 'icon' => 'book-open', 'match' => 'subjects.*', 'roles' => ['admin'], 'bottom' => false, 'section' => 'main'],
            ['label' => 'Tahun Ajaran', 'short' => 'Tahun', 'route' => 'academic-years.index', 'icon' => 'calendar-range', 'match' => 'academic-years.*', 'roles' => ['admin'], 'bottom' => false, 'section' => 'main'],
            ['label' => 'Laporan', 'short' => 'Laporan', 'route' => 'reports.index', 'icon' => 'chart-column', 'match' => 'reports.*', 'roles' => ['admin', 'teacher'], 'bottom' => false, 'section' => 'main'],
            ['label' => 'Pengaturan', 'short' => 'Atur', 'route' => 'settings.edit', 'icon' => 'settings', 'match' => 'settings.*', 'roles' => ['admin'], 'bottom' => false, 'section' => 'system'],
        ];

        return array_values(array_map(
            fn (array $item) => [
                'label' => $item['label'],
                'short' => $item['short'],
                'route' => $item['route'],
                'icon' => $item['icon'],
                'match' => $item['match'],
                'bottom' => $item['bottom'],
                'section' => $item['section'],
            ],
            array_filter($items, fn (array $item) => in_array($user->role->value, $item['roles'], true))
        ));
    }
}
