<template>
  <div class="w-full max-w-md fade-in">
    <div class="bg-white rounded-2xl elev-3 p-8">
      <div class="text-center mb-6">
        <div class="w-14 h-14 rounded-2xl gradient-gold flex items-center justify-center text-white font-extrabold text-xl mx-auto mb-3">P</div>
        <h1 class="text-xl font-extrabold text-primary">Login Peserta</h1>
        <span class="inline-flex items-center gap-2 text-xs font-semibold bg-primary/10 text-primary px-3 py-1.5 rounded-full mt-2">
          <span class="dot bg-primary"></span>
          {{ ruangan?.nama || 'Ruangan' }}
        </span>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-gray-600 mb-1.5">NISN</label>
          <input v-model="form.nisn" type="text" inputmode="numeric" maxlength="10" placeholder="Masukkan 10 digit NISN" class="field" />
          <div v-if="form.errors.nisn" class="text-danger text-xs mt-1">{{ form.errors.nisn }}</div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-600 mb-1.5">Tanggal Lahir</label>
          <input v-model="form.tanggal_lahir" type="date" class="field" />
          <div v-if="form.errors.tanggal_lahir" class="text-danger text-xs mt-1">{{ form.errors.tanggal_lahir }}</div>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="btn w-full py-3.5 rounded-xl text-white font-bold gradient-gold hover:opacity-90 disabled:opacity-50 transition mt-2"
        >
          {{ form.processing ? 'Memproses...' : 'Masuk' }}
        </button>
      </form>

      <div class="text-center mt-5">
        <Link :href="route('pin.show')" class="inline-flex items-center gap-1 text-sm text-primary font-semibold hover:underline">&larr; Ganti Ruangan</Link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

defineOptions({ layout: GuestLayout });

defineProps({ ruangan: Object });

const form = useForm({
  nisn: '',
  tanggal_lahir: ''
});

function submit() {
  form.post(window.route('peserta.login.post'));
}
</script>