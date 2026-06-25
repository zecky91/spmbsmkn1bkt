<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ruangan;
use Inertia\Inertia;

class RuanganController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Ruangan', [
            'ruangan' => Ruangan::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'pin' => 'required|string|size:4',
            'kapasitas' => 'required|integer|min:1',
            'aktif' => 'required|boolean',
        ]);

        Ruangan::create($data);

        return back()->with('success', 'Ruangan berhasil ditambahkan.');
    }

    public function update(Request $request, Ruangan $ruangan)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'pin' => 'required|string|size:4',
            'kapasitas' => 'required|integer|min:1',
            'aktif' => 'required|boolean',
        ]);

        $ruangan->update($data);

        return back()->with('success', 'Ruangan berhasil diperbarui.');
    }

    public function destroy(Ruangan $ruangan)
    {
        // Prevent deletion if linked to students or supervisors
        if ($ruangan->siswa()->exists() || $ruangan->pengawas()->exists()) {
            return back()->with('error', 'Ruangan tidak bisa dihapus karena masih digunakan oleh peserta atau pengawas.');
        }

        $ruangan->delete();
        return back()->with('success', 'Ruangan berhasil dihapus.');
    }
}
