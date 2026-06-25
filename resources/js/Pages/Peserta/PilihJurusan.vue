<template>
  <div class="w-full max-w-lg fade-in">
    <div class="bg-white rounded-2xl elev-3 p-8">
      <div class="text-center mb-6">
        <div class="w-14 h-14 rounded-2xl gradient-primary flex items-center justify-center text-white font-extrabold text-xl mx-auto mb-3">P</div>
        <h1 class="text-xl font-extrabold text-primary">Pilih Jurusan Ujian</h1>
      </div>

      <!-- Student Info -->
      <div class="bg-gray-50 rounded-xl p-4 mb-6 space-y-2">
        <div class="flex justify-between text-sm">
          <span class="text-gray-500">Nama</span>
          <span class="font-semibold text-gray-700">{{ siswa.nama }}</span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-gray-500">NISN</span>
          <span class="font-semibold text-gray-700">{{ siswa.nisn }}</span>
        </div>
        <div class="flex justify-between text-sm">
          <span class="text-gray-500">Ruangan</span>
          <span class="font-semibold text-gray-700">{{ siswa.ruangan?.nama }}</span>
        </div>
      </div>

      <!-- J1 Finished Banner -->
      <div v-if="j1_finished" class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 text-center">
        <div class="text-success font-bold mb-1 flex items-center justify-center gap-2">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Ujian Jurusan 1 Selesai!
          </div>
        <p class="text-sm text-gray-500">Lanjutkan ke ujian jurusan pilihan kedua.</p>
      </div>

      <!-- Active Jurusan -->
      <div class="border-2 border-secondary rounded-xl p-5 mb-6 text-center bg-amber-50/50">
        <div class="text-sm text-gray-500 mb-1">{{ j1_finished ? 'Jurusan Pilihan 2' : 'Jurusan Pilihan 1' }}</div>
        <div class="text-xl font-extrabold text-primary">{{ activeJurusan?.nama }}</div>
        <div class="text-xs text-gray-400 mt-1">Kode: {{ activeJurusan?.kode }}</div>
      </div>

      <button
        @click="showConfirm = true"
        class="btn w-full py-3.5 rounded-xl text-white font-bold gradient-gold hover:opacity-90 transition"
      >
        Mulai Ujian
      </button>

      <ConfirmModal
        :show="showConfirm"
        title="Mulai Ujian?"
        :message="`Anda akan memulai ujian untuk jurusan ${activeJurusan?.nama}. Waktu akan mulai berjalan setelah konfirmasi.`"
        confirm-text="Ya, Mulai Ujian"
        variant="primary"
        @close="showConfirm = false"
        @confirm="startExam"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';
import ConfirmModal from '../../Components/ConfirmModal.vue';

defineOptions({ layout: GuestLayout });

const props = defineProps({
  siswa: Object,
  active_jurusan_id: Number,
  j1_finished: Boolean
});

const showConfirm = ref(false);

const activeJurusan = computed(() => {
  if (props.siswa.jurusan1?.id === props.active_jurusan_id) return props.siswa.jurusan1;
  if (props.siswa.jurusan2?.id === props.active_jurusan_id) return props.siswa.jurusan2;
  return props.siswa.jurusan1;
});

function startExam() {
  showConfirm.value = false;
  router.post(window.route('peserta.pilih-jurusan.confirm'), {
    jurusan_id: props.active_jurusan_id
  });
}
</script>