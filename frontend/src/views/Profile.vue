<template>
  <div class="records-shell p-8 min-h-screen space-y-6">

    <!-- ===================================================== -->
    <!-- PROFILE SUMMARY (expanded, full width) -->
    <!-- ===================================================== -->

    <div class="neo-card p-8">

      <div class="flex flex-col sm:flex-row sm:items-center gap-6">

        <div
          class="w-28 h-28 bg-blue-500 rounded-full flex items-center justify-center text-white text-4xl font-semibold shrink-0"
        >
          {{ initials }}
        </div>

        <div class="flex-1">

          <h3 class="text-2xl font-semibold text-white">
            {{ fullName }}
          </h3>

          <p class="text-sm text-gray-400 mt-1">
            {{ employee.position || 'Employee' }}
          </p>

          <span
            v-if="employee.employment_status"
            class="inline-block mt-3 px-3 py-1 rounded-full text-xs bg-green-500/10 text-green-400"
          >
            {{ employee.employment_status }}
          </span>

        </div>

        <div class="sm:text-right sm:pl-6 sm:border-l border-t sm:border-t-0 border-[#1e293b] pt-5 sm:pt-0">
          <p class="text-xs text-gray-500">
            Employee Code
          </p>

          <p class="text-sm text-gray-300 font-mono mt-1">
            {{ employee.employee_code || '—' }}
          </p>
        </div>

      </div>

    </div>


    <!-- ===================================================== -->
    <!-- EMPLOYEE INFORMATION beside PERSONAL/CONTACT/EMERGENCY -->
    <!-- ===================================================== -->

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

      <!-- HR CONTROLLED INFORMATION (read-only) -->

      <div class="neo-card p-6">

        <div class="flex items-center gap-2 mb-5">

          <div
            class="w-8 h-8 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center shrink-0"
          >
            <Building2 class="w-4 h-4" />
          </div>

          <div>
            <h3 class="text-lg font-medium text-white">
              Employee Information
            </h3>

            <p class="text-xs text-gray-500">
              These details are managed by the administrator.
            </p>
          </div>

        </div>


        <div class="grid grid-cols-2 gap-x-5 gap-y-5 border border-[#1e293b] rounded-xl p-5">

          <div>
            <p class="text-sm text-gray-400">Department</p>
            <p class="font-medium text-white mt-1">
              {{ employee.department_name || '—' }}
            </p>
          </div>

          <div>
            <p class="text-sm text-gray-400">Position</p>
            <p class="font-medium text-white mt-1">
              {{ employee.position || '—' }}
            </p>
          </div>

          <div>
            <p class="text-sm text-gray-400">Level</p>
            <p class="font-medium text-white mt-1">
              {{ employee.level || '—' }}
            </p>
          </div>

          <div>
            <p class="text-sm text-gray-400">Salary</p>
            <p class="font-medium text-white mt-1">
              {{ formattedSalary }}
            </p>
          </div>

          <div class="col-span-2">
            <p class="text-sm text-gray-400">Date Hired</p>
            <p class="font-medium text-white mt-1">
              {{ formattedDateHired }}
            </p>
          </div>

        </div>

      </div>


      <!-- ================================================= -->
      <!-- PERSONAL INFORMATION + CONTACT INFORMATION + EMERGENCY CONTACT (one form) -->
      <!-- ================================================= -->

      <form
        @submit.prevent="updateProfile"
        class="neo-card p-6"
      >

        <!-- PERSONAL INFORMATION -->

        <div class="flex items-center justify-between gap-2 mb-5">

          <div class="flex items-center gap-2">

            <div
              class="w-8 h-8 bg-yellow-500/10 text-yellow-400 rounded-lg flex items-center justify-center shrink-0"
            >
              <User class="w-4 h-4" />
            </div>

            <h3 class="text-lg font-medium text-white">
              Personal Information
            </h3>

          </div>

          <button
            v-if="!isEditing"
            type="button"
            @click="startEdit"
            class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-blue-400 bg-blue-500/10 rounded-lg hover:bg-blue-500/20 transition"
          >
            <Pencil class="w-3.5 h-3.5" />
            Edit
          </button>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

          <!-- First Name -->

          <div>

            <label class="block text-sm font-medium text-gray-400 mb-1">
              First Name
            </label>

            <input
              v-if="isEditing"
              v-model="employee.first_name"
              type="text"
              placeholder="Enter first name"
              class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <p v-else class="font-medium text-white mt-1">
              {{ employee.first_name || '—' }}
            </p>

          </div>

          <!-- Last Name -->

          <div>

            <label class="block text-sm font-medium text-gray-400 mb-1">
              Last Name
            </label>

            <input
              v-if="isEditing"
              v-model="employee.last_name"
              type="text"
              placeholder="Enter last name"
              class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />

            <p v-else class="font-medium text-white mt-1">
              {{ employee.last_name || '—' }}
            </p>

          </div>

          <!-- Sex -->

          <div>

            <label class="block text-sm font-medium text-gray-400 mb-1">
              Sex
            </label>

            <select
              v-if="isEditing"
              v-model="employee.sex"
              class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>

            <p v-else class="font-medium text-white mt-1">
              {{ employee.sex || '—' }}
            </p>

          </div>

          <div>

            <label class="block text-sm font-medium text-gray-400 mb-1">
              Nationality
            </label>

            <select
              v-if="isEditing"
              v-model="employee.nationality"
              class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="Filipino">Filipino</option>
              <option value="Other">Other</option>
            </select>

            <p v-else class="font-medium text-white mt-1">
              {{ employee.nationality || '—' }}
            </p>

          </div>

          <div>

            <label class="block text-sm font-medium text-gray-400 mb-1">
              Civil Status
            </label>

            <select
              v-if="isEditing"
              v-model="employee.civil_status"
              class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="Single">Single</option>
              <option value="Married">Married</option>
              <option value="Widowed">Widowed</option>
              <option value="Separated">Separated</option>
              <option value="Divorce">Divorce</option>
            </select>

            <p v-else class="font-medium text-white mt-1">
              {{ employee.civil_status || '—' }}
            </p>

          </div>

        </div>


        <!-- CONTACT INFORMATION -->

        <div class="border-t border-[#1e293b] pt-6 mt-6">


          <div class="flex items-center gap-2 mb-5">

            <div
              class="w-8 h-8 bg-green-500/10 text-green-400 rounded-lg flex items-center justify-center shrink-0"
            >
              <Phone class="w-4 h-4" />
            </div>

            <div>
              <h3 class="text-lg font-medium text-white">
                Contact Information
              </h3>

              <p class="text-xs text-gray-500">
                You can update your personal contact information.
              </p>
            </div>

          </div>


          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Contact Number -->

            <div>

              <label class="block text-sm font-medium text-gray-400 mb-1">
                Contact Number
              </label>

              <input
                v-if="isEditing"
                v-model="employee.contact_number"
                type="tel"
                placeholder="Enter contact number"
                class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />

              <p v-else class="font-medium text-white mt-1">
                {{ employee.contact_number || '—' }}
              </p>

            </div>


            <!-- Email -->

            <div>

              <label class="block text-sm font-medium text-gray-400 mb-1">
                Email
              </label>

              <input
                :value="employee.email"
                type="email"
                disabled
                class="w-full px-3 py-2 border border-[#1e293b] rounded-lg bg-[#0a1119] text-gray-500 cursor-not-allowed"
              />

              <p class="text-xs text-gray-500 mt-1">
                To change your email, use Account Settings.
              </p>

            </div>

            <!-- Personal Email -->

            <div>

              <label class="block text-sm font-medium text-gray-400 mb-1">
                Personal Email
              </label>

              <input
                v-if="isEditing"
                v-model="employee.personal_email"
                type="email"
                placeholder="Personal Email"
                class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />

              <p v-else class="font-medium text-white mt-1">
                {{ employee.personal_email || '—' }}
              </p>

            </div>


            <!-- Address -->

            <div class="md:col-span-2">

              <label class="block text-sm font-medium text-gray-400 mb-1">
                Address
              </label>

              <textarea
                v-if="isEditing"
                v-model="employee.address"
                rows="3"
                placeholder="Enter your complete address"
                class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
              ></textarea>

              <p v-else class="font-medium text-white mt-1 whitespace-pre-line">
                {{ employee.address || '—' }}
              </p>

            </div>

          </div>

        </div>


        <!-- ================================================= -->
        <!-- EMERGENCY CONTACT -->
        <!-- ================================================= -->

        <div class="border-t border-[#1e293b] pt-6 mt-6">

          <div class="flex items-center gap-2 mb-5">

            <div
              class="w-8 h-8 bg-red-500/10 text-red-400 rounded-lg flex items-center justify-center shrink-0"
            >
              <ShieldAlert class="w-4 h-4" />
            </div>

            <div>

              <h3 class="text-lg font-medium text-white">
                Emergency Contact
              </h3>

              <p class="text-xs text-gray-500">
                Provide someone who can be contacted in case of emergency.
              </p>

            </div>

          </div>


          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Emergency Contact Name -->

            <div>

              <label class="block text-sm font-medium text-gray-400 mb-1">
                Contact Name
              </label>

              <input
                v-if="isEditing"
                v-model="employee.emergency_contact_name"
                type="text"
                placeholder="Enter emergency contact name"
                class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />

              <p v-else class="font-medium text-white mt-1">
                {{ employee.emergency_contact_name || '—' }}
              </p>

            </div>


            <!-- Relationship -->

            <div>

              <label class="block text-sm font-medium text-gray-400 mb-1">
                Relationship
              </label>

              <input
                v-if="isEditing"
                v-model="employee.emergency_contact_relationship"
                type="text"
                placeholder="e.g. Spouse, Parent, Sibling"
                class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />

              <p v-else class="font-medium text-white mt-1">
                {{ employee.emergency_contact_relationship || '—' }}
              </p>

            </div>


            <!-- Emergency Contact Number -->

            <div class="md:col-span-2">

              <label class="block text-sm font-medium text-gray-400 mb-1">
                Contact Number
              </label>

              <input
                v-if="isEditing"
                v-model="employee.emergency_contact_number"
                type="tel"
                placeholder="Enter emergency contact number"
                class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />

              <p v-else class="font-medium text-white mt-1">
                {{ employee.emergency_contact_number || '—' }}
              </p>

            </div>

          </div>

        </div>


        <!-- SAVE PROFILE -->

        <div v-if="isEditing" class="flex justify-end gap-3 mt-6">

          <button
            type="button"
            @click="cancelEdit"
            class="px-6 py-2 text-gray-300 bg-[#0d1520] border border-[#1e293b] rounded-lg hover:bg-[#111d2e] transition"
          >
            Cancel
          </button>

          <button
            type="submit"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition"
          >
            Save Changes
          </button>

        </div>

      </form>

    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

