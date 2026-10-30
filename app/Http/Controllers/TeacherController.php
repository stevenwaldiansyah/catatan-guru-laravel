<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Teacher;
use App\Models\User;
use App\Support\Search;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $teachers = Teacher::query()
            ->with('user')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q')->toString();
                $query->where(function ($query) use ($term) {
                    Search::where($query, 'name', $term);
                    Search::where($query, 'nip', $term, 'or');
                    $query->orWhereHas('user', function ($user) use ($term) {
                        Search::where($user, 'email', $term);
                        Search::where($user, 'username', $term, 'or');
                    });
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('teachers.index', compact('teachers'));
    }

    public function create(): View
    {
        return view('teachers.form', ['teacher' => new Teacher]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateTeacher($request);

        DB::transaction(function () use ($data, $request) {
            $user = User::query()->create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => UserRole::Teacher,
                'is_active' => $request->boolean('is_active'),
            ]);

            Teacher::query()->create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'nip' => $data['nip'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        });

        return redirect()->route('teachers.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function edit(Teacher $teacher): View
    {
        $teacher->load('user');

        return view('teachers.form', compact('teacher'));
    }

    public function update(Request $request, Teacher $teacher): RedirectResponse
    {
        $data = $this->validateTeacher($request, $teacher);

        if ($teacher->user_id === $request->user()->id && ! $request->boolean('is_active')) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun yang sedang dipakai.');
        }

        DB::transaction(function () use ($data, $request, $teacher) {
            $teacher->user->update([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'is_active' => $request->boolean('is_active'),
                ...($data['password'] ? ['password' => $data['password']] : []),
            ]);

            $teacher->update([
                'name' => $data['name'],
                'nip' => $data['nip'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
        });

        return redirect()->route('teachers.index')->with('success', 'Data guru diperbarui.');
    }

    public function destroy(Request $request, Teacher $teacher): RedirectResponse
    {
        if ($teacher->user_id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun yang sedang dipakai.');
        }

        DB::transaction(function () use ($teacher) {
            $teacher->user?->update(['is_active' => false]);
            $teacher->delete();
        });

        return redirect()->route('teachers.index')->with('success', 'Akun guru dinonaktifkan dan diarsipkan.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateTeacher(Request $request, ?Teacher $teacher = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($teacher?->user_id)],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($teacher?->user_id)],
            'password' => [$teacher ? 'nullable' : 'required', 'string', 'min:8'],
            'nip' => ['nullable', 'string', 'max:30', Rule::unique('teachers', 'nip')->ignore($teacher?->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);
    }
}
