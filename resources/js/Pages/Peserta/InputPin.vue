<template>
  <div class="w-full max-w-md fade-in">
    <div class="bg-white rounded-2xl elev-3 p-8">
      <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-2xl gradient-gold flex items-center justify-center text-white font-extrabold text-2xl mx-auto mb-4">P</div>
        <h1 class="text-2xl font-extrabold text-primary mb-1">PPDB Online Exam</h1>
        <p class="text-sm text-gray-400">Sistem Ujian Seleksi Penerimaan Peserta Didik Baru</p>
      </div>

      <div class="text-center mb-6">
        <h2 class="text-lg font-bold text-gray-700">Masuk ke Ruangan Ujian</h2>
        <p class="text-sm text-gray-400 mt-1">Masukkan PIN 4 digit dari Pengawas</p>
      </div>

      <form @submit.prevent="submit">
        <PinInput v-model="form.pin" :disabled="form.processing" class="mb-6" />
        
        <div v-if="form.errors.pin" class="text-danger text-sm text-center mb-4">{{ form.errors.pin }}</div>

        <button
          type="submit"
          :disabled="form.processing || form.pin.length < 4"
          class="btn w-full py-3.5 rounded-xl text-white font-bold text-base gradient-gold hover:opacity-90 disabled:opacity-50 transition"
        >
          <span v-if="form.processing">Memverifikasi...</span>
          <span v-else>Verifikasi PIN</span>
        </button>
      </form>

      <p class="text-xs text-gray-400 text-center mt-6">
        Hubungi pengawas jika belum menerima PIN ruangan.
      </p>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';
import PinInput from '../../Components/PinInput.vue';

defineOptions({ layout: GuestLayout });

const form = useForm({ pin: '' });

function submit() {
  form.post(window.route('pin.verify'));
}
</script>