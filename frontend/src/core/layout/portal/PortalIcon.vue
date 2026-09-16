<script setup lang="ts">
withDefaults(defineProps<{ name: string; size?: number | string }>(), {
  size: 20,
})

// Inline icon set shared by all three portal layouts. Paths are drawn with
// currentColor so each portal theme tints them automatically.
</script>

<template>
  <svg
    fill="none"
    stroke="currentColor"
    viewBox="0 0 24 24"
    :width="size"
    :height="size"
    aria-hidden="true"
  >
    <path
      v-if="name === 'home'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h3a1 1 0 001-1V10"
    />
    <path
      v-else-if="name === 'calendar'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
    />
    <path
      v-else-if="name === 'users'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
    />
    <path
      v-else-if="name === 'file'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
    />
    <path
      v-else-if="name === 'tooth'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
    />
    <path
      v-else-if="name === 'layers'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M4 7l8-4 8 4-8 4-8-4zM4 12l8 4 8-4M4 17l8 4 8-4"
    />
    <path
      v-else-if="name === 'invoice'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"
    />
    <path
      v-else-if="name === 'box'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
    />
    <path
      v-else-if="name === 'pharmacy'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
    />
    <path
      v-else-if="name === 'lab'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
    />
    <path
      v-else-if="name === 'xray'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M6 3L3 6l18 18M3 6v12a2 2 0 002 2h14a2 2 0 002-2V6"
    />
    <path
      v-else-if="name === 'doctor'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
    />
    <path
      v-else-if="name === 'employees'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
    />
    <path
      v-else-if="name === 'branch'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
    />
    <path
      v-else-if="name === 'org'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-4h6v4M10 10h.01M14 10h.01M10 14h.01M14 14h.01"
    />
    <path
      v-else-if="name === 'shield'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
    />
    <path
      v-else-if="name === 'crm'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
    />
    <path
      v-else-if="name === 'chart'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
    />
    <path
      v-else-if="name === 'ai'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"
    />
    <path
      v-else-if="name === 'plug'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M8 9l3 3-3 3m5 0h3M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"
    />
    <path
      v-else-if="name === 'card'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M3 10h18M7 15h3m-6 5h14a2 2 0 002-2V6a2 2 0 00-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2z"
    />
    <path
      v-else-if="name === 'settings'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
    />
    <path
      v-else-if="name === 'queue'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M4 6h16M4 12h16M4 18h10"
    />
    <path
      v-else-if="name === 'cash'"
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v1m0 8v1m0-1v-1m0-8c-1.11 0-2.08.402-2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
    />
    <path
      v-else
      stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
      d="M4 6h16M4 12h16M4 18h16"
    />
  </svg>
</template>
