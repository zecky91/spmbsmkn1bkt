<template>
  <PengawasLayout :title="'Monitoring ' + ruangan?.nama" subtitle="Pantau peserta ujian di ruangan Anda">
    <!-- Stats Row -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
      <StatCard label="Total Peserta" :value="liveStats.total" color="primary">
        <template #icon><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></template>
      </StatCard>
      <StatCard label="Login" :value="liveStats.login" color="primary">
        <template #icon><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg></template>
      </StatCard>
      <StatCard label="Proses" :value="liveStats.proses" color="warning">
        <template #icon><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></template>
      </StatCard>
      <StatCard label="Selesai" :value="liveStats.selesai" color="success">
        <template #icon><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg></template>
      </StatCard>
      <StatCard label="Macet" :value="liveStats.macet" color="danger">
        <template #icon><svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M12 2L2 20h20L12 2z"/></svg></template>
      </StatCard>
    </div>

    <!-- Student Table -->
    <div class="bg-white rounded-2xl elev-1 p-5 fade-in">
      <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <h2 class="text-base font-bold text-gray-700">Daftar Peserta</h2>
        <input v-model="search" placeholder="Cari nama atau NISN..." class="field max-w-xs text-sm" />
      </div>
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>NISN</th>
              <th>Jurusan 1</th>
              <th>Jurusan 2</th>
              <th>Status</th>
              <th>Nilai Akhir</th>
              <th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody v-if="paginatedSiswa.length > 0">
            <tr v-for="(s, i) in paginatedSiswa" :key="s.id">
              <td>{{ (currentPage - 1) * perPage + i + 1 }}</td>
              <td class="font-semibold">{{ s.nama }}</td>
              <td class="font-mono text-sm">{{ s.nisn }}</td>
              <td>{{ s.jurusan1?.kode || '-' }}</td>
              <td>{{ s.jurusan2?.kode || '-' }}</td>
              <td><Badge :status="s.status" /></td>
              <td>
                <div v-if="s.hasil_ujian?.length">
                  <div v-for="h in s.hasil_ujian" :key="h.id" class="text-xs whitespace-nowrap">
                    <span class="font-semibold">{{ h.jurusan_id == s.jurusan1_id ? s.jurusan1?.kode : s.jurusan2?.kode }}:</span>
                    {{ Number(h.score_akhir).toFixed(1) }}
                  </div>
                </div>
                <div v-else class="text-xs text-gray-400">-</div>
              </td>
              <td class="text-right">
                <button
                  v-if="s.status === 'macet'"
                  @click="confirmReset(s)"
                  class="btn text-xs px-3 py-1.5 rounded-lg bg-primary text-white hover:bg-primary-dark"
                >Reset</button>
                <span v-else class="text-xs text-gray-300">&mdash;</span>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="8" class="text-center py-8 text-gray-400">Tidak ada peserta ditemukan.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Controls -->
      <div v-if="filteredSiswa.length > 0" class="flex flex-col sm:flex-row justify-between items-center mt-4 px-2">
        <div class="text-sm text-gray-500 mb-3 sm:mb-0">
          Menampilkan {{ (currentPage - 1) * perPage + 1 }} - {{ Math.min(currentPage * perPage, filteredSiswa.length) }} dari {{ filteredSiswa.length }} data
        </div>
        <div class="flex gap-2">
          <button @click="currentPage--" :disabled="currentPage === 1" class="btn px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 disabled:opacity-50 text-sm font-semibold transition-colors">Sebelumnya</button>
          <button @click="currentPage++" :disabled="currentPage * perPage >= filteredSiswa.length" class="btn px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 disabled:opacity-50 text-sm font-semibold transition-colors">Selanjutnya</button>
        </div>
      </div>
    </div>

    <ConfirmModal
      :show="showResetModal"
      :title="'Reset Status ' + (resetTarget?.nama || '')"
      message="Status peserta akan direset ke 'Login'. Jawaban yang sudah tersimpan tidak akan dihapus."
      confirm-text="Ya, Reset"
      variant="warning"
      @close="showResetModal = false"
      @confirm="doReset"
    />
  </PengawasLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import PengawasLayout from '../../Layouts/PengawasLayout.vue';
import StatCard from '../../Components/StatCard.vue';
import Badge from '../../Components/Badge.vue';
import ConfirmModal from '../../Components/ConfirmModal.vue';

const props = defineProps({
  ruangan: Object,
  siswa: Array,
  stats: Object
});

const search = ref('');
const filterStatus = ref('');
const showResetModal = ref(false);
const resetTarget = ref(null);

const liveSiswa = ref([...props.siswa]);
const liveStats = reactive({ ...props.stats });

const filteredSiswa = computed(() => {
  let list = liveSiswa.value;
  if (search.value) {
    const q = search.value.toLowerCase();
    list = list.filter(s => s.nama.toLowerCase().includes(q) || s.nisn.includes(q));
  }
  if (filterStatus.value) list = list.filter(s => s.status === filterStatus.value);
  return list;
});

const currentPage = ref(1);
const perPage = 15;

const paginatedSiswa = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredSiswa.value.slice(start, start + perPage);
});

// Reset page to 1 when filters change
watch([search, filterStatus], () => {
  currentPage.value = 1;
});

function confirmReset(s) {
  resetTarget.value = s;
  showResetModal.value = true;
}

function doReset() {
  showResetModal.value = false;
  if (resetTarget.value) {
    router.post(window.route('pengawas.siswa.reset', resetTarget.value.id));
  }
}

// Polling
let pollInterval;
onMounted(() => {
  pollInterval = setInterval(async () => {
    try {
      const res = await fetch(window.route('pengawas.dashboard.poll'));
      const data = await res.json();
      liveSiswa.value = data.siswa;
      Object.assign(liveStats, data.stats);
    } catch (e) {}
  }, 5000);
});

onUnmounted(() => clearInterval(pollInterval));
</script>