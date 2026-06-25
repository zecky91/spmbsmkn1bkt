<template>
  <div class="min-h-screen flex items-center justify-center p-4 auth-bg">
    <slot />
    <Toast />
  </div>
</template>

<script setup>
import Toast from '../Components/Toast.vue';
</script>
