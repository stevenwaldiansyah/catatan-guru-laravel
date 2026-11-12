<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Enums\JournalStatus;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsSchool;
use Tests\TestCase;

class SchoolWorkflowTest extends TestCase
{
    use BuildsSchool, RefreshDatabase;

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'username' => 'siti',
            'email' => 'siti@example.com',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'login' => 'siti',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::factory()->create([
            'username' => 'nonaktif',
            'is_active' => false,
        ]);

        $this->post('/login', [
            'login' => 'nonaktif',
            'password' => 'password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_teacher_cannot_open_admin_pages_or_another_teachers_journal(): void
    {
        $school = $this->buildSchool();

        $journal = TeachingJournal::query()->create([
            'teacher_id' => $school['teacher']->id,
            'class_id' => $school['class']->id,
            'subject_id' => $school['subject']->id,
            'academic_year_id' => $school['year']->id,
            'semester_id' => $school['semester']->id,
            'date' => '2026-10-29',
            'session_label' => 'Jam 1',
            'material' => 'Aljabar',
            'status' => JournalStatus::Draft,
        ]);

        $this->actingAs($school['teacherUser'])->get('/guru')->assertForbidden();
        $this->actingAs($school['otherUser'])->get(route('journals.show', $journal))->assertForbidden();
        $this->actingAs($school['admin'])->get('/guru')->assertOk();
    }

    public function test_duplicate_attendance_is_rejected(): void
    {
        $school = $this->buildSchool();
        $payload = [
            'assignment_id' => $school['assignment']->id,
            'date' => '2026-10-29',
            'session_label' => 'Jam 1',
            'status' => [$school['student']->id => 'hadir'],
        ];

        $this->actingAs($school['teacherUser'])->post(route('attendance.store'), $payload)->assertRedirect();
        $this->actingAs($school['teacherUser'])->post(route('attendance.store'), $payload)->assertSessionHas('error');
        $this->assertDatabaseCount('attendance_sessions', 1);
    }

    public function test_gradebook_routes_are_removed(): void
    {
        $school = $this->buildSchool();

        $this->actingAs($school['teacherUser'])->get('/nilai')->assertNotFound();
        $this->actingAs($school['teacherUser'])->get('/laporan/nilai')->assertNotFound();
        $this->actingAs($school['admin'])->get('/laporan')->assertOk()->assertDontSee('Catatan siswa');
        $this->actingAs($school['admin'])->get('/laporan/catatan')->assertNotFound();
    }

    public function test_admin_can_view_and_delete_a_student(): void
    {
        $school = $this->buildSchool();
        $student = $school['student'];

        $this->actingAs($school['admin'])
            ->get(route('students.index'))
            ->assertOk()
            ->assertSee('Lihat')
            ->assertSee('Hapus')
            ->assertSee($student->name);

        $this->actingAs($school['admin'])
            ->get(route('students.show', $student))
            ->assertOk()
            ->assertSee('Data siswa')
            ->assertSee($student->nis);

        $this->actingAs($school['teacherUser'])
            ->delete(route('students.destroy', $student))
            ->assertForbidden();

        $this->actingAs($school['admin'])
            ->delete(route('students.destroy', $student))
            ->assertRedirect(route('students.index'));

        $this->assertSoftDeleted($student);
        $this->assertNull(Student::query()->find($student->id));
    }

    public function test_today_schedule_matches_attendance_for_the_same_class(): void
    {
        $school = $this->buildSchool();

        Schedule::query()->create([
            'teaching_assignment_id' => $school['assignment']->id,
            'day_of_week' => now()->dayOfWeekIso,
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'room' => 'R-1',
            'label' => 'Jam 1',
        ]);

        AttendanceSession::query()->create([
            'teacher_id' => $school['teacher']->id,
            'class_id' => $school['class']->id,
            'subject_id' => $school['subject']->id,
            'academic_year_id' => $school['year']->id,
            'semester_id' => $school['semester']->id,
            'date' => today()->toDateString(),
            'session_label' => 'Sesi 1',
        ]);

        $this->actingAs($school['teacherUser'])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('X RPL 1')
            ->assertSee('Sudah mengajar')
            ->assertDontSee('Belum diisi');
    }

    public function test_journal_menu_combines_attendance_and_hides_notes(): void
    {
        $school = $this->buildSchool();

        $this->actingAs($school['admin'])
            ->get(route('journals.index'))
            ->assertOk()
            ->assertSee('Jurnal')
            ->assertSee('Absensi')
            ->assertDontSee('Isi absensi')
            ->assertDontSee('Catatan siswa');

        $this->actingAs($school['teacherUser'])
            ->get(route('journals.index'))
            ->assertOk()
            ->assertDontSee('>Catatan<', false);

        $this->actingAs($school['admin'])
            ->get(route('attendance.index'))
            ->assertRedirect(route('journals.index').'#absensi');
    }

    public function test_adding_a_journal_records_attendance_for_the_selected_class(): void
    {
        $school = $this->buildSchool();

        $this->actingAs($school['teacherUser'])
            ->get(route('journals.create'))
            ->assertOk()
            ->assertSee('Absensi')
            ->assertDontSee('Isi absensi');

        $this->actingAs($school['teacherUser'])
            ->get(route('journals.students', ['assignment_id' => $school['assignment']->id]))
            ->assertOk()
            ->assertJsonPath('class_name', 'X RPL 1')
            ->assertJsonPath('students.0.name', 'Siswa Nol');

        $this->actingAs($school['teacherUser'])
            ->post(route('journals.store'), [
                'assignment_id' => $school['assignment']->id,
                'date' => '2026-11-11',
                'session_label' => 'Sesi 1',
                'material' => 'Aljabar',
                'status' => 'draft',
                'attendance' => [$school['student']->id => 'hadir'],
            ])
            ->assertRedirect();

        $journal = TeachingJournal::query()->first();
        $session = AttendanceSession::query()->first();
        $this->assertNotNull($journal);
        $this->assertNotNull($session);
        $this->assertSame($journal->id, $session->journal_id);
        $this->assertSame($school['class']->id, $session->class_id);
        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $school['student']->id,
            'status' => AttendanceStatus::Present->value,
        ]);
    }

