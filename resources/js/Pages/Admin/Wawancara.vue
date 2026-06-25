<template>
  <AdminLayout title="Input Nilai Wawancara" subtitle="Kelola nilai wawancara peserta">
    
    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl elev-1 p-5 mb-4 fade-in">
      <div class="flex flex-wrap gap-3 items-center">
        <input v-model="search" placeholder="Cari nama/NISN..." class="field max-w-xs text-sm flex-1" />
        <select v-model="filterJurusan" class="field max-w-[200px] text-sm">
          <option value="">Semua Jurusan</option>
          <option v-for="j in jurusan" :key="j.id" :value="j.id">{{ j.nama }} ({{ j.kode }})</option>
        </select>
        <div class="text-sm text-gray-500 ml-auto">
          Total: <span class="font-bold text-[#1E3A5F]">{{ filteredSiswa.length }}</span> peserta
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl elev-1 p-5 fade-in">
      <div class="overflow-x-auto">
        <table class="data-table w-full">
          <thead>
            <tr class="text-left border-b border-gray-100">
              <th class="py-3 px-2 font-bold text-gray-500">No</th>
              <th class="py-3 px-2 font-bold text-gray-500">Nama</th>
              <th class="py-3 px-2 font-bold text-gray-500">NISN</th>
              <th class="py-3 px-2 font-bold text-gray-500">Jurusan 1</th>
              <th class="py-3 px-2 font-bold text-gray-500">Jurusan 2</th>
              <th class="py-3 px-2 font-bold text-gray-500">Nilai Wawancara</th>
              <th class="py-3 px-2 font-bold text-gray-500 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody v-if="paginatedSiswa.length > 0">
            <tr v-for="(s, i) in paginatedSiswa" :key="s.id" class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
              <td class="py-3 px-2">{{ (currentPage - 1) * perPage + i + 1 }}</td>
              <td class="py-3 px-2 font-semibold">{{ s.nama }}</td>
              <td class="py-3 px-2 font-mono text-sm">{{ s.nisn }}</td>
              <td class="py-3 px-2">{{ s.jurusan1?.kode || '-' }}</td>
              <td class="py-3 px-2">{{ s.jurusan2?.kode || '-' }}</td>
              <td class="py-3 px-2">
                <div v-if="s.hasil_ujian?.length">
                  <div v-for="h in s.hasil_ujian" :key="h.id" class="text-xs whitespace-nowrap">
                    <span class="font-semibold">{{ h.jurusan_id == s.jurusan1_id ? s.jurusan1?.kode : s.jurusan2?.kode }}:</span>
                    {{ h.nilai_wawancara !== null ? h.nilai_wawancara : '-' }}
                  </div>
                </div>
                <div v-else class="text-xs text-gray-400">-</div>
              </td>
              <td class="py-3 px-2 text-right">
                <button @click="openWawancara(s)" class="text-xs px-2.5 py-1.5 rounded-lg bg-green-500 text-white hover:bg-green-600 font-semibold transition-colors">
                  Input Nilai
                </button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="7" class="text-center py-12">
                <div class="flex flex-col items-center justify-center text-gray-500">
                  <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                  <p class="text-base font-semibold" v-if="!filterJurusan">Silakan pilih Jurusan terlebih dahulu</p>
                  <p class="text-base font-semibold" v-else>Tidak ada siswa di jurusan ini</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="filteredSiswa.length > 0" class="flex flex-col sm:flex-row justify-between items-center mt-4 px-2">
        <div class="text-sm text-gray-500 mb-3 sm:mb-0">
          Menampilkan {{ (currentPage - 1) * perPage + 1 }} - {{ Math.min(currentPage * perPage, filteredSiswa.length) }} dari {{ filteredSiswa.length }} data
        </div>
        <div class="flex gap-2">
          <button @click="currentPage--" :disabled="currentPage === 1" class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 disabled:opacity-50 text-sm font-semibold transition-colors">Sebelumnya</button>
          <button @click="currentPage++" :disabled="currentPage * perPage >= filteredSiswa.length" class="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 disabled:opacity-50 text-sm font-semibold transition-colors">Selanjutnya</button>
        </div>
      </div>
    </div>

    <!-- Wawancara Modal -->
    <Modal :show="showWawancaraModal" max-width="500px" :closeable="false" :show-close-button="true" @close="showWawancaraModal = false">
      <template #header>
        <h3 class="text-lg font-bold">Input Wawancara - {{ wawancaraSiswa?.nama }}</h3>
      </template>
      <div class="space-y-4">
        <div v-for="jur in getJurusanList(wawancaraSiswa)" :key="jur.id">
          <label class="block text-sm font-semibold mb-1">{{ jur.nama }} ({{ jur.kode }})</label>
          <input type="number" min="0" max="100" v-model="wawancaraForm.wawancara[jur.id]" class="field w-full text-sm" placeholder="0 - 100">
        </div>
        <p v-if="getJurusanList(wawancaraSiswa).length === 0" class="text-sm text-gray-500">Belum ada jurusan yang dipilih.</p>
      </div>
      <template #footer>
        <button @click="showWawancaraModal = false" class="text-sm bg-gray-200 text-gray-700 hover:bg-gray-300 px-4 py-2 rounded-lg font-bold transition-colors">Batal</button>
        <button @click="saveWawancara" class="text-sm bg-[#1E3A5F] text-white hover:bg-[#152B47] px-4 py-2 rounded-lg font-bold transition-colors" :disabled="wawancaraForm.processing">Simpan</button>
      </template>
    </Modal>

  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Modal from '../../Components/Modal.vue';

const props = defineProps({ siswa: Array, jurusan: Array });

const search = ref('');
const filterJurusan = ref('');

const filteredSiswa = computed(() => {
  if (!filterJurusan.value) return [];
  
  let list = props.siswa;
  
  if (search.value) {
    const q = search.value.toLowerCase();
    list = list.filter(s => s.nama.toLowerCase().includes(q) || s.nisn.includes(q));
  }
  
  list = list.filter(s => s.jurusan1_id == filterJurusan.value || s.jurusan2_id == filterJurusan.value);
  
  return list;
});

const currentPage = ref(1);
const perPage = 15;

const paginatedSiswa = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredSiswa.value.slice(start, start + perPage);
});

// Reset pagination when filters change
watch([search, filterJurusan], () => {
  currentPage.value = 1;
});

const showWawancaraModal = ref(false);
const wawancaraSiswa = ref(null);
const wawancaraForm = useForm({ wawancara: {} });

function getJurusanList(s) {
  if (!s) return [];
  const list = [];
  if (s.jurusan1) list.push(s.jurusan1);
  if (s.jurusan2) list.push(s.jurusan2);
  
  if (filterJurusan.value) {
    return list.filter(j => j.id == filterJurusan.value);
  }
  
  return list;
}

function openWawancara(s) {
  wawancaraSiswa.value = s;
  const initWawancara = {};
  if (s.hasil_ujian) {
    s.hasil_ujian.forEach(h => {
      initWawancara[h.jurusan_id] = h.nilai_wawancara !== null ? h.nilai_wawancara : '';
    });
  }
  wawancaraForm.wawancara = initWawancara;
  showWawancaraModal.value = true;
}

function saveWawancara() {
  wawancaraForm.post(window.route('admin.siswa.wawancara', wawancaraSiswa.value.id), {
    preserveScroll: true,
    onSuccess: () => { showWawancaraModal.value = false; }
  });
}
</script>
