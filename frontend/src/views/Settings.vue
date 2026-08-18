<template>
  <div class="dashboard-shell min-h-screen p-8 space-y-8">

    <!-- ========================= -->
    <!-- SETTINGS NAVIGATION -->
    <!-- ========================= -->
    <div class="neo-card overflow-hidden">

      <div class="border-b border-slate-700 px-6">
        <nav class="flex flex-wrap gap-1">

          <button
            v-for="tab in settingsTabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-4 px-5 text-sm font-medium border-b-2 transition-all duration-200',
              activeTab === tab.id
                ? 'border-blue-500 text-blue-400'
                : 'border-transparent text-slate-400 hover:text-white hover:border-slate-500'
            ]"
          >
            <component
              :is="tab.icon"
              class="w-4 h-4 mr-2 inline"
            />

            {{ tab.name }}
          </button>

        </nav>
      </div>

    </div>


    <!-- ========================= -->
    <!-- EMPLOYEE PROFILE -->
    <!-- ========================= -->
    <div
      v-if="activeTab === 'profile'"
      class="neo-card p-6"
    >

      <div class="mb-6">
        <h2 class="text-xl font-semibold text-white">
          Employee Profile
        </h2>

        <p class="text-sm text-slate-400 mt-1">
          Manage your personal employee information.
        </p>
      </div>


      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Profile Picture -->
        <div class="lg:col-span-1">

          <div class="profile-panel text-center">

            <div
              class="w-32 h-32 bg-blue-600 rounded-full
                     flex items-center justify-center
                     text-white text-4xl font-semibold
                     mx-auto mb-5 shadow-lg"
            >
              {{ userProfile.first_name?.charAt(0) }}
            </div>

            <h3 class="text-lg font-semibold text-white">
              {{ userProfile.name }}
            </h3>

            <p class="text-sm text-slate-400 mt-1">
              {{ userProfile.position }}
            </p>

            <button
              class="mt-5 px-4 py-2 text-sm
                     bg-blue-600 text-white rounded-lg
                     hover:bg-blue-700 transition"
            >
              Change Photo
            </button>

          </div>

        </div>


        <!-- Profile Information -->
        <div class="lg:col-span-2">

          <form
            @submit.prevent="updateProfile"
            class="space-y-5"
          >

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

              <!-- Full Name -->
              <div>
                <label class="form-label">
                  Full Name
                </label>

                <input
                  v-model="userProfile.name"
                  type="text"
                  class="form-input"
                />
              </div>


              <!-- Employee ID -->
              <div>
                <label class="form-label">
                  Employee ID
                </label>

                <input
                  v-model="userProfile.employeeId"
                  type="text"
                  disabled
                  class="form-input-disabled"
                />
              </div>


              <!-- Position -->
              <div>
                <label class="form-label">
                  Position
                </label>

                <input
                  v-model="userProfile.position"
                  type="text"
                  class="form-input"
                />
              </div>


              <!-- Department -->
              <div>
                <label class="form-label">
                  Department
                </label>

                <select
                  v-model="userProfile.department"
                  class="form-input"
                >
                  <option value="Computer Science">
                    Computer Science
                  </option>

                  <option value="Mathematics">
                    Mathematics
                  </option>

                  <option value="English">
                    English
                  </option>

                  <option value="Science">
                    Science
                  </option>
                </select>
              </div>


              <!-- Gender -->
              <div>
                <label class="form-label">
                  Gender
                </label>

                <select
                  v-model="userProfile.gender"
                  class="form-input"
                >
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Other">Other</option>
                </select>
              </div>

            </div>


            <div class="flex justify-end pt-4 border-t border-slate-700">

              <button
                type="submit"
                class="primary-button"
              >
                Save Changes
              </button>

            </div>

          </form>

        </div>

      </div>

    </div>


    <!-- ========================= -->
    <!-- ACCOUNT SETTINGS -->
    <!-- ========================= -->
    <div
      v-if="activeTab === 'account'"
      class="neo-card p-6"
    >

      <div class="mb-6">
        <h2 class="text-xl font-semibold text-white">
          Account Settings
        </h2>

        <p class="text-sm text-slate-400 mt-1">
          Manage your account credentials and contact information.
        </p>
      </div>


      <div class="space-y-8">


        <!-- ================= EMAIL ================= -->
        <section>

          <h3 class="section-title">
            Email Settings
          </h3>

          <form
            @submit.prevent="updateEmail"
            class="space-y-5"
          >

            <div>
              <label class="form-label">
                Current Email
              </label>

              <input
                v-model="accountSettings.email.current"
                type="email"
                disabled
                class="form-input-disabled"
              />
            </div>


            <div>
              <label class="form-label">
                New Email
              </label>

              <input
                v-model="accountSettings.email.new"
                type="email"
                class="form-input"
              />
            </div>


            <div>
              <label class="form-label">
                Confirm New Email
              </label>

              <input
                v-model="accountSettings.email.confirm"
                type="email"
                class="form-input"
              />
            </div>


            <button
              type="submit"
              class="primary-button"
            >
              Update Email
            </button>

          </form>

        </section>


        <!-- ================= PASSWORD ================= -->
        <section class="settings-section">

          <h3 class="section-title">
            Password Settings
          </h3>

          <form
            @submit.prevent="updatePassword"
            class="space-y-5"
          >

            <!-- Current Password -->
            <div>

              <label class="form-label">
                Current Password
              </label>

              <div class="relative">

                <input
                  v-model="accountSettings.password.current"
                  :type="showCurrentPassword ? 'text' : 'password'"
                  class="form-input pr-10"
                />

                <button
                  type="button"
                  @click="showCurrentPassword = !showCurrentPassword"
                  class="password-toggle"
                >

                  <Eye
                    v-if="showCurrentPassword"
                    class="w-4 h-4"
                  />

                  <EyeOff
                    v-else
                    class="w-4 h-4"
                  />

                </button>

              </div>

            </div>


            <!-- New Password -->
            <div>

              <label class="form-label">
                New Password
              </label>

              <div class="relative">

                <input
                  v-model="accountSettings.password.new"
                  :type="showNewPassword ? 'text' : 'password'"
                  class="form-input pr-10"
                />

                <button
                  type="button"
                  @click="showNewPassword = !showNewPassword"
                  class="password-toggle"
                >

                  <Eye
                    v-if="showNewPassword"
                    class="w-4 h-4"
                  />

                  <EyeOff
                    v-else
                    class="w-4 h-4"
                  />

                </button>

              </div>

            </div>


            <!-- Confirm Password -->
            <div>

              <label class="form-label">
                Confirm New Password
              </label>

              <div class="relative">

                <input
                  v-model="accountSettings.password.confirm"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  class="form-input pr-10"
                />

                <button
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="password-toggle"
                >

                  <Eye
                    v-if="showConfirmPassword"
                    class="w-4 h-4"
                  />

                  <EyeOff
                    v-else
                    class="w-4 h-4"
                  />

                </button>

              </div>

            </div>


            <!-- Password Requirements -->
            <div class="info-box">

              <p class="text-sm text-blue-300">

                <strong>Password Requirements:</strong>

                <br>

                • At least 8 characters long
                <br>

                • Contains uppercase and lowercase letters
                <br>

                • Contains at least one number
                <br>

                • Contains at least one special character

              </p>

            </div>


            <button
              type="submit"
              class="primary-button"
            >
              Update Password
            </button>

          </form>

        </section>


        <!-- ================= PHONE ================= -->
        <section class="settings-section">

          <h3 class="section-title">
            Phone Settings
          </h3>

          <form
            @submit.prevent="updatePhone"
            class="space-y-5"
          >

            <div>

              <label class="form-label">
                Current Phone Number
              </label>

              <input
                v-model="accountSettings.phone.current"
                type="tel"
                disabled
                class="form-input-disabled"
              />

            </div>


            <div>

              <label class="form-label">
                New Phone Number
              </label>

              <input
                v-model="accountSettings.phone.new"
                type="tel"
                class="form-input"
              />

            </div>


            <button
              type="submit"
              class="primary-button"
            >
              Update Phone
            </button>

          </form>

        </section>


        <!-- ================= POSITION ================= -->
        <section class="settings-section">

          <h3 class="section-title">
            Position Settings
          </h3>

          <form
            @submit.prevent="updatePosition"
            class="space-y-5"
          >

            <div>

              <label class="form-label">
                Current Position
              </label>

              <input
                v-model="accountSettings.position.current"
                type="text"
                disabled
                class="form-input-disabled"
              />

            </div>


            <div>

              <label class="form-label">
                New Position
              </label>

              <input
                v-model="accountSettings.position.new"
                type="text"
                disabled
                class="form-input-disabled"
              />

            </div>


            <div>

              <label class="form-label">
                Reason for Change
              </label>

              <textarea
                v-model="accountSettings.position.reason"
                rows="3"
                class="form-input"
                placeholder="Please provide reason for position change"
              ></textarea>

            </div>


            <button
              type="submit"
              class="primary-button"
            >
              Request Position Change
            </button>

          </form>

        </section>

      </div>

    </div>


    <!-- ========================= -->
    <!-- HELP DESK -->
    <!-- ========================= -->
    <div
      v-if="activeTab === 'help'"
      class="neo-card p-6"
    >

      <div class="mb-6">

        <h2 class="text-xl font-semibold text-white">
          Help Desk
        </h2>

        <p class="text-sm text-slate-400 mt-1">
          Find answers or submit a support request.
        </p>

      </div>


      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- FAQ -->
        <div>

          <h3 class="section-title">
            Frequently Asked Questions
          </h3>

          <div class="space-y-3">

            <div
              v-for="faq in faqs"
              :key="faq.id"
              class="faq-card"
            >

              <button
                @click="toggleFAQ(faq.id)"
                class="w-full px-4 py-4 text-left
                       flex justify-between items-center
                       hover:bg-slate-800 transition"
              >

                <span class="font-medium text-white">
                  {{ faq.question }}
                </span>

                <ChevronDown
                  :class="[
                    'w-4 h-4 text-slate-400 transition-transform',
                    expandedFAQ === faq.id ? 'rotate-180' : ''
                  ]"
                />

              </button>


              <div
                v-if="expandedFAQ === faq.id"
                class="px-4 py-4 border-t border-slate-700"
              >

                <p class="text-sm text-slate-300 leading-relaxed">
                  {{ faq.answer }}
                </p>

              </div>

            </div>

          </div>

        </div>


        <!-- Support Ticket -->
        <div>

          <h3 class="section-title">
            Submit Support Ticket
          </h3>

          <form
            @submit.prevent="submitTicket"
            class="space-y-5"
          >

            <div>

              <label class="form-label">
                Issue Type
              </label>

              <select
                v-model="ticket.type"
                class="form-input"
              >

                <option value="">
                  Select Issue Type
                </option>

                <option value="technical">
                  Technical Issue
                </option>

                <option value="account">
                  Account Problem
                </option>

                <option value="leave">
                  Leave Application Issue
                </option>

                <option value="attendance">
                  Attendance Issue
                </option>

                <option value="other">
                  Other
                </option>

              </select>

            </div>


            <div>

              <label class="form-label">
                Subject
              </label>

              <input
                v-model="ticket.subject"
                type="text"
                class="form-input"
              />

            </div>


            <div>

              <label class="form-label">
                Description
              </label>

              <textarea
                v-model="ticket.description"
                rows="4"
                class="form-input"
                placeholder="Please describe your issue in detail"
              ></textarea>

            </div>


            <div>

              <label class="form-label">
                Priority
              </label>

              <select
                v-model="ticket.priority"
                class="form-input"
              >

                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>

              </select>

            </div>


            <button
              type="submit"
              class="primary-button"
            >
              Submit Ticket
            </button>

          </form>

        </div>

      </div>

    </div>


    <!-- ========================= -->
    <!-- ABOUT US -->
    <!-- ========================= -->
    <div
      v-if="activeTab === 'about'"
      class="neo-card p-6"
    >

      <div class="mb-6">

        <h2 class="text-xl font-semibold text-white">
          About EL System
        </h2>

        <p class="text-sm text-slate-400 mt-1">
          Information about the system and support services.
        </p>

      </div>


      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- System Information -->
        <div>

          <h3 class="section-title">
            System Information
          </h3>

          <div class="space-y-4">

            <div class="dark-info-card">

              <h4 class="info-title">
                Version Information
              </h4>

              <p class="info-text">
                EL System v2.1.0
              </p>

              <p class="info-text">
                Last Updated: January 15, 2024
              </p>

            </div>


            <div class="dark-info-card">

              <h4 class="info-title">
                System Features
              </h4>

              <ul class="info-text space-y-1">

                <li>• Online Leave Application</li>
                <li>• Attendance Tracking</li>
                <li>• Faculty Records Management</li>
                <li>• Report Generation</li>
                <li>• Multi-level Approval System</li>

              </ul>

            </div>


            <div class="dark-info-card">

              <h4 class="info-title">
                System Requirements
              </h4>

              <ul class="info-text space-y-1">

                <li>• Modern web browser</li>
                <li>• Internet connection</li>
                <li>• Minimum screen resolution: 1024x768</li>

              </ul>

            </div>

          </div>

        </div>


        <!-- Contact Information -->
        <div>

          <h3 class="section-title">
            Contact Information
          </h3>

          <div class="space-y-4">

            <div class="dark-info-card">

              <h4 class="info-title">
                Technical Support
              </h4>

              <p class="info-text">
                Email: support@elsystem.edu
              </p>

              <p class="info-text">
                Phone: (123) 456-7890
              </p>

              <p class="info-text">
                Hours: Monday - Friday, 8:00 AM - 5:00 PM
              </p>

            </div>


            <div class="dark-info-card">

              <h4 class="info-title">
                ADAS Office
              </h4>

              <p class="info-text">
                Email: adas@university.edu
              </p>

              <p class="info-text">
                Phone: (123) 456-7891
              </p>

              <p class="info-text">
                Location: Admin Building, Room 201
              </p>

            </div>


            <div class="dark-info-card">

              <h4 class="info-title">
                Development Team
              </h4>

              <p class="info-text">
                Lead Developer: John Developer
              </p>

              <p class="info-text">
                UI/UX Designer: Sarah Designer
              </p>

              <p class="info-text">
                System Administrator: Mike Admin
              </p>

            </div>

          </div>

        </div>

      </div>


      <!-- Privacy -->
      <div class="mt-8 pt-8 border-t border-slate-700">

        <h3 class="section-title">
          Privacy & Security
        </h3>

        <div class="info-box">

          <p class="text-sm text-blue-300">

            <strong>Privacy Policy:</strong>
            EL System is committed to protecting your privacy.
            All personal information is stored securely and used only
            for official university purposes.

          </p>

        </div>

      </div>

    </div>


    <!-- ========================= -->
    <!-- SIGN OUT -->
    <!-- ========================= -->
    <div class="neo-card p-6">

      <div
        class="flex flex-col sm:flex-row
               items-start sm:items-center
               justify-between gap-4"
      >

        <div>

          <h3 class="text-lg font-medium text-white">
            Sign Out
          </h3>

          <p class="text-sm text-slate-400 mt-1">
            Sign out of your account and return to login page.
          </p>

        </div>


        <button
          @click="logout"
          class="px-6 py-2
                 bg-red-600 text-white
                 rounded-lg
                 hover:bg-red-700
                 transition"
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
  Eye,
  EyeOff
} from 'lucide-vue-next'

