
<template>
  <div class="settings-shell p-8 min-h-screen space-y-6">

    <div class="neo-card">
      <div class="border-b border-[#cbd8e8] px-6 py-4">
        <nav class="flex -mb-px flex-wrap">

          <button
            v-for="tab in settingsTabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-4 px-6 text-sm font-medium border-b-2 transition-colors',
              activeTab === tab.id
                ? 'border-[#2563eb] text-[#2563eb]'
                : 'border-transparent text-[var(--text-muted)] hover:text-[var(--text)] hover:border-[#cbd8e8]'
            ]"
          >
            <component :is="tab.icon" class="w-4 h-4 mr-2 inline" />
            {{ tab.name }}
          </button>

        </nav>
      </div>
    </div>


    <div
      v-if="activeTab === 'account'"
      class="neo-card p-6"
    >

      <h2 class="text-xl font-semibold text-[var(--text)] mb-6">
        Account Settings
      </h2>

      <div class="space-y-6">

        <div>

          <h3 class="text-lg font-medium text-[var(--text)] mb-4">
            Email Settings
          </h3>

          <form
            @submit.prevent="updateEmail"
            class="space-y-4"
          >

            <div>

              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Current Email
              </label>

              <input
                v-model="accountSettings.email.current"
                type="email"
                disabled
                class="w-full px-3 py-2 border border-[#cbd8e8] rounded-lg bg-[#f3f7fc] text-[var(--text-muted)]"
              />

            </div>

            <div>

              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                New Email
              </label>

              <input
                v-model="accountSettings.email.new"
                type="email"
                class="field-editable w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />

            </div>

            <div>

              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Confirm New Email
              </label>

              <input
                v-model="accountSettings.email.confirm"
                type="email"
                class="field-editable w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />

            </div>

            <button
              type="submit"
              class="px-4 py-2 bg-[#2563eb] text-white rounded-lg hover:bg-[#1d4ed8]"
            >
              Update Email
            </button>

          </form>

        </div>


        <div class="border-t border-[#cbd8e8] pt-6">

          <h3 class="text-lg font-medium text-[var(--text)] mb-4">
            Password Settings
          </h3>

          <form
            @submit.prevent="updatePassword"
            class="space-y-4"
          >

            <div>

              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Current Password
              </label>

              <div class="relative">

                <input
                  v-model="accountSettings.password.current"
                  :type="showCurrentPassword ? 'text' : 'password'"
                  class="field-editable w-full px-3 py-2 pr-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <button
                  type="button"
                  @click="showCurrentPassword = !showCurrentPassword"
                  class="absolute right-3 top-2.5"
                >

                  <Eye
                    v-if="showCurrentPassword"
                    class="w-4 h-4 text-[var(--text-muted)]"
                  />

                  <EyeOff
                    v-else
                    class="w-4 h-4 text-[var(--text-muted)]"
                  />

                </button>

              </div>

            </div>


            <div>

              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                New Password
              </label>

              <div class="relative">

                <input
                  v-model="accountSettings.password.new"
                  :type="showNewPassword ? 'text' : 'password'"
                  class="field-editable w-full px-3 py-2 pr-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <button
                  type="button"
                  @click="showNewPassword = !showNewPassword"
                  class="absolute right-3 top-2.5"
                >

                  <Eye
                    v-if="showNewPassword"
                    class="w-4 h-4 text-[var(--text-muted)]"
                  />

                  <EyeOff
                    v-else
                    class="w-4 h-4 text-[var(--text-muted)]"
                  />

                </button>

              </div>

            </div>


            <div>

              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Confirm New Password
              </label>

              <div class="relative">

                <input
                  v-model="accountSettings.password.confirm"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  class="field-editable w-full px-3 py-2 pr-10 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <button
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-3 top-2.5"
                >

                  <Eye
                    v-if="showConfirmPassword"
                    class="w-4 h-4 text-[var(--text-muted)]"
                  />

                  <EyeOff
                    v-else
                    class="w-4 h-4 text-[var(--text-muted)]"
                  />

                </button>

              </div>

            </div>


            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">

              <p class="text-sm text-blue-700">

                <strong>Password Requirements:</strong><br>

                • At least 8 characters long<br>
                • Contains uppercase and lowercase letters<br>
                • Contains at least one number<br>
                • Contains at least one special character

              </p>

            </div>


            <button
              type="submit"
              class="px-4 py-2 bg-[#2563eb] text-white rounded-lg hover:bg-[#1d4ed8]"
            >
              Update Password
            </button>

          </form>

        </div>

      </div>

    </div>


    <div
      v-if="activeTab === 'leave-rules'"
      class="neo-card p-6"
    >

      <h2 class="text-xl font-semibold text-[var(--text)] mb-2">
        Leave Rules
      </h2>

      <p class="text-[var(--text-muted)] mb-6">
        View the leave rules configured by the administrator.
      </p>


      <div
        v-if="leaveRulesLoading"
        class="text-center py-8 text-[var(--text-muted)]"
      >
        Loading leave rules...
      </div>


      <div
        v-else
        class="space-y-4"
      >

        <div class="border border-[#c8d8eb] rounded-xl p-5 bg-[var(--surface-muted)]">

          <h3 class="text-lg font-semibold text-[var(--text)]">
            Minimum Filing Notice
          </h3>

          <p class="text-sm text-[var(--text-muted)] mt-2">

            Employees should file their leave at least

            <strong class="text-[var(--text)]">
              {{ leaveRules.minimum_notice_days }} day(s)
            </strong>

            before the intended leave date.

          </p>

        </div>


        <div class="border border-[#c8d8eb] rounded-xl p-5 bg-[var(--surface-muted)]">

          <h3 class="text-lg font-semibold text-[var(--text)]">
            Supporting Documents
          </h3>

          <p class="text-sm text-[var(--text-muted)] mt-2">

            Supporting documents are

            <strong class="text-[var(--text)]">
              {{ leaveRules.require_documents ? 'required' : 'not required' }}
            </strong>

            when filing leave.

          </p>

        </div>


        <div class="border border-[#c8d8eb] rounded-xl p-5 bg-[var(--surface-muted)]">

          <h3 class="text-lg font-semibold text-[var(--text)]">
            Maximum Consecutive Leave Days
          </h3>

          <p class="text-sm text-[var(--text-muted)] mt-2">

            Employees may file up to

            <strong class="text-[var(--text)]">
              {{ leaveRules.max_consecutive_days }} day(s)
            </strong>

            of consecutive leave per application.

          </p>

        </div>


        <div class="border border-[#c8d8eb] rounded-xl p-5 bg-[var(--surface-muted)]">

          <h3 class="text-lg font-semibold text-[var(--text)]">
            General Leave Policy
          </h3>

          <p class="text-sm text-[var(--text-muted)] mt-2 whitespace-pre-line">

            {{ leaveRules.general_policy || 'No general leave policy has been provided.' }}

          </p>

        </div>

      </div>

    </div>


    <div
      v-if="activeTab === 'help'"
      class="neo-card p-6"
    >

      <h2 class="text-xl font-semibold text-[var(--text)] mb-2">
        Help & Frequently Asked Questions
      </h2>

      <p class="text-[var(--text-muted)] mb-6">
        Find answers to common questions about using the ENHS Leave System.
      </p>


      <div class="space-y-4">

        <div
          v-for="faq in faqs"
          :key="faq.id"
          class="border border-[#c8d8eb] rounded-lg"
        >

          <button
            @click="toggleFAQ(faq.id)"
            class="w-full px-4 py-3 text-left flex justify-between items-center hover:bg-[#eef4fb]"
          >

            <span class="font-medium text-[var(--text)]">
              {{ faq.question }}
            </span>

            <ChevronDown
              :class="[
                'w-4 h-4 transition-transform text-[var(--text-muted)]',
                expandedFAQ === faq.id ? 'rotate-180' : ''
              ]"
            />

          </button>


          <div
            v-if="expandedFAQ === faq.id"
            class="px-4 py-3 border-t border-[#c8d8eb]"
          >

            <p class="text-[var(--text-muted)]">
              {{ faq.answer }}
            </p>

          </div>

        </div>

      </div>

    </div>


    <div
      v-if="activeTab === 'about'"
      class="neo-card overflow-hidden"
    >

      <div class="neo-card p-6">

        <div
          class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
        >

          <div>

            <h2 class="text-xl font-semibold text-[var(--text)]">
              Settings
            </h2>

            <p class="text-sm text-[var(--text-muted)] mt-1">
              Information about the ENHS Leave System
            </p>

          </div>


          <div>

            <h2 class="text-2xl font-bold text-[var(--text)]">
              ENHS Leave System
            </h2>

            <p class="text-blue-600 mt-1 text-sm">
              Echague National High School Leave Management System
            </p>

          </div>

        </div>


        <p class="mt-5 max-w-3xl text-sm leading-6 text-[var(--text-muted)]">

          A web-based system designed to help Echague National High School
          manage employee leave applications, attendance records, leave credits,
          and related employee information in a more organized and efficient way.

        </p>

      </div>


      <div class="p-8">

        <section class="mb-10">

          <div class="flex items-center gap-3 mb-4">

            <div
              class="w-9 h-9 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center"
            >
              <Info class="w-5 h-5" />
            </div>

            <div>

              <h3 class="text-lg font-semibold text-[var(--text)]">
                About the System
              </h3>

              <p class="text-sm text-[var(--text-muted)]">
                Overview of the ENHS Leave System
              </p>

            </div>

          </div>


          <div class="bg-[var(--surface-muted)] border border-[#c8d8eb] rounded-xl p-5">

            <p class="text-sm text-[var(--text-muted)] leading-7">

              The ENHS Leave System is a web-based Leave Management System
              developed for Echague National High School. It provides a
              centralized platform for managing employee leave applications,
              leave records, attendance, leave credits, and reports.
              The system is intended to reduce manual record-keeping,
              improve information organization, and make employee leave
              management more accessible and efficient.

            </p>

          </div>

        </section>


        <section class="mb-10">

          <div class="mb-5">

            <h3 class="text-lg font-semibold text-[var(--text)]">
              System Features
            </h3>

            <p class="text-sm text-[var(--text-muted)] mt-1">
              Main functions available in the ENHS Leave System
            </p>

          </div>


          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div
              class="flex gap-4 p-5 border border-[#c8d8eb] rounded-xl hover:border-blue-300 hover:bg-blue-50 transition"
            >

              <div
                class="w-10 h-10 shrink-0 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center"
              >
                <User class="w-5 h-5" />
              </div>

              <div>

                <h4 class="font-semibold text-[var(--text)]">
                  Employee Account Management
                </h4>

                <p class="text-sm text-[var(--text-muted)] mt-1 leading-6">
                  Manage employee accounts and authorized access to the system.
                </p>

              </div>

            </div>


            <div
              class="flex gap-4 p-5 border border-[#c8d8eb] rounded-xl hover:border-blue-300 hover:bg-blue-50 transition"
            >

              <div
                class="w-10 h-10 shrink-0 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center"
              >
                <FileText class="w-5 h-5" />
              </div>

              <div>

                <h4 class="font-semibold text-[var(--text)]">
                  Online Leave Application
                </h4>

                <p class="text-sm text-[var(--text-muted)] mt-1 leading-6">
                  Submit and monitor leave applications through the system.
                </p>

              </div>

            </div>


            <div
              class="flex gap-4 p-5 border border-[#c8d8eb] rounded-xl hover:border-blue-300 hover:bg-blue-50 transition"
            >

              <div
                class="w-10 h-10 shrink-0 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center"
              >
                <FileText class="w-5 h-5" />
              </div>

              <div>

                <h4 class="font-semibold text-[var(--text)]">
                  Leave Credit Management
                </h4>

                <p class="text-sm text-[var(--text-muted)] mt-1 leading-6">
                  View and manage employee leave credits and balances.
                </p>

              </div>

            </div>


            <div
              class="flex gap-4 p-5 border border-[#c8d8eb] rounded-xl hover:border-blue-300 hover:bg-blue-50 transition"
            >

              <div
                class="w-10 h-10 shrink-0 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center"
              >
                <FileText class="w-5 h-5" />
              </div>

              <div>

                <h4 class="font-semibold text-[var(--text)]">
                  Attendance Records
                </h4>

                <p class="text-sm text-[var(--text-muted)] mt-1 leading-6">
                  Maintain and access employee attendance information.
                </p>

              </div>

            </div>


            <div
              class="flex gap-4 p-5 border border-[#c8d8eb] rounded-xl hover:border-blue-300 hover:bg-blue-50 transition"
            >

              <div
                class="w-10 h-10 shrink-0 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center"
              >
                <FileText class="w-5 h-5" />
              </div>

              <div>

                <h4 class="font-semibold text-[var(--text)]">
                  Reports
                </h4>

                <p class="text-sm text-[var(--text-muted)] mt-1 leading-6">
                  Generate organized reports based on available system records.
                </p>

              </div>

            </div>


            <div
              class="flex gap-4 p-5 border border-[#c8d8eb] rounded-xl hover:border-blue-300 hover:bg-blue-50 transition"
            >

              <div
                class="w-10 h-10 shrink-0 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center"
              >
                <Settings class="w-5 h-5" />
              </div>

              <div>

                <h4 class="font-semibold text-[var(--text)]">
                  Activity Monitoring
                </h4>

                <p class="text-sm text-[var(--text-muted)] mt-1 leading-6">
                  Monitor relevant system activities and records for accountability.
                </p>

              </div>

            </div>

          </div>

        </section>


        <section class="mb-10">

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="border border-[#c8d8eb] rounded-xl p-6">

              <div class="flex items-center gap-3 mb-5">

                <div
                  class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center"
                >
                  <Info class="w-5 h-5" />
                </div>

                <div>

                  <h3 class="font-semibold text-[var(--text)]">
                    Organization
                  </h3>

                  <p class="text-sm text-[var(--text-muted)]">
                    Intended school environment
                  </p>

                </div>

              </div>


              <h4 class="font-medium text-[var(--text)]">
                Echague National High School
              </h4>

              <p class="text-sm text-[var(--text-muted)] leading-6 mt-2">

                The system is intended to support the school's employee
                leave and attendance management processes by providing
                a centralized and organized digital platform.

              </p>

            </div>


            <div class="border border-[#c8d8eb] rounded-xl p-6">

              <div class="flex items-center gap-3 mb-5">

                <div
                  class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center"
                >
                  <User class="w-5 h-5" />
                </div>

                <div>

                  <h3 class="font-semibold text-[var(--text)]">
                    System Users
                  </h3>

                  <p class="text-sm text-[var(--text-muted)]">
                    Authorized users of the system
                  </p>

                </div>

              </div>


              <div class="space-y-3">

                <div class="flex items-center gap-3">

                  <span class="w-2 h-2 bg-blue-500 rounded-full"></span>

                  <span class="text-sm text-[var(--text-muted)]">
                    Administrator / ADAS
                  </span>

                </div>


                <div class="flex items-center gap-3">

                  <span class="w-2 h-2 bg-blue-500 rounded-full"></span>

                  <span class="text-sm text-[var(--text-muted)]">
                    Teaching Employees
                  </span>

                </div>


                <div class="flex items-center gap-3">

                  <span class="w-2 h-2 bg-blue-500 rounded-full"></span>

                  <span class="text-sm text-[var(--text-muted)]">
                    Non-Teaching Employees
                  </span>

                </div>

              </div>

            </div>

          </div>

        </section>


        <section class="mb-10">

          <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">

            <h3 class="text-lg font-semibold text-[var(--text)] mb-2">
              Purpose of the System
            </h3>

            <p class="text-sm text-[var(--text-muted)] leading-7">

              The ENHS Leave System aims to provide a centralized platform
              for managing employee leave applications, attendance records,
              leave credits, and reports. By organizing these processes
              digitally, the system helps reduce manual record-keeping
              and improves the accessibility, accuracy, and organization
              of employee information.

            </p>

          </div>

        </section>


        <section class="mb-10">

          <h3 class="text-lg font-semibold text-[var(--text)] mb-1">
            System Details
          </h3>

          <p class="text-sm text-[var(--text-muted)] mb-5">
            Basic information about the system
          </p>


          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div class="border border-[#c8d8eb] rounded-xl p-5">

              <p class="text-xs font-medium text-[var(--text-muted)] uppercase tracking-wide">
                System Name
              </p>

              <p class="font-semibold text-[var(--text)] mt-2">
                ENHS Leave System
              </p>

            </div>


            <div class="border border-[#c8d8eb] rounded-xl p-5">

              <p class="text-xs font-medium text-[var(--text-muted)] uppercase tracking-wide">
                System Type
              </p>

              <p class="font-semibold text-[var(--text)] mt-2">
                Web-Based Leave Management System
              </p>

            </div>


            <div class="border border-[#c8d8eb] rounded-xl p-5">

              <p class="text-xs font-medium text-[var(--text-muted)] uppercase tracking-wide">
                Primary Users
              </p>

              <p class="font-semibold text-[var(--text)] mt-2">
                Administrators and Employees
              </p>

            </div>

          </div>

        </section>


        <section>

          <div
            class="border border-blue-200 bg-blue-50 rounded-xl p-6"
          >

            <div class="flex items-start gap-4">

              <div
                class="w-10 h-10 shrink-0 bg-white text-blue-600 rounded-lg flex items-center justify-center border border-blue-100"
              >
                <Settings class="w-5 h-5" />
              </div>

              <div>

                <h3 class="text-lg font-semibold text-[var(--text)]">
                  Privacy & Security
                </h3>

                <p class="text-sm text-[var(--text-muted)] leading-7 mt-2">

                  The ENHS Leave System is designed to protect employee
                  information and restrict access to authorized users.
                  Account credentials, employee records, leave applications,
                  attendance information, and other system data should only
                  be accessed and managed according to the user's assigned
                  role and authorized responsibilities.

                </p>

              </div>

            </div>

          </div>

        </section>

      </div>

    </div>


    <div class="neo-card p-6">

      <div class="flex items-center justify-between">

        <div>

          <h3 class="text-lg font-medium text-[var(--text)]">
            Sign Out
          </h3>

          <p class="text-sm text-[var(--text-muted)] mt-1">
            Sign out of your account and return to login page
          </p>

        </div>


        <button
          @click="logout"
          class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-500"
        >
          Logout
        </button>

      </div>

    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

