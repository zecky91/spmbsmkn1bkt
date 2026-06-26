<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({});

const restoreForm = useForm({
    backup_file: null,
});

const fileInput = ref(null);
const isUploading = ref(false);

const handleFileUpload = (e) => {
    restoreForm.backup_file = e.target.files[0];
};

const submitRestore = () => {
    if (!restoreForm.backup_file) {
        alert('Pilih file .sql terlebih dahulu!');
        return;
    }

    if (!confirm('PERINGATAN KRITIKAL: Tindakan ini akan MENGHAPUS semua data saat ini dan menggantinya dengan data dari file backup. Apakah Anda 100% yakin ingin melanjutkan?')) {
        return;
    }

    isUploading.value = true;
    restoreForm.post(route('admin.backup.upload'), {
        preserveScroll: true,
        onSuccess: () => {
            isUploading.value = false;
            restoreForm.reset('backup_file');
            if (fileInput.value) fileInput.value.value = '';
            alert('Database berhasil dipulihkan!');
        },
        onError: () => {
            isUploading.value = false;
        },
        onFinish: () => {
            isUploading.value = false;
        }
    });
};
</script>

<template>
    <Head title="Backup & Restore Data" />

    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Backup & Restore Database</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Panel Backup -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">Backup Data (Unduh SQL)</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                Unduh seluruh data aplikasi saat ini (Siswa, Nilai, Soal, dll) ke dalam satu file <code>.sql</code>. 
                                Sangat disarankan untuk rutin melakukan backup.
                            </p>
                        </header>

                        <div class="mt-6 flex items-center gap-4">
                            <a :href="route('admin.backup.download')" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Unduh Backup Sekarang
                            </a>
                        </div>
                    </section>
                </div>

                <!-- Panel Restore -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg border-t-4 border-red-500">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 text-red-600">Restore Data (Unggah SQL)</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                Unggah file <code>.sql</code> yang sebelumnya Anda unduh untuk memulihkan data. <br>
                                <strong class="text-red-500">Peringatan:</strong> Proses ini akan menghapus data yang ada saat ini dan menggantinya dengan data dari file backup!
                            </p>
                        </header>

                        <form @submit.prevent="submitRestore" class="mt-6 space-y-6">
                            <div>
                                <label class="block font-medium text-sm text-gray-700" for="backup_file">File SQL Backup</label>
                                <input 
                                    type="file" 
                                    id="backup_file" 
                                    ref="fileInput"
                                    accept=".sql"
                                    @change="handleFileUpload"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm p-2 border"
                                    required
                                >
                                <p v-if="restoreForm.errors.backup_file" class="text-sm text-red-600 mt-2">{{ restoreForm.errors.backup_file }}</p>
                            </div>

                            <div class="flex items-center gap-4">
                                <button 
                                    type="submit" 
                                    :disabled="restoreForm.processing || isUploading"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50">
                                    <svg v-if="isUploading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    {{ isUploading ? 'Memulihkan Data...' : 'Mulai Restore Data' }}
                                </button>
                            </div>
                        </form>
                    </section>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>