import {
  getMyProfile,
  updateMyProfile,
  updateEmail as updateEmailAPI,
  updatePassword as updatePasswordAPI,
  updatePhone as updatePhoneAPI
} from '@/services/employee'


// ===============================
// ROUTER
// ===============================

const router = useRouter()


// ===============================
// SETTINGS TABS
// ===============================

const activeTab = ref('profile')

const settingsTabs = [
  {
    id: 'profile',
    name: 'Employee Profile',
    icon: User
  },
  {
    id: 'account',
    name: 'Account',
    icon: Settings
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


// ===============================
// USER PROFILE
// ===============================

const userProfile = ref({

  first_name: '',
  middle_name: '',
  last_name: '',

  name: '',
  employeeId: '',
  position: '',
  department: '',
  gender: '',
  phone: ''

})


// ===============================
// ACCOUNT SETTINGS
// ===============================

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


// ===============================
// PASSWORD VISIBILITY
// ===============================

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)


// ===============================
// LOAD PROFILE
// ===============================

const loadProfile = async () => {

  try {

    const data = await getMyProfile()

    userProfile.value = {

      first_name: data.first_name,
      middle_name: data.middle_name,
      last_name: data.last_name,

      name:
        `${data.first_name} ${data.last_name}`,

      employeeId:
        data.employee_code,

      position:
        data.position,

      department:
        data.department,

      gender:
        data.sex,

      phone:
        data.contact_number

    }

    accountSettings.value.email.current =
      data.email

    accountSettings.value.phone.current =
      data.contact_number

    accountSettings.value.position.current =
      data.position

  }
  catch (error) {

    console.error(
      'Failed loading profile:',
      error
    )

  }

}


// ===============================
// FAQ
// ===============================

const expandedFAQ =
  ref<number | null>(null)

const faqs = [

  {
    id: 1,
    question: 'How do I apply for leave?',
    answer:
      'Navigate to the Leave Application page from the sidebar menu. Fill out the form with your leave details, attach any required documents, and submit it for ADAS approval.'
  },

  {
    id: 2,
    question: 'What is the leave approval process?',
    answer:
      'Leave applications go through a multi-step approval process: Faculty submits → ADAS reviews → Principal approves → ADAS processes → Division final approval.'
  },

  {
    id: 3,
    question: 'How many leave credits do I have?',
    answer:
      'You can view your available leave credits on your Dashboard or in the Faculty Records section.'
  },

  {
    id: 4,
    question: 'How do I check my attendance record?',
    answer:
      'Go to the Attendance page from the sidebar menu. You can view your daily attendance, monthly summaries, and annual attendance records.'
  }

]


const toggleFAQ = (id: number) => {

  expandedFAQ.value =
    expandedFAQ.value === id
      ? null
      : id

}


// ===============================
// SUPPORT TICKET
// ===============================

const ticket = ref({

  type: '',
  subject: '',
  description: '',
  priority: 'medium'

})


// ===============================
// UPDATE PROFILE
// ===============================

const updateProfile = async () => {

  try {

    await updateMyProfile({

      first_name:
        userProfile.value.first_name,

      middle_name:
        userProfile.value.middle_name,

      last_name:
        userProfile.value.last_name,

      sex:
        userProfile.value.gender,

      department:
        userProfile.value.department,

      position:
        userProfile.value.position

    })

    alert(
      'Profile updated successfully'
    )

  }
  catch (error) {

    console.error(error)

    alert(
      'Update failed'
    )

  }

}


// ===============================
// UPDATE EMAIL
// ===============================

const updateEmail = async () => {

  try {

    await updateEmailAPI(
      accountSettings.value.email.new
    )

    alert(
      'Email updated successfully'
    )

    accountSettings.value.email.current =
      accountSettings.value.email.new

    accountSettings.value.email.new = ''

    accountSettings.value.email.confirm = ''

  }
  catch (error) {

    console.error(error)

    alert(
      'Email update failed'
    )

  }

}


// ===============================
// UPDATE PASSWORD
// ===============================

const updatePassword = async () => {

  try {

    await updatePasswordAPI({

      current_password:
        accountSettings.value.password.current,

      new_password:
        accountSettings.value.password.new,

      new_password_confirmation:
        accountSettings.value.password.confirm

    })

    alert(
      'Password updated successfully'
    )

    accountSettings.value.password.current = ''
    accountSettings.value.password.new = ''
    accountSettings.value.password.confirm = ''

  }
  catch (error) {

    console.error(error)

    alert(
      'Password update failed'
    )

  }

}


// ===============================
// UPDATE PHONE
// ===============================

const updatePhone = async () => {

  try {

    await updatePhoneAPI(
      accountSettings.value.phone.new
    )

    alert(
      'Phone updated successfully'
    )

    accountSettings.value.phone.current =
      accountSettings.value.phone.new

    accountSettings.value.phone.new = ''

  }
  catch (error) {

    console.error(error)

    alert(
      'Phone update failed'
    )

  }

}


// ===============================
// UPDATE POSITION
// ===============================

const updatePosition = () => {

  console.log(
    'Update position:',
    accountSettings.value.position
  )

}


// ===============================
// SUPPORT TICKET
// ===============================

const submitTicket = () => {

  console.log(
    'Submit support ticket:',
    ticket.value
  )

}


// ===============================
// LOGOUT
// ===============================

const logout = () => {

  router.push('/login')

}


// ===============================
// INITIAL LOAD
// ===============================

onMounted(() => {

  loadProfile()

})

</script>


<style scoped>

/* ========================================= */
/* DASHBOARD BACKGROUND */
/* ========================================= */

.dashboard-shell {
  background: #080D14;
}


/* ========================================= */
/* MAIN CARD */
/* ========================================= */

.neo-card {

  background: #111D2E;

  border: 1px solid #1E293B;

  border-radius: 1.4rem;

  box-shadow:
    0 10px 22px rgba(15, 23, 42, 0.25);

  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;

}


.neo-card:hover {

  box-shadow:
    0 14px 28px rgba(15, 23, 42, 0.35);

}


/* ========================================= */
/* PROFILE PANEL */
/* ========================================= */

.profile-panel {

  background: #0D1726;

  border: 1px solid #1E293B;

  border-radius: 1rem;

  padding: 1.5rem;

}


/* ========================================= */
/* FORM LABEL */
/* ========================================= */

.form-label {

  display: block;

  font-size: 0.875rem;

  font-weight: 500;

  color: #CBD5E1;

  margin-bottom: 0.5rem;

}


/* ========================================= */
/* FORM INPUT */
/* ========================================= */

.form-input {

  width: 100%;

  padding: 0.625rem 0.75rem;

  background: #FFFFFF;

  color: #111827;

  border: 1px solid #CBD5E1;

  border-radius: 0.5rem;

  outline: none;

  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease;

}


.form-input:focus {

  border-color: #3B82F6;

  box-shadow:
    0 0 0 2px rgba(59, 130, 246, 0.2);

}


/* ========================================= */
/* DISABLED INPUT */
/* ========================================= */

.form-input-disabled {

  width: 100%;

  padding: 0.625rem 0.75rem;

  background: #E5E7EB;

  color: #6B7280;

  border: 1px solid #CBD5E1;

  border-radius: 0.5rem;

}


/* ========================================= */
/* PRIMARY BUTTON */
/* ========================================= */

.primary-button {

  padding: 0.625rem 1.25rem;

  background: #2563EB;

  color: white;

  border-radius: 0.5rem;

  font-size: 0.875rem;

  font-weight: 500;

  transition:
    background 0.2s ease,
    transform 0.2s ease;

}


.primary-button:hover {

  background: #1D4ED8;

  transform: translateY(-1px);

}


/* ========================================= */
/* SECTION TITLES */
/* ========================================= */

.section-title {

  font-size: 1.125rem;

  font-weight: 600;

  color: #F8FAFC;

  margin-bottom: 1rem;

}


/* ========================================= */
/* SETTINGS SECTION */
/* ========================================= */

.settings-section {

  border-top: 1px solid #334155;

  padding-top: 2rem;

}


/* ========================================= */
/* PASSWORD TOGGLE */
/* ========================================= */

.password-toggle {

  position: absolute;

  right: 0.75rem;

  top: 50%;

  transform: translateY(-50%);

  color: #94A3B8;

  transition: color 0.2s ease;

}


.password-toggle:hover {

  color: #3B82F6;

}


/* ========================================= */
/* INFO BOX */
/* ========================================= */

.info-box {

  background: rgba(30, 64, 175, 0.15);

  border: 1px solid rgba(59, 130, 246, 0.35);

  border-radius: 0.75rem;

  padding: 1rem;

}


/* ========================================= */
/* FAQ CARD */
/* ========================================= */

.faq-card {

  border: 1px solid #334155;

  border-radius: 0.75rem;

  overflow: hidden;

  background: #0D1726;

}


/* ========================================= */
/* DARK INFORMATION CARD */
/* ========================================= */

.dark-info-card {

  background: #0D1726;

  border: 1px solid #1E293B;

  border-radius: 0.75rem;

  padding: 1rem;

}


.info-title {

  font-weight: 600;

  color: #F8FAFC;

  margin-bottom: 0.5rem;

}


.info-text {

  font-size: 0.875rem;

  color: #94A3B8;

}


/* ========================================= */
/* TEXT SPACING */
/* ========================================= */

.neo-card h2,
.neo-card h3,
.neo-card h4,
.neo-card p,
.neo-card span,
.neo-card button {

  letter-spacing: -0.01em;

}

</style>