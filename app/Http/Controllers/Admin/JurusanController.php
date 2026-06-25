<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jurusan;
use Inertia\Inertia;

class JurusanController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Jurusan', [
            'jurusan' => Jurusan::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|string|unique:jurusan,kode|max:20',
            'nama' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'kuota' => 'required|integer|min:1',
            'aktif' => 'required|boolean',
            'teknik_penilaian' => 'required|string|in:dikotomi,likert',
        ]);

        Jurusan::create($data);

        return back()->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function update(Request $request, Jurusan $jurusan)
    {
        $data = $request->validate([
            'kode' => 'required|string|max:20|unique:jurusan,kode,' . $jurusan->id,
            'nama' => 'required|string|max:255',
            'icon' => 'nullable|string|max:10',
            'kuota' => 'required|integer|min:1',
            'aktif' => 'required|boolean',
            'teknik_penilaian' => 'required|string|in:dikotomi,likert',
        ]);

        $jurusan->update($data);

        return back()->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan)
    {
        // Prevent deletion if linked to students or questions
        if ($jurusan->soal()->exists() || $jurusan->siswaPilihan1()->exists() || $jurusan->siswaPilihan2()->exists()) {
            return back()->with('error', 'Jurusan tidak bisa dihapus karena masih terhubung dengan soal atau data siswa.');
        }

        $jurusan->delete();
        return back()->with('success', 'Jurusan berhasil dihapus.');
    }
}
