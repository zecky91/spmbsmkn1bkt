<template>
  <AdminLayout title="Dashboard Admin" subtitle="Monitoring seluruh peserta ujian">
    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <StatCard label="Total Peserta" :value="liveStats.total" color="primary" />
      <StatCard label="Sedang Ujian" :value="liveStats.proses" color="warning" />
      <StatCard label="Selesai" :value="liveStats.selesai" color="success" />
      <StatCard label="Macet" :value="liveStats.macet" color="danger" />
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl elev-1 p-4 sm:p-5 mb-4 fade-in">
      <div class="flex flex-col sm:flex-row flex-wrap gap-3 items-stretch sm:items-center">
        <input v-model="search" placeholder="Cari nama/NISN..." class="field text-sm flex-1 w-full sm:w-auto sm:max-w-xs" />
        <select v-model="filterRoom" class="field text-sm w-full sm:w-auto sm:max-w-[200px]">
          <option value="">Semua Ruangan</option>
          <option v-for="r in ruangan" :key="r.id" :value="r.id">{{ r.nama }}</option>
        </select>
        <select v-model="filterStatus" class="field text-sm w-full sm:w-auto sm:max-w-[180px]">
          <option value="">Semua Status</option>
          <option value="belum_login">Belum Login</option>
          <option value="login">Login</option>
          <option value="proses">Proses</option>
          <option value="selesai">Selesai</option>
          <option value="macet">Macet</option>
        </select>
        <a :href="route('admin.export')" class="btn w-full sm:w-auto sm:ml-auto inline-flex justify-center items-center gap-2 px-4 py-2.5 rounded-xl bg-success text-white text-sm font-semibold hover:opacity-90">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export CSV
        </a>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl elev-1 p-5 fade-in">
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>No</th><th>Nama</th><th>NISN</th><th>Ruangan</th><th>Jurusan 1</th><th>Jurusan 2</th><th>Status</th><th>Tes Online</th><th>Tes Wawancara</th><th>Nilai Akhir</th><th class="text-right">Aksi</th>
            </tr>
          </thead>
          <tbody v-if="paginatedSiswa.length > 0">
            <tr v-for="(s, i) in paginatedSiswa" :key="s.id">
              <td>{{ (currentPage - 1) * perPage + i + 1 }}</td>
              <td class="font-semibold">{{ s.nama }}</td>
              <td class="font-mono text-sm">{{ s.nisn }}</td>
              <td>{{ s.ruangan?.nama || '-' }}</td>
              <td>{{ s.jurusan1?.kode || '-' }}</td>
              <td>{{ s.jurusan2?.kode || '-' }}</td>
              <td><Badge :status="s.status" /></td>
              <td>
                <div v-if="s.hasil_ujian?.length">
                  <div v-for="h in s.hasil_ujian" :key="h.id" class="text-xs whitespace-nowrap">
                    <span class="font-semibold">{{ h.jurusan_id == s.jurusan1_id ? s.jurusan1?.kode : s.jurusan2?.kode }}:</span>
                    {{ h.score_ujian !== null ? Number(h.score_ujian).toFixed(1) : '-' }}
                  </div>
                </div>
                <div v-else class="text-xs text-gray-400">-</div>
              </td>
              <td>
                <div v-if="s.hasil_ujian?.length">
                  <div v-for="h in s.hasil_ujian" :key="'w-'+h.id" class="text-xs whitespace-nowrap">
                    <span class="font-semibold">{{ h.jurusan_id == s.jurusan1_id ? s.jurusan1?.kode : s.jurusan2?.kode }}:</span>
                    {{ h.nilai_wawancara !== null ? h.nilai_wawancara : '-' }}
                  </div>
                </div>
                <div v-else class="text-xs text-gray-400">-</div>
              </td>
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
                <div class="flex gap-1.5 justify-end">
                  <button v-if="s.status === 'macet'" @click="confirmAction(s, 'reset')" class="btn text-xs px-2.5 py-1.5 rounded-lg bg-primary text-white">Reset</button>
                  <button v-if="s.status !== 'selesai' && s.status !== 'belum_login'" @click="confirmAction(s, 'gugur')" class="btn text-xs px-2.5 py-1.5 rounded-lg bg-danger text-white">Gugurkan</button>
                </div>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="11" class="text-center py-8 text-gray-400">Tidak ada data.</td>
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

    <ConfirmModal :show="showConfirmModal" :title="confirmTitle" :message="confirmMessage" :confirm-text="confirmBtnText" :variant="confirmVariant" @close="showConfirmModal = false" @confirm="executeAction" />

    <!-- Modal logic wawancara removed, moved to dedicated page -->

  </AdminLayout>
</template>

<script setup>
import { ref, computed, reactive, onMounted, onUnmounted } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import StatCard from '../../Components/StatCard.vue';
import Badge from '../../Components/Badge.vue';
import ConfirmModal from '../../Components/ConfirmModal.vue';
import Modal from '../../Components/Modal.vue';

const props = defineProps({ siswa: Array, ruangan: Array, stats: Object });

const search = ref('');
const filterRoom = ref('');
const filterStatus = ref('');
const liveSiswa = ref([...props.siswa]);
const liveStats = reactive({ ...props.stats });

// Wawancara logic removed from here

const showConfirmModal = ref(false);
const actionTarget = ref(null);
const actionType = ref('');

const confirmTitle = computed(() => actionType.value === 'reset' ? `Reset ${actionTarget.value?.nama}` : `Gugurkan ${actionTarget.value?.nama}`);
const confirmMessage = computed(() => actionType.value === 'reset' ? 'Status akan direset ke Login. Jawaban tetap tersimpan.' : 'Peserta akan digugurkan dan semua jawaban dihapus. Tindakan ini tidak dapat dibatalkan.');
const confirmBtnText = computed(() => actionType.value === 'reset' ? 'Ya, Reset' : 'Ya, Gugurkan');
const confirmVariant = computed(() => actionType.value === 'reset' ? 'warning' : 'danger');

const filteredSiswa = computed(() => {
  let list = liveSiswa.value;
  if (search.value) {
    const q = search.value.toLowerCase();
    list = list.filter(s => s.nama.toLowerCase().includes(q) || s.nisn.includes(q));
  }
  if (filterRoom.value) list = list.filter(s => s.ruangan_id == filterRoom.value);
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
import { watch } from 'vue';
watch([search, filterRoom, filterStatus], () => {
  currentPage.value = 1;
});

function confirmAction(s, type) { actionTarget.value = s; actionType.value = type; showConfirmModal.value = true; }
function executeAction() {
  showConfirmModal.value = false;
  const routeName = actionType.value === 'reset' ? 'admin.siswa.reset' : 'admin.siswa.gugur';
  router.post(window.route(routeName, actionTarget.value.id));
}

// Wawancara methods removed from here

let poll;
onMounted(() => { poll = setInterval(async () => { try { const r = await fetch(window.route('admin.dashboard.poll')); const d = await r.json(); liveSiswa.value = d.siswa; Object.assign(liveStats, d.stats); } catch(e){} }, 5000); });
onUnmounted(() => clearInterval(poll));
</script>