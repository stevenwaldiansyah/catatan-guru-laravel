<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Support\Search;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $subjects = Subject::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $like = Search::like($request->string('q')->toString());
                $query->where('name', 'like', $like)->orWhere('code', 'like', $like);
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        return view('subjects.form', ['subject' => new Subject(['is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Subject::query()->create($this->validated($request));

        return redirect()->route('subjects.index')->with('success', 'Mata pelajaran ditambahkan.');
    }

    public function edit(Subject $subject): View
    {
        return view('subjects.form', compact('subject'));
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $subject->update($this->validated($request, $subject));

        return redirect()->route('subjects.index')->with('success', 'Mata pelajaran diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        if ($subject->assignments()->exists()) {
            return back()->with('error', 'Mata pelajaran masih dipakai pada penugasan mengajar.');
        }

        $subject->delete();

        return redirect()->route('subjects.index')->with('success', 'Mata pelajaran dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Subject $subject = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects', 'code')->ignore($subject?->id)],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
