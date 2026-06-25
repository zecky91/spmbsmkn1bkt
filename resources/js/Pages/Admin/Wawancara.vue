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

        <!-- Filter Status Wawancara -->
        <div v-if="filterJurusan" class="flex rounded-xl overflow-hidden border border-gray-200 text-sm font-semibold">
          <button
            @click="filterStatus = 'belum'"
            :class="filterStatus === 'belum' ? 'bg-orange-500 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
            class="px-4 py-2 transition-colors flex items-center gap-1.5"
          >
            <span class="w-2 h-2 rounded-full bg-current"></span>
            Belum ({{ belumCount }})
          </button>
          <button
            @click="filterStatus = 'sudah'"
            :class="filterStatus === 'sudah' ? 'bg-green-500 text-white' : 'bg-white text-gray-500 hover:bg-gray-50'"
            class="px-4 py-2 border-l border-gray-200 transition-colors flex items-center gap-1.5"
          >
            <span class="w-2 h-2 rounded-full bg-current"></span>
            Sudah ({{ sudahCount }})
          </button>
        </div>

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
                <div v-if="getNilaiForJurusan(s) !== null">
                  <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-green-700 bg-green-50 px-2.5 py-1 rounded-full">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    {{ getNilaiForJurusan(s) }}
                  </span>
                </div>
                <span v-else class="inline-flex items-center gap-1.5 text-xs font-semibold text-orange-600 bg-orange-50 px-2.5 py-1 rounded-full">
                  <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                  Belum diisi
                </span>
              </td>
              <td class="py-3 px-2 text-right">
                <button @click="openWawancara(s)" class="text-xs px-2.5 py-1.5 rounded-lg bg-green-500 text-white hover:bg-green-600 font-semibold transition-colors">
                  {{ getNilaiForJurusan(s) !== null ? 'Edit Nilai' : 'Input Nilai' }}
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
                  <p class="text-base font-semibold" v-else>Tidak ada siswa ditemukan</p>
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
const filterStatus = ref('belum'); // default: tampilkan yang belum

// Cek apakah siswa sudah memiliki nilai wawancara untuk jurusan yang sedang di-filter
function hasNilaiWawancara(s) {
  if (!filterJurusan.value || !s.hasil_ujian?.length) return false;
  const h = s.hasil_ujian.find(h => h.jurusan_id == filterJurusan.value);
  return h && h.nilai_wawancara !== null;
}

// Ambil nilai wawancara untuk jurusan yang sedang di-filter
function getNilaiForJurusan(s) {
  if (!filterJurusan.value || !s.hasil_ujian?.length) return null;
  const h = s.hasil_ujian.find(h => h.jurusan_id == filterJurusan.value);
  return (h && h.nilai_wawancara !== null) ? h.nilai_wawancara : null;
}

// Daftar siswa di jurusan ini (sebelum filter status)
const siswaByJurusan = computed(() => {
  if (!filterJurusan.value) return [];
  let list = props.siswa;
  if (search.value) {
    const q = search.value.toLowerCase();
    list = list.filter(s => s.nama.toLowerCase().includes(q) || s.nisn.includes(q));
  }
  return list.filter(s => s.jurusan1_id == filterJurusan.value || s.jurusan2_id == filterJurusan.value);
});

// Hitung jumlah untuk badge counter
const belumCount = computed(() => siswaByJurusan.value.filter(s => !hasNilaiWawancara(s)).length);
const sudahCount = computed(() => siswaByJurusan.value.filter(s => hasNilaiWawancara(s)).length);

const filteredSiswa = computed(() => {
  if (!filterJurusan.value) return [];
  return siswaByJurusan.value.filter(s => {
    if (filterStatus.value === 'belum') return !hasNilaiWawancara(s);
    if (filterStatus.value === 'sudah') return hasNilaiWawancara(s);
    return true;
  });
});

const currentPage = ref(1);
const perPage = 15;

const paginatedSiswa = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredSiswa.value.slice(start, start + perPage);
});

// Reset pagination ketika filter berubah
watch([search, filterJurusan, filterStatus], () => {
  currentPage.value = 1;
});

// Reset filterStatus ke 'belum' ketika jurusan berubah
watch(filterJurusan, () => {
  filterStatus.value = 'belum';
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