    public function test_journal_report_matches_the_journal_form_fields(): void
    {
        $school = $this->buildSchool();

        TeachingJournal::query()->create([
            'teacher_id' => $school['teacher']->id,
            'class_id' => $school['class']->id,
            'subject_id' => $school['subject']->id,
            'academic_year_id' => $school['year']->id,
            'semester_id' => $school['semester']->id,
            'date' => '2026-10-29',
            'session_label' => 'Sesi 1',
            'material' => 'Aljabar',
            'activities' => 'Diskusi',
            'method' => 'Ceramah',
            'notes' => 'Kelas tenang',
            'status' => JournalStatus::Completed,
        ]);

        $this->actingAs($school['teacherUser'])
            ->get(route('journals.create'))
            ->assertOk()
            ->assertDontSee('Kegiatan pembelajaran')
            ->assertDontSee('Metode');

        $page = $this->actingAs($school['admin'])->get(route('reports.show', 'jurnal'));
        $page->assertOk()
            ->assertSee('Tanggal / Jam')
            ->assertSee('Mata Pelajaran')
            ->assertSee('Materi & Kegiatan Pembelajaran')
            ->assertSee('Kendala & Refleksi')
            ->assertSee('Aljabar')
            ->assertSee('Kelas tenang')
            ->assertDontSee('Metode')
            ->assertDontSee('Status');

        $csv = $this->actingAs($school['admin'])->get(route('reports.csv', 'jurnal'))->assertOk()->streamedContent();
        $this->assertStringContainsString('Kendala & Refleksi', $csv);
        $this->assertStringContainsString('Aljabar', $csv);
        $this->assertStringNotContainsString('Metode', $csv);
        $this->assertStringNotContainsString('Status', $csv);

        $report = app(\App\Services\ReportBuilder::class)->build(
            $school['admin'],
            'jurnal',
            \Illuminate\Http\Request::create('/laporan/jurnal')
        );
        $html = view('reports.pdf', [
            'report' => $report,
            'schoolName' => 'SMK CONTOH',
            'placeDate' => 'Boyolali, 11 November 2026',
        ])->render();
        $this->assertStringContainsString('LAPORAN JURNAL KEGIATAN PEMBELAJARAN GURU', $html);
        $this->assertStringContainsString('Mengetahui', $html);
        $this->assertStringContainsString('Kepala Sekolah', $html);
        $this->assertStringContainsString('Guru Mata Pelajaran', $html);
        $this->assertStringContainsString('X RPL 1', $html);
    }

