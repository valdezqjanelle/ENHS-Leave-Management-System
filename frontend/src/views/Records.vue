<template>
  <div class="space-y-5 text-gray-100">

    <!-- admin -->

    <div v-if="userRole === 'ADAS'" class="space-y-5">

      <!-- Header -->
      <div class="neo-card p-5">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">

          <div>
            <h2 class="text-xl font-semibold text-white">
              Faculty Records
            </h2>

            <p class="text-sm text-gray-400 mt-1">
              Manage and view faculty profiles and records
            </p>
          </div>

          <div class="flex gap-2">
            <button
              @click="exportRecords"
              class="dashboard-button secondary"
            >
              Export Records
            </button>

            <button
              @click="showAddFacultyModal = true"
              class="dashboard-button primary"
            >
              + Add Faculty
            </button>
          </div>

        </div>
      </div>


      <!-- Search / Filters -->
      <div class="neo-card p-4">

        <div class="flex flex-col lg:flex-row gap-3">

          <!-- Search -->
          <div class="flex-1">
            <div class="relative">

              <Search
                class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 w-4 h-4"
              />

              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search by name, department, position..."
                class="dashboard-input pl-10"
              />

            </div>
          </div>


          <!-- Department -->
          <select
            v-model="filterDepartment"
            class="dashboard-input lg:w-52"
          >
            <option value="">All Departments</option>
            <option value="Computer Science">Computer Science</option>
            <option value="Mathematics">Mathematics</option>
            <option value="English">English</option>
            <option value="Science">Science</option>
          </select>


          <!-- Status -->
          <select
            v-model="filterStatus"
            class="dashboard-input lg:w-40"
          >
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="on-leave">On Leave</option>
            <option value="inactive">Inactive</option>
          </select>

        </div>
      </div>


      <!-- Faculty Cards -->
      <div
        class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4"
      >

        <div
          v-for="faculty in filteredFaculty"
          :key="faculty.id"
          class="neo-card faculty-card cursor-pointer"
          @click="viewFacultyDetails(faculty)"
        >

          <div class="p-5">

            <!-- Faculty Header -->
            <div class="flex items-start justify-between mb-4">

              <div class="flex items-center min-w-0">

                <div
                  class="w-12 h-12 shrink-0 rounded-xl bg-blue-600/20 border border-blue-500/20 flex items-center justify-center text-blue-400 text-lg font-semibold"
                >
                  {{ faculty.name.charAt(0) }}
                </div>

                <div class="ml-3 min-w-0">

                  <h3
                    class="text-base font-semibold text-white truncate"
                  >
                    {{ faculty.name }}
                  </h3>

                  <p class="text-sm text-gray-400 truncate">
                    {{ faculty.position }}
                  </p>

                  <div class="flex items-center mt-1">

                    <div
                      class="w-1.5 h-1.5 rounded-full mr-2"
                      :class="getStatusIndicatorClass(faculty.status)"
                    ></div>

                    <span class="text-xs text-gray-500 capitalize">
                      {{ faculty.status.replace('-', ' ') }}
                    </span>

                  </div>

                </div>

              </div>


              <!-- Actions -->
              <div class="flex space-x-1 shrink-0">

                <button
                  @click.stop="editFaculty(faculty)"
                  class="icon-button hover:text-blue-400 hover:bg-blue-500/10"
                  title="Edit"
                >
                  <Edit class="w-4 h-4" />
                </button>

                <button
                  @click.stop="deleteFaculty(faculty)"
                  class="icon-button hover:text-red-400 hover:bg-red-500/10"
                  title="Delete"
                >
                  <Trash2 class="w-4 h-4" />
                </button>

              </div>

            </div>


            <!-- Faculty Information -->
            <div class="space-y-2.5">

              <div class="info-row">
                <Building class="info-icon" />
                <span>{{ faculty.department }}</span>
              </div>

              <div class="info-row">
                <Mail class="info-icon" />
                <span class="truncate">{{ faculty.email }}</span>
              </div>

              <div class="info-row">
                <Phone class="info-icon" />
                <span>{{ faculty.phone }}</span>
              </div>

            </div>


            <!-- Stats -->
            <div class="mt-4 pt-4 border-t border-[#1E293B]">

              <div class="grid grid-cols-3 gap-2 text-center">

                <div>
                  <p class="stat-label">
                    Leave Credits
                  </p>

                  <p class="stat-value">
                    {{ faculty.leaveCredits }}
                  </p>
                </div>

                <div>
                  <p class="stat-label">
                    Absences
                  </p>

                  <p class="stat-value">
                    {{ faculty.absences }}
                  </p>
                </div>

                <div>
                  <p class="stat-label">
                    Leaves Filed
                  </p>

                  <p class="stat-value">
                    {{ faculty.leavesFiled }}
                  </p>
                </div>

              </div>

            </div>

          </div>
        </div>

      </div>


      <!-- Empty State -->
      <div
        v-if="filteredFaculty.length === 0"
        class="neo-card p-10 text-center"
      >
        <Search class="w-10 h-10 text-gray-600 mx-auto mb-3" />

        <h3 class="text-base font-medium text-gray-300">
          No faculty records found
        </h3>

        <p class="text-sm text-gray-500 mt-1">
          Try changing your search or filters.
        </p>
      </div>


      <!-- ===================================================== -->
      <!-- FACULTY DETAILS MODAL -->
      <!-- ===================================================== -->

      <div
        v-if="selectedFaculty"
        class="modal-overlay"
      >

        <div class="modal-card max-w-5xl">

          <!-- Modal Header -->
          <div class="modal-header">

            <div>
              <h3 class="text-lg font-semibold text-white">
                Faculty Profile
              </h3>

              <p class="text-sm text-gray-500 mt-0.5">
                {{ selectedFaculty.name }}
              </p>
            </div>

            <button
              @click="selectedFaculty = null"
              class="modal-close"
            >
              <X class="w-5 h-5" />
            </button>

          </div>


          <div class="p-5">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

              <!-- Profile -->
              <div class="dark-section">

                <div class="text-center">

                  <div
                    class="w-20 h-20 rounded-2xl bg-blue-600/20 border border-blue-500/20 flex items-center justify-center text-blue-400 text-2xl font-semibold mx-auto mb-3"
                  >
                    {{ selectedFaculty.name.charAt(0) }}
                  </div>

                  <h4 class="text-base font-semibold text-white">
                    {{ selectedFaculty.name }}
                  </h4>

                  <p class="text-sm text-gray-400">
                    {{ selectedFaculty.position }}
                  </p>

                  <p class="text-xs text-gray-500 mt-1">
                    {{ selectedFaculty.department }}
                  </p>

                </div>


                <div class="mt-5 space-y-3">

                  <div class="info-row">
                    <Mail class="info-icon" />
                    <span>{{ selectedFaculty.email }}</span>
                  </div>

                  <div class="info-row">
                    <Phone class="info-icon" />
                    <span>{{ selectedFaculty.phone }}</span>
                  </div>

                  <div class="info-row">
                    <Calendar class="info-icon" />
                    <span>Joined {{ selectedFaculty.joinDate }}</span>
                  </div>

                </div>

              </div>


              <!-- Details -->
              <div class="lg:col-span-2 space-y-4">

                <!-- Leave Credits -->
                <div class="dark-section">

                  <h4 class="section-title">
                    Leave Credits
                  </h4>

                  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

                    <div class="record-stat">
                      <Heart class="record-icon text-red-400" />
                      <p class="record-number">
                        {{ selectedFaculty.leaveBreakdown.sick }}
                      </p>
                      <p class="record-label">
                        Sick Leave
                      </p>
                    </div>

                    <div class="record-stat">
                      <Umbrella class="record-icon text-blue-400" />
                      <p class="record-number">
                        {{ selectedFaculty.leaveBreakdown.vacation }}
                      </p>
                      <p class="record-label">
                        Vacation
                      </p>
                    </div>

                    <div class="record-stat">
                      <User class="record-icon text-yellow-400" />
                      <p class="record-number">
                        {{ selectedFaculty.leaveBreakdown.personal }}
                      </p>
                      <p class="record-label">
                        Personal
                      </p>
                    </div>

                    <div class="record-stat">
                      <Baby class="record-icon text-purple-400" />
                      <p class="record-number">
                        {{ selectedFaculty.leaveBreakdown.maternity }}
                      </p>
                      <p class="record-label">
                        Maternity
                      </p>
                    </div>

                  </div>

                </div>


                <!-- Attendance -->
                <div class="dark-section">

                  <h4 class="section-title">
                    Attendance Summary
                  </h4>

                  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

                    <div class="summary-box green">
                      <p>Present</p>
                      <strong>
                        {{ selectedFaculty.attendance.present }}
                      </strong>
                    </div>

                    <div class="summary-box yellow">
                      <p>Late</p>
                      <strong>
                        {{ selectedFaculty.attendance.late }}
                      </strong>
                    </div>

                    <div class="summary-box red">
                      <p>Absent</p>
                      <strong>
                        {{ selectedFaculty.attendance.absent }}
                      </strong>
                    </div>

                    <div class="summary-box blue">
                      <p>Leave Days</p>
                      <strong>
                        {{ selectedFaculty.attendance.leaveDays }}
                      </strong>
                    </div>

                  </div>

                </div>


                <!-- Leave History -->
                <div class="dark-section">

                  <h4 class="section-title">
                    Recent Leave History
                  </h4>

                  <div
                    v-if="selectedFaculty.recentLeaves.length"
                    class="space-y-2"
                  >

                    <div
                      v-for="leave in selectedFaculty.recentLeaves"
                      :key="leave.id"
                      class="flex items-center justify-between gap-3 p-3 rounded-lg bg-[#080D14] border border-[#1E293B]"
                    >

                      <div class="flex items-center min-w-0">

                        <div
                          class="w-2 h-2 rounded-full mr-3 shrink-0"
                          :class="getLeaveStatusClass(leave.status)"
                        ></div>

                        <div class="min-w-0">

                          <p class="text-sm font-medium text-gray-200">
                            {{ leave.type }}
                          </p>

                          <p class="text-xs text-gray-500">
                            {{ formatDateRange(leave.startDate, leave.endDate) }}
                          </p>

                        </div>

                      </div>

                      <span
                        class="px-2 py-1 text-xs font-medium rounded-full shrink-0"
                        :class="getLeaveStatusBadgeClass(leave.status)"
                      >
                        {{ leave.status }}
                      </span>

                    </div>

                  </div>

                  <p
                    v-else
                    class="text-sm text-gray-500"
                  >
                    No recent leave records.
                  </p>

                </div>

              </div>

            </div>

          </div>

        </div>

      </div>


      <!-- ===================================================== -->
      <!-- ADD FACULTY MODAL -->
      <!-- ===================================================== -->

      <div
        v-if="showAddFacultyModal"
        class="modal-overlay"
      >

        <div class="modal-card max-w-2xl">

          <div class="modal-header">

            <div>
              <h3 class="text-lg font-semibold text-white">
                Add New Faculty
              </h3>

              <p class="text-xs text-gray-500 mt-0.5">
                Create a new faculty record
              </p>
            </div>

            <button
              @click="showAddFacultyModal = false"
              class="modal-close"
            >
              <X class="w-5 h-5" />
            </button>

          </div>


          <form
            @submit.prevent="addFaculty"
            class="p-5 space-y-4"
          >

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

              <div>
                <label class="form-label">
                  Full Name
                </label>

                <input
                  v-model="newFaculty.name"
                  type="text"
                  required
                  class="dashboard-input"
                />
              </div>


              <div>
                <label class="form-label">
                  Position
                </label>

                <input
                  v-model="newFaculty.position"
                  type="text"
                  required
                  class="dashboard-input"
                />
              </div>


              <div>
                <label class="form-label">
                  Department
                </label>

                <select
                  v-model="newFaculty.department"
                  required
                  class="dashboard-input"
                >
                  <option value="">Select Department</option>
                  <option value="Computer Science">Computer Science</option>
                  <option value="Mathematics">Mathematics</option>
                  <option value="English">English</option>
                  <option value="Science">Science</option>
                </select>
              </div>


              <div>
                <label class="form-label">
                  Email
                </label>

                <input
                  v-model="newFaculty.email"
                  type="email"
                  required
                  class="dashboard-input"
                />
              </div>


              <div>
                <label class="form-label">
                  Phone
                </label>

                <input
                  v-model="newFaculty.phone"
                  type="tel"
                  required
                  class="dashboard-input"
                />
              </div>


              <div>
                <label class="form-label">
                  Employee ID
                </label>

                <input
                  v-model="newFaculty.employeeId"
                  type="text"
                  required
                  class="dashboard-input"
                />
              </div>

            </div>


            <div class="flex justify-end gap-2 pt-3 border-t border-[#1E293B]">

              <button
                type="button"
                @click="showAddFacultyModal = false"
                class="dashboard-button secondary"
              >
                Cancel
              </button>

              <button
                type="submit"
                class="dashboard-button primary"
              >
                Add Faculty
              </button>

            </div>

          </form>

        </div>

      </div>

    </div>


    <!-- ========================================================= -->
    <!-- FACULTY PERSONAL RECORDS -->
    <!-- ========================================================= -->

    <div v-else class="space-y-5">

      <!-- Header -->
      <div class="neo-card p-5">

        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">

          <div>
            <h2 class="text-xl font-semibold text-white">
              My Records
            </h2>

            <p class="text-sm text-gray-400 mt-1">
              View your personal profile and records
            </p>
          </div>

          <div class="flex gap-2">

            <button
              @click="exportPersonalRecords"
              class="dashboard-button secondary"
            >
              Export Records
            </button>

            <button
              @click="showEditProfileModal = true"
              class="dashboard-button primary"
            >
              Edit Profile
            </button>

          </div>

        </div>

      </div>


      <!-- Personal Profile -->
      <div class="neo-card p-5">

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">

          <div
            class="w-20 h-20 shrink-0 rounded-2xl bg-blue-600/20 border border-blue-500/20 flex items-center justify-center text-blue-400 text-2xl font-semibold"
          >
            {{ personalProfile.name.charAt(0) }}
          </div>

          <div class="flex-1 min-w-0">

            <h3 class="text-xl font-semibold text-white">
              {{ personalProfile.name }}
            </h3>

            <p class="text-sm text-gray-400 mt-0.5">
              {{ personalProfile.position }}
            </p>

            <p class="text-xs text-gray-500 mt-1">
              {{ personalProfile.department }}
            </p>


            <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-2">

              <div class="info-row">
                <Mail class="info-icon" />
                <span class="truncate">
                  {{ personalProfile.email }}
                </span>
              </div>

              <div class="info-row">
                <Phone class="info-icon" />
                <span>
                  {{ personalProfile.phone }}
                </span>
              </div>

              <div class="info-row">
                <Calendar class="info-icon" />
                <span>
                  Joined {{ personalProfile.joinDate }}
                </span>
              </div>

            </div>

          </div>


          <div class="flex items-center shrink-0">

            <div
              class="w-2 h-2 rounded-full mr-2"
              :class="getStatusIndicatorClass(personalProfile.status)"
            ></div>

            <span class="text-xs text-gray-400 capitalize">
              {{ personalProfile.status.replace('-', ' ') }}
            </span>

          </div>

        </div>

      </div>


      <!-- Leave Credits -->
      <div class="neo-card p-5">

        <h3 class="section-title mb-4">
          My Leave Credits
        </h3>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

          <div class="record-stat">

            <Heart class="record-icon text-red-400" />

            <p class="record-number">
              {{ personalProfile.leaveCredits.vacation }}
            </p>

            <p class="record-label">
              Vacation Leave
            </p>

          </div>


          <div class="record-stat">

            <Umbrella class="record-icon text-blue-400" />

            <p class="record-number">
              {{ personalProfile.leaveCredits.sick }}
            </p>

            <p class="record-label">
              Sick Leave
            </p>

          </div>


          <div class="record-stat">

            <User class="record-icon text-green-400" />

            <p class="record-number">
              {{ personalProfile.leaveCredits.maternity }}
            </p>

            <p class="record-label">
              Maternity Leave
            </p>

          </div>


          <div class="record-stat">

            <Baby class="record-icon text-purple-400" />

            <p class="record-number">
              {{ personalProfile.leaveCredits.paternity }}
            </p>

            <p class="record-label">
              Paternity Leave
            </p>

          </div>

        </div>

      </div>


      <!-- Recent Leave History -->
      <div class="neo-card p-5">

        <h3 class="section-title mb-4">
          My Recent Leave History
        </h3>

        <div class="space-y-2">

          <div
            v-for="leave in personalProfile.recentLeaves"
            :key="leave.id"
            class="flex items-center justify-between gap-3 p-3 rounded-lg bg-[#080D14] border border-[#1E293B]"
          >

            <div class="flex items-center min-w-0">

              <div
                class="w-2 h-2 rounded-full mr-3 shrink-0"
                :class="getLeaveStatusClass(leave.status)"
              ></div>

              <div class="min-w-0">

                <p class="text-sm font-medium text-gray-200">
                  {{ leave.type }}
                </p>

                <p class="text-xs text-gray-500">
                  {{ formatDateRange(leave.startDate, leave.endDate) }}
                </p>

              </div>

            </div>

            <span
              class="px-2 py-1 text-xs font-medium rounded-full shrink-0"
              :class="getLeaveStatusBadgeClass(leave.status)"
            >
              {{ leave.status }}
            </span>

          </div>

        </div>

      </div>


      <!-- Edit Profile Modal -->
      <div
        v-if="showEditProfileModal"
        class="modal-overlay"
      >

        <div class="modal-card max-w-xl">

          <div class="modal-header">

            <div>
              <h3 class="text-lg font-semibold text-white">
                Edit My Profile
              </h3>

              <p class="text-xs text-gray-500 mt-0.5">
                Update your contact information
              </p>
            </div>

            <button
              @click="showEditProfileModal = false"
              class="modal-close"
            >
              <X class="w-5 h-5" />
            </button>

          </div>


          <form
            @submit.prevent="updateProfile"
            class="p-5 space-y-4"
          >

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

              <div>
                <label class="form-label">
                  Email
                </label>

                <input
                  v-model="personalProfile.email"
                  type="email"
                  required
                  class="dashboard-input"
                />
              </div>


              <div>
                <label class="form-label">
                  Phone
                </label>

                <input
                  v-model="personalProfile.phone"
                  type="tel"
                  required
                  class="dashboard-input"
                />
              </div>

            </div>


            <div class="flex justify-end gap-2 pt-3 border-t border-[#1E293B]">

              <button
                type="button"
                @click="showEditProfileModal = false"
                class="dashboard-button secondary"
              >
                Cancel
              </button>

              <button
                type="submit"
                class="dashboard-button primary"
              >
                Update Profile
              </button>

            </div>

          </form>

        </div>

      </div>

    </div>

  </div>
