<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\JournalStatus;
use App\Models\AttendanceSession;
use App\Models\ClassEnrollment;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentNote;
use App\Models\TeachingAssignment;
use App\Models\TeachingJournal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsSchool;
use Tests\TestCase;

class RecordOwnershipTest extends TestCase
{
    use BuildsSchool, RefreshDatabase;

    public function test_journal_rejects_a_schedule_from_another_assignment_and_accepts_a_missing_schedule(): void
    {
        $school = $this->buildSchool();
        $other = $this->secondClass($school);

        $ownSchedule = $this->scheduleFor($school['assignment']);
        $otherAssignmentSchedule = $this->scheduleFor($other['assignment']);
        $otherTeacherSchedule = $this->scheduleFor($this->assignmentFor($school, $school['otherTeacher']));

        $this->actingAs($school['teacherUser'])
            ->post(route('journals.store'), $this->journalPayload($school['assignment']->id, [
                'schedule_id' => $otherAssignmentSchedule->id,
                'material' => 'Jadwal kelas lain',
            ]))
            ->assertStatus(422);

        $this->actingAs($school['teacherUser'])
            ->post(route('journals.store'), $this->journalPayload($school['assignment']->id, [
                'schedule_id' => $otherTeacherSchedule->id,
                'material' => 'Jadwal guru lain',
            ]))
            ->assertStatus(422);

        $this->assertDatabaseCount('teaching_journals', 0);

        $this->actingAs($school['teacherUser'])
            ->post(route('journals.store'), $this->journalPayload($school['assignment']->id, [
                'material' => 'Tanpa jadwal',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('teaching_journals', [
            'material' => 'Tanpa jadwal',
            'schedule_id' => null,
            'teacher_id' => $school['teacher']->id,
        ]);

        $this->actingAs($school['teacherUser'])
            ->post(route('journals.store'), $this->journalPayload($school['assignment']->id, [
                'schedule_id' => $ownSchedule->id,
                'material' => 'Jadwal sendiri',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('teaching_journals', [
            'material' => 'Jadwal sendiri',
            'schedule_id' => $ownSchedule->id,
        ]);
    }

    public function test_attendance_rejects_a_journal_from_another_assignment_and_accepts_a_missing_journal(): void
    {
        $school = $this->buildSchool();
        $other = $this->secondClass($school);

        $foreignJournal = $this->journalFor($other['assignment'], 'Jurnal kelas lain');
        $otherTeacherJournal = $this->journalFor($this->assignmentFor($school, $school['otherTeacher']), 'Jurnal guru lain');
        $ownJournal = $this->journalFor($school['assignment'], 'Jurnal sendiri', '2026-10-30');

        $this->actingAs($school['teacherUser'])
            ->post(route('attendance.store'), $this->attendancePayload($school, [
                'journal_id' => $foreignJournal->id,
            ]))
            ->assertStatus(422);

        $this->actingAs($school['teacherUser'])
            ->post(route('attendance.store'), $this->attendancePayload($school, [
                'journal_id' => $otherTeacherJournal->id,
            ]))
            ->assertStatus(422);

        $this->assertDatabaseCount('attendance_sessions', 0);

        $this->actingAs($school['teacherUser'])
            ->post(route('attendance.store'), $this->attendancePayload($school, [
                'session_label' => 'Tanpa jurnal',
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('attendance_sessions', [
            'session_label' => 'Tanpa jurnal',
            'journal_id' => null,
            'teacher_id' => $school['teacher']->id,
        ]);

        $this->actingAs($school['teacherUser'])
            ->post(route('attendance.store'), $this->attendancePayload($school, [
                'session_label' => 'Dengan jurnal',
                'date' => '2026-10-30',
                'journal_id' => $ownJournal->id,
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('attendance_sessions', [
            'session_label' => 'Dengan jurnal',
            'journal_id' => $ownJournal->id,
        ]);
    }

    public function test_attendance_rejects_another_teachers_journal_and_a_journal_on_the_wrong_day_or_semester(): void
    {
        $school = $this->buildSchool();
        $otherTeacherJournal = $this->journalFor($this->assignmentFor($school, $school['otherTeacher']), 'Jurnal guru lain');
        $wrongDay = $this->journalFor($school['assignment'], 'Jurnal hari lain', '2026-10-28');

        $laterSemester = Semester::query()->create([
            'academic_year_id' => $school['year']->id,
            'name' => 'Genap',
            'number' => 2,
            'starts_on' => '2027-01-01',
            'ends_on' => '2027-06-30',
            'is_active' => false,
        ]);
        $wrongSemester = TeachingJournal::query()->create([
            'teacher_id' => $school['teacher']->id,
            'class_id' => $school['class']->id,
            'subject_id' => $school['subject']->id,
            'academic_year_id' => $school['year']->id,
            'semester_id' => $laterSemester->id,
            'date' => '2026-10-29',
            'session_label' => 'Jam 1',
            'material' => 'Jurnal semester lain',
            'status' => JournalStatus::Draft,
        ]);

        $this->actingAs($school['teacherUser'])
            ->post(route('attendance.store'), $this->attendancePayload($school, [
                'journal_id' => $otherTeacherJournal->id,
            ]))
            ->assertStatus(422);

        $this->actingAs($school['teacherUser'])
            ->post(route('attendance.store'), $this->attendancePayload($school, [
                'date' => '2026-10-29',
                'journal_id' => $wrongDay->id,
            ]))
            ->assertStatus(422);

        $this->actingAs($school['teacherUser'])
            ->post(route('attendance.store'), $this->attendancePayload($school, [
                'journal_id' => $wrongSemester->id,
            ]))
            ->assertStatus(422);

        $this->assertDatabaseCount('attendance_sessions', 0);
    }

    public function test_changing_attendance_class_drops_records_from_the_previous_class(): void
    {
        $school = $this->buildSchool();
        $other = $this->secondClass($school);

        $this->actingAs($school['teacherUser'])
            ->post(route('attendance.store'), $this->attendancePayload($school, [
                'status' => [$school['student']->id => 'hadir'],
            ]))
            ->assertRedirect();

        $session = AttendanceSession::query()->firstOrFail();
        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $school['student']->id,
        ]);

        $this->actingAs($school['teacherUser'])
            ->put(route('attendance.update', $session), [
                'assignment_id' => $other['assignment']->id,
                'date' => '2026-10-29',
                'session_label' => 'Jam 1',
                'status' => [$other['student']->id => 'hadir'],
            ])
            ->assertRedirect();

        $session->refresh();
        $this->assertSame($other['class']->id, $session->class_id);
        $this->assertDatabaseMissing('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $school['student']->id,
        ]);
        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $other['student']->id,
            'status' => 'hadir',
        ]);
        $this->assertDatabaseCount('attendance_records', 1);

        $this->actingAs($school['teacherUser'])
            ->get(route('attendance.show', $session))
            ->assertOk()
            ->assertSee('Hadir: 1', false);

        $this->actingAs($school['teacherUser'])
            ->get(route('attendance.recap', ['class_id' => $other['class']->id]))
            ->assertOk()
            ->assertSee($other['student']->name)
            ->assertDontSee($school['student']->name);
    }

    public function test_editing_a_past_session_keeps_a_deactivated_student(): void
    {
        $school = $this->buildSchool();

        $this->actingAs($school['teacherUser'])
            ->post(route('attendance.store'), $this->attendancePayload($school))
            ->assertRedirect();

        $session = AttendanceSession::query()->firstOrFail();
        $school['student']->update(['status' => 'nonaktif']);
        $school['student']->enrollments()->update([
            'status' => EnrollmentStatus::Moved,
            'ended_on' => '2026-10-30',
        ]);

        $this->actingAs($school['teacherUser'])
            ->put(route('attendance.update', $session), [
                'assignment_id' => $school['assignment']->id,
                'date' => '2026-10-29',
                'session_label' => 'Jam 1',
                'status' => [$school['student']->id => 'sakit'],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $session->id,
            'student_id' => $school['student']->id,
            'status' => 'sakit',
        ]);
        $this->assertDatabaseCount('attendance_records', 1);
    }

    public function test_student_search_treats_like_wildcards_as_literals(): void
    {
        $school = $this->buildSchool();
        Student::query()->create([
            'nis' => '100%_9',
            'name' => '100%_Andi',
            'gender' => 'L',
            'status' => 'aktif',
        ]);

        $this->actingAs($school['admin'])
            ->get(route('students.index', ['q' => '100%_']))
            ->assertOk()
            ->assertSee('100%_Andi')
            ->assertDontSee($school['student']->name);

        $this->actingAs($school['admin'])
            ->get(route('students.index', ['q' => '100X']))
            ->assertOk()
            ->assertDontSee('100%_Andi');

        Student::query()->create([
            'nis' => '9001',
            'name' => 'A\\Budi',
            'gender' => 'L',
            'status' => 'aktif',
        ]);

        $this->actingAs($school['admin'])
            ->get(route('students.index', ['q' => 'A\\B']))
            ->assertOk()
            ->assertSee('A\\Budi', false)
            ->assertDontSee('100%_Andi');
    }

    public function test_journal_schedule_must_fall_on_the_same_weekday(): void
    {
        $school = $this->buildSchool();
        $monday = $this->scheduleFor($school['assignment'], 1);

        $this->actingAs($school['teacherUser'])
            ->post(route('journals.store'), $this->journalPayload($school['assignment']->id, [
                'date' => '2026-10-29',
                'schedule_id' => $monday->id,
                'material' => 'Hari tidak cocok',
            ]))
            ->assertStatus(422);

        $this->assertDatabaseCount('teaching_journals', 0);
    }

    public function test_note_update_keeps_identity_after_transfer_and_rejects_non_owners(): void
    {
        $school = $this->buildSchool();
        $other = $this->secondClass($school);

        $note = StudentNote::query()->create([
            'teacher_id' => $school['teacher']->id,
            'student_id' => $school['student']->id,
            'class_id' => $school['class']->id,
            'date' => '2026-10-29',
            'category' => 'akademik',
            'description' => 'Catatan awal',
        ]);

        $school['student']->enrollments()->update([
            'status' => EnrollmentStatus::Moved,
            'ended_on' => '2026-10-30',
        ]);
        ClassEnrollment::query()->create([
            'student_id' => $school['student']->id,
            'class_id' => $other['class']->id,
            'academic_year_id' => $school['year']->id,
            'status' => EnrollmentStatus::Active,
            'started_on' => '2026-10-30',
        ]);

        $this->actingAs($school['otherUser'])
            ->put(route('notes.update', $note), [
                'date' => '2026-10-30',
                'category' => 'sikap',
                'description' => 'Diedit guru lain',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('student_notes', [
            'id' => $note->id,
            'description' => 'Catatan awal',
        ]);

        $this->actingAs($school['teacherUser'])
            ->put(route('notes.update', $note), [
                'student_id' => $other['student']->id,
                'class_id' => $other['class']->id,
                'date' => '2026-10-30',
                'category' => 'sikap',
                'description' => 'Identitas diganti',
            ])
            ->assertSessionHasErrors(['student_id', 'class_id']);

        $this->actingAs($school['teacherUser'])
            ->get(route('notes.edit', $note))
            ->assertOk()
            ->assertSee($school['student']->name)
            ->assertDontSee('name="student_id"', false);

        $this->actingAs($school['teacherUser'])
            ->put(route('notes.update', $note), [
                'date' => '2026-10-30',
                'category' => 'sikap',
                'description' => 'Deskripsi baru',
            ])
            ->assertRedirect();

        $note->refresh();
        $this->assertSame($school['student']->id, $note->student_id);
        $this->assertSame($school['class']->id, $note->class_id);
        $this->assertSame('Deskripsi baru', $note->description);
        $this->assertSame('sikap', $note->category);
    }

    /**
     * @param  array<string, mixed>  $school
     * @return array{class: SchoolClass, student: Student, assignment: TeachingAssignment}
     */
    private function secondClass(array $school): array
    {
        $class = SchoolClass::query()->create([
            'academic_year_id' => $school['year']->id,
            'name' => 'X RPL 2',
            'level' => 'X',
        ]);

        $student = Student::query()->create([
            'nis' => (string) random_int(100000, 999999),
            'name' => 'Siswa Dua',
            'gender' => 'P',
            'status' => 'aktif',
        ]);

        ClassEnrollment::query()->create([
            'student_id' => $student->id,
            'class_id' => $class->id,
            'academic_year_id' => $school['year']->id,
            'status' => EnrollmentStatus::Active,
            'started_on' => '2026-07-01',
        ]);

        $assignment = TeachingAssignment::query()->create([
            'teacher_id' => $school['teacher']->id,
            'class_id' => $class->id,
            'subject_id' => $school['subject']->id,
            'academic_year_id' => $school['year']->id,
            'semester_id' => $school['semester']->id,
        ]);

        return compact('class', 'student', 'assignment');
    }

    /**
     * @param  array<string, mixed>  $school
     */
    private function assignmentFor(array $school, mixed $teacher): TeachingAssignment
    {
        return TeachingAssignment::query()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $school['class']->id,
            'subject_id' => $school['subject']->id,
            'academic_year_id' => $school['year']->id,
            'semester_id' => $school['semester']->id,
        ]);
    }

    private function scheduleFor(TeachingAssignment $assignment, int $dayOfWeek = 4): Schedule
    {
        return Schedule::query()->create([
            'teaching_assignment_id' => $assignment->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => '07:00:00',
            'end_time' => '08:30:00',
            'room' => 'R-'.$assignment->id,
        ]);
    }

    private function journalFor(TeachingAssignment $assignment, string $material, string $date = '2026-10-29'): TeachingJournal
    {
        return TeachingJournal::query()->create([
            'teacher_id' => $assignment->teacher_id,
            'class_id' => $assignment->class_id,
            'subject_id' => $assignment->subject_id,
            'academic_year_id' => $assignment->academic_year_id,
            'semester_id' => $assignment->semester_id,
            'date' => $date,
            'session_label' => 'Jam 1',
            'material' => $material,
            'status' => JournalStatus::Draft,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function journalPayload(int $assignmentId, array $overrides = []): array
    {
        return array_merge([
            'assignment_id' => $assignmentId,
            'date' => '2026-10-29',
            'session_label' => 'Jam 1',
            'material' => 'Aljabar',
            'status' => 'draft',
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $school
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function attendancePayload(array $school, array $overrides = []): array
    {
        return array_merge([
            'assignment_id' => $school['assignment']->id,
            'date' => '2026-10-29',
            'session_label' => 'Jam 1',
            'status' => [$school['student']->id => 'hadir'],
        ], $overrides);
    }
}
