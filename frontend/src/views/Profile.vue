<template>
  <div class="records-shell p-8 min-h-screen space-y-6">
    <div class="neo-card p-8">
      <div class="flex flex-col sm:flex-row sm:items-center gap-6">
        <div class="flex flex-col items-center gap-2 shrink-0">
        <div
          class="w-28 h-28 bg-blue-600 rounded-full flex items-center justify-center text-white text-4xl font-semibold shrink-0 overflow-hidden"
        >
          <img v-if="photoSource" :src="photoSource" alt="Your profile photo" class="employee-profile-image" @error="photoFailed = true" />
          <span v-else>{{ initials || 'U' }}</span>
        </div>

          <button type="button" class="edit-photo-button" :aria-expanded="photoEditorOpen" aria-controls="profile-photo-editor" :disabled="photoBusy" @click="togglePhotoEditor">
            <Pencil class="w-4 h-4" aria-hidden="true" />
            Edit Photo
          </button>
        </div>

        <div class="flex-1 min-w-0">
          <h3 class="text-2xl font-semibold text-[var(--text)] break-words">
            {{ fullName }}
          </h3>

          <p class="text-sm text-[var(--text-muted)] mt-1">
            {{ employee.position || "Employee" }}
          </p>

          <span
            v-if="employee.employment_status"
            :class="
              normalizeEmploymentStatus(employee.employment_status) === 'active'
                ? 'bg-green-50 text-green-700'
                : 'bg-slate-100 text-slate-600'
            "
            class="inline-block mt-3 px-3 py-1 rounded-full text-xs"
          >
            {{ employee.employment_status }}
          </span>
        </div>

        <div
          class="sm:text-right sm:pl-6 sm:border-l border-t sm:border-t-0 border-[#cbd8e8] pt-5 sm:pt-0"
        >
          <p class="text-xs text-[var(--text-muted)]">
            Employee Code
          </p>

          <p class="text-sm text-[var(--text)] font-mono mt-1">
            {{ employee.employee_code || "—" }}
          </p>
        </div>
      </div>
    <section v-if="photoEditorOpen" id="profile-photo-editor" class="photo-editor mt-6" aria-labelledby="profile-photo-title">
      <h3 id="profile-photo-title" class="text-lg font-semibold text-[var(--text)] mb-2">Profile Photo</h3>
      <p class="text-sm text-[var(--text-muted)] mb-4">Choose a photo to preview above, then save it to your profile.</p>
      <label for="profile-photo-file" class="block text-sm font-medium text-[var(--text)] mb-2">Choose a photo</label>
      <input id="profile-photo-file" ref="photoInput" type="file" accept="image/jpeg,image/png,image/webp" :disabled="photoBusy" @change="selectPhoto" class="photo-file-input" />
      <p class="text-sm text-[var(--text-muted)] mt-2">JPG, PNG or WebP. Maximum 2 MB and 4096 × 4096 pixels.</p>
      <div v-if="selectedPhoto" class="crop-controls">
        <p class="text-sm text-[var(--text-muted)] mb-2">Drag the photo and adjust zoom. The area inside the circle will appear on your profile.</p>
        <div class="crop-stage">
          <canvas ref="cropCanvas" width="280" height="280" aria-label="Profile photo crop preview" @pointerdown="startCropDrag" @pointermove="moveCropDrag" @pointerup="endCropDrag" @pointercancel="endCropDrag"></canvas>
          <div class="crop-circle" aria-hidden="true"></div>
        </div>
        <label for="photo-zoom" class="crop-zoom-label">Zoom</label>
        <input id="photo-zoom" type="range" min="1" max="3" step="0.01" v-model.number="cropZoom" :disabled="photoBusy || !cropReady" @input="drawCrop" />
        <button type="button" class="photo-cancel" :disabled="photoBusy || !cropReady" @click="resetCrop">Reset Position</button>
      </div>
      <div class="flex flex-wrap gap-2 mt-4">
        <button type="button" :disabled="!selectedPhoto || !cropReady || photoBusy" @click="savePhoto" class="photo-save">{{ photoBusy ? 'Please wait…' : 'Save Photo' }}</button>
        <button v-if="selectedPhoto" type="button" :disabled="photoBusy" @click="clearPhotoSelection" class="photo-cancel">Cancel</button>
        <button v-if="hasPhoto" type="button" :disabled="photoBusy" @click="deletePhoto" class="photo-remove">Remove Photo</button>
      </div>
      <p v-if="photoError" role="alert" class="text-red-600 mt-3">{{ photoError }}</p>
    </section>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
      <div class="neo-card p-6">
        <div class="flex items-center gap-2 mb-5">
          <div
            class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center shrink-0"
          >
            <Building2 class="w-4 h-4" />
          </div>

          <div>
            <h3 class="text-lg font-medium text-[var(--text)]">
              Employee Information
            </h3>

            <p class="text-xs text-[var(--text-muted)]">
              These details are managed by the administrator.
            </p>
          </div>
        </div>

        <div
          class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-5 border border-[#c8d8eb] rounded-xl p-5"
        >
          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Employee Code
            </p>

            <p class="font-medium text-[var(--text)] mt-1 break-words">
              {{ employee.employee_code || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Email
            </p>

            <p class="font-medium text-[var(--text)] mt-1 break-all">
              {{ employee.email || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Personnel Type
            </p>

            <p class="font-medium text-[var(--text)] mt-1 break-words">
              {{ employee.personnel_type || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Employment Category
            </p>

            <p class="font-medium text-[var(--text)] mt-1 break-words">
              {{ employee.employment_category || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Department
            </p>

            <p class="font-medium text-[var(--text)] mt-1 break-words">
              {{ employee.department_name || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Position
            </p>

            <p class="font-medium text-[var(--text)] mt-1 break-words">
              {{ employee.position || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Level
            </p>

            <p class="font-medium text-[var(--text)] mt-1">
              {{ employee.level || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Supervisor
            </p>

            <p class="font-medium text-[var(--text)] mt-1 break-words">
              {{ supervisorName }}
            </p>
          </div>

          <!-- FIX: use resolvedSalaryGrade instead of employee.salary_grade directly,
               so the value still shows even if the profile API omits it but returns position_id -->
          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Salary Grade
            </p>

            <p class="font-medium text-[var(--text)] mt-1">
              {{ resolvedSalaryGrade || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Salary Step
            </p>

            <p class="font-medium text-[var(--text)] mt-1">
              {{
                employee.salary_step
                  ? `Step ${employee.salary_step}`
                  : "—"
              }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Current Salary
            </p>

            <p class="font-medium text-[var(--text)] mt-1">
              {{ formattedSalary }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Date Hired
            </p>

            <p class="font-medium text-[var(--text)] mt-1">
              {{ formattedDateHired }}
            </p>
          </div>

          <div class="sm:col-span-2">
            <p class="text-sm text-[var(--text-muted)]">
              Employment Status
            </p>

            <span
              :class="
                normalizeEmploymentStatus(employee.employment_status) === 'active'
                  ? 'bg-green-50 text-green-700'
                  : 'bg-slate-100 text-slate-600'
              "
              class="inline-block mt-1 px-3 py-1 rounded-full text-xs"
            >
              {{ employee.employment_status || "—" }}
            </span>
          </div>
        </div>
      </div>

      <form
        @submit.prevent="updateProfile"
        class="neo-card p-6"
      >
        <div class="flex items-center justify-between gap-2 mb-5">
          <div class="flex items-center gap-2">
            <div
              class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center shrink-0"
            >
              <User class="w-4 h-4" />
            </div>

            <h3 class="text-lg font-medium text-[var(--text)]">
              Personal Information
            </h3>
          </div>

          <button
            v-if="!isEditing"
            type="button"
            @click="startEdit"
            class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition"
          >
            <Pencil class="w-3.5 h-3.5" />
            Edit
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              First Name
            </label>

            <input
              v-if="isEditing"
              v-model="employee.first_name"
              type="text"
              placeholder="Enter first name"
              class="profile-input"
            />

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1 break-words"
            >
              {{ employee.first_name || "—" }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              Middle Name
            </label>

            <input
              v-if="isEditing"
              v-model="employee.middle_name"
              type="text"
              placeholder="Enter middle name"
              class="profile-input"
            />

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1 break-words"
            >
              {{ employee.middle_name || "—" }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              Last Name
            </label>

            <input
              v-if="isEditing"
              v-model="employee.last_name"
              type="text"
              placeholder="Enter last name"
              class="profile-input"
            />

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1 break-words"
            >
              {{ employee.last_name || "—" }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              Extension Name
            </label>

            <select
              v-if="isEditing"
              v-model="employee.extension_name"
              class="profile-input"
            >
              <option value="">None</option>
              <option value="Jr.">Jr.</option>
              <option value="Sr.">Sr.</option>
              <option value="II">II</option>
              <option value="III">III</option>
              <option value="IV">IV</option>
              <option value="V">V</option>
            </select>

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1"
            >
              {{ employee.extension_name || "—" }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              Date of Birth
            </label>

            <input
              v-if="isEditing"
              v-model="employee.date_of_birth"
              type="date"
              class="profile-input"
            />

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1"
            >
              {{ formattedDateOfBirth }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              Sex
            </label>

            <select
              v-if="isEditing"
              v-model="employee.sex"
              class="profile-input"
            >
              <option value="">Select</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1"
            >
              {{ employee.sex || "—" }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              Nationality
            </label>

            <input
              v-if="isEditing"
              v-model="employee.nationality"
              type="text"
              placeholder="e.g. Filipino"
              class="profile-input"
            />

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1 break-words"
            >
              {{ employee.nationality || "—" }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              Civil Status
            </label>

            <select
              v-if="isEditing"
              v-model="employee.civil_status"
              class="profile-input"
            >
              <option value="">Select</option>
              <option value="Single">Single</option>
              <option value="Married">Married</option>
              <option value="Widowed">Widowed</option>
              <option value="Separated">Separated</option>
              <option value="Divorced">Divorced</option>
            </select>

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1"
            >
              {{ employee.civil_status || "—" }}
            </p>
          </div>
        </div>

        <div class="border-t border-[#cbd8e8] pt-6 mt-6">
          <div class="flex items-center gap-2 mb-5">
            <div
              class="w-8 h-8 bg-green-50 text-green-600 rounded-lg flex items-center justify-center shrink-0"
            >
              <Phone class="w-4 h-4" />
            </div>

            <div>
              <h3 class="text-lg font-medium text-[var(--text)]">
                Contact Information
              </h3>

              <p class="text-xs text-[var(--text-muted)]">
                You can update your personal contact information.
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Contact Number
              </label>

              <input
                v-if="isEditing"
                v-model="employee.contact_number"
                type="tel"
                inputmode="numeric"
                maxlength="11"
                @input="
                  employee.contact_number = employee.contact_number
                    .replace(/\D/g, '')
                    .slice(0, 11)
                "
                placeholder="09XXXXXXXXX"
                class="profile-input"
              />

              <p
                v-else
                class="font-medium text-[var(--text)] mt-1"
              >
                {{ employee.contact_number || "—" }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Email
              </label>

              <input
                :value="employee.email"
                type="email"
                disabled
                class="w-full px-3 py-2 border border-[#cbd8e8] rounded-lg bg-[#f3f7fc] text-[var(--text-muted)] cursor-not-allowed"
              />

              <p class="text-xs text-[var(--text-muted)] mt-1">
                To change your email, use Account Settings.
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Personal Email
              </label>

              <input
                v-if="isEditing"
                v-model="employee.personal_email"
                type="email"
                placeholder="personal@email.com"
                class="profile-input"
              />

              <p
                v-else
                class="font-medium text-[var(--text)] mt-1 break-all"
              >
                {{ employee.personal_email || "—" }}
              </p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Address
              </label>

              <textarea
                v-if="isEditing"
                v-model="employee.address"
                rows="3"
                placeholder="Enter your complete address"
                class="profile-input resize-none"
              ></textarea>

              <p
                v-else
                class="font-medium text-[var(--text)] mt-1 whitespace-pre-line break-words"
              >
                {{ employee.address || "—" }}
              </p>
            </div>
          </div>
        </div>

        <div class="border-t border-[#cbd8e8] pt-6 mt-6">
          <div class="flex items-center gap-2 mb-5">
            <div
              class="w-8 h-8 bg-red-50 text-red-600 rounded-lg flex items-center justify-center shrink-0"
            >
              <ShieldAlert class="w-4 h-4" />
            </div>

            <div>
              <h3 class="text-lg font-medium text-[var(--text)]">
                Emergency Contact
              </h3>

              <p class="text-xs text-[var(--text-muted)]">
                Provide someone who can be contacted in case of emergency.
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Contact Name
              </label>

              <input
                v-if="isEditing"
                v-model="employee.emergency_contact_name"
                type="text"
                placeholder="Enter emergency contact name"
                class="profile-input"
              />

              <p
                v-else
                class="font-medium text-[var(--text)] mt-1 break-words"
              >
                {{ employee.emergency_contact_name || "—" }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Relationship
              </label>

              <input
                v-if="isEditing"
                v-model="employee.emergency_contact_relationship"
                type="text"
                placeholder="e.g. Spouse, Parent, Sibling"
                class="profile-input"
              />

              <p
                v-else
                class="font-medium text-[var(--text)] mt-1 break-words"
              >
                {{ employee.emergency_contact_relationship || "—" }}
              </p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Contact Number
              </label>

              <input
                v-if="isEditing"
                v-model="employee.emergency_contact_number"
                type="tel"
                inputmode="numeric"
                maxlength="11"
                @input="
                  employee.emergency_contact_number =
                    employee.emergency_contact_number
                      .replace(/\D/g, '')
                      .slice(0, 11)
                "
                placeholder="09XXXXXXXXX"
                class="profile-input"
              />

              <p
                v-else
                class="font-medium text-[var(--text)] mt-1"
              >
                {{ employee.emergency_contact_number || "—" }}
              </p>
            </div>
          </div>
        </div>

        <div
          v-if="isEditing"
          class="flex flex-col sm:flex-row justify-end gap-3 mt-6"
        >
          <button
            type="button"
            @click="cancelEdit"
            class="w-full sm:w-auto px-6 py-2 text-[var(--text-muted)] bg-[#f3f7fc] border border-[#cbd8e8] rounded-lg hover:bg-[#e8f0f9] transition"
          >
            Cancel
          </button>

          <button
            type="submit"
            class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { notify } from "@/composables/useNotifications";
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";

import {
  User,
  Building2,
  Phone,
  ShieldAlert,
  Pencil,
} from "lucide-vue-next";

import {
  getMyProfile,
  uploadProfilePhoto,
  removeProfilePhoto,
  updateMyProfile,
} from "@/services/employee";

const photoEditorOpen = ref(false)
const togglePhotoEditor = () => {
  photoEditorOpen.value = !photoEditorOpen.value
  if (!photoEditorOpen.value) { clearPhotoSelection(); photoError.value = '' }
}
const savedPhoto = ref('')
const hasPhoto = ref(false)
const photoFailed = ref(false)
const selectedPhoto = ref<File | null>(null)
const previewPhoto = ref('')
const photoBusy = ref(false)
const photoInput = ref<HTMLInputElement | null>(null)
const photoSource = computed(() => previewPhoto.value || (photoFailed.value ? '' : savedPhoto.value))
const photoError = ref('')
const cropCanvas = ref<HTMLCanvasElement | null>(null)
const cropZoom = ref(1)
const cropReady = ref(false)
let cropImage: HTMLImageElement | null = null
let cropX = 0
let cropY = 0
let dragPoint: { x: number; y: number; pointer: number } | null = null
let selectionVersion = 0
const cropSize = 280
const drawCrop = () => {
  const canvas = cropCanvas.value
  if (!canvas || !cropImage || !cropReady.value) return
  const ctx = canvas.getContext('2d')
  if (!ctx) return
  const scale = Math.max(cropSize / cropImage.naturalWidth, cropSize / cropImage.naturalHeight) * cropZoom.value
  const width = cropImage.naturalWidth * scale
  const height = cropImage.naturalHeight * scale
  cropX = Math.max(-(width - cropSize) / 2, Math.min((width - cropSize) / 2, cropX))
  cropY = Math.max(-(height - cropSize) / 2, Math.min((height - cropSize) / 2, cropY))
  ctx.fillStyle = '#fff'
  ctx.fillRect(0, 0, cropSize, cropSize)
  ctx.drawImage(cropImage, (cropSize - width) / 2 + cropX, (cropSize - height) / 2 + cropY, width, height)
  // Keep the round avatar above in sync with the crop preview.
  previewPhoto.value = canvas.toDataURL('image/jpeg', .9)
}
const resetCrop = () => {
  cropZoom.value = 1
  cropX = 0
  cropY = 0
  drawCrop()
}
const startCropDrag = (event: PointerEvent) => {
  if (photoBusy.value || !cropReady.value || (event.pointerType === 'mouse' && event.button !== 0)) return
  cropCanvas.value?.setPointerCapture(event.pointerId)
  dragPoint = { x: event.clientX, y: event.clientY, pointer: event.pointerId }
}
const moveCropDrag = (event: PointerEvent) => {
  if (!dragPoint || dragPoint.pointer !== event.pointerId || photoBusy.value) return
  const rect = cropCanvas.value?.getBoundingClientRect()
  if (!rect || !rect.width) return
  cropX += (event.clientX - dragPoint.x) * cropSize / rect.width
  cropY += (event.clientY - dragPoint.y) * cropSize / rect.height
  dragPoint.x = event.clientX
  dragPoint.y = event.clientY
  drawCrop()
}
const endCropDrag = (event: PointerEvent) => {
  if (dragPoint?.pointer === event.pointerId) dragPoint = null
}
const clearPhotoSelection = () => {
  selectionVersion += 1
  cropReady.value = false
  cropImage = null
  dragPoint = null
  if (previewPhoto.value) URL.revokeObjectURL(previewPhoto.value)
  previewPhoto.value = ''
  selectedPhoto.value = null
  if (photoInput.value) photoInput.value.value = ''
}
const selectPhoto = async (event: Event) => {
  const input = event.target as HTMLInputElement
  const file = input.files?.[0]
  clearPhotoSelection()
  photoError.value = ''
  if (!file) return
  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 2 * 1024 * 1024) {
    photoError.value = 'Choose a JPG, PNG or WebP image up to 2 MB.'
    return
  }
  const version = selectionVersion
  const url = URL.createObjectURL(file)
  try {
    const image = new Image()
    image.src = url
    await image.decode()
    if (version !== selectionVersion) return
    if (image.naturalWidth > 4096 || image.naturalHeight > 4096) {
      photoError.value = 'Choose an image no larger than 4096 × 4096 pixels.'
      return
    }
    selectedPhoto.value = file
    cropImage = image
    await nextTick()
    if (version !== selectionVersion) return
    cropReady.value = true
    photoFailed.value = false
    resetCrop()
  } catch {
    if (version === selectionVersion) photoError.value = 'Unable to read this image. Please choose another photo.'
  } finally {
    URL.revokeObjectURL(url)
  }
}
const photoRequestError = (error: any) => {
  const validation = error.response?.data?.errors?.photo
  photoError.value = validation?.[0] || error.response?.data?.message || 'Unable to update your photo.'
}
const savePhoto = async () => {
  if (!selectedPhoto.value || !cropReady.value || photoBusy.value) return
  photoBusy.value = true
  photoError.value = ''
  try {
    const canvas = cropCanvas.value
    if (!canvas || !cropImage) throw new Error('Crop is unavailable.')
    // Export the same square shown in the circle at a sharper resolution.
    const output = document.createElement('canvas')
    output.width = output.height = 512
    const ctx = output.getContext('2d')
    if (!ctx) throw new Error('Image editing is unavailable.')
    const scale = Math.max(cropSize / cropImage.naturalWidth, cropSize / cropImage.naturalHeight) * cropZoom.value
    const ratio = 512 / cropSize
    const width = cropImage.naturalWidth * scale
    const height = cropImage.naturalHeight * scale
    ctx.fillStyle = '#fff'
    ctx.fillRect(0, 0, 512, 512)
    ctx.drawImage(cropImage, ((cropSize - width) / 2 + cropX) * ratio, ((cropSize - height) / 2 + cropY) * ratio, width * ratio, height * ratio)
    const blob = await new Promise<Blob>((resolve, reject) => {
      output.toBlob(value => value ? resolve(value) : reject(new Error('Unable to crop photo.')), 'image/jpeg', .9)
    })
    const croppedFile = new File([blob], 'profile-photo.jpg', { type: 'image/jpeg' })
    const data = await uploadProfilePhoto(croppedFile)
    savedPhoto.value = data.photo_url || ''
    hasPhoto.value = data.has_photo
    photoFailed.value = false
    clearPhotoSelection()
    window.dispatchEvent(new Event('els-profile-photo-updated'))
    photoEditorOpen.value = false
    notify('Profile photo saved successfully.')
  } catch (error) { photoRequestError(error) }
  finally { photoBusy.value = false }
}
const deletePhoto = async () => {
  if (photoBusy.value || !confirm('Remove your profile photo?')) return
  photoBusy.value = true
  photoError.value = ''
  try {
    await removeProfilePhoto()
    savedPhoto.value = ''
    hasPhoto.value = false
    clearPhotoSelection()
    window.dispatchEvent(new Event('els-profile-photo-updated'))
    photoEditorOpen.value = false
    notify('Profile photo removed.')
  } catch (error) { photoRequestError(error) }
  finally { photoBusy.value = false }
}
onUnmounted(clearPhotoSelection)

const employee = ref({
  employee_id: "",
  employee_code: "",
  email: "",
  role: "",

  first_name: "",
  middle_name: "",
  last_name: "",
  extension_name: "",

  date_of_birth: "",

  sex: "",
  nationality: "",
  civil_status: "",

  contact_number: "",
  personal_email: "",
  address: "",

  emergency_contact_name: "",
  emergency_contact_number: "",
  emergency_contact_relationship: "",

  personnel_type: "",
  employment_category: "",

  department_id: null as number | null,
  department_name: "",

  position_id: null as number | null,
  position: "",

  level: "",

  supervisor_id: null as number | null,
  supervisor: "",

  salary_grade: "",
  salary_step: null as number | null,

  salary: "" as string | number,

  employment_status: "",
  date_hired: "",
});

// NOTE: The /positions endpoint is admin-only on the backend (confirmed by
// a 403 for employee accounts), so it can't be used here as a fallback.
// The real fix is on the backend: the route behind getMyProfile() must
// include salary_grade (or an eager-loaded position.salary_grade) in its
// response for the logged-in employee. Until that's in place, this will
// show "—".
const resolvedSalaryGrade = computed(() => {
  return employee.value.salary_grade || "";
});

const isEditing = ref(false);

let editSnapshot: typeof employee.value | null = null;

const startEdit = () => {
  editSnapshot = JSON.parse(
    JSON.stringify(employee.value)
  );

  isEditing.value = true;
};

const cancelEdit = () => {
  if (editSnapshot) {
    employee.value = JSON.parse(
      JSON.stringify(editSnapshot)
    );
  }

  editSnapshot = null;
  isEditing.value = false;
};

const fullName = computed(() => {
  return [
    employee.value.first_name,
    employee.value.middle_name,
    employee.value.last_name,
    employee.value.extension_name,
  ]
    .filter(Boolean)
    .join(" ");
});

const initials = computed(() => {
  return (
    employee.value.first_name.charAt(0) +
    employee.value.last_name.charAt(0)
  ).toUpperCase();
});

const supervisorName = computed(() => {
  if (employee.value.supervisor) {
    return employee.value.supervisor;
  }

  return "—";
});

const normalizeEmploymentStatus = (
  status?: string
) => {
  return String(status || "")
    .trim()
    .toLowerCase();
};

const formatDate = (
  date?: string | null
) => {
  if (!date) {
    return "—";
  }

  const parsedDate = new Date(date);

  if (Number.isNaN(parsedDate.getTime())) {
    return "—";
  }

  const month = String(
    parsedDate.getUTCMonth() + 1
  ).padStart(2, "0");

  const day = String(
    parsedDate.getUTCDate()
  ).padStart(2, "0");

  const year = parsedDate.getUTCFullYear();

  return `${month}-${day}-${year}`;
};

const formattedDateOfBirth = computed(() => {
  return formatDate(
    employee.value.date_of_birth
  );
});

const formattedDateHired = computed(() => {
  return formatDate(
    employee.value.date_hired
  );
});

const formattedSalary = computed(() => {
  const raw = employee.value.salary;

  if (
    raw === "" ||
    raw === null ||
    raw === undefined
  ) {
    return "—";
  }

  const num =
    typeof raw === "number"
      ? raw
      : parseFloat(String(raw));

  if (Number.isNaN(num)) {
    return "—";
  }

  return `₱${num.toLocaleString("en-PH", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
});

const loadProfile = async () => {
  try {
    const data = await getMyProfile();

    savedPhoto.value = data.photo_url || '';
    hasPhoto.value = Boolean(data.has_photo);
    photoFailed.value = false;

    employee.value = {
      employee_id:
        data.employee_id ?? "",

      employee_code:
        data.employee_code ?? "",

      email:
        data.email ?? "",

      role:
        data.role ?? "",

      first_name:
        data.first_name ?? "",

      middle_name:
        data.middle_name ?? "",

      last_name:
        data.last_name ?? "",

      extension_name:
        data.extension_name ?? "",

      date_of_birth:
        data.date_of_birth ?? "",

      sex:
        data.sex ?? "",

      nationality:
        data.nationality ?? "",

      civil_status:
        data.civil_status ?? "",

      contact_number:
        data.contact_number ?? "",

      personal_email:
        data.personal_email ?? "",

      address:
        data.address ?? "",

      emergency_contact_name:
        data.emergency_contact_name ?? "",

      emergency_contact_number:
        data.emergency_contact_number ?? "",

      emergency_contact_relationship:
        data.emergency_contact_relationship ?? "",

      personnel_type:
        data.personnel_type ?? "",

      employment_category:
        data.employment_category ?? "",

      department_id:
        data.department_id ?? null,

      department_name:
        data.department_name ??
        data.department?.department_name ??
        "",

      position_id:
        data.position_id ??
        data.position?.id ??
        null,

      position:
        data.position ??
        data.position?.name ??
        "",

      level:
        data.level ?? "",

      supervisor_id:
        data.supervisor_id ??
        data.supervisor?.employee_id ??
        null,

      supervisor:
        typeof data.supervisor === "string"
          ? data.supervisor
          : data.supervisor
            ? `${data.supervisor.first_name ?? ""} ${
                data.supervisor.middle_name
                  ? data.supervisor.middle_name + " "
                  : ""
              }${data.supervisor.last_name ?? ""}`.trim()
            : "",

      salary_grade:
        data.salary_grade ??
        data.position?.salary_grade ??
        "",

      salary_step:
        data.salary_step ?? null,

      salary:
        data.salary ?? "",

      employment_status:
        data.employment_status ?? "",

      date_hired:
        data.date_hired ?? "",
    };
  } catch (error) {
    console.error(
      "Failed to load employee profile:",
      error
    );
  }
};

const updateProfile = async () => {
  try {
    await updateMyProfile({
      first_name:
        employee.value.first_name,

      middle_name:
        employee.value.middle_name,

      last_name:
        employee.value.last_name,

      extension_name:
        employee.value.extension_name,

      date_of_birth:
        employee.value.date_of_birth,

      sex:
        employee.value.sex,

      contact_number:
        employee.value.contact_number,

      nationality:
        employee.value.nationality,

      civil_status:
        employee.value.civil_status,

      address:
        employee.value.address,

      personal_email:
        employee.value.personal_email,

      emergency_contact_name:
        employee.value.emergency_contact_name,

      emergency_contact_number:
        employee.value.emergency_contact_number,

      emergency_contact_relationship:
        employee.value.emergency_contact_relationship,
    });

    notify(
      "Profile updated successfully."
    );

    await loadProfile();

    isEditing.value = false;

    editSnapshot = null;
  } catch (error: any) {
    console.error(
      "Profile update failed:",
      error
    );

    if (
      error.response?.data?.message
    ) {
      notify(
        error.response.data.message
      );
    } else if (
      error.response?.data?.errors
    ) {
      const errors =
        error.response.data.errors;

      const messages =
        Object.values(errors)
          .flat()
          .join("\n");

      notify(messages);
    } else {
      notify(
        "Failed to update profile."
      );
    }
  }
};

onMounted(async () => {
  await loadProfile();
});
</script>

<style scoped>
.crop-controls { margin-top: 1rem; }
.crop-stage { position: relative; width: 280px; max-width: 100%; aspect-ratio: 1; overflow: hidden; border-radius: .75rem; background: #e2e8f0; }
.crop-stage canvas { display: block; width: 100%; height: 100%; touch-action: none; cursor: grab; }
.crop-stage canvas:active { cursor: grabbing; }
.crop-circle { position: absolute; inset: 0; border: 2px solid #fff; border-radius: 50%; box-shadow: 0 0 0 100px rgba(15,23,42,.5); pointer-events: none; }
.crop-zoom-label { display: block; margin: .75rem 0 .25rem; color: var(--text); font-size: .875rem; }
.crop-controls input[type=range] { display: block; width: 280px; max-width: 100%; margin-bottom: .75rem; accent-color: #2563eb; }

.edit-photo-button { display: inline-flex; align-items: center; gap: .4rem; padding: .4rem .65rem; border: 1px solid var(--border); border-radius: .5rem; color: var(--primary); background: var(--surface); font-size: .8rem; font-weight: 600; }
.edit-photo-button:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }
.photo-editor { padding-top: 1.25rem; border-top: 1px solid var(--border); }

.employee-profile-image { width: 100%; height: 100%; object-fit: cover; }
.photo-file-input { max-width: 100%; color: var(--text); }
.photo-save,.photo-cancel,.photo-remove { padding: .5rem 1rem; border-radius: .5rem; font-weight: 600; }
.photo-save { background: #2563eb; color: #fff; }
.photo-cancel { background: var(--surface-muted); color: var(--text); }
.photo-remove { background: #dc2626; color: #fff; }
.photo-save:disabled,.photo-cancel:disabled,.photo-remove:disabled { opacity: .5; cursor: not-allowed; }
.photo-save:focus-visible,.photo-cancel:focus-visible,.photo-remove:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }

.records-shell {
  background: var(--app-bg);
  min-height: 100vh;
}

.neo-card {
  background: var(--surface);
  border: 1px solid #cbd8e8;
  border-radius: 1.4rem;
  box-shadow: 0 10px 22px rgba(23, 32, 51, 0.06);
  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;
}

.neo-card:hover {
  box-shadow: 0 14px 26px rgba(23, 32, 51, 0.09);
}

.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card button {
  letter-spacing: -0.01em;
}

.profile-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  color: var(--text);
  background: var(--surface-muted);
  border: 1px solid #c8d8eb;
  border-radius: 0.5rem;
  outline: none;
  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease;
}

.profile-input::placeholder {
  color: #94a3b8;
}

.profile-input:focus {
  border-color: #7aa7e8;
  box-shadow:
    0 0 0 3px
    rgba(37, 99, 235, 0.12);
}

.profile-input option {
  background: var(--surface);
  color: var(--text);
}

@media (max-width: 640px) {
  .records-shell {
    padding: 1rem;
  }

  .neo-card {
    border-radius: 1rem;
  }
}
</style>