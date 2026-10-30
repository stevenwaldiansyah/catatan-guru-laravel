<?php

namespace Tests\Feature;

use App\Enums\JournalStatus;
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
}
