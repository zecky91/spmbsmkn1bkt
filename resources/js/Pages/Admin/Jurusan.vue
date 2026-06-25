<template>
  <AdminLayout title="Input Jurusan" subtitle="Kelola data jurusan">
    <div class="bg-white rounded-2xl elev-1 p-5 fade-in">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-gray-700">Daftar Jurusan ({{ jurusan.length }})</h2>
        <button @click="openCreate" class="btn px-4 py-2.5 rounded-xl gradient-gold text-white text-sm font-semibold">+ Tambah</button>
      </div>
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead><tr><th>No</th><th>Kode</th><th>Nama</th><th>Icon</th><th>Kuota</th><th>Teknik Penilaian</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
          <tbody>
            <tr v-for="(j, i) in jurusan" :key="j.id">
              <td>{{ i+1 }}</td>
              <td class="font-mono font-bold text-primary">{{ j.kode }}</td>
              <td class="font-semibold">{{ j.nama }}</td>
              <td class="text-xl">{{ j.icon }}</td>
              <td>{{ j.kuota }}</td>
              <td>
                <span class="badge" :class="j.teknik_penilaian === 'likert' ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-emerald-50 text-emerald-700 border border-emerald-100'">
                  <span class="dot" :class="j.teknik_penilaian === 'likert' ? 'bg-indigo-500' : 'bg-success'"></span>
                  {{ j.teknik_penilaian === 'likert' ? 'Skala Likert' : 'Skor Dikotomi' }}
                </span>
              </td>
              <td><span class="badge" :class="j.aktif ? 'badge-selesai' : 'badge-belum'"><span class="dot" :class="j.aktif ? 'bg-success' : 'bg-gray-400'"></span>{{ j.aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
              <td class="text-right"><div class="flex gap-1.5 justify-end"><button @click="openEdit(j)" class="btn text-xs px-2.5 py-1.5 rounded-lg bg-primary/10 text-primary">Edit</button><button @click="confirmDelete(j)" class="btn text-xs px-2.5 py-1.5 rounded-lg bg-danger/10 text-danger">Hapus</button></div></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <Modal :show="showForm" @close="showForm = false">
      <template #header><h3 class="text-lg font-bold">{{ isEditing ? 'Edit Jurusan' : 'Tambah Jurusan' }}</h3></template>
      <form @submit.prevent="save" class="space-y-3">
        <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Kode</label><input v-model="form.kode" class="field text-sm" placeholder="contoh: TKJ" /></div>
        <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Nama</label><input v-model="form.nama" class="field text-sm" /></div>
        <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Icon (emoji)</label><input v-model="form.icon" class="field text-sm" placeholder="💻" /></div>
        <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Kuota</label><input v-model="form.kuota" type="number" class="field text-sm" /></div>
        <div>
          <label class="text-xs font-semibold text-gray-500 mb-1 block">Teknik Penilaian</label>
          <select v-model="form.teknik_penilaian" class="field text-sm">
            <option value="dikotomi">Skor Dikotomi (Benar = 1, Salah = 0)</option>
            <option value="likert">Skala Likert (A=4, B=3, C=2, D=1)</option>
          </select>
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="form.aktif" class="rounded" /> Aktif</label>
      </form>
      <template #footer>
        <button @click="showForm = false" class="btn px-4 py-2 rounded-xl bg-gray-100 text-gray-600 text-sm">Batal</button>
        <button @click="save" :disabled="form.processing" class="btn px-4 py-2 rounded-xl gradient-gold text-white text-sm font-semibold">Simpan</button>
      </template>
    </Modal>
    <ConfirmModal :show="showDel" title="Hapus Jurusan" :message="`Yakin hapus jurusan ${delTarget?.nama}?`" @close="showDel = false" @confirm="doDel" />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Modal from '../../Components/Modal.vue';
import ConfirmModal from '../../Components/ConfirmModal.vue';

const props = defineProps({ jurusan: Array });
const showForm = ref(false); const showDel = ref(false);
const isEditing = ref(false); const editId = ref(null); const delTarget = ref(null);
const form = useForm({ kode: '', nama: '', icon: '', kuota: 36, aktif: true, teknik_penilaian: 'dikotomi' });
function openCreate() { form.reset(); form.clearErrors(); form.teknik_penilaian = 'dikotomi'; isEditing.value = false; showForm.value = true; }
function openEdit(j) { form.kode = j.kode; form.nama = j.nama; form.icon = j.icon; form.kuota = j.kuota; form.aktif = !!j.aktif; form.teknik_penilaian = j.teknik_penilaian || 'dikotomi'; isEditing.value = true; editId.value = j.id; form.clearErrors(); showForm.value = true; }
function save() {
  if (isEditing.value) form.put(window.route('admin.jurusan.update', editId.value), { onSuccess: () => showForm.value = false, preserveScroll: true });
  else form.post(window.route('admin.jurusan.store'), { onSuccess: () => showForm.value = false, preserveScroll: true });
}
function confirmDelete(j) { delTarget.value = j; showDel.value = true; }
function doDel() { showDel.value = false; if (delTarget.value) useForm({}).delete(window.route('admin.jurusan.destroy', delTarget.value.id), { preserveScroll: true }); }
</script>