<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengawas;
use App\Models\Ruangan;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class PengawasController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Pengawas', [
            'pengawas' => Pengawas::with('ruangan')->get(),
            'ruangan' => Ruangan::where('aktif', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|unique:pengawas,username|max:255',
            'password' => 'required|string|min:4',
            'ruangan_id' => 'required|exists:ruangan,id',
        ]);

        $data['password'] = Hash::make($data['password']);

        Pengawas::create($data);

        return back()->with('success', 'Pengawas berhasil ditambahkan.');
    }

    public function update(Request $request, Pengawas $pengawas)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:pengawas,username,' . $pengawas->id,
            'password' => 'nullable|string|min:4',
            'ruangan_id' => 'required|exists:ruangan,id',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $pengawas->update($data);

        return back()->with('success', 'Pengawas berhasil diperbarui.');
    }

    public function destroy(Pengawas $pengawas)
    {
        $pengawas->delete();
        return back()->with('success', 'Pengawas berhasil dihapus.');
    }
}
