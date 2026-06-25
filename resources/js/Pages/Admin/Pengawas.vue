<template>
  <AdminLayout title="Data Pengawas" subtitle="Kelola akun pengawas ujian">
    <div class="bg-white rounded-2xl elev-1 p-5 fade-in">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-gray-700">Daftar Pengawas ({{ pengawas.length }})</h2>
        <button @click="openCreate" class="btn px-4 py-2.5 rounded-xl gradient-gold text-white text-sm font-semibold">+ Tambah</button>
      </div>
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead><tr><th>No</th><th>Nama</th><th>Username</th><th>Ruangan</th><th class="text-right">Aksi</th></tr></thead>
          <tbody>
            <tr v-for="(p, i) in pengawas" :key="p.id">
              <td>{{ i+1 }}</td>
              <td class="font-semibold">{{ p.nama }}</td>
              <td class="font-mono text-sm">{{ p.username }}</td>
              <td>{{ p.ruangan?.nama || '-' }}</td>
              <td class="text-right"><div class="flex gap-1.5 justify-end">
                <button @click="openEdit(p)" class="btn text-xs px-2.5 py-1.5 rounded-lg bg-primary/10 text-primary">Edit</button>
                <button @click="confirmDelete(p)" class="btn text-xs px-2.5 py-1.5 rounded-lg bg-danger/10 text-danger">Hapus</button>
              </div></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <Modal :show="showForm" @close="showForm = false">
      <template #header><h3 class="text-lg font-bold">{{ isEditing ? 'Edit Pengawas' : 'Tambah Pengawas' }}</h3></template>
      <form @submit.prevent="save" class="space-y-3">
        <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Nama</label><input v-model="form.nama" class="field text-sm" /></div>
        <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Username</label><input v-model="form.username" class="field text-sm" /></div>
        <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Password {{ isEditing ? '(kosongkan jika tidak diubah)' : '' }}</label><input v-model="form.password" type="password" class="field text-sm" /></div>
        <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Ruangan</label><select v-model="form.ruangan_id" class="field text-sm"><option value="">Pilih Ruangan</option><option v-for="r in ruangan" :key="r.id" :value="r.id">{{ r.nama }}</option></select></div>
      </form>
      <template #footer>
        <button @click="showForm = false" class="btn px-4 py-2 rounded-xl bg-gray-100 text-gray-600 text-sm">Batal</button>
        <button @click="save" :disabled="form.processing" class="btn px-4 py-2 rounded-xl gradient-gold text-white text-sm font-semibold">Simpan</button>
      </template>
    </Modal>
    <ConfirmModal :show="showDel" title="Hapus Pengawas" :message="`Yakin hapus pengawas ${delTarget?.nama}?`" @close="showDel = false" @confirm="doDel" />
  </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Modal from '../../Components/Modal.vue';
import ConfirmModal from '../../Components/ConfirmModal.vue';

const props = defineProps({ pengawas: Array, ruangan: Array });
const showForm = ref(false); const showDel = ref(false);
const isEditing = ref(false); const editId = ref(null); const delTarget = ref(null);
const form = useForm({ nama: '', username: '', password: '', ruangan_id: '' });
function openCreate() { form.reset(); form.clearErrors(); isEditing.value = false; showForm.value = true; }
function openEdit(p) { form.nama = p.nama; form.username = p.username; form.password = ''; form.ruangan_id = p.ruangan_id || ''; isEditing.value = true; editId.value = p.id; form.clearErrors(); showForm.value = true; }
function save() {
  if (isEditing.value) form.put(window.route('admin.pengawas.update', editId.value), { onSuccess: () => showForm.value = false, preserveScroll: true });
  else form.post(window.route('admin.pengawas.store'), { onSuccess: () => showForm.value = false, preserveScroll: true });
}
function confirmDelete(p) { delTarget.value = p; showDel.value = true; }
function doDel() { showDel.value = false; if (delTarget.value) useForm({}).delete(window.route('admin.pengawas.destroy', delTarget.value.id), { preserveScroll: true }); }
</script>