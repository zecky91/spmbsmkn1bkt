<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\Ruangan;
use App\Models\Jurusan;
use Inertia\Inertia;

class SiswaController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Siswa', [
            'siswa' => Siswa::with(['ruangan', 'jurusan1', 'jurusan2', 'creator'])->get(),
            'ruangan' => Ruangan::where('aktif', true)->get(),
            'jurusan' => Jurusan::where('aktif', true)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nisn'          => 'required|string|size:10|unique:siswa,nisn',
            'nik'           => 'required|string|size:16',
            'nama'          => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'asal_sekolah'  => 'required|string|max:255',
            'ruangan_id'    => 'nullable|exists:ruangan,id',
            'jurusan1_id'   => 'required|exists:jurusan,id',
            'jurusan2_id'   => 'nullable|exists:jurusan,id|different:jurusan1_id',
            'afirmasi'      => 'nullable|boolean',
            'prestasi'      => 'nullable|boolean',
        ]);

        $data['status'] = 'belum_login';
        $data['created_by'] = $request->session()->get('admin_id');

        Siswa::create($data);

        return back()->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function update(Request $request, Siswa $siswa)
    {
        $data = $request->validate([
            'nisn'          => 'required|string|size:10|unique:siswa,nisn,' . $siswa->id,
            'nik'           => 'required|string|size:16',
            'nama'          => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'asal_sekolah'  => 'required|string|max:255',
            'ruangan_id'    => 'nullable|exists:ruangan,id',
            'jurusan1_id'   => 'required|exists:jurusan,id',
            'jurusan2_id'   => 'nullable|exists:jurusan,id|different:jurusan1_id',
            'afirmasi'      => 'nullable|boolean',
            'prestasi'      => 'nullable|boolean',
            'status'        => 'nullable|string|in:belum_login,login,proses,selesai,macet',
        ]);

        $siswa->update($data);

        return back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return back()->with('success', 'Data siswa berhasil dihapus.');
    }
}