</template>


<script setup lang="ts">

import { ref, computed } from 'vue'

import {
  Search,
  Edit,
  Trash2,
  Building,
  Mail,
  Phone,
  Calendar,
  X,
  Heart,
  Umbrella,
  User,
  Baby
} from 'lucide-vue-next'


/* =========================================================
   TYPES
========================================================= */

interface Faculty {

  id: string

  name: string

  position: string

  department: string

  email: string

  phone: string

  status: 'active' | 'on-leave' | 'inactive'

  joinDate: string

  leaveCredits: number

  absences: number

  leavesFiled: number

  leaveBreakdown: {

    sick: number

    vacation: number

    personal: number

    maternity: number

  }

  attendance: {

    present: number

    late: number

    absent: number

    leaveDays: number

  }

  recentLeaves: Array<{

    id: string

    type: string

    startDate: string

    endDate: string

    status: string

  }>

}


/* =========================================================
   USER ROLE
========================================================= */

const userRole = ref<'ADAS' | 'Faculty'>(
  localStorage.getItem('userRole') === 'ADAS'
    ? 'ADAS'
    : 'Faculty'
)


/* =========================================================
   ADMIN STATE
========================================================= */

const searchQuery = ref('')

const filterDepartment = ref('')

const filterStatus = ref('')

