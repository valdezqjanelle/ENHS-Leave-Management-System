<template>
  <div class="max-w-5xl mx-auto bg-white p-6 text-[11px] text-black border-2 border-black font-serif leading-tight">
    <div class="text-center mb-6">
      <h1 class="text-[16px] mt-1 font-semibold">Upload Documents</h1>
      <p class="text-sm text-gray-700 mt-2">This page is for employees to upload their own supporting files only.</p>
    </div>

    <div class="text-black border border-gray-300 p-6 rounded-lg shadow-sm">
      <h2 class="text-base font-semibold mb-3">Document Upload</h2>

      <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
        <input
          type="file"
          ref="fileInput"
          multiple
          accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
          @change="handleFileUpload"
          class="hidden"
        />

        <button
          type="button"
          @click="fileInput?.click()"
          class="px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
        >
          Select Files to Upload
        </button>

        <p class="text-xs text-gray-500 mt-3">
          Allowed file types: PDF, DOC, DOCX, JPG, PNG. Maximum file size: 150MB per file.
        </p>
      </div>

      <div v-if="attachments.length > 0" class="mt-5 space-y-3">
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded text-xs">
          <span class="font-medium">Uploaded Files</span>
          <button
            type="button"
            @click="clearAttachments"
            class="text-blue-600 hover:text-blue-800"
          >
            Clear All
          </button>
        </div>

        <div class="space-y-2">
          <div
            v-for="(file, index) in attachments"
            :key="index"
            class="flex items-center justify-between p-3 bg-gray-100 rounded"
          >
            <div>
              <div class="font-medium text-sm">{{ file.name }}</div>
              <div class="text-[10px] text-gray-600">{{ formatFileSize(file.size) }}</div>
            </div>
            <button
              type="button"
              @click="removeFile(index)"
              class="text-red-600 hover:text-red-800 text-xs"
            >
              Remove
            </button>
          </div>
        </div>
      </div>

      <div v-else class="mt-4 text-sm text-gray-600">
        No files selected yet.
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface Attachment {
  name: string
  size: number
  file: File
}

const attachments = ref<Attachment[]>([])
const fileInput = ref<HTMLInputElement | null>(null)
const maxFileSize = 150 * 1024 * 1024

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const files = target.files

  if (!files) {
    return
  }

  for (let i = 0; i < files.length; i++) {
    const file = files[i]
    if (!file) {
      continue
    }

    if (file.size > maxFileSize) {
      window.alert(`File "${file.name}" exceeds the 150MB limit and was not added.`)
      continue
    }

    attachments.value.push({
      name: file.name,
      size: file.size,
      file
    })
  }

  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const removeFile = (index: number) => {
  attachments.value.splice(index, 1)
}

const clearAttachments = () => {
  attachments.value = []
}

const formatFileSize = (bytes: number) => {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}
</script>