import {
  User,
  Building2,
  Phone,
  ShieldAlert,
  Pencil
} from 'lucide-vue-next'

import {
  getMyProfile,
  updateMyProfile
} from '@/services/employee'

/* =========================================================
   EMPLOYEE PROFILE
========================================================= */

const employee = ref({
  employee_id: '',
  employee_code: '',

  first_name: '',
  middle_name: '',
  last_name: '',

  sex: '',
  department_id: null as number | null,
  department_name: '',
  position: '',
  level: '',

  salary: '' as string | number,
  contact_number: '',
  employment_status: '',
  date_hired: '',

  email: '',
  personal_email: '',
  nationality: '',
  civil_status: '',
  address: '',

  emergency_contact_name: '',
  emergency_contact_number: '',
  emergency_contact_relationship: '',

  role: ''
})

/* =========================================================
   EDIT MODE
   Personal / Contact / Emergency fields (including First
   Name, Last Name, and Sex) are read-only until the employee
   clicks "Edit"; a snapshot is kept so Cancel can revert any
   unsaved changes.
========================================================= */

const isEditing = ref(false)
let editSnapshot: typeof employee.value | null = null

const startEdit = () => {
  editSnapshot = JSON.parse(JSON.stringify(employee.value))
  isEditing.value = true
}