const selectedFaculty = ref<Faculty | null>(null)

const showAddFacultyModal = ref(false)


/* =========================================================
   FACULTY PERSONAL PROFILE
========================================================= */

const showEditProfileModal = ref(false)

const personalProfile = ref({

  name: 'Jane Doe',

  position: 'Assistant Professor',

  department: 'Computer Science',

  email: 'jane.doe@university.edu',

  phone: '+1 234-567-8902',

  status: 'active' as 'active' | 'on-leave' | 'inactive',

  joinDate: 'August 20, 2021',

  leaveCredits: {

    vacation: 15,

    sick: 10,

    maternity: 5,

    paternity: 7

  },

  recentLeaves: [

    {
      id: '1',
      type: 'Vacation Leave',
      startDate: '2024-05-15',
      endDate: '2024-05-17',
      status: 'Approved'
    },

    {
      id: '2',
      type: 'Sick Leave',
      startDate: '2024-04-05',
      endDate: '2024-04-06',
      status: 'Pending'
    },

    {
      id: '3',
      type: 'Personal Leave',
      startDate: '2024-03-10',
      endDate: '2024-03-10',
      status: 'Approved'
    }

  ]

})


/* =========================================================
   NEW FACULTY
========================================================= */

const newFaculty = ref({

  name: '',

  position: '',

  department: '',

  email: '',

  phone: '',

  employeeId: ''

})


/* =========================================================
   MOCK DATA
========================================================= */

const mockFaculty: Faculty[] = [

  {

    id: '1',

    name: 'John Smith',

    position: 'Professor',

    department: 'Computer Science',

    email: 'john.smith@university.edu',

    phone: '+1 234-567-8901',

    status: 'active',

    joinDate: 'January 15, 2020',

    leaveCredits: 12,

    absences: 3,

    leavesFiled: 5,

    leaveBreakdown: {

      sick: 5,

      vacation: 10,

      personal: 3,

      maternity: 0

    },

    attendance: {

      present: 142,

      late: 8,

      absent: 3,

      leaveDays: 7

    },

    recentLeaves: [

      {
        id: '1',
        type: 'Sick Leave',
        startDate: '2024-01-15',
        endDate: '2024-01-16',
        status: 'Approved'
      },

      {
        id: '2',
        type: 'Vacation Leave',
        startDate: '2023-12-20',
        endDate: '2023-12-24',
        status: 'Approved'
      }

    ]

  },


  {

    id: '2',

    name: 'Sarah Johnson',

    position: 'Assistant Professor',

    department: 'Mathematics',

    email: 'sarah.johnson@university.edu',

    phone: '+1 234-567-8902',

    status: 'on-leave',

    joinDate: 'August 10, 2021',

    leaveCredits: 8,

    absences: 5,

    leavesFiled: 7,

    leaveBreakdown: {

      sick: 3,

      vacation: 8,

      personal: 2,

      maternity: 0

    },

    attendance: {

      present: 135,

      late: 12,

      absent: 5,

      leaveDays: 8

    },

    recentLeaves: [

      {
        id: '3',
        type: 'Personal Leave',
        startDate: '2024-01-20',
        endDate: '2024-01-20',
        status: 'Pending'
      },

      {
        id: '4',
        type: 'Vacation Leave',
        startDate: '2024-01-10',
        endDate: '2024-01-12',
        status: 'Approved'
      }

    ]

  }

]


/* =========================================================
   FILTER
========================================================= */

const filteredFaculty = computed(() => {

  return mockFaculty.filter(faculty => {

    const search =
      searchQuery.value.toLowerCase().trim()

    const matchesSearch =
      search === '' ||

      faculty.name
        .toLowerCase()
        .includes(search) ||

      faculty.department
        .toLowerCase()
        .includes(search) ||

      faculty.position
        .toLowerCase()
        .includes(search)


    const matchesDept =
      filterDepartment.value === '' ||

      faculty.department === filterDepartment.value


    const matchesStatus =
      filterStatus.value === '' ||

      faculty.status === filterStatus.value


    return (
      matchesSearch &&
      matchesDept &&
      matchesStatus
    )

  })

})


/* =========================================================
   STATUS
========================================================= */

const getStatusIndicatorClass = (status: string) => {

  const classes: Record<string, string> = {

    active: 'bg-green-500',

    'on-leave': 'bg-yellow-500',

    inactive: 'bg-red-500'

  }

  return classes[status] || 'bg-gray-500'

}


/* =========================================================
   LEAVE STATUS
========================================================= */

const getLeaveStatusClass = (status: string) => {

  const classes: Record<string, string> = {

    Approved: 'bg-green-500',

    Pending: 'bg-yellow-500',

    Rejected: 'bg-red-500'

  }

  return classes[status] || 'bg-gray-500'

}


const getLeaveStatusBadgeClass = (status: string) => {

  const classes: Record<string, string> = {

    Approved:
      'bg-green-500/10 text-green-400 border border-green-500/20',

    Pending:
      'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20',

    Rejected:
      'bg-red-500/10 text-red-400 border border-red-500/20'

  }

  return classes[status] ||
    'bg-gray-500/10 text-gray-400 border border-gray-500/20'

}


/* =========================================================
   DATE
========================================================= */

const formatDateRange = (
  start: string,
  end: string
) => {

  const startDate =
    new Date(start).toLocaleDateString(
      'en-US',
      {
        month: 'short',
        day: 'numeric'
      }
    )

  const endDate =
    new Date(end).toLocaleDateString(
      'en-US',
      {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      }
    )

  return `${startDate} - ${endDate}`

}


/* =========================================================
   FACULTY ACTIONS
========================================================= */

const viewFacultyDetails = (
  faculty: Faculty
) => {

  selectedFaculty.value = faculty

}


const editFaculty = (
  faculty: Faculty
) => {

  console.log(
    'Edit faculty:',
    faculty.name
  )

}


const deleteFaculty = (
  faculty: Faculty
) => {

  if (
    confirm(
      `Are you sure you want to delete ${faculty.name}?`
    )
  ) {

    console.log(
      'Delete faculty:',
      faculty.name
    )

  }

}


/* =========================================================
   ADD FACULTY
========================================================= */

const addFaculty = () => {

  console.log(
    'Add new faculty:',
    newFaculty.value
  )

  showAddFacultyModal.value = false

  newFaculty.value = {

    name: '',

    position: '',

    department: '',

    email: '',

    phone: '',

    employeeId: ''

  }

}


/* =========================================================
   EXPORT
========================================================= */

const exportRecords = () => {

  console.log(
    'Export faculty records'
  )

}


const exportPersonalRecords = () => {

  console.log(
    'Exporting personal records...'
  )

}


/* =========================================================
   PROFILE
========================================================= */

const updateProfile = () => {

  console.log(
    'Updating profile...'
  )

  showEditProfileModal.value = false

}

</script>


<style scoped>

/* =========================================================
   DASHBOARD BASE
========================================================= */

:global(body) {
  background: #080D14;
}


/* =========================================================
   CARDS
========================================================= */

.neo-card {

  background: #111D2E;

  border: 1px solid #1E293B;

  border-radius: 1rem;

  box-shadow:
    0 10px 22px
    rgba(15, 23, 42, 0.18);

  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease,
    border-color 0.2s ease;

}


.neo-card:hover {

  box-shadow:
    0 14px 26px
    rgba(15, 23, 42, 0.25);

}


/* =========================================================
   FACULTY CARD
========================================================= */

.faculty-card {

  transition:
    transform 0.2s ease,
    border-color 0.2s ease,
    box-shadow 0.2s ease;

}


.faculty-card:hover {

  transform: translateY(-2px);

  border-color: #263A55;

}


/* =========================================================
   INPUTS
========================================================= */

.dashboard-input {

  width: 100%;

  padding:
    0.6rem 0.75rem;

  background: #080D14;

  color: #E5E7EB;

  border:
    1px solid #1E293B;

  border-radius:
    0.65rem;

  font-size:
    0.875rem;

  outline: none;

  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease;

}


.dashboard-input::placeholder {

  color: #64748B;

}


.dashboard-input:focus {

  border-color:
    #2563EB;

  box-shadow:
    0 0 0 2px
    rgba(37, 99, 235, 0.15);

}


.dashboard-input option {

  background: #111D2E;

  color: #E5E7EB;

}


/* =========================================================
   BUTTONS
========================================================= */

.dashboard-button {

  display: inline-flex;

  align-items: center;

  justify-content: center;

  padding:
    0.6rem 0.9rem;

  border-radius:
    0.65rem;

  font-size:
    0.875rem;

  font-weight:
    500;

  transition:
    all 0.2s ease;

}


.dashboard-button.primary {

  background:
    #2563EB;

  color:
    white;

}


.dashboard-button.primary:hover {

  background:
    #1D4ED8;

}


.dashboard-button.secondary {

  background:
    #1E293B;

  color:
    #CBD5E1;

  border:
    1px solid #334155;

}


.dashboard-button.secondary:hover {

  background:
    #26354A;

  color:
    white;

}


/* =========================================================
   ICON BUTTON
========================================================= */

.icon-button {

  width: 2rem;

  height: 2rem;

  display: flex;

  align-items: center;

  justify-content: center;

  border-radius:
    0.5rem;

  color:
    #64748B;

  transition:
    all 0.2s ease;

}


/* =========================================================
   INFO ROW
========================================================= */

.info-row {

  display: flex;

  align-items: center;

  gap: 0.5rem;

  min-width: 0;

  color:
    #94A3B8;

  font-size:
    0.8rem;

}


.info-icon {

  width:
    0.9rem;

  height:
    0.9rem;

  flex-shrink: 0;

  color:
    #64748B;

}


/* =========================================================
   STATS
========================================================= */

.stat-label {

  font-size:
    0.68rem;

  color:
    #64748B;

}


.stat-value {

  margin-top:
    0.2rem;

  font-size:
    0.9rem;

  font-weight:
    600;

  color:
    #E2E8F0;

}


/* =========================================================
   SECTION
========================================================= */

.section-title {

  font-size:
    0.95rem;

  font-weight:
    600;

  color:
    #F1F5F9;

}


/* =========================================================
   DARK SECTION
========================================================= */

.dark-section {

  background:
    #111D2E;

  border:
    1px solid #1E293B;

  border-radius:
    0.85rem;

  padding:
    1rem;

}


/* =========================================================
   RECORD STAT
========================================================= */

.record-stat {

  text-align:
    center;

  padding:
    0.8rem;

  border-radius:
    0.75rem;

  background:
    #080D14;

  border:
    1px solid #1E293B;

}


.record-icon {

  width:
    1.2rem;

  height:
    1.2rem;

  margin:
    0 auto 0.4rem;

}


.record-number {

  font-size:
    1.25rem;

  font-weight:
    700;

  color:
    #F1F5F9;

}


.record-label {

  margin-top:
    0.15rem;

  font-size:
    0.7rem;

  color:
    #64748B;

}


/* =========================================================
   SUMMARY BOXES
========================================================= */

.summary-box {

  padding:
    0.8rem;

  border-radius:
    0.7rem;

  border:
    1px solid;

}


.summary-box p {

  font-size:
    0.7rem;

  font-weight:
    500;

}


.summary-box strong {

  display:
    block;

  margin-top:
    0.15rem;

  font-size:
    1.35rem;

}


.summary-box.green {

  background:
    rgba(34, 197, 94, 0.08);

  border-color:
    rgba(34, 197, 94, 0.15);

  color:
    #4ADE80;

}


.summary-box.yellow {

  background:
    rgba(234, 179, 8, 0.08);

  border-color:
    rgba(234, 179, 8, 0.15);

  color:
    #FACC15;

}


.summary-box.red {

  background:
    rgba(239, 68, 68, 0.08);

  border-color:
    rgba(239, 68, 68, 0.15);

  color:
    #F87171;

}


.summary-box.blue {

  background:
    rgba(37, 99, 235, 0.08);

  border-color:
    rgba(37, 99, 235, 0.15);

  color:
    #60A5FA;

}


/* =========================================================
   MODAL
========================================================= */

.modal-overlay {

  position:
    fixed;

  inset:
    0;

  z-index:
    50;

  display:
    flex;

  align-items:
    flex-start;

  justify-content:
    center;

  padding:
    5rem 1rem 2rem;

  overflow-y:
    auto;

  background:
    rgba(0, 0, 0, 0.72);

  backdrop-filter:
    blur(4px);

}


.modal-card {

  width:
    100%;

  max-height:
    calc(100vh - 7rem);

  overflow-y:
    auto;

  background:
    #111D2E;

  border:
    1px solid #1E293B;

  border-radius:
    1rem;

  box-shadow:
    0 25px 60px
    rgba(0, 0, 0, 0.45);

}


/* =========================================================
   MODAL HEADER
========================================================= */

.modal-header {

  display:
    flex;

  align-items:
    center;

  justify-content:
    space-between;

  padding:
    1rem 1.25rem;

  border-bottom:
    1px solid #1E293B;

}


.modal-close {

  display:
    flex;

  align-items:
    center;

  justify-content:
    center;

  width:
    2rem;

  height:
    2rem;

  border-radius:
    0.5rem;

  color:
    #64748B;

  transition:
    all 0.2s ease;

}


.modal-close:hover {

  color:
    #F1F5F9;

  background:
    #1E293B;

}


/* =========================================================
   FORM LABEL
========================================================= */

.form-label {

  display:
    block;

  margin-bottom:
    0.35rem;

  font-size:
    0.75rem;

  font-weight:
    500;

  color:
    #94A3B8;

}


/* =========================================================
   SCROLLBAR
========================================================= */

.modal-card::-webkit-scrollbar {

  width:
    6px;

}


.modal-card::-webkit-scrollbar-track {

  background:
    #080D14;

}


.modal-card::-webkit-scrollbar-thumb {

  background:
    #334155;

  border-radius:
    999px;

}


.modal-card::-webkit-scrollbar-thumb:hover {

  background:
    #475569;

}

</style>