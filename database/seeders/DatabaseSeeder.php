<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Ruangan
        DB::table('ruangan')->insert([
            ['id' => 1, 'nama' => 'Ruang 1 (R1)', 'pin' => '1111', 'kapasitas' => 35, 'aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nama' => 'Ruang 2 (R2)', 'pin' => '2222', 'kapasitas' => 35, 'aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nama' => 'Ruang 3 (R3)', 'pin' => '3333', 'kapasitas' => 30, 'aktif' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nama' => 'Ruang 4 (R4)', 'pin' => '4444', 'kapasitas' => 30, 'aktif' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 2. Seed Jurusan
        DB::table('jurusan')->insert([
            ['id' => 1, 'kode' => 'TKP', 'nama' => 'Teknik Konstruksi dan Perumahan', 'icon' => '🏗️', 'kuota' => 36, 'aktif' => true, 'teknik_penilaian' => 'likert', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'kode' => 'DPIB', 'nama' => 'Desain Pemodelan dan Informasi Bangunan', 'icon' => '📐', 'kuota' => 72, 'aktif' => true, 'teknik_penilaian' => 'dikotomi', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'kode' => 'TP', 'nama' => 'Teknik Pemesinan', 'icon' => '⚙️', 'kuota' => 72, 'aktif' => true, 'teknik_penilaian' => 'likert', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'kode' => 'TKR', 'nama' => 'Teknik Kendaraan Ringan', 'icon' => '🚗', 'kuota' => 72, 'aktif' => true, 'teknik_penilaian' => 'likert', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'kode' => 'TSM', 'nama' => 'Teknik Sepeda Motor', 'icon' => '🏍️', 'kuota' => 72, 'aktif' => true, 'teknik_penilaian' => 'likert', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'kode' => 'TAB', 'nama' => 'Teknik Alat Berat', 'icon' => '🚜', 'kuota' => 36, 'aktif' => true, 'teknik_penilaian' => 'likert', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'kode' => 'TPEL', 'nama' => 'Teknik Pengelasan', 'icon' => '🔥', 'kuota' => 36, 'aktif' => true, 'teknik_penilaian' => 'likert', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'kode' => 'TAV', 'nama' => 'Teknik Audio Video', 'icon' => '📺', 'kuota' => 36, 'aktif' => true, 'teknik_penilaian' => 'likert', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'kode' => 'TEI', 'nama' => 'Teknik Elektronika Industri', 'icon' => '🔌', 'kuota' => 72, 'aktif' => true, 'teknik_penilaian' => 'likert', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 10, 'kode' => 'TITL', 'nama' => 'Teknik Instalasi Tenaga Listrik', 'icon' => '⚡', 'kuota' => 72, 'aktif' => true, 'teknik_penilaian' => 'likert', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 11, 'kode' => 'TPTUP', 'nama' => 'Teknik Pemanasan, Tata Udara, dan Pendinginan (Heating, Ventilation, and Air Conditioning)', 'icon' => '❄️', 'kuota' => 36, 'aktif' => true, 'teknik_penilaian' => 'likert', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 12, 'kode' => 'TG', 'nama' => 'Teknik Geomatika', 'icon' => '🗺️', 'kuota' => 36, 'aktif' => true, 'teknik_penilaian' => 'likert', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 13, 'kode' => 'TKJ', 'nama' => 'Teknik Komputer dan Jaringan', 'icon' => '🖥️', 'kuota' => 72, 'aktif' => true, 'teknik_penilaian' => 'dikotomi', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Seed Pengawas
        DB::table('pengawas')->insert([
            ['id' => 1, 'nama' => 'Budi Santoso', 'username' => 'pengawas_r1', 'password' => Hash::make('password'), 'ruangan_id' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nama' => 'Siti Aminah', 'username' => 'pengawas_r2', 'password' => Hash::make('password'), 'ruangan_id' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nama' => 'Agus Wijaya', 'username' => 'pengawas_r3', 'password' => Hash::make('password'), 'ruangan_id' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 4. Seed Siswa
        DB::table('siswa')->insert([
            [
                'id' => 1, 'nisn' => '0098765432', 'nik' => '3201010101090001', 'nama' => 'Ahmad Fauzi',
                'tanggal_lahir' => '2009-03-15', 'asal_sekolah' => 'SMPN 1 Bandung', 'ruangan_id' => 1,
                'jurusan1_id' => 1, 'jurusan2_id' => 2, 'afirmasi' => null, 'prestasi' => null,
                'status' => 'proses', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 2, 'nisn' => '0098765433', 'nik' => '3201010101090002', 'nama' => 'Bella Permata',
                'tanggal_lahir' => '2009-06-21', 'asal_sekolah' => 'SMPN 2 Bandung', 'ruangan_id' => 1,
                'jurusan1_id' => 2, 'jurusan2_id' => 3, 'afirmasi' => null, 'prestasi' => null,
                'status' => 'selesai', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 3, 'nisn' => '0098765434', 'nik' => '3201010101090003', 'nama' => 'Citra Dewi',
                'tanggal_lahir' => '2009-01-09', 'asal_sekolah' => 'MTs Al-Hidayah', 'ruangan_id' => 1,
                'jurusan1_id' => 3, 'jurusan2_id' => null, 'afirmasi' => null, 'prestasi' => null,
                'status' => 'proses', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 4, 'nisn' => '0098765435', 'nik' => '3201010101090004', 'nama' => 'Dimas Pratama',
                'tanggal_lahir' => '2009-11-30', 'asal_sekolah' => 'SMPN 3 Bandung', 'ruangan_id' => 1,
                'jurusan1_id' => 1, 'jurusan2_id' => 4, 'afirmasi' => null, 'prestasi' => null,
                'status' => 'macet', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 5, 'nisn' => '0098765436', 'nik' => '3201010101090005', 'nama' => 'Eka Saputra',
                'tanggal_lahir' => '2009-08-12', 'asal_sekolah' => 'SMP Negeri 5', 'ruangan_id' => 2,
                'jurusan1_id' => 4, 'jurusan2_id' => null, 'afirmasi' => null, 'prestasi' => null,
                'status' => 'login', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 6, 'nisn' => '0098765437', 'nik' => '3201010101090006', 'nama' => 'Fitri Handayani',
                'tanggal_lahir' => '2009-04-25', 'asal_sekolah' => 'SMP Bina Bangsa', 'ruangan_id' => 2,
                'jurusan1_id' => 2, 'jurusan2_id' => 1, 'afirmasi' => null, 'prestasi' => null,
                'status' => 'proses', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 7, 'nisn' => '0098765438', 'nik' => '3201010101090007', 'nama' => 'Galih Nugroho',
                'tanggal_lahir' => '2009-09-18', 'asal_sekolah' => 'SMPN 4 Bandung', 'ruangan_id' => 2,
                'jurusan1_id' => 5, 'jurusan2_id' => 6, 'afirmasi' => null, 'prestasi' => null,
                'status' => 'belum_login', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 8, 'nisn' => '0098765439', 'nik' => '3201010101090008', 'nama' => 'Hana Lestari',
                'tanggal_lahir' => '2009-12-02', 'asal_sekolah' => 'MTs Nurul Iman', 'ruangan_id' => 3,
                'jurusan1_id' => 6, 'jurusan2_id' => null, 'afirmasi' => null, 'prestasi' => null,
                'status' => 'selesai', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 9, 'nisn' => '0098765440', 'nik' => '3201010101090009', 'nama' => 'Indra Kusuma',
                'tanggal_lahir' => '2009-07-07', 'asal_sekolah' => 'SMPN 6 Bandung', 'ruangan_id' => 3,
                'jurusan1_id' => 1, 'jurusan2_id' => 3, 'afirmasi' => null, 'prestasi' => null,
                'status' => 'proses', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 10, 'nisn' => '0098765441', 'nik' => '3201010101090010', 'nama' => 'Joko Susilo',
                'tanggal_lahir' => '2009-02-14', 'asal_sekolah' => 'SMP Pelita Hati', 'ruangan_id' => 3,
                'jurusan1_id' => 3, 'jurusan2_id' => null, 'afirmasi' => null, 'prestasi' => null,
                'status' => 'login', 'created_at' => now(), 'updated_at' => now()
            ],
        ]);

        // 5. Seed Soal (Bank Soal)
        DB::table('soal')->insert([
            [
                'id' => 1, 'jurusan_id' => 1, // TKJ
                'pertanyaan' => 'Perangkat yang menghubungkan beberapa komputer dalam satu jaringan lokal (LAN) disebut...',
                'opsi_a' => 'Modem', 'opsi_b' => 'Switch', 'opsi_c' => 'Repeater', 'opsi_d' => 'Bridge', 'opsi_e' => 'Router',
                'kunci' => 'B', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 2, 'jurusan_id' => 1, // TKJ
                'pertanyaan' => 'IP address kelas C memiliki rentang oktet pertama...',
                'opsi_a' => '0–127', 'opsi_b' => '128–191', 'opsi_c' => '192–223', 'opsi_d' => '224–239', 'opsi_e' => '240–255',
                'kunci' => 'C', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 3, 'jurusan_id' => 1, // TKJ
                'pertanyaan' => 'Topologi jaringan di mana semua node terhubung ke satu titik pusat disebut...',
                'opsi_a' => 'Bus', 'opsi_b' => 'Ring', 'opsi_c' => 'Mesh', 'opsi_d' => 'Star', 'opsi_e' => 'Tree',
                'kunci' => 'D', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 4, 'jurusan_id' => 2, // RPL
                'pertanyaan' => 'Bahasa pemrograman untuk membangun aplikasi web di sisi server adalah...',
                'opsi_a' => 'CSS', 'opsi_b' => 'HTML', 'opsi_c' => 'PHP', 'opsi_d' => 'SVG', 'opsi_e' => 'JSON',
                'kunci' => 'C', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 5, 'jurusan_id' => 2, // RPL
                'pertanyaan' => 'HTML adalah singkatan dari...',
                'opsi_a' => 'Hyper Text Markup Language', 'opsi_b' => 'High Tech Modern Language', 'opsi_c' => 'Home Tool Markup Language', 'opsi_d' => 'Hyperlinks Text Mark Language', 'opsi_e' => 'Hyper Transfer Markup Logic',
                'kunci' => 'A', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 6, 'jurusan_id' => 2, // RPL
                'pertanyaan' => 'Struktur data yang menerapkan prinsip LIFO (Last In First Out) adalah...',
                'opsi_a' => 'Queue', 'opsi_b' => 'Stack', 'opsi_c' => 'Array', 'opsi_d' => 'Tree', 'opsi_e' => 'Graph',
                'kunci' => 'B', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 7, 'jurusan_id' => 3, // MM
                'pertanyaan' => 'Format gambar yang mendukung transparansi adalah...',
                'opsi_a' => 'JPG', 'opsi_b' => 'BMP', 'opsi_c' => 'PNG', 'opsi_d' => 'GIF', 'opsi_e' => 'PNG & GIF',
                'kunci' => 'E', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 8, 'jurusan_id' => 3, // MM
                'pertanyaan' => 'Software pengolah grafis berbasis vektor adalah...',
                'opsi_a' => 'Photoshop', 'opsi_b' => 'Lightroom', 'opsi_c' => 'CorelDRAW', 'opsi_d' => 'Premiere', 'opsi_e' => 'Audition',
                'kunci' => 'C', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 9, 'jurusan_id' => 4, // AKL
                'pertanyaan' => 'Persamaan dasar akuntansi yang benar adalah...',
                'opsi_a' => 'Aset = Utang − Modal', 'opsi_b' => 'Aset = Utang + Modal', 'opsi_c' => 'Modal = Aset + Utang', 'opsi_d' => 'Utang = Aset + Modal', 'opsi_e' => 'Aset = Modal − Utang',
                'kunci' => 'B', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 10, 'jurusan_id' => 5, // OTKP
                'pertanyaan' => 'Dokumen yang digunakan untuk surat-menyurat resmi instansi disebut...',
                'opsi_a' => 'Memo', 'opsi_b' => 'Nota', 'opsi_c' => 'Surat dinas', 'opsi_d' => 'Kuitansi', 'opsi_e' => 'Faktur',
                'kunci' => 'C', 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'id' => 11, 'jurusan_id' => 6, // BDP
                'pertanyaan' => 'Strategi pemasaran melalui media sosial dikenal dengan istilah...',
                'opsi_a' => 'Telemarketing', 'opsi_b' => 'Direct selling', 'opsi_c' => 'Digital marketing', 'opsi_d' => 'Door to door', 'opsi_e' => 'Barter',
                'kunci' => 'C', 'created_at' => now(), 'updated_at' => now()
            ],
        ]);

        // 6. Seed Admin
        DB::table('admin')->insert([
            ['id' => 1, 'nama' => 'Administrator', 'username' => 'admin', 'password' => Hash::make('admin123'), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nama' => 'Ahmad Zaki', 'username' => 'ahmad_zaki', 'password' => Hash::make('@smkn1BKT'), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nama' => 'Novri Irmanto', 'username' => 'novri_irmanto', 'password' => Hash::make('@smkn1BKT'), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'nama' => 'Yulia Sandra', 'username' => 'yulia_sandra', 'password' => Hash::make('@smkn1BKT'), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'nama' => 'Mardayoni', 'username' => 'mardayoni12', 'password' => Hash::make('@smkn1BKT'), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'nama' => 'Alrozika', 'username' => 'alrozika', 'password' => Hash::make('@smkn1BKT'), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 7, 'nama' => 'Desnita', 'username' => 'desnita', 'password' => Hash::make('@smkn1BKT'), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 8, 'nama' => 'Atikah Rahmah Putri', 'username' => 'atikah_rahmah_putri', 'password' => Hash::make('@smkn1BKT'), 'created_at' => now(), 'updated_at' => now()],
            ['id' => 9, 'nama' => 'Rahmad Noevriadi', 'username' => 'rahmad_noevriadi', 'password' => Hash::make('@smkn1BKT'), 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