    public function test_attendance_report_includes_presence_percentage_in_page_and_csv(): void
    {
        $school = $this->buildSchool();

        $session = AttendanceSession::query()->create([
            'teacher_id' => $school['teacher']->id,
            'class_id' => $school['class']->id,
            'subject_id' => $school['subject']->id,
            'academic_year_id' => $school['year']->id,
            'semester_id' => $school['semester']->id,
            'date' => '2026-10-29',
            'session_label' => 'Sesi laporan',
        ]);

        AttendanceRecord::query()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $school['student']->id,
            'status' => AttendanceStatus::Present,
        ]);

        $this->actingAs($school['admin'])
            ->get(route('reports.show', 'absensi'))
            ->assertOk()
            ->assertSee('Kehadiran')
            ->assertSee('Kelas')
            ->assertSee('X RPL 1')
            ->assertSee('100%');

        $csv = $this->actingAs($school['admin'])->get(route('reports.csv', 'absensi'));
        $csv->assertOk();
        $content = $csv->streamedContent();
        $this->assertStringContainsString('Kelas', $content);
        $this->assertStringContainsString('X RPL 1', $content);
        $this->assertStringContainsString('Kehadiran', $content);
        $this->assertStringContainsString('100%', $content);

        $report = app(\App\Services\ReportBuilder::class)->build(
            $school['admin'],
            'absensi',
            \Illuminate\Http\Request::create('/laporan/absensi')
        );
        $html = view('reports.pdf', [
            'report' => $report,
            'schoolName' => 'SMK CONTOH',
            'placeDate' => 'Boyolali, 11 November 2026',
        ])->render();
        $this->assertStringContainsString('LAPORAN REKAP ABSENSI SISWA', $html);
        $this->assertStringContainsString('Total Siswa', $html);
        $this->assertStringContainsString('Mengetahui', $html);
        $this->assertStringContainsString('100%', $html);
    }

    public function test_attendance_show_keeps_a_soft_deleted_student_name(): void
    {
        $school = $this->buildSchool();
        $student = $school['student'];

        $session = AttendanceSession::query()->create([
            'teacher_id' => $school['teacher']->id,
            'class_id' => $school['class']->id,
            'subject_id' => $school['subject']->id,
            'academic_year_id' => $school['year']->id,
            'semester_id' => $school['semester']->id,
            'date' => '2026-10-29',
            'session_label' => 'Sesi 1',
        ]);

        AttendanceRecord::query()->create([
            'attendance_session_id' => $session->id,
            'student_id' => $student->id,
            'status' => AttendanceStatus::Present,
        ]);

        $student->delete();

        $this->actingAs($school['admin'])
            ->get(route('attendance.show', $session))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('Siswa dihapus');
    }

    public function test_admin_can_review_and_export_a_teachers_records(): void
    {
        $school = $this->buildSchool();

        TeachingJournal::query()->create([
            'teacher_id' => $school['teacher']->id,
            'class_id' => $school['class']->id,
            'subject_id' => $school['subject']->id,
            'academic_year_id' => $school['year']->id,
            'semester_id' => $school['semester']->id,
            'date' => '2026-11-11',
            'session_label' => 'Sesi 1',
            'material' => 'Persamaan linear',
            'status' => JournalStatus::Completed,
        ]);

        $this->actingAs($school['teacherUser'])
            ->get(route('reports.index'))
            ->assertOk()
            ->assertDontSee('Catatan dan Absensi');

        $this->actingAs($school['teacherUser'])
            ->get(route('reports.teacher', $school['teacher']))
            ->assertForbidden();

        $this->actingAs($school['admin'])
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Laporan')
            ->assertSee('Catatan dan Absensi')
            ->assertSee($school['teacher']->name)
            ->assertDontSee('>Riwayat<', false);

        $this->actingAs($school['admin'])
            ->get(route('reports.teacher', $school['teacher']))
            ->assertOk()
            ->assertSee('Matematika')
            ->assertSee('Sesi 1')
            ->assertSee('PDF')
            ->assertSee('CSV');

        $this->actingAs($school['admin'])
            ->get(route('reports.teacher.journals.csv', $school['teacher']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
