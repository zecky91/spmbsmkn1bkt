<template>
  <div class="min-h-screen bg-[#F5F5F5] text-[#2b3445] flex">
    <!-- Sidebar -->
    <aside class="hidden lg:flex flex-col w-64 shrink-0 gradient-primary min-h-screen px-4 py-6 fixed left-0 top-0 text-white z-50">
      <div class="flex items-center gap-3 px-2 mb-8">
        <div class="w-10 h-10 rounded-xl gradient-gold flex items-center justify-center text-white font-extrabold text-lg">P</div>
        <div>
          <div class="text-white font-bold leading-tight">PPDB Exam</div>
          <div class="text-[11px] text-white/60">Panel Admin</div>
        </div>
      </div>
      <div class="text-[11px] uppercase tracking-wider text-white/40 px-3 mb-2">Menu</div>
      <nav class="flex flex-col gap-1.5 flex-1">
        <Link
          v-for="item in menuItems"
          :key="item.route"
          :href="route(item.route)"
          class="sidebar-link"
          :class="{ active: $page.component.startsWith(item.componentPrefix) }"
        >
          <component :is="item.icon" class="w-5 h-5 shrink-0" />
          <span>{{ item.label }}</span>
        </Link>
      </nav>
      <div class="mt-auto pt-6 border-t border-white/10">
        <div class="bg-white/8 rounded-2xl p-3 flex items-center gap-3 mb-3">
          <div class="w-9 h-9 rounded-full bg-[#B8862E] flex items-center justify-center text-white font-bold text-sm">
            {{ getInitials($page.props.auth.admin?.nama) }}
          </div>
          <div class="leading-tight">
            <div class="text-white text-sm font-semibold truncate max-w-[130px]">{{ $page.props.auth.admin?.nama || 'Admin' }}</div>
            <div class="text-[11px] text-white/50">Administrator</div>
          </div>
        </div>
        <Link :href="route('admin.logout')" method="post" as="button" type="button" class="sidebar-link w-full text-left text-white/70 hover:text-white">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/></svg>
          <span>Keluar</span>
        </Link>
      </div>
    </aside>

    <!-- Main Content Container -->
    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">
      <!-- Topbar -->
      <header class="bg-white elev-1 rounded-2xl px-5 py-4 flex items-center justify-between m-4 lg:mx-6 lg:mt-6 lg:mb-4 fade-in">
        <div>
          <h1 class="text-xl font-extrabold text-[#1E3A5F]">{{ title }}</h1>
          <p class="text-sm text-gray-500">{{ subtitle }}</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="flex items-center gap-2 text-xs font-semibold text-success bg-green-50 px-3 py-1.5 rounded-full">
            <span class="dot bg-[#4CAF50] animate-pulse"></span> Live • polling 5s
          </span>
          <span class="hidden sm:block text-sm font-mono text-gray-500">{{ time }}</span>
        </div>
      </header>

      <!-- Content Area -->
      <main class="flex-1 px-4 lg:px-6 pb-6">
        <slot />
      </main>
    </div>
    <Toast />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Toast from '../Components/Toast.vue';

defineProps({
  title: { type: String, default: 'Dashboard Admin' },
  subtitle: { type: String, default: 'PPDB Online Exam System' }
});

const time = ref('');
let intervalId;

// Simple inline icons — must be declared BEFORE menuItems to avoid TDZ
import { h } from 'vue';
const IconGrid = () => h('svg', { fill: 'none', stroke: 'currentColor', 'stroke-width': 2, viewBox: '0 0 24 24' }, [
  h('rect', { x: 3, y: 3, width: 7, height: 7, rx: 1.5 }),
  h('rect', { x: 14, y: 3, width: 7, height: 7, rx: 1.5 }),
  h('rect', { x: 3, y: 14, width: 7, height: 7, rx: 1.5 }),
  h('rect', { x: 14, y: 14, width: 7, height: 7, rx: 1.5 })
]);
const IconUsers = () => h('svg', { fill: 'none', stroke: 'currentColor', 'stroke-width': 2, viewBox: '0 0 24 24' }, [
  h('path', { d: 'M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2' }),
  h('circle', { cx: 9, cy: 7, r: 4 }),
  h('path', { d: 'M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75' })
]);
const IconLayers = () => h('svg', { fill: 'none', stroke: 'currentColor', 'stroke-width': 2, viewBox: '0 0 24 24' }, [
  h('polygon', { points: '12 2 2 7 12 12 22 7 12 2' }),
  h('polyline', { points: '2 17 12 22 22 17' }),
  h('polyline', { points: '2 12 12 17 22 12' })
]);
const IconBook = () => h('svg', { fill: 'none', stroke: 'currentColor', 'stroke-width': 2, viewBox: '0 0 24 24' }, [
  h('path', { d: 'M4 19.5A2.5 2.5 0 0 1 6.5 17H20' }),
  h('path', { d: 'M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z' })
]);
const IconDoor = () => h('svg', { fill: 'none', stroke: 'currentColor', 'stroke-width': 2, viewBox: '0 0 24 24' }, [
  h('path', { d: 'M13 4v16M3 4h14v16H3z' }),
  h('circle', { cx: 10, cy: 12, r: 1, fill: 'currentColor' })
]);
const IconShield = () => h('svg', { fill: 'none', stroke: 'currentColor', 'stroke-width': 2, viewBox: '0 0 24 24' }, [
  h('path', { d: 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z' })
]);

const allowedWawancaraUsers = ['ahmad_zaki', 'yulia_sandra', 'mardayoni12'];
const canWawancara = computed(() => {
  const admin = usePage().props.auth?.admin;
  return admin && allowedWawancaraUsers.includes(admin.username);
});

const menuItems = computed(() => {
  const items = [
    { label: 'Dashboard', route: 'admin.dashboard', componentPrefix: 'Admin/Dashboard', icon: IconGrid },
    { label: 'Data Siswa', route: 'admin.siswa.index', componentPrefix: 'Admin/Siswa', icon: IconUsers },
    { label: 'Input Jurusan', route: 'admin.jurusan.index', componentPrefix: 'Admin/Jurusan', icon: IconLayers },
    { label: 'Bank Soal', route: 'admin.soal.index', componentPrefix: 'Admin/Soal', icon: IconBook },
    { label: 'Ruangan & PIN', route: 'admin.ruangan.index', componentPrefix: 'Admin/Ruangan', icon: IconDoor },
    { label: 'Pengawas', route: 'admin.pengawas.index', componentPrefix: 'Admin/Pengawas', icon: IconShield },
  ];
  
  if (canWawancara.value) {
    items.push({ label: 'Input Wawancara', route: 'admin.wawancara.index', componentPrefix: 'Admin/Wawancara', icon: IconUsers });
    items.push({ label: 'Rangking PPDB', route: 'admin.rangking.index', componentPrefix: 'Admin/Rangking', icon: IconLayers });
  }
  
  return items;
});

function getInitials(name) {
  if (!name) return 'AD';
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
</script>