import {
  User,
  Settings,
  HelpCircle,
  Info,
  ChevronDown,
  FileText,
  Eye,
  EyeOff
} from 'lucide-vue-next'

import {
  getMyProfile,
  updateEmail as updateEmailAPI,
  updatePassword as updatePasswordAPI,
  updatePhone as updatePhoneAPI
} from '@/services/employee'

import { getLeaveSettings } from '@/services/leaveSettings'

const router = useRouter()

const activeTab = ref('account')

const settingsTabs = [
  {
    id: 'account',
    name: 'Account',
    icon: Settings
  },
  {
    id: 'leave-rules',
    name: 'Leave Rules',
    icon: FileText
  },
  {
    id: 'help',
    name: 'Help Desk',
    icon: HelpCircle
  },
  {
    id: 'about',
    name: 'About Us',
    icon: Info
  }
]

const accountSettings = ref({
  email: {
    current: '',
    new: '',
    confirm: ''
  },

  password: {
    current: '',
    new: '',
    confirm: ''
  },

  phone: {
    current: '',
    new: ''
  },

  position: {
    current: '',
    new: '',
    reason: ''
  }
})

const loadAccountInfo = async () => {
  try {
    const data = await getMyProfile()

    accountSettings.value.email.current = data.email ?? ''
    accountSettings.value.phone.current = data.contact_number ?? ''
    accountSettings.value.position.current = data.position ?? ''
  } catch (error) {
    console.error('Failed to load account info:', error)
  }
}