const cancelEdit = () => {
  if (editSnapshot) {
    employee.value = JSON.parse(JSON.stringify(editSnapshot))
  }
  editSnapshot = null
  isEditing.value = false
}

const fullName = computed(() => {
  return [
    employee.value.first_name,
    employee.value.middle_name,
    employee.value.last_name
  ]
    .filter(Boolean)
    .join(' ')
})

const initials = computed(() => {
  return (
    employee.value.first_name.charAt(0) +
    employee.value.last_name.charAt(0)
  ).toUpperCase()
})

const formattedDateHired = computed(() => {
  if (!employee.value.date_hired) return '—'

  const date = new Date(employee.value.date_hired)
  if (isNaN(date.getTime())) return employee.value.date_hired

  const month = String(date.getUTCMonth() + 1).padStart(2, '0')
  const day = String(date.getUTCDate()).padStart(2, '0')
  const year = date.getUTCFullYear()

  return `${month}-${day}-${year}`
})

const formattedSalary = computed(() => {
  const raw = employee.value.salary
  if (raw === '' || raw === null || raw === undefined) return '—'

  const num = typeof raw === 'number' ? raw : parseFloat(String(raw))
  if (isNaN(num)) return '—'

  return `₱${num.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
})

/* =========================================================
   LOAD PROFILE
========================================================= */

const loadProfile = async () => {
  try {
    const data = await getMyProfile()

    employee.value = {
      employee_id: data.employee_id ?? '',
      employee_code: data.employee_code ?? '',

      first_name: data.first_name ?? '',
      middle_name: data.middle_name ?? '',
      last_name: data.last_name ?? '',

      sex: data.sex ?? '',
      department_id: data.department_id ?? null,
      department_name: data.department_name ?? '',
      position: data.position ?? '',
      level: data.level ?? '',

      salary: data.salary ?? '',
      contact_number: data.contact_number ?? '',
      employment_status: data.employment_status ?? '',
      date_hired: data.date_hired ?? '',

      email: data.email ?? '',
      personal_email: data.personal_email ?? '',
      nationality: data.nationality ?? '',
      civil_status: data.civil_status ?? '',
      address: data.address ?? '',

      emergency_contact_name: data.emergency_contact_name ?? '',
      emergency_contact_number: data.emergency_contact_number ?? '',
      emergency_contact_relationship:
        data.emergency_contact_relationship ?? '',

      role: data.role ?? ''
    }
  } catch (error) {
    console.error('Failed to load employee profile:', error)
  }
}

/* =========================================================
   UPDATE EMPLOYEE PROFILE
========================================================= */

const updateProfile = async () => {
  try {
    await updateMyProfile({
      first_name: employee.value.first_name,
      last_name: employee.value.last_name,
      sex: employee.value.sex,

      contact_number: employee.value.contact_number,

      nationality: employee.value.nationality,
      civil_status: employee.value.civil_status,

      address: employee.value.address,
      personal_email: employee.value.personal_email,

      emergency_contact_name: employee.value.emergency_contact_name,
      emergency_contact_number: employee.value.emergency_contact_number,
      emergency_contact_relationship:
        employee.value.emergency_contact_relationship
    })

    alert('Profile updated successfully.')

    await loadProfile()

    isEditing.value = false
    editSnapshot = null
  } catch (error: any) {
    console.error('Profile update failed:', error)

    if (error.response?.data?.message) {
      alert(error.response.data.message)
    } else if (error.response?.data?.errors) {
      const errors = error.response.data.errors

      const messages = Object.values(errors).flat().join('\n')

      alert(messages)
    } else {
      alert('Failed to update profile.')
    }
  }
}

/* =========================================================
   INITIAL LOAD
========================================================= */

onMounted(async () => {
  await loadProfile()
})
</script>

<style scoped>
.records-shell {
  background: #080d14;
}

.neo-card {
  background: #111d2e;
  border: 1px solid #1e293b;
  border-radius: 1.4rem;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.neo-card:hover {
  box-shadow: 0 14px 26px rgba(15, 23, 42, 0.06);
}

.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card button {
  letter-spacing: -0.01em;
}
</style>