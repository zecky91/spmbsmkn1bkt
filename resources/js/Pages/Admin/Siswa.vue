<template>
  <AdminLayout title="Data Siswa" subtitle="Kelola data peserta ujian">
    <div class="bg-white rounded-2xl elev-1 p-5 fade-in">
      <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <input v-model="search" placeholder="Cari nama/NISN..." class="field max-w-xs text-sm" />
        <button @click="openCreate" class="btn px-4 py-2.5 rounded-xl gradient-gold text-white text-sm font-semibold">+ Tambah Siswa</button>
      </div>
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead><tr><th>No</th><th>Nama</th><th>NISN</th><th>Asal Sekolah</th><th>Ruangan</th><th>Jur 1</th><th>Jur 2</th><th>Status</th><th>Diinput Oleh</th><th class="text-right">Aksi</th></tr></thead>
          <tbody v-if="paginatedSiswa.length > 0">
            <tr v-for="(s, i) in paginatedSiswa" :key="s.id">
              <td>{{ (currentPage - 1) * perPage + i + 1 }}</td>
              <td class="font-semibold">{{ s.nama }}</td>
              <td class="font-mono text-sm">{{ s.nisn }}</td>
              <td>{{ s.asal_sekolah }}</td>
              <td>{{ s.ruangan?.nama || '-' }}</td>
              <td>{{ s.jurusan1?.kode || '-' }}</td>
              <td>{{ s.jurusan2?.kode || '-' }}</td>
              <td><Badge :status="s.status" /></td>
              <td class="text-sm text-gray-500">{{ s.creator?.nama || '-' }}</td>
              <td class="text-right">
                <div class="flex gap-1.5 justify-end">
                  <button @click="openEdit(s)" class="btn text-xs px-2.5 py-1.5 rounded-lg bg-primary/10 text-primary">Edit</button>
                  <button @click="confirmDelete(s)" class="btn text-xs px-2.5 py-1.5 rounded-lg bg-danger/10 text-danger">Hapus</button>
                </div>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr>
              <td colspan="10" class="text-center py-8 text-gray-400">Tidak ada data siswa ditemukan.</td>
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

    <!-- Create/Edit Modal -->
    <Modal :show="showFormModal" @close="showFormModal = false" max-width="560px">
      <template #header><h3 class="text-lg font-bold text-gray-800">{{ isEditing ? 'Edit Siswa' : 'Tambah Siswa' }}</h3></template>
      <form @submit.prevent="saveForm" class="space-y-3">
        <div class="grid grid-cols-2 gap-3">
          <div class="col-span-2"><label class="text-xs font-semibold text-gray-500 mb-1 block">Nama Lengkap</label><input v-model="form.nama" class="field text-sm" /></div>
          <div><label class="text-xs font-semibold text-gray-500 mb-1 block">NISN (10 digit)</label><input v-model="form.nisn" maxlength="10" class="field text-sm" /><div v-if="form.errors.nisn" class="text-danger text-xs mt-0.5">{{ form.errors.nisn }}</div></div>
          <div><label class="text-xs font-semibold text-gray-500 mb-1 block">NIK (16 digit)</label><input v-model="form.nik" maxlength="16" class="field text-sm" /></div>
          <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Tanggal Lahir</label><input v-model="form.tanggal_lahir" type="date" class="field text-sm" /></div>
          <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Asal Sekolah</label><input v-model="form.asal_sekolah" class="field text-sm" /></div>
          <div v-if="isEditing"><label class="text-xs font-semibold text-gray-500 mb-1 block">Ruangan (opsional)</label><select v-model="form.ruangan_id" class="field text-sm"><option value="">Belum ditentukan</option><option v-for="r in ruangan" :key="r.id" :value="r.id">{{ r.nama }}</option></select></div>
          <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Jurusan 1</label><select v-model="form.jurusan1_id" class="field text-sm"><option value="">Pilih...</option><option v-for="j in jurusan" :key="j.id" :value="j.id">{{ j.nama }}</option></select></div>
          <div class="col-span-2"><label class="text-xs font-semibold text-gray-500 mb-1 block">Jurusan 2 (Opsional)</label><select v-model="form.jurusan2_id" class="field text-sm"><option value="">Tidak ada</option><option v-for="j in jurusan" :key="j.id" :value="j.id">{{ j.nama }}</option></select></div>
          <div v-if="isEditing" class="col-span-2"><label class="text-xs font-semibold text-gray-500 mb-1 block">Status</label><select v-model="form.status" class="field text-sm"><option value="belum_login">Belum Login</option><option value="login">Login</option><option value="proses">Proses</option><option value="selesai">Selesai</option><option value="macet">Macet</option></select></div>
          <div class="flex items-center gap-4 col-span-2">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="form.afirmasi" class="rounded" /> Afirmasi</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="form.prestasi" class="rounded" /> Prestasi</label>
          </div>
        </div>
      </form>
      <template #footer>
        <button @click="showFormModal = false" class="btn px-4 py-2 rounded-xl bg-gray-100 text-gray-600 text-sm">Batal</button>
        <button @click="saveForm" :disabled="form.processing" class="btn px-4 py-2 rounded-xl gradient-gold text-white text-sm font-semibold">{{ form.processing ? 'Menyimpan...' : 'Simpan' }}</button>
      </template>
    </Modal>

    <ConfirmModal :show="showDeleteModal" title="Hapus Siswa" :message="`Yakin ingin menghapus data ${deleteTarget?.nama}?`" @close="showDeleteModal = false" @confirm="doDelete" />
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Modal from '../../Components/Modal.vue';
import Badge from '../../Components/Badge.vue';
import ConfirmModal from '../../Components/ConfirmModal.vue';

