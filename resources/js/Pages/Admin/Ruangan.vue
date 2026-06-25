<template>
  <AdminLayout title="Ruangan & PIN" subtitle="Kelola ruangan ujian">
    <div class="bg-white rounded-2xl elev-1 p-5 fade-in">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-gray-700">Daftar Ruangan ({{ ruangan.length }})</h2>
        <button @click="openCreate" class="btn px-4 py-2.5 rounded-xl gradient-gold text-white text-sm font-semibold">+ Tambah</button>
      </div>
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead><tr><th>No</th><th>Nama</th><th>PIN</th><th>Kapasitas</th><th>Status</th><th class="text-right">Aksi</th></tr></thead>
          <tbody>
            <tr v-for="(r, i) in ruangan" :key="r.id">
              <td>{{ i+1 }}</td>
              <td class="font-semibold">{{ r.nama }}</td>
              <td>
                <span class="font-mono font-bold text-primary cursor-pointer" @click="togglePin(r.id)">
                  {{ visiblePins[r.id] ? r.pin : 'â€¢â€¢â€¢â€¢' }}
                </span>
              </td>
              <td>{{ r.kapasitas }}</td>
              <td><span class="badge" :class="r.aktif ? 'badge-selesai' : 'badge-belum'"><span class="dot" :class="r.aktif ? 'bg-success' : 'bg-gray-400'"></span>{{ r.aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
              <td class="text-right"><div class="flex gap-1.5 justify-end">
                <button @click="openEdit(r)" class="btn text-xs px-2.5 py-1.5 rounded-lg bg-primary/10 text-primary">Edit</button>
                <button @click="confirmDelete(r)" class="btn text-xs px-2.5 py-1.5 rounded-lg bg-danger/10 text-danger">Hapus</button>
              </div></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <Modal :show="showForm" @close="showForm = false">
      <template #header><h3 class="text-lg font-bold">{{ isEditing ? 'Edit Ruangan' : 'Tambah Ruangan' }}</h3></template>
      <form @submit.prevent="save" class="space-y-3">
        <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Nama Ruangan</label><input v-model="form.nama" class="field text-sm" /></div>
        <div><label class="text-xs font-semibold text-gray-500 mb-1 block">PIN (4 digit)</label><input v-model="form.pin" maxlength="4" class="field text-sm font-mono" /></div>
        <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Kapasitas</label><input v-model="form.kapasitas" type="number" class="field text-sm" /></div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="form.aktif" class="rounded" /> Aktif</label>
      </form>
      <template #footer>
        <button @click="showForm = false" class="btn px-4 py-2 rounded-xl bg-gray-100 text-gray-600 text-sm">Batal</button>
        <button @click="save" :disabled="form.processing" class="btn px-4 py-2 rounded-xl gradient-gold text-white text-sm font-semibold">Simpan</button>
      </template>
    </Modal>
    <ConfirmModal :show="showDel" title="Hapus Ruangan" :message="`Yakin hapus ruangan ${delTarget?.nama}?`" @close="showDel = false" @confirm="doDel" />
  </AdminLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Modal from '../../Components/Modal.vue';
import ConfirmModal from '../../Components/ConfirmModal.vue';

const props = defineProps({ ruangan: Array });
const showForm = ref(false); const showDel = ref(false);
const isEditing = ref(false); const editId = ref(null); const delTarget = ref(null);
const visiblePins = reactive({});
const form = useForm({ nama: '', pin: '', kapasitas: 36, aktif: true });
function togglePin(id) { visiblePins[id] = !visiblePins[id]; }
function openCreate() { form.reset(); form.clearErrors(); isEditing.value = false; showForm.value = true; }
function openEdit(r) { form.nama = r.nama; form.pin = r.pin; form.kapasitas = r.kapasitas; form.aktif = !!r.aktif; isEditing.value = true; editId.value = r.id; form.clearErrors(); showForm.value = true; }
function save() {
  if (isEditing.value) form.put(window.route('admin.ruangan.update', editId.value), { onSuccess: () => showForm.value = false, preserveScroll: true });
  else form.post(window.route('admin.ruangan.store'), { onSuccess: () => showForm.value = false, preserveScroll: true });
}
function confirmDelete(r) { delTarget.value = r; showDel.value = true; }
function doDel() { showDel.value = false; if (delTarget.value) useForm({}).delete(window.route('admin.ruangan.destroy', delTarget.value.id), { preserveScroll: true }); }
</script>