const updateEmail = async () => {
  const newEmail =
    accountSettings.value.email.new.trim()

  const confirmEmail =
    accountSettings.value.email.confirm.trim()

  if (!newEmail) {
    alert('Please enter your new email.')
    return
  }

  if (newEmail !== confirmEmail) {
    alert('Email confirmation does not match.')
    return
  }

  try {
    await updateEmailAPI(newEmail)

    alert('Email updated successfully.')

    accountSettings.value.email.new = ''
    accountSettings.value.email.confirm = ''

    await loadAccountInfo()

  } catch (error: any) {
    console.error('Email update failed:', error)

    if (error.response?.data?.message) {
      alert(error.response.data.message)
    } else {
      alert('Email update failed.')
    }
  }
}

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const updatePassword = async () => {

  const currentPassword =
    accountSettings.value.password.current

  const newPassword =
    accountSettings.value.password.new

  const confirmPassword =
    accountSettings.value.password.confirm

  if (!currentPassword) {
    alert('Please enter your current password.')
    return
  }

  if (!newPassword) {
    alert('Please enter your new password.')
    return
  }

  if (newPassword !== confirmPassword) {
    alert('Password confirmation does not match.')
    return
  }

  if (newPassword.length < 8) {
    alert('Password must be at least 8 characters long.')
    return
  }

  try {

    await updatePasswordAPI({
      current_password: currentPassword,

      new_password: newPassword,

      new_password_confirmation:
        confirmPassword
    })

    alert('Password updated successfully.')

    accountSettings.value.password.current = ''
    accountSettings.value.password.new = ''
    accountSettings.value.password.confirm = ''

  } catch (error: any) {

    console.error('Password update failed:', error)

    if (error.response?.data?.message) {
      alert(error.response.data.message)
    } else if (error.response?.data?.errors) {

      const errors = error.response.data.errors

      const messages = Object.values(errors)
        .flat()
        .join('\n')

      alert(messages)

    } else {
      alert('Password update failed.')
    }
  }
}

