<template>
  <Teleport to="body">
    <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex min-h-screen items-center justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="close"></div>

        <!-- Modal panel -->
        <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                  {{ title }}
                </h3>
                <slot name="content"></slot>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
            <slot name="footer"></slot>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
defineProps<{
  isOpen: boolean
  title: string
}>()

const emit = defineEmits<{
  close: []
}>()

const close = () => {
  emit('close')
}
</script>