<template>
  <AdminLayout title="Bank Soal" subtitle="Kelola soal ujian per jurusan">
    <div class="bg-white rounded-2xl elev-1 p-5 fade-in">
      <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div class="flex gap-3 items-center">
          <select v-model="filterJurusan" class="field max-w-[200px] text-sm">
            <option value="">Semua Jurusan</option>
            <option v-for="j in jurusan" :key="j.id" :value="j.id">{{ j.nama }}</option>
          </select>
          <span class="text-sm text-gray-400">{{ filteredSoal.length }} soal</span>
        </div>
        <div class="flex gap-2">
          <button @click="showImport = true" class="btn inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary/10 text-primary text-sm font-semibold">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import CSV
          </button>
          <button @click="openCreate" class="btn px-4 py-2.5 rounded-xl gradient-gold text-white text-sm font-semibold">+ Tambah Soal</button>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead><tr><th>No</th><th>Pertanyaan</th><th>Jurusan</th><th>Kunci</th><th class="text-right">Aksi</th></tr></thead>
          <tbody>
            <tr v-for="(s, i) in filteredSoal" :key="s.id">
              <td>{{ i+1 }}</td>
              <td class="max-w-xs truncate">{{ s.pertanyaan }}</td>
              <td><span class="badge badge-login"><span class="dot bg-primary"></span>{{ s.jurusan?.kode }}</span></td>
              <td class="font-mono font-bold" :class="s.jurusan?.teknik_penilaian === 'likert' ? 'text-indigo-600' : 'text-success'">{{ s.jurusan?.teknik_penilaian === 'likert' ? 'Likert' : s.kunci?.toUpperCase() }}</td>
              <td class="text-right"><div class="flex gap-1.5 justify-end">
                <button @click="openPreview(s)" class="btn text-xs px-2.5 py-1.5 rounded-lg bg-gray-100 text-gray-600 inline-flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  Lihat
                </button>
                <button @click="openEdit(s)" class="btn text-xs px-2.5 py-1.5 rounded-lg bg-primary/10 text-primary">Edit</button>
                <button @click="confirmDelete(s)" class="btn text-xs px-2.5 py-1.5 rounded-lg bg-danger/10 text-danger">Hapus</button>
              </div></td>
            </tr>
            <tr v-if="filteredSoal.length === 0"><td colspan="5" class="text-center py-8 text-gray-400">Belum ada soal.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Form Modal -->
    <Modal :show="showForm" @close="showForm = false" max-width="640px">
      <template #header><h3 class="text-lg font-bold">{{ isEditing ? 'Edit Soal' : 'Tambah Soal' }}</h3></template>
      <form @submit.prevent="save" class="space-y-3">
        <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Jurusan</label><select v-model="form.jurusan_id" class="field text-sm"><option value="">Pilih Jurusan</option><option v-for="j in jurusan" :key="j.id" :value="j.id">{{ j.nama }}</option></select></div>
        <div><label class="text-xs font-semibold text-gray-500 mb-1 block">Pertanyaan</label><textarea v-model="form.pertanyaan" rows="3" class="field text-sm"></textarea></div>
        <div class="grid grid-cols-1 gap-2">
          <div v-for="letter in (isLikert ? ['a','b','c','d'] : ['a','b','c','d','e'])" :key="letter"><label class="text-xs font-semibold text-gray-500 mb-1 block">Opsi {{ letter.toUpperCase() }}</label><input v-model="form['opsi_' + letter]" class="field text-sm" /></div>
        </div>
        <div v-if="!isLikert"><label class="text-xs font-semibold text-gray-500 mb-1 block">Kunci Jawaban</label><select v-model="form.kunci" class="field text-sm"><option v-for="l in ['a','b','c','d','e']" :key="l" :value="l">{{ l.toUpperCase() }}</option></select></div>
      </form>
      <template #footer>
        <button @click="showForm = false" class="btn px-4 py-2 rounded-xl bg-gray-100 text-gray-600 text-sm">Batal</button>
        <button @click="save" :disabled="form.processing" class="btn px-4 py-2 rounded-xl gradient-gold text-white text-sm font-semibold">Simpan</button>
      </template>
    </Modal>

    <!-- Preview Modal -->
    <Modal :show="showPreviewModal" @close="showPreviewModal = false" max-width="560px">
      <template #header><h3 class="text-lg font-bold">Preview Soal</h3></template>
      <div v-if="previewSoal">
        <p class="text-sm font-semibold text-gray-800 mb-4">{{ previewSoal.pertanyaan }}</p>
        <div class="space-y-2">
          <div v-for="l in (previewSoal.jurusan?.teknik_penilaian === 'likert' ? ['a','b','c','d'] : ['a','b','c','d','e'])" :key="l" class="flex items-center gap-3 p-2.5 rounded-lg text-sm" :class="(previewSoal.jurusan?.teknik_penilaian !== 'likert' && l === previewSoal.kunci) ? 'bg-green-50 border border-green-200' : 'bg-gray-50'">
            <span class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs" :class="(previewSoal.jurusan?.teknik_penilaian !== 'likert' && l === previewSoal.kunci) ? 'bg-success text-white' : 'bg-gray-200 text-gray-500'">{{ l.toUpperCase() }}</span>
            <span>{{ previewSoal['opsi_' + l] }}</span>
          </div>
        </div>
      </div>
    </Modal>

    <!-- Import Modal -->
    <Modal :show="showImport" @close="showImport = false">
      <template #header><h3 class="text-lg font-bold">Import Soal dari CSV</h3></template>
      <div class="space-y-3">
        <div class="text-sm text-gray-500 space-y-1">
          <p><b>Skor Dikotomi:</b> Jurusan, Pertanyaan, Opsi A, B, C, D, E, Kunci</p>
          <p><b>Skala Likert:</b> Jurusan, Pertanyaan, Opsi A, B, C, D <span class="text-gray-400">(Opsi E &amp; Kunci boleh kosong)</span></p>
          <p class="text-gray-400">Mendukung file <b>.xlsx</b> (Excel) atau <b>.csv</b>.</p>
        </div>
        <a href="/template_import_soal.xlsx" download class="text-primary hover:underline text-sm font-semibold flex items-center gap-1 mb-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          Download Template Excel
        </a>
        <input type="file" ref="csvInput" accept=".csv, .xlsx, .xls" class="field text-sm w-full" />
      </div>
      <template #footer>
        <button @click="showImport = false" class="btn px-4 py-2 rounded-xl bg-gray-100 text-gray-600 text-sm">Batal</button>
        <button @click="doImport" class="btn px-4 py-2 rounded-xl gradient-primary text-white text-sm font-semibold">Import</button>
      </template>
    </Modal>

    <ConfirmModal :show="showDel" title="Hapus Soal" message="Soal ini akan dihapus permanen." @close="showDel = false" @confirm="doDel" />
  </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Modal from '../../Components/Modal.vue';