const updatePhone = async () => {

  const newPhone =
    accountSettings.value.phone.new.trim()

  if (!newPhone) {
    alert('Please enter your new phone number.')
    return
  }

  try {

    await updatePhoneAPI(newPhone)

    alert('Phone number updated successfully.')

    accountSettings.value.phone.new = ''

    await loadAccountInfo()

  } catch (error: any) {

    console.error('Phone update failed:', error)

    if (error.response?.data?.message) {
      alert(error.response.data.message)
    } else {
      alert('Phone update failed.')
    }
  }
}

const updatePosition = () => {

  alert(
    'Position changes must be requested through the administrator.'
  )
}

const leaveRules = ref({
  minimum_notice_days: 0,
  require_documents: false,
  max_consecutive_days: 0,
  general_policy: ''
})

const leaveRulesLoading = ref(false)

const loadLeaveRules = async () => {

  leaveRulesLoading.value = true

  try {

    const data = await getLeaveSettings()

    leaveRules.value = {
      minimum_notice_days:
        data.minimum_notice_days ?? 0,

      require_documents:
        data.require_documents ?? false,

      max_consecutive_days:
        data.max_consecutive_days ?? 0,

      general_policy:
        data.general_policy ?? ''
    }

  } catch (error) {

    console.error(
      'Failed to load leave rules:',
      error
    )

  } finally {

    leaveRulesLoading.value = false

  }
}

