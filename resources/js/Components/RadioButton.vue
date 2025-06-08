<template>
  <input 
    type="radio" 
    :checked="checked"
    @change="$emit('update:modelValue', $event.target.value)"
    :value="value"
    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
  >
</template>

<script setup>
defineProps({
  modelValue: String,
  value: String,
  checked: Boolean
});

defineEmits(['update:modelValue']);
</script>