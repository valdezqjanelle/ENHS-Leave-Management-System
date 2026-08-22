<template>
  <div class="dashboard-shell p-8 min-h-screen space-y-8">
    <!-- Admin Attendance View -->
    <div v-if="userRole === 'ADAS'" class="space-y-8">
      <!-- Header with Date Navigation -->
      <div class="neo-card p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h2 class="text-xl font-semibold text-gray-100">Faculty Attendance</h2>
            <p class="text-sm text-gray-400 mt-1">Track and manage faculty attendance records</p>
          </div>

          <div class="flex items-center space-x-4">
            <button
              @click="previousMonth"
              class="p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
            >
              <ChevronLeft class="w-5 h-5" />
            </button>

            <div class="text-center">
              <p class="text-lg font-medium text-gray-100">{{ currentMonth }}</p>
              <p class="text-sm text-gray-500">{{ currentYear }}</p>
            </div>

            <button
              @click="nextMonth"
              class="p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
            >
              <ChevronRight class="w-5 h-5" />
            </button>

            <button
              @click="goToToday"
              class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Today
            </button>
          </div>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="neo-card stats-card border-green-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
              <CheckCircle class="w-6 h-6 text-green-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm text-gray-400">Present Today</h3>
              <p class="text-2xl font-semibold text-gray-100">{{ stats.present }}</p>
            </div>
          </div>
        </div>

        <div class="neo-card stats-card border-yellow-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-lg">
              <Clock class="w-6 h-6 text-yellow-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm text-gray-400">Late</h3>
              <p class="text-2xl font-semibold text-gray-100">{{ stats.late }}</p>
            </div>
          </div>
        </div>

        <div class="neo-card stats-card border-red-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
              <XCircle class="w-6 h-6 text-red-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm text-gray-400">Absent</h3>
              <p class="text-2xl font-semibold text-gray-100">{{ stats.absent }}</p>
            </div>
          </div>
        </div>

        <div class="neo-card stats-card border-blue-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
              <Calendar class="w-6 h-6 text-blue-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm text-gray-400">On Leave</h3>
              <p class="text-2xl font-semibold text-gray-100">{{ stats.onLeave }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter and Search -->
      <div class="neo-card p-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 w-5 h-5" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search by faculty name, department..."
                class="w-full pl-10 pr-4 py-2 bg-white/5 border border-gray-700 rounded-lg text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <div class="flex gap-2">
            <select
              v-model="filterDepartment"
              class="px-4 py-2 bg-white/5 border border-gray-700 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="" class="text-black">All Departments</option>
              <option value="cs" class="text-black">Computer Science</option>
              <option value="math" class="text-black">Mathematics</option>
              <option value="english" class="text-black">English</option>
              <option value="science" class="text-black">Science</option>
            </select>

            <select
              v-model="filterStatus"
              class="px-4 py-2 bg-white/5 border border-gray-700 rounded-lg text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="" class="text-black">All Status</option>
              <option value="present" class="text-black">Present</option>
              <option value="late" class="text-black">Late</option>
              <option value="absent" class="text-black">Absent</option>
              <option value="leave" class="text-black">On Leave</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Attendance Table -->
      <div class="neo-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-100">Attendance Records</h3>
            <div class="flex gap-2">
              <button
                @click="exportData"
                class="px-4 py-2 text-sm bg-gray-700 text-white rounded-lg hover:bg-gray-600"
              >
                Export
              </button>
              <button
                @click="markAttendance"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                Mark Attendance
              </button>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-white/5">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Faculty
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Department
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Check In
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Check Out
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Monthly Attendance
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/60">
              <tr v-for="faculty in filteredFaculty" :key="faculty.id" class="hover:bg-white/5">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                      {{ faculty.name.charAt(0) }}
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-100">{{ faculty.name }}</div>
                      <div class="text-sm text-gray-500">{{ faculty.position }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                  {{ faculty.department }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                  {{ faculty.today.checkIn || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                  {{ faculty.today.checkOut || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getStatusClass(faculty.today.status)">
                    {{ faculty.today.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center space-x-4 text-xs">
                    <div class="flex items-center">
                      <div class="w-2 h-2 bg-green-500 rounded-full mr-1"></div>
                      <span class="text-gray-400">{{ faculty.monthly.present }}</span>
                    </div>
                    <div class="flex items-center">
                      <div class="w-2 h-2 bg-yellow-500 rounded-full mr-1"></div>
                      <span class="text-gray-400">{{ faculty.monthly.late }}</span>
                    </div>
                    <div class="flex items-center">
                      <div class="w-2 h-2 bg-red-500 rounded-full mr-1"></div>
                      <span class="text-gray-400">{{ faculty.monthly.absent }}</span>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    @click="viewDetails(faculty)"
                    class="text-blue-400 hover:text-blue-300 mr-3"
                  >
                    View Details
                  </button>
                  <button
                    @click="editAttendance(faculty)"
                    class="text-gray-400 hover:text-gray-200"
                  >
                    Edit
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Attendance Calendar View -->
      <div class="neo-card p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-100">Monthly Calendar View</h3>
          <div class="flex gap-2">
            <button
              @click="viewMode = 'table'"
              :class="['px-3 py-1 text-sm rounded-lg', viewMode === 'table' ? 'bg-blue-600 text-white' : 'bg-white/5 text-gray-300']"
            >
              Table View
            </button>
            <button
              @click="viewMode = 'calendar'"
              :class="['px-3 py-1 text-sm rounded-lg', viewMode === 'calendar' ? 'bg-blue-600 text-white' : 'bg-white/5 text-gray-300']"
            >
              Calendar View
            </button>
          </div>
        </div>

        <div v-if="viewMode === 'calendar'" class="grid grid-cols-7 gap-1">
          <!-- Days of week -->
          <div v-for="day in daysOfWeek" :key="day" class="text-center text-xs font-medium text-gray-500 py-2">
            {{ day }}
          </div>

          <!-- Calendar days -->
          <div
            v-for="day in calendarDays"
            :key="day.date"
            :class="[
              'border rounded-lg p-2 min-h-[80px] cursor-pointer hover:bg-white/5',
              day.isToday ? 'bg-blue-500/10 border-blue-500' : 'border-gray-700',
              !day.isCurrentMonth ? 'text-gray-600' : 'text-gray-200'
            ]"
            @click="selectDate(day)"
          >
            <div class="text-sm font-medium">{{ day.day }}</div>
            <div v-if="day.attendance" class="mt-1 space-y-1">
              <div class="text-xs">
                <span class="text-green-400">{{ day.attendance.present }}</span> present
              </div>
              <div class="text-xs">
                <span class="text-red-400">{{ day.attendance.absent }}</span> absent
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Attendance Details Modal -->
      <div v-if="selectedFaculty" class="fixed inset-0 bg-black/60 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 w-11/12 md:w-3/4 lg:w-2/3 neo-card">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-100">Attendance Details - {{ selectedFaculty.name }}</h3>
            <button @click="selectedFaculty = null" class="text-gray-400 hover:text-gray-200">
              <X class="w-6 h-6" />
            </button>
          </div>

          <div class="space-y-6">
            <!-- Summary Stats -->
            <div class="grid grid-cols-4 gap-4">
              <div class="bg-green-500/10 p-4 rounded-lg">
                <p class="text-sm text-green-400 font-medium">Present</p>
                <p class="text-2xl font-bold text-green-300">{{ selectedFaculty.monthly.present }}</p>
              </div>
              <div class="bg-yellow-500/10 p-4 rounded-lg">
                <p class="text-sm text-yellow-400 font-medium">Late</p>
                <p class="text-2xl font-bold text-yellow-300">{{ selectedFaculty.monthly.late }}</p>
              </div>
              <div class="bg-red-500/10 p-4 rounded-lg">
                <p class="text-sm text-red-400 font-medium">Absent</p>
                <p class="text-2xl font-bold text-red-300">{{ selectedFaculty.monthly.absent }}</p>
              </div>
              <div class="bg-blue-500/10 p-4 rounded-lg">
                <p class="text-sm text-blue-400 font-medium">On Leave</p>
                <p class="text-2xl font-bold text-blue-300">{{ selectedFaculty.monthly.leave }}</p>
              </div>
            </div>

            <!-- Daily Records -->
            <div>
              <h4 class="text-md font-semibold text-gray-200 mb-3">Daily Records</h4>
              <div class="max-h-64 overflow-y-auto">
                <table class="w-full text-sm">
                  <thead class="bg-white/5">
                    <tr>
                      <th class="px-4 py-2 text-left text-gray-400">Date</th>
                      <th class="px-4 py-2 text-left text-gray-400">Check In</th>
                      <th class="px-4 py-2 text-left text-gray-400">Check Out</th>
                      <th class="px-4 py-2 text-left text-gray-400">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-700/60">
                    <tr v-for="record in selectedFaculty.dailyRecords" :key="record.date">
                      <td class="px-4 py-2 text-gray-300">{{ record.date }}</td>
                      <td class="px-4 py-2 text-gray-300">{{ record.checkIn }}</td>
                      <td class="px-4 py-2 text-gray-300">{{ record.checkOut }}</td>
                      <td class="px-4 py-2">
                        <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getStatusClass(record.status)">
                          {{ record.status }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Faculty Personal Attendance View -->
    <div v-else class="space-y-8">
      <!-- Personal Attendance Header -->
      <div class="neo-card p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h2 class="text-xl font-semibold text-gray-100">My Attendance</h2>
            <p class="text-sm text-gray-400 mt-1">View your personal attendance records and statistics</p>
          </div>

          <div class="flex items-center space-x-4">
            <button
              @click="previousMonth"
              class="p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
            >
              <ChevronLeft class="w-5 h-5" />
            </button>

            <div class="text-center">
              <p class="text-lg font-medium text-gray-100">{{ currentMonth }}</p>
              <p class="text-sm text-gray-500">{{ currentYear }}</p>
            </div>

            <button
              @click="nextMonth"
              class="p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg"
            >
              <ChevronRight class="w-5 h-5" />
            </button>

            <button
              @click="goToToday"
              class="px-3 py-1 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Today
            </button>
          </div>
        </div>
      </div>

      <!-- Personal Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="neo-card stats-card border-green-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
              <CheckCircle class="w-6 h-6 text-green-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm text-gray-400">Days Present</h3>
              <p class="text-2xl font-semibold text-gray-100">{{ personalStats.present }}</p>
            </div>
          </div>
        </div>

        <div class="neo-card stats-card border-yellow-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-lg">
              <Clock class="w-6 h-6 text-yellow-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm text-gray-400">Days Late</h3>
              <p class="text-2xl font-semibold text-gray-100">{{ personalStats.late }}</p>
            </div>
          </div>
        </div>

        <div class="neo-card stats-card border-red-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
              <XCircle class="w-6 h-6 text-red-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm text-gray-400">Days Absent</h3>
              <p class="text-2xl font-semibold text-gray-100">{{ personalStats.absent }}</p>
            </div>
          </div>
        </div>

        <div class="neo-card stats-card border-blue-500 p-6">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
              <Calendar class="w-6 h-6 text-blue-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm text-gray-400">Days on Leave</h3>
              <p class="text-2xl font-semibold text-gray-100">{{ personalStats.leave }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Personal Attendance Records -->
      <div class="neo-card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
          <h3 class="text-lg font-semibold text-gray-100">My Attendance Records</h3>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-white/5">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Check In
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Check Out
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                  Notes
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/60">
              <tr v-for="record in personalAttendanceRecords" :key="record.date" class="hover:bg-white/5">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-200">
                  {{ record.date }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                  {{ record.checkIn || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                  {{ record.checkOut || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getStatusClass(record.status)">
                    {{ record.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ record.notes || '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Monthly Summary -->
      <div class="neo-card p-6">
        <h3 class="text-lg font-semibold text-gray-100 mb-4">Monthly Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h4 class="text-md font-medium text-gray-300 mb-3">Attendance Rate</h4>
            <div class="flex items-center">
              <div class="flex-1 bg-white/10 rounded-full h-4 mr-3">
                <div
                  class="bg-green-500 h-4 rounded-full"
                  :style="{ width: attendanceRate + '%' }"
                ></div>
              </div>
              <span class="text-sm font-medium text-gray-100">{{ attendanceRate }}%</span>
            </div>
          </div>

          <div>
            <h4 class="text-md font-medium text-gray-300 mb-3">Punctuality Rate</h4>
            <div class="flex items-center">
              <div class="flex-1 bg-white/10 rounded-full h-4 mr-3">
                <div
                  class="bg-blue-500 h-4 rounded-full"
                  :style="{ width: punctualityRate + '%' }"
                ></div>
              </div>
              <span class="text-sm font-medium text-gray-100">{{ punctualityRate }}%</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import {
  ChevronLeft,
  ChevronRight,
  CheckCircle,
  Clock,
  XCircle,
  Calendar,
  Search,
  X
} from 'lucide-vue-next'

interface Faculty {
  id: string
  name: string
  department: string
  position: string
  today: {
    checkIn: string
    checkOut: string
    status: 'present' | 'late' | 'absent' | 'leave'
  }
  monthly: {
    present: number
    late: number
    absent: number
    leave: number
  }
  dailyRecords: Array<{
    date: string
    checkIn: string
    checkOut: string
    status: string
  }>
}

const userRole = ref<'ADAS' | 'Faculty'>(localStorage.getItem('userRole') === 'ADAS' ? 'ADAS' : 'Faculty')
const searchQuery = ref('')
const filterDepartment = ref('')
const filterStatus = ref('')
const selectedFaculty = ref<Faculty | null>(null)
const viewMode = ref<'table' | 'calendar'>('table')

// Personal attendance data for faculty
const personalStats = ref({
  present: 22,
  late: 2,
  absent: 1,
  leave: 3
})

const personalAttendanceRecords = ref([
  { date: 'May 8, 2024', checkIn: '8:00 AM', checkOut: '5:00 PM', status: 'present', notes: 'On time' },
  { date: 'May 7, 2024', checkIn: '8:15 AM', checkOut: '5:10 PM', status: 'late', notes: '15 minutes late' },
  { date: 'May 6, 2024', checkIn: '-', checkOut: '-', status: 'absent', notes: 'Sick leave' },
  { date: 'May 5, 2024', checkIn: '7:55 AM', checkOut: '5:00 PM', status: 'present', notes: 'Early arrival' },
  { date: 'May 4, 2024', checkIn: '8:00 AM', checkOut: '5:00 PM', status: 'present', notes: 'Regular day' },
  { date: 'May 3, 2024', checkIn: '8:00 AM', checkOut: '5:00 PM', status: 'present', notes: 'Regular day' },
  { date: 'May 2, 2024', checkIn: '8:05 AM', checkOut: '5:00 PM', status: 'late', notes: '5 minutes late' },
  { date: 'May 1, 2024', checkIn: '8:00 AM', checkOut: '5:00 PM', status: 'present', notes: 'Regular day' }
])

const attendanceRate = computed(() => {
  const totalDays = personalStats.value.present + personalStats.value.late + personalStats.value.absent + personalStats.value.leave
  return totalDays > 0 ? Math.round((personalStats.value.present / totalDays) * 100) : 0
})

const punctualityRate = computed(() => {
  const presentDays = personalStats.value.present + personalStats.value.late
  return presentDays > 0 ? Math.round((personalStats.value.present / presentDays) * 100) : 0
})

const currentDate = new Date()
const currentMonth = ref(currentDate.toLocaleDateString('en-US', { month: 'long' }))
const currentYear = ref(currentDate.getFullYear())

const stats = ref({
  present: 142,
  late: 8,
  absent: 3,
  onLeave: 3
})

const mockFaculty: Faculty[] = [
  {
    id: '1',
    name: 'John Smith',
    department: 'Computer Science',
    position: 'Professor',
    today: {
      checkIn: '8:00 AM',
      checkOut: '5:00 PM',
      status: 'present'
    },
    monthly: {
      present: 18,
      late: 2,
      absent: 0,
      leave: 1
    },
    dailyRecords: [
      { date: '2024-01-15', checkIn: '8:00 AM', checkOut: '5:00 PM', status: 'present' },
      { date: '2024-01-14', checkIn: '8:15 AM', checkOut: '5:00 PM', status: 'late' },
      { date: '2024-01-13', checkIn: '-', checkOut: '-', status: 'leave' }
    ]
  },
  {
    id: '2',
    name: 'Sarah Johnson',
    department: 'Mathematics',
    position: 'Assistant Professor',
    today: {
      checkIn: '8:30 AM',
      checkOut: '5:30 PM',
      status: 'late'
    },
    monthly: {
      present: 15,
      late: 3,
      absent: 1,
      leave: 2
    },
    dailyRecords: [
      { date: '2024-01-15', checkIn: '8:30 AM', checkOut: '5:30 PM', status: 'late' },
      { date: '2024-01-14', checkIn: '8:00 AM', checkOut: '5:00 PM', status: 'present' },
      { date: '2024-01-13', checkIn: '-', checkOut: '-', status: 'absent' }
    ]
  }
]

const filteredFaculty = computed(() => {
  return mockFaculty.filter(faculty => {
    const matchesSearch = searchQuery.value === '' ||
                         faculty.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         faculty.department.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesDept = filterDepartment.value === '' ||
                       faculty.department.toLowerCase().includes(filterDepartment.value.toLowerCase())

    const matchesStatus = filterStatus.value === '' ||
                         faculty.today.status === filterStatus.value

    return matchesSearch && matchesDept && matchesStatus
  })
})

const daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']

const calendarDays = computed(() => {
  const year = currentYear.value
  const month = currentDate.getMonth()
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  const startDate = new Date(firstDay)
  startDate.setDate(startDate.getDate() - firstDay.getDay())

  const days = []
  const current = new Date(startDate)

  for (let i = 0; i < 42; i++) {
    days.push({
      date: current.toISOString().split('T')[0],
      day: current.getDate(),
      isCurrentMonth: current.getMonth() === month,
      isToday: current.toDateString() === new Date().toDateString(),
      attendance: i % 7 === 0 ? null : {
        present: Math.floor(Math.random() * 10) + 5,
        absent: Math.floor(Math.random() * 3)
      }
    })
    current.setDate(current.getDate() + 1)
  }

  return days
})

const getStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    'present': 'bg-green-500/10 text-green-400',
    'late': 'bg-yellow-500/10 text-yellow-400',
    'absent': 'bg-red-500/10 text-red-400',
    'leave': 'bg-blue-500/10 text-blue-400'
  }
  return classes[status] || 'bg-gray-500/10 text-gray-400'
}

const previousMonth = () => {
  currentDate.setMonth(currentDate.getMonth() - 1)
  currentMonth.value = currentDate.toLocaleDateString('en-US', { month: 'long' })
  currentYear.value = currentDate.getFullYear()
}

const nextMonth = () => {
  currentDate.setMonth(currentDate.getMonth() + 1)
  currentMonth.value = currentDate.toLocaleDateString('en-US', { month: 'long' })
  currentYear.value = currentDate.getFullYear()
}

const goToToday = () => {
  const today = new Date()
  currentDate.setMonth(today.getMonth())
  currentMonth.value = today.toLocaleDateString('en-US', { month: 'long' })
  currentYear.value = today.getFullYear()
}

const viewDetails = (faculty: Faculty) => {
  selectedFaculty.value = faculty
}

const editAttendance = (faculty: Faculty) => {
  console.log('Edit attendance for:', faculty.name)
}

const markAttendance = () => {
  console.log('Open attendance marking interface')
}

const exportData = () => {
  console.log('Export attendance data')
}

const selectDate = (day: any) => {
  console.log('Selected date:', day.date)
}
</script>

<style scoped>
.dashboard-shell {
  background: #080D14;
}

.neo-card {
  background: #111D2E;
  border: 1px solid #1E293B;
  border-radius: 1.4rem;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.neo-card:hover {
  box-shadow: 0 14px 26px rgba(15, 23, 42, 0.06);
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
</style>