import ConfirmModal from '../../Components/ConfirmModal.vue';

const props = defineProps({ soal: Array, jurusan: Array });
const filterJurusan = ref(''); const showForm = ref(false); const showDel = ref(false); const showImport = ref(false); const showPreviewModal = ref(false);
const isEditing = ref(false); const editId = ref(null); const delTarget = ref(null); const previewSoal = ref(null); const csvInput = ref(null);
const form = useForm({ jurusan_id: '', pertanyaan: '', opsi_a: '', opsi_b: '', opsi_c: '', opsi_d: '', opsi_e: '', kunci: 'a' });
const filteredSoal = computed(() => filterJurusan.value ? props.soal.filter(s => s.jurusan_id == filterJurusan.value) : props.soal);
function openCreate() { form.reset(); form.clearErrors(); isEditing.value = false; showForm.value = true; }
function openEdit(s) { Object.keys(form.data()).forEach(k => { if (k in s) form[k] = s[k]; }); isEditing.value = true; editId.value = s.id; form.clearErrors(); showForm.value = true; }
function openPreview(s) { previewSoal.value = s; showPreviewModal.value = true; }
const selectedJurusan = computed(() => props.jurusan.find(j => j.id == form.jurusan_id));
const isLikert = computed(() => selectedJurusan.value?.teknik_penilaian === 'likert');

function save() {
  if (isLikert.value) {
    form.opsi_e = '-';
    form.kunci = 'a';
  }
  if (isEditing.value) form.put(window.route('admin.soal.update', editId.value), { onSuccess: () => showForm.value = false, preserveScroll: true });
  else form.post(window.route('admin.soal.store'), { onSuccess: () => showForm.value = false, preserveScroll: true });
}
function confirmDelete(s) { delTarget.value = s; showDel.value = true; }
function doDel() { showDel.value = false; if (delTarget.value) useForm({}).delete(window.route('admin.soal.destroy', delTarget.value.id), { preserveScroll: true }); }
function doImport() {
  const file = csvInput.value?.files?.[0];
  if (!file) {
    alert("Pilih file CSV terlebih dahulu.");
    return;
  }
  const fd = new FormData();
  fd.append('file', file);
  router.post(window.route('admin.soal.import'), fd, { 
    onSuccess: () => { showImport.value = false; csvInput.value.value = ''; },
    onError: (err) => { alert(Object.values(err)[0]); }
  });
}
</script>