<?php

namespace App\Http\Controllers\Pengawas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengawas;
use App\Models\Siswa;
use Inertia\Inertia;

class DashboardController extends Controller
{
    private function getRoomData($pengawasId)
    {
        $pengawas = Pengawas::with('ruangan')->find($pengawasId);
        $ruangan = $pengawas->ruangan;

        $siswa = Siswa::where('ruangan_id', $ruangan->id)
            ->with(['jurusan1', 'jurusan2', 'hasilUjian'])
            ->get();

        // Calculate statistics
        $stats = [
            'total' => $siswa->count(),
            'belum_login' => $siswa->where('status', 'belum_login')->count(),
            'login' => $siswa->where('status', 'login')->count(),
            'proses' => $siswa->where('status', 'proses')->count(),
            'selesai' => $siswa->where('status', 'selesai')->count(),
            'macet' => $siswa->where('status', 'macet')->count(),
        ];

        return [
            'ruangan' => $ruangan,
            'siswa' => $siswa,
            'stats' => $stats
        ];
    }

    public function index(Request $request)
    {
        $data = $this->getRoomData($request->session()->get('pengawas_id'));

        return Inertia::render('Pengawas/Dashboard', $data);
    }

    public function poll(Request $request)
    {
        $data = $this->getRoomData($request->session()->get('pengawas_id'));

        return response()->json($data);
    }

    public function reset(Request $request, Siswa $siswa)
    {
        $pengawas = Pengawas::find($request->session()->get('pengawas_id'));

        // Security check: ensure student is in the supervisor's room
        if ($siswa->ruangan_id !== $pengawas->ruangan_id) {
            return back()->with('error', 'Aksi tidak diizinkan.');
        }

        // Reset status to belum_login so they can login again
        $siswa->update(['status' => 'belum_login']);

        return back()->with('success', "Status siswa {$siswa->nama} berhasil direset.");
    }
}
