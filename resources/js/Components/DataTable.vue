<template>
  <div>
    <div v-if="searchable" class="mb-4">
      <input v-model="search" :placeholder="searchPlaceholder" class="field max-w-sm" />
    </div>
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead>
          <tr>
            <th v-for="col in columns" :key="col.key" :class="col.class">{{ col.label }}</th>
            <th v-if="$slots.actions" class="text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, idx) in filteredData" :key="row.id || idx">
            <td v-for="col in columns" :key="col.key" :class="col.class">
              <slot :name="'cell-' + col.key" :row="row" :value="row[col.key]">
                {{ row[col.key] }}
              </slot>
            </td>
            <td v-if="$slots.actions" class="text-right">
              <slot name="actions" :row="row" />
            </td>
          </tr>
          <tr v-if="filteredData.length === 0">
            <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="text-center py-8 text-gray-400">
              <slot name="empty">Tidak ada data ditemukan.</slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  columns: { type: Array, required: true },
  data: { type: Array, default: () => [] },
  searchable: { type: Boolean, default: false },
  searchPlaceholder: { type: String, default: 'Cari...' },
  searchKeys: { type: Array, default: () => [] }
});

const search = ref('');

const filteredData = computed(() => {
  if (!search.value || !props.searchable) return props.data;
  const q = search.value.toLowerCase();
  const keys = props.searchKeys.length ? props.searchKeys : props.columns.map(c => c.key);
  return props.data.filter(row =>
    keys.some(k => String(row[k] || '').toLowerCase().includes(q))
  );
});
</script>