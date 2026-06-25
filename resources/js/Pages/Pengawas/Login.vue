<template>
  <div class="w-full max-w-md fade-in">
    <div class="bg-white rounded-2xl elev-3 p-8">
      <div class="text-center mb-6">
        <div class="w-14 h-14 rounded-2xl gradient-primary flex items-center justify-center text-white font-extrabold text-xl mx-auto mb-3">
          <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        </div>
        <h1 class="text-xl font-extrabold text-primary">Login Pengawas</h1>
        <p class="text-sm text-gray-400 mt-1">Masuk untuk memantau ruangan ujian</p>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-semibold text-gray-600 mb-1.5">Username</label>
          <input v-model="form.username" type="text" placeholder="Masukkan username" class="field" autocomplete="username" />
          <div v-if="form.errors.username" class="text-danger text-xs mt-1">{{ form.errors.username }}</div>
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-600 mb-1.5">Password</label>
          <input v-model="form.password" type="password" placeholder="Masukkan password" class="field" autocomplete="current-password" />
          <div v-if="form.errors.password" class="text-danger text-xs mt-1">{{ form.errors.password }}</div>
        </div>
        <button type="submit" :disabled="form.processing" class="btn w-full py-3.5 rounded-xl text-white font-bold gradient-primary hover:opacity-90 disabled:opacity-50 transition">
          {{ form.processing ? 'Memproses...' : 'Masuk' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import GuestLayout from '../../Layouts/GuestLayout.vue';

defineOptions({ layout: GuestLayout });

const form = useForm({ username: '', password: '' });

function submit() {
  form.post(window.route('pengawas.login.post'), {
    onFinish: () => form.reset('password')
  });
}
</script>