const expandedFAQ = ref<number | null>(null)

const faqs = [
  {
    id: 1,

    question: 'How do I apply for leave?',

    answer:
      'Navigate to the Leave Application page from the sidebar menu. Fill out the form with your leave details, attach any required documents, and submit the application for review.'
  },

  {
    id: 2,

    question: 'What is the leave approval process?',

    answer:
      'After an employee submits a leave application, it is reviewed and processed by the designated approving personnel according to the school’s leave approval procedure.'
  },

  {
    id: 3,

    question: 'How many leave credits do I have?',

    answer:
      'You can check your available leave credits by going to the Records page from the sidebar menu. Your leave credits and related leave records are displayed there.'
  },

  {
    id: 4,

    question: 'How do I check my attendance record?',

    answer:
      'Go to the Attendance page from the sidebar menu to view your attendance records and attendance information.'
  }
]

const toggleFAQ = (id: number) => {

  expandedFAQ.value =
    expandedFAQ.value === id
      ? null
      : id

}

const logout = () => {

  localStorage.removeItem('token')

  router.push('/login')

}

onMounted(async () => {

  await Promise.all([
    loadAccountInfo(),
    loadLeaveRules()
  ])

})
</script>

<style scoped>
.settings-shell {
  background: var(--app-bg);
}

.neo-card {
  background: var(--surface);
  border: 1px solid #cbd8e8;
  border-radius: 1.4rem;
  box-shadow: 0 10px 22px rgba(23, 32, 51, 0.06);
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.neo-card:hover {
  box-shadow: 0 14px 26px rgba(23, 32, 51, 0.09);
}

.stats-card {
  border-left: 4px solid currentColor;
  padding: 1.35rem;
}

.stats-card .p-3 {
  border-radius: 0.9rem;
}

.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card button {
  letter-spacing: -0.01em;
}

.field-editable {
  background: var(--surface-muted);
  color: var(--text);
  border-color: #c8d8eb;
}

.field-editable::placeholder {
  color: #94a3b8;
}

.field-editable:focus {
  outline: none;
  border-color: #7aa7e8;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}
</style>

