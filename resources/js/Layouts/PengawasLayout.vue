<template>
  <div class="min-h-screen bg-[#F5F5F5] text-[#2b3445] flex">
    <!-- Mobile Overlay -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/50 z-40 lg:hidden transition-opacity"></div>

    <!-- Sidebar -->
    <aside 
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
      class="fixed left-0 top-0 z-50 flex flex-col w-64 shrink-0 gradient-primary min-h-screen px-4 py-6 text-white transition-transform duration-300 ease-in-out lg:translate-x-0"
    >
      <div class="flex items-center gap-3 px-2 mb-8">
        <div class="w-10 h-10 rounded-xl gradient-gold flex items-center justify-center text-white font-extrabold text-lg">P</div>
        <div class="flex-1">
          <div class="text-white font-bold leading-tight">PPDB Exam</div>
          <div class="text-[11px] text-white/60">Panel Pengawas</div>
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden text-white/70 hover:text-white p-1">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <div class="text-[11px] uppercase tracking-wider text-white/40 px-3 mb-2">Menu</div>
      <nav class="flex flex-col gap-1.5 flex-1">
        <Link
          :href="route('pengawas.dashboard')"
          class="sidebar-link"
          :class="{ active: $page.component.startsWith('Pengawas/Dashboard') }"
          @click="sidebarOpen = false"
        >
          <IconGrid class="w-5 h-5 shrink-0" />
          <span>Monitoring Ruangan</span>
        </Link>
      </nav>
      <div class="mt-auto pt-6 border-t border-white/10">
        <div class="bg-white/8 rounded-2xl p-3 flex items-center gap-3 mb-3">
          <div class="w-9 h-9 rounded-full bg-[#B8862E] flex items-center justify-center text-white font-bold text-sm">
            {{ getInitials($page.props.auth.pengawas?.nama) }}
          </div>
          <div class="leading-tight">
            <div class="text-white text-sm font-semibold truncate max-w-[130px]">{{ $page.props.auth.pengawas?.nama || 'Pengawas' }}</div>
            <div class="text-[11px] text-white/50">{{ $page.props.auth.pengawas?.ruangan?.nama || 'Pengawas' }}</div>
          </div>
        </div>
        <Link :href="route('pengawas.logout')" method="post" as="button" type="button" class="sidebar-link w-full text-left text-white/70 hover:text-white">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>
          <span>Keluar</span>
        </Link>
      </div>
    </aside>

    <!-- Main Content Container -->
    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen w-full">
      <!-- Topbar -->
      <header class="bg-white elev-1 rounded-2xl px-4 sm:px-5 py-3 sm:py-4 flex items-center justify-between m-3 sm:m-4 lg:mx-6 lg:mt-6 lg:mb-4 fade-in">
        <div class="flex items-center gap-3">
          <button @click="sidebarOpen = true" class="lg:hidden text-[#1E3A5F] p-1 -ml-1">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
          </button>
          <div>
            <h1 class="text-lg sm:text-xl font-extrabold text-[#1E3A5F]">{{ title }}</h1>
            <p class="text-xs sm:text-sm text-gray-500">{{ subtitle }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2 sm:gap-3">
          <span class="hidden sm:flex items-center gap-2 text-xs font-semibold text-success bg-green-50 px-3 py-1.5 rounded-full">
            <span class="dot bg-[#4CAF50] animate-pulse"></span> Live • polling 5s
          </span>
          <span class="flex sm:hidden items-center">
            <span class="dot bg-[#4CAF50] animate-pulse"></span>
          </span>
          <span class="hidden md:block text-sm font-mono text-gray-500">{{ time }}</span>
        </div>
      </header>

      <!-- Content Area -->
      <main class="flex-1 px-3 sm:px-4 lg:px-6 pb-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
  title: { type: String, default: 'Monitoring Ruangan' },
  subtitle: { type: String, default: 'Pantau peserta ujian di ruangan Anda' }
});

const sidebarOpen = ref(false);
const time = ref('');
let intervalId;

function getInitials(name) {
  if (!name) return 'PW';
  return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
}

onMounted(() => {
  const tick = () => {
    time.value = new Date().toLocaleTimeString('id-ID');
  };
  tick();
  intervalId = setInterval(tick, 1000);
});

onUnmounted(() => {
  clearInterval(intervalId);
});

// Simple inline IconGrid
import { h } from 'vue';
const IconGrid = () => h('svg', { fill: 'none', stroke: 'currentColor', 'stroke-width': 2, viewBox: '0 0 24 24' }, [
  h('rect', { x: 3, y: 3, width: 7, height: 7, rx: 1.5 }),
  h('rect', { x: 14, y: 3, width: 7, height: 7, rx: 1.5 }),
  h('rect', { x: 3, y: 14, width: 7, height: 7, rx: 1.5 }),
  h('rect', { x: 14, y: 14, width: 7, height: 7, rx: 1.5 })
]);
</script>