const props = defineProps({ siswa: Array, ruangan: Array, jurusan: Array });
const search = ref('');
const showFormModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const editId = ref(null);
const deleteTarget = ref(null);

const form = useForm({ nama: '', nisn: '', nik: '', tanggal_lahir: '', asal_sekolah: '', ruangan_id: '', jurusan1_id: '', jurusan2_id: '', afirmasi: false, prestasi: false, status: 'belum_login' });

const filteredSiswa = computed(() => {
  let list = props.siswa;
  if (search.value) {
    const q = search.value.toLowerCase();
    list = list.filter(s => s.nama.toLowerCase().includes(q) || s.nisn.includes(q));
  }
  return list;
});

const currentPage = ref(1);
const perPage = 15;

const paginatedSiswa = computed(() => {
  const start = (currentPage.value - 1) * perPage;
  return filteredSiswa.value.slice(start, start + perPage);
});

// Reset page to 1 when search changes
import { watch } from 'vue';
watch(search, () => {
  currentPage.value = 1;
});

function openCreate() { form.reset(); form.clearErrors(); isEditing.value = false; editId.value = null; showFormModal.value = true; }
function openEdit(s) {
  form.nama = s.nama; form.nisn = s.nisn; form.nik = s.nik; form.tanggal_lahir = s.tanggal_lahir ? String(s.tanggal_lahir).substring(0, 10) : ''; form.asal_sekolah = s.asal_sekolah;
  form.ruangan_id = s.ruangan_id || ''; form.jurusan1_id = s.jurusan1_id || ''; form.jurusan2_id = s.jurusan2_id || '';
  form.afirmasi = s.afirmasi == 1; form.prestasi = s.prestasi == 1; form.status = s.status || 'belum_login';
  isEditing.value = true; editId.value = s.id; form.clearErrors(); showFormModal.value = true;
}
function saveForm() {
  if (isEditing.value) {
    form.put(window.route('admin.siswa.update', editId.value), { onSuccess: () => showFormModal.value = false, preserveScroll: true });
  } else {
    form.post(window.route('admin.siswa.store'), { onSuccess: () => form.reset(), preserveScroll: true });
  }
}
function confirmDelete(s) { deleteTarget.value = s; showDeleteModal.value = true; }
function doDelete() { showDeleteModal.value = false; if (deleteTarget.value) { useForm({}).delete(window.route('admin.siswa.destroy', deleteTarget.value.id), { preserveScroll: true }); } }
</script>