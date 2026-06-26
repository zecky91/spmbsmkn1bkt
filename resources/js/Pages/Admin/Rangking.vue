<template>
  <AdminLayout title="Rangking PPDB" subtitle="Peringkat siswa berdasarkan nilai akhir dan prioritas pilihan">
    
    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl elev-1 p-5 mb-4 fade-in">
      <div class="flex flex-wrap gap-3 items-center">
        <select v-model="filterJurusan" class="field max-w-[250px] text-sm">
          <option value="">Pilih Jurusan untuk melihat Rangking</option>
          <option v-for="j in jurusan" :key="j.id" :value="j.id">{{ j.nama }} ({{ j.kode }})</option>
        </select>
        
        <input v-if="filterJurusan" v-model="search" placeholder="Cari nama/NISN..." class="field max-w-xs text-sm" />

        <div v-if="filterJurusan" class="text-sm text-gray-500 ml-auto">
          Total Rangking: <span class="font-bold text-[#1E3A5F]">{{ rankedSiswa.length }}</span> peserta
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl elev-1 p-5 fade-in">
      <div class="overflow-x-auto">
        <table class="data-table w-full">
          <thead>
            <tr class="text-left border-b border-gray-100">
              <th class="py-3 px-2 font-bold text-gray-500 text-center">Peringkat</th>
              <th class="py-3 px-2 font-bold text-gray-500">Nama</th>
              <th class="py-3 px-2 font-bold text-gray-500">NISN</th>
              <th class="py-3 px-2 font-bold text-gray-500">Asal Sekolah</th>
              <th class="py-3 px-2 font-bold text-gray-500 text-center">Pilihan Ke-</th>
              <th class="py-3 px-2 font-bold text-gray-500 text-center">Afirmasi</th>
              <th class="py-3 px-2 font-bold text-gray-500 text-center">Prestasi</th>
              <th class="py-3 px-2 font-bold text-gray-500 text-right">Nilai Online</th>
              <th class="py-3 px-2 font-bold text-gray-500 text-right">Nilai Wawancara</th>
              <th class="py-3 px-2 font-bold text-gray-500 text-right">Nilai Akhir</th>
              <th class="py-3 px-2 font-bold text-gray-500">Keterangan</th>
              <th class="py-3 px-2 font-bold text-gray-500 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody v-if="paginatedSiswa.length > 0">
            <tr v-for="s in paginatedSiswa" :key="s.id" class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
              <td class="py-3 px-2 text-center">
                <span :class="{
                  'bg-yellow-100 text-yellow-700 font-bold px-2.5 py-1 rounded-lg': s.peringkat === 1,
                  'bg-gray-200 text-gray-700 font-bold px-2.5 py-1 rounded-lg': s.peringkat === 2,
                  'bg-orange-100 text-orange-700 font-bold px-2.5 py-1 rounded-lg': s.peringkat === 3,
                  'font-semibold text-gray-600': s.peringkat > 3
                }">
                  {{ s.peringkat }}
                </span>
              </td>
              <td class="py-3 px-2 font-semibold">{{ s.nama }}</td>
              <td class="py-3 px-2 font-mono text-sm">{{ s.nisn }}</td>
              <td class="py-3 px-2 text-sm">{{ s.asal_sekolah || '-' }}</td>
              <td class="py-3 px-2 text-center">
                <span :class="s.pilihan === 1 ? 'text-blue-600 font-bold' : 'text-gray-500 font-semibold'">
                  Pilihan {{ s.pilihan }}
                </span>
              </td>
              <td class="py-3 px-2 text-sm font-semibold text-center">
                <span v-if="s.afirmasi == '1' || s.afirmasi === true || s.afirmasi === 'Ya' || s.afirmasi === 'ya'" class="text-green-600 bg-green-50 px-2 py-1 rounded-md">Ya</span>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="py-3 px-2 text-sm font-semibold text-center">
                <span v-if="s.prestasi == '1' || s.prestasi === true || s.prestasi === 'Ya' || s.prestasi === 'ya'" class="text-blue-600 bg-blue-50 px-2 py-1 rounded-md">Ya</span>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="py-3 px-2 text-right font-mono">{{ s.nilai_online !== null ? Number(s.nilai_online).toFixed(1) : '-' }}</td>
              <td class="py-3 px-2 text-right font-mono">{{ s.nilai_wawancara !== null ? Number(s.nilai_wawancara).toFixed(1) : '-' }}</td>
              <td class="py-3 px-2 text-right font-bold text-lg text-[#1E3A5F]">
                {{ s.nilai_akhir !== null ? Math.round(Number(s.nilai_akhir)) : '-' }}
              </td>
              <td class="py-3 px-2 text-sm text-gray-600 truncate max-w-[150px]" :title="s.keterangan">{{ s.keterangan || '-' }}</td>
              <td class="py-3 px-2 text-center">
                <button @click="openEditModal(s)" class="bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors flex items-center gap-1 mx-auto">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                  Edit
                </button>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="12" class="text-center py-12">
                <div class="flex flex-col items-center justify-center text-gray-500">
                  <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                  <p class="text-base font-semibold" v-if="!filterJurusan">Silakan pilih Jurusan terlebih dahulu untuk melihat rangking.</p>
                  <p class="text-base font-semibold" v-else>Tidak ada data siswa untuk jurusan ini.</p>
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

    <!-- Edit Modal -->
    <Modal :show="showEditModal" max-width="500px" :closeable="false" :show-close-button="true" @close="closeEditModal">
      <template #header>
        <h3 class="text-lg font-bold">Edit Data Rangking - {{ editSiswa?.nama }}</h3>
        <p class="text-sm text-gray-500 font-normal mt-1">NISN: {{ editSiswa?.nisn }} | Pilihan {{ editSiswa?.pilihan }}</p>
      </template>

      <form id="editForm" @submit.prevent="submitEdit" class="space-y-4">
        <div>
          <label class="block text-sm font-semibold mb-1">Nilai Tes Online</label>
          <input type="number" step="0.01" min="0" max="100" v-model="editForm.score_ujian" class="field w-full text-sm" placeholder="0 - 100" />
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">Nilai Wawancara</label>
          <input type="number" step="0.01" min="0" max="100" v-model="editForm.nilai_wawancara" class="field w-full text-sm" placeholder="0 - 100" />
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">Keterangan (Nama Petugas/Catatan)</label>
          <input type="text" v-model="editForm.keterangan" class="field w-full text-sm" placeholder="Cth: Budi (Wawancara)" maxlength="255" />
        </div>
      </form>

      <template #footer>
        <button type="button" @click="closeEditModal" :disabled="isSubmitting" class="text-sm bg-gray-200 text-gray-700 hover:bg-gray-300 px-4 py-2 rounded-lg font-bold transition-colors disabled:opacity-50">Batal</button>
        <button type="submit" form="editForm" class="text-sm bg-[#1E3A5F] text-white hover:bg-[#152B47] px-4 py-2 rounded-lg font-bold transition-colors disabled:opacity-50 flex items-center gap-2" :disabled="isSubmitting">
          <svg v-if="isSubmitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
          {{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
        </button>
      </template>
    </Modal>

  </AdminLayout>
</template>

<script setup>
import { ref, computed, watch, reactive } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Modal from '../../Components/Modal.vue';

const props = defineProps({ siswa: Array, jurusan: Array });

const filterJurusan = ref('');
const search = ref('');

// Computed property untuk memproses dan merangking siswa berdasarkan opsi B
const rankedSiswa = computed(() => {
  if (!filterJurusan.value) return [];

  const jurusanId = filterJurusan.value;
  let pilihan1 = [];
  let pilihan2 = [];

  props.siswa.forEach(s => {
    // Cek apakah siswa memilih jurusan ini (sebagai pilihan 1 atau 2)
    const isPilihan1 = s.jurusan1_id == jurusanId;
    const isPilihan2 = s.jurusan2_id == jurusanId;

    if (!isPilihan1 && !isPilihan2) return;

    // Ambil nilai ujian/wawancara khusus untuk jurusan ini
    let nilaiOnline = null;
    let nilaiWawancara = null;
    let nilaiAkhir = 0; // Default 0 jika belum ada nilai
    let keterangan = '';

    if (s.hasil_ujian && s.hasil_ujian.length > 0) {
      const h = s.hasil_ujian.find(h => h.jurusan_id == jurusanId);
      if (h) {
        nilaiOnline = h.score_ujian;
        nilaiWawancara = h.nilai_wawancara;
        nilaiAkhir = h.score_akhir || 0;
        keterangan = h.keterangan || '';
      }
    }

    const dataSiswa = {
      ...s,
      pilihan: isPilihan1 ? 1 : 2,
      nilai_online: nilaiOnline,
      nilai_wawancara: nilaiWawancara,
      nilai_akhir: nilaiAkhir,
      keterangan: keterangan
    };

    if (isPilihan1) {
      pilihan1.push(dataSiswa);
    } else {
      pilihan2.push(dataSiswa);
    }
  });

  // Sort Pilihan 1 berdasarkan nilai akhir descending
  pilihan1.sort((a, b) => b.nilai_akhir - a.nilai_akhir);
  
  // Sort Pilihan 2 berdasarkan nilai akhir descending
  pilihan2.sort((a, b) => b.nilai_akhir - a.nilai_akhir);

  // Gabungkan (Option B: Prioritas Pilihan 1 Dulu)
  const combined = [...pilihan1, ...pilihan2];

  // Berikan nomor peringkat
  return combined.map((s, index) => {
    s.peringkat = index + 1;
    return s;
  });
});

// Search filter berjalan SETELAH perengkingan, jadi peringkat aslinya tidak berubah/hilang saat dicari
const filteredSiswa = computed(() => {
  let list = rankedSiswa.value;
  if (search.value) {
    const q = search.value.toLowerCase();
    list = list.filter(s => s.nama.toLowerCase().includes(q) || s.nisn.includes(q));
  }
  return list;
});

const currentPage = ref(1);
const perPage = 25; // Lebih banyak baris untuk halaman rangking

const paginatedSiswa = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredSiswa.value.slice(start, start + perPage);
});

// Reset pagination
watch([search, filterJurusan], () => {
  currentPage.value = 1;
});

// Edit Logic
const showEditModal = ref(false);
const editSiswa = ref(null);
const isSubmitting = ref(false);

const editForm = reactive({
  score_ujian: null,
  nilai_wawancara: null,
  keterangan: ''
});

function openEditModal(s) {
  editSiswa.value = s;
  editForm.score_ujian = s.nilai_online;
  editForm.nilai_wawancara = s.nilai_wawancara;
  editForm.keterangan = s.keterangan;
  showEditModal.value = true;
}

function closeEditModal() {
  showEditModal.value = false;
  editSiswa.value = null;
}

import axios from 'axios';

async function submitEdit() {
  if (!editSiswa.value || !filterJurusan.value) return;
  
  isSubmitting.value = true;
  
  try {
    const response = await axios.post(route('admin.rangking.update', editSiswa.value.id), {
      jurusan_id: filterJurusan.value,
      score_ujian: editForm.score_ujian !== '' ? editForm.score_ujian : null,
      nilai_wawancara: editForm.nilai_wawancara !== '' ? editForm.nilai_wawancara : null,
      keterangan: editForm.keterangan
    });
    
    if (response.data && response.data.hasil) {
      const data = response.data;
      
      // Update data di local state agar tidak perlu reload halaman
      const idx = props.siswa.findIndex(x => x.id === editSiswa.value.id);
      if (idx !== -1) {
        if (!props.siswa[idx].hasil_ujian) {
          props.siswa[idx].hasil_ujian = [];
        }
        
        const hIdx = props.siswa[idx].hasil_ujian.findIndex(h => h.jurusan_id == filterJurusan.value);
        if (hIdx !== -1) {
          props.siswa[idx].hasil_ujian[hIdx] = data.hasil;
        } else {
          props.siswa[idx].hasil_ujian.push(data.hasil);
        }
      }
      
      closeEditModal();
    }
  } catch (error) {
    console.error("Error submitting data:", error.response?.data || error.message);
    alert('Gagal menyimpan data: ' + (error.response?.data?.message || 'Terjadi kesalahan jaringan'));
  } finally {
    isSubmitting.value = false;
  }
}
</script>
