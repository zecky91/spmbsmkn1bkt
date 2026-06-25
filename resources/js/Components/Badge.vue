<template>
  <span class="badge" :class="badgeClass">
    <span class="dot" :class="dotClass"></span>
    {{ label }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: { type: String, default: 'belum_login' }
});

const statusMap = {
  belum_login: { class: 'badge-belum', dot: 'bg-gray-400', label: 'Belum Login' },
  login: { class: 'badge-login', dot: 'bg-primary', label: 'Login' },
  proses: { class: 'badge-proses', dot: 'bg-warning', label: 'Proses' },
  selesai: { class: 'badge-selesai', dot: 'bg-success', label: 'Selesai' },
  macet: { class: 'badge-macet', dot: 'bg-danger', label: 'Macet' }
};

const config = computed(() => statusMap[props.status] || statusMap.belum_login);
const badgeClass = computed(() => config.value.class);
const dotClass = computed(() => config.value.dot);
const label = computed(() => config.value.label);
</script>