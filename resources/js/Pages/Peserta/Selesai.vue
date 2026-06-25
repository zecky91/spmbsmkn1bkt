<template>
  <div class="w-full max-w-lg fade-in">
    <div class="bg-white rounded-2xl elev-3 p-8 text-center">
      <div class="w-20 h-20 rounded-full bg-green-50 flex items-center justify-center mx-auto mb-5">
        <svg class="w-10 h-10 text-success" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
          <path d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
        </svg>
      </div>

      <h1 class="text-2xl font-extrabold text-primary mb-1">Ujian Selesai!</h1>
      <p class="text-sm text-gray-400 mb-6">Terima kasih telah menyelesaikan ujian seleksi PPDB.</p>

      <div class="bg-gray-50 rounded-xl p-4 mb-6 text-left space-y-2">
        <div class="flex justify-between text-sm">
          <span class="text-gray-500">Nama</span>
          <span class="font-semibold">{{ siswa.nama }}</span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-gray-500">NISN</span>
          <span class="font-semibold">{{ siswa.nisn }}</span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-gray-500">Ruangan</span>
          <span class="font-semibold">{{ siswa.ruangan?.nama }}</span>
        </div>
      </div>

      <!-- Results per jurusan -->
      <div class="space-y-3 mb-6">
        <div v-for="result in results" :key="result.id" class="bg-blue-50/60 rounded-xl p-4 text-left">
          <div class="flex items-center justify-between mb-2">
            <span class="font-bold text-primary text-sm">{{ result.jurusan?.nama }}</span>
            <span class="badge badge-selesai"><span class="dot bg-success"></span> Selesai</span>
          </div>
          <div class="flex gap-4 text-xs text-gray-500">
            <span>Dijawab: <b class="text-gray-700">{{ result.jumlah_jawab }}/{{ result.total_soal }}</b></span>
            <span>Durasi: <b class="text-gray-700">{{ formatDuration(result.waktu_mulai, result.waktu_selesai) }}</b></span>
          </div>
        </div>
      </div>

      <p class="text-xs text-gray-400 mb-5">Hasil ujian akan diproses oleh panitia. Silakan tunggu pengumuman resmi.</p>

      <Link
        :href="route('peserta.logout')"
        method="post"
        as="button"
        class="btn w-full py-3 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 font-semibold"
      >
        Keluar
      </Link>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

defineOptions({ layout: GuestLayout });

defineProps({
  siswa: Object,
  results: Array
});

function formatDuration(start, end) {
  if (!start || !end) return '-';
  const diff = Math.floor((new Date(end) - new Date(start)) / 1000);
  const m = Math.floor(diff / 60);
  const s = diff % 60;
  return `${m} menit ${s} detik`;
}
</script>