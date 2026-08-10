<template>
  <div class="space-y-6">
    <!-- Admin Attendance View -->
    <div v-if="userRole === 'ADAS'">
      <!-- Header with Date Navigation -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h2 class="text-xl font-semibold text-gray-800">Faculty Attendance</h2>
            <p class="text-sm text-gray-600 mt-1">Track and manage faculty attendance records</p>
          </div>
          
          <div class="flex items-center space-x-4">
            <button
              @click="previousMonth"
              class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg"
            >
              <ChevronLeft class="w-5 h-5" />
            </button>
            
            <div class="text-center">
              <p class="text-lg font-medium text-gray-900">{{ currentMonth }}</p>
              <p class="text-sm text-gray-500">{{ currentYear }}</p>
            </div>
            
            <button
              @click="nextMonth"
              class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg"
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
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-full">
              <CheckCircle class="w-6 h-6 text-green-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm font-medium text-gray-500">Present Today</h3>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.present }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-full">
              <Clock class="w-6 h-6 text-yellow-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm font-medium text-gray-500">Late</h3>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.late }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-full">
              <XCircle class="w-6 h-6 text-red-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm font-medium text-gray-500">Absent</h3>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.absent }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-full">
              <Calendar class="w-6 h-6 text-blue-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm font-medium text-gray-500">On Leave</h3>
              <p class="text-2xl font-semibold text-gray-900">{{ stats.onLeave }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filter and Search -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search by faculty name, department..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
          
          <div class="flex gap-2">
            <select
              v-model="filterDepartment"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Departments</option>
              <option value="cs">Computer Science</option>
              <option value="math">Mathematics</option>
              <option value="english">English</option>
              <option value="science">Science</option>
            </select>
            
            <select
              v-model="filterStatus"
              class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Status</option>
              <option value="present">Present</option>
              <option value="late">Late</option>
              <option value="absent">Absent</option>
              <option value="leave">On Leave</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Attendance Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-800">Attendance Records</h3>
            <div class="flex gap-2">
              <button
                @click="exportData"
                class="px-4 py-2 text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700"
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
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Faculty
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Department
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Check In
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Check Out
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Monthly Attendance
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="faculty in filteredFaculty" :key="faculty.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold">
                    {{ faculty.name.charAt(0) }}
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ faculty.name }}</div>
                    <div class="text-sm text-gray-500">{{ faculty.position }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ faculty.department }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ faculty.today.checkIn || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
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
                    <span class="text-gray-600">{{ faculty.monthly.present }}</span>
                  </div>
                  <div class="flex items-center">
                    <div class="w-2 h-2 bg-yellow-500 rounded-full mr-1"></div>
                    <span class="text-gray-600">{{ faculty.monthly.late }}</span>
                  </div>
                  <div class="flex items-center">
                    <div class="w-2 h-2 bg-red-500 rounded-full mr-1"></div>
                    <span class="text-gray-600">{{ faculty.monthly.absent }}</span>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button
                  @click="viewDetails(faculty)"
                  class="text-blue-600 hover:text-blue-900 mr-3"
                >
                  View Details
                </button>
                <button
                  @click="editAttendance(faculty)"
                  class="text-gray-600 hover:text-gray-900"
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
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-800">Monthly Calendar View</h3>
          <div class="flex gap-2">
            <button
              @click="viewMode = 'table'"
              :class="['px-3 py-1 text-sm rounded-lg', viewMode === 'table' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']"
            >
              Table View
            </button>
            <button
              @click="viewMode = 'calendar'"
              :class="['px-3 py-1 text-sm rounded-lg', viewMode === 'calendar' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700']"
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
              'border rounded-lg p-2 min-h-[80px] cursor-pointer hover:bg-gray-50',
              day.isToday ? 'bg-blue-50 border-blue-500' : 'border-gray-200',
              !day.isCurrentMonth ? 'text-gray-400' : ''
            ]"
            @click="selectDate(day)"
          >
            <div class="text-sm font-medium">{{ day.day }}</div>
            <div v-if="day.attendance" class="mt-1 space-y-1">
              <div class="text-xs">
                <span class="text-green-600">{{ day.attendance.present }}</span> present
              </div>
              <div class="text-xs">
                <span class="text-red-600">{{ day.attendance.absent }}</span> absent
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Attendance Details Modal -->
      <div v-if="selectedFaculty" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-lg bg-white">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Attendance Details - {{ selectedFaculty.name }}</h3>
            <button @click="selectedFaculty = null" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>
          
          <div class="space-y-6">
            <!-- Summary Stats -->
            <div class="grid grid-cols-4 gap-4">
              <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-sm text-green-600 font-medium">Present</p>
                <p class="text-2xl font-bold text-green-800">{{ selectedFaculty.monthly.present }}</p>
              </div>
              <div class="bg-yellow-50 p-4 rounded-lg">
                <p class="text-sm text-yellow-600 font-medium">Late</p>
                <p class="text-2xl font-bold text-yellow-800">{{ selectedFaculty.monthly.late }}</p>
              </div>
              <div class="bg-red-50 p-4 rounded-lg">
                <p class="text-sm text-red-600 font-medium">Absent</p>
                <p class="text-2xl font-bold text-red-800">{{ selectedFaculty.monthly.absent }}</p>
              </div>
              <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-sm text-blue-600 font-medium">On Leave</p>
                <p class="text-2xl font-bold text-blue-800">{{ selectedFaculty.monthly.leave }}</p>
              </div>
            </div>
            
            <!-- Daily Records -->
            <div>
              <h4 class="text-md font-semibold text-gray-800 mb-3">Daily Records</h4>
              <div class="max-h-64 overflow-y-auto">
                <table class="w-full text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-2 text-left">Date</th>
                      <th class="px-4 py-2 text-left">Check In</th>
                      <th class="px-4 py-2 text-left">Check Out</th>
                      <th class="px-4 py-2 text-left">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="record in selectedFaculty.dailyRecords" :key="record.date">
                      <td class="px-4 py-2">{{ record.date }}</td>
                      <td class="px-4 py-2">{{ record.checkIn }}</td>
                      <td class="px-4 py-2">{{ record.checkOut }}</td>
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
    <div v-else>
      <!-- Personal Attendance Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h2 class="text-xl font-semibold text-gray-800">My Attendance</h2>
            <p class="text-sm text-gray-600 mt-1">View your personal attendance records and statistics</p>
          </div>
          
          <div class="flex items-center space-x-4">
            <button
              @click="previousMonth"
              class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg"
            >
              <ChevronLeft class="w-5 h-5" />
            </button>
            
            <div class="text-center">
              <p class="text-lg font-medium text-gray-900">{{ currentMonth }}</p>
              <p class="text-sm text-gray-500">{{ currentYear }}</p>
            </div>
            
            <button
              @click="nextMonth"
              class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg"
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
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-full">
              <CheckCircle class="w-6 h-6 text-green-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm font-medium text-gray-500">Days Present</h3>
              <p class="text-2xl font-semibold text-gray-900">{{ personalStats.present }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-full">
              <Clock class="w-6 h-6 text-yellow-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm font-medium text-gray-500">Days Late</h3>
              <p class="text-2xl font-semibold text-gray-900">{{ personalStats.late }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-full">
              <XCircle class="w-6 h-6 text-red-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm font-medium text-gray-500">Days Absent</h3>
              <p class="text-2xl font-semibold text-gray-900">{{ personalStats.absent }}</p>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-full">
              <Calendar class="w-6 h-6 text-blue-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-sm font-medium text-gray-500">Days on Leave</h3>
              <p class="text-2xl font-semibold text-gray-900">{{ personalStats.leave }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Personal Attendance Records -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-800">My Attendance Records</h3>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Check In
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Check Out
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Notes
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="record in personalAttendanceRecords" :key="record.date" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ record.date }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ record.checkIn || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
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
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Monthly Summary</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h4 class="text-md font-medium text-gray-700 mb-3">Attendance Rate</h4>
            <div class="flex items-center">
              <div class="flex-1 bg-gray-200 rounded-full h-4 mr-3">
                <div 
                  class="bg-green-500 h-4 rounded-full" 
                  :style="{ width: attendanceRate + '%' }"
                ></div>
              </div>
              <span class="text-sm font-medium text-gray-900">{{ attendanceRate }}%</span>
            </div>
          </div>
          
          <div>
            <h4 class="text-md font-medium text-gray-700 mb-3">Punctuality Rate</h4>
            <div class="flex items-center">
              <div class="flex-1 bg-gray-200 rounded-full h-4 mr-3">
                <div 
                  class="bg-blue-500 h-4 rounded-full" 
                  :style="{ width: punctualityRate + '%' }"
                ></div>
              </div>
              <span class="text-sm font-medium text-gray-900">{{ punctualityRate }}%</span>
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
    'present': 'bg-green-100 text-green-800',
    'late': 'bg-yellow-100 text-yellow-800',
    'absent': 'bg-red-100 text-red-800',
    'leave': 'bg-blue-100 text-blue-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
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
