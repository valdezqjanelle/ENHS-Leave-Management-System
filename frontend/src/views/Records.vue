<template>
  <div class="space-y-6">
    <!-- Admin Records View -->
    <div v-if="userRole === 'ADAS'">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h2 class="text-xl font-semibold text-gray-800">Faculty Records</h2>
            <p class="text-sm text-gray-600 mt-1">Manage and view faculty profiles and records</p>
          </div>
          
          <div class="flex gap-2">
            <button
              @click="exportRecords"
              class="px-4 py-2 text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700"
            >
              Export Records
            </button>
            <button
              @click="showAddFacultyModal = true"
              class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Add Faculty
            </button>
          </div>
        </div>
      </div>

    <!-- Search and Filter -->
    <div class="bg-white rounded-lg shadow p-4">
      <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search by name, department, position..."
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
            <option value="active">Active</option>
            <option value="on-leave">On Leave</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Faculty Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="faculty in filteredFaculty"
        :key="faculty.id"
        class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow cursor-pointer"
        @click="viewFacultyDetails(faculty)"
      >
        <div class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center">
              <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center text-white text-xl font-semibold">
                {{ faculty.name.charAt(0) }}
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ faculty.name }}</h3>
                <p class="text-sm text-gray-600">{{ faculty.position }}</p>
                <div class="flex items-center mt-1">
                  <div
                    class="w-2 h-2 rounded-full mr-2"
                    :class="getStatusIndicatorClass(faculty.status)"
                  ></div>
                  <span class="text-xs text-gray-500">{{ faculty.status }}</span>
                </div>
              </div>
            </div>
            
            <div class="flex space-x-1">
              <button
                @click.stop="editFaculty(faculty)"
                class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg"
              >
                <Edit class="w-4 h-4" />
              </button>
              <button
                @click.stop="deleteFaculty(faculty)"
                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg"
              >
                <Trash2 class="w-4 h-4" />
              </button>
            </div>
          </div>
          
          <div class="space-y-3">
            <div class="flex items-center text-sm">
              <Building class="w-4 h-4 mr-2 text-gray-400" />
              <span class="text-gray-600">{{ faculty.department }}</span>
            </div>
            
            <div class="flex items-center text-sm">
              <Mail class="w-4 h-4 mr-2 text-gray-400" />
              <span class="text-gray-600">{{ faculty.email }}</span>
            </div>
            
            <div class="flex items-center text-sm">
              <Phone class="w-4 h-4 mr-2 text-gray-400" />
              <span class="text-gray-600">{{ faculty.phone }}</span>
            </div>
          </div>
          
          <!-- Quick Stats -->
          <div class="mt-4 pt-4 border-t border-gray-200">
            <div class="grid grid-cols-3 gap-2 text-center">
              <div>
                <p class="text-xs text-gray-500">Leave Credits</p>
                <p class="text-sm font-semibold text-gray-900">{{ faculty.leaveCredits }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500">Absences</p>
                <p class="text-sm font-semibold text-gray-900">{{ faculty.absences }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500">Leaves Filed</p>
                <p class="text-sm font-semibold text-gray-900">{{ faculty.leavesFiled }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Faculty Details Modal -->
    <div v-if="selectedFaculty" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-lg bg-white max-h-[80vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-6">
          <h3 class="text-xl font-semibold text-gray-900">Faculty Profile - {{ selectedFaculty.name }}</h3>
          <button @click="selectedFaculty = null" class="text-gray-400 hover:text-gray-600">
            <X class="w-6 h-6" />
          </button>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Profile Information -->
          <div class="lg:col-span-1">
            <div class="bg-gray-50 rounded-lg p-6">
              <div class="text-center">
                <div class="w-24 h-24 bg-blue-500 rounded-full flex items-center justify-center text-white text-3xl font-semibold mx-auto mb-4">
                  {{ selectedFaculty.name.charAt(0) }}
                </div>
                <h4 class="text-lg font-semibold text-gray-900">{{ selectedFaculty.name }}</h4>
                <p class="text-sm text-gray-600">{{ selectedFaculty.position }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ selectedFaculty.department }}</p>
                
                <div class="mt-4 text-left space-y-2">
                  <div class="flex items-center text-sm">
                    <Mail class="w-4 h-4 mr-2 text-gray-400" />
                    <span class="text-gray-600">{{ selectedFaculty.email }}</span>
                  </div>
                  <div class="flex items-center text-sm">
                    <Phone class="w-4 h-4 mr-2 text-gray-400" />
                    <span class="text-gray-600">{{ selectedFaculty.phone }}</span>
                  </div>
                  <div class="flex items-center text-sm">
                    <Calendar class="w-4 h-4 mr-2 text-gray-400" />
                    <span class="text-gray-600">Joined {{ selectedFaculty.joinDate }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Detailed Information -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Leave Credits -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
              <h4 class="text-lg font-semibold text-gray-900 mb-4">Leave Credits</h4>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                  <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <Heart class="w-6 h-6 text-red-600" />
                  </div>
                  <p class="text-sm text-gray-600">Sick Leave</p>
                  <p class="text-xl font-bold text-gray-900">{{ selectedFaculty.leaveBreakdown.sick }}</p>
                </div>
                <div class="text-center">
                  <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <Umbrella class="w-6 h-6 text-blue-600" />
                  </div>
                  <p class="text-sm text-gray-600">Vacation</p>
                  <p class="text-xl font-bold text-gray-900">{{ selectedFaculty.leaveBreakdown.vacation }}</p>
                </div>
                <div class="text-center">
                  <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <User class="w-6 h-6 text-yellow-600" />
                  </div>
                  <p class="text-sm text-gray-600">Personal</p>
                  <p class="text-xl font-bold text-gray-900">{{ selectedFaculty.leaveBreakdown.personal }}</p>
                </div>
                <div class="text-center">
                  <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <Baby class="w-6 h-6 text-purple-600" />
                  </div>
                  <p class="text-sm text-gray-600">Maternity</p>
                  <p class="text-xl font-bold text-gray-900">{{ selectedFaculty.leaveBreakdown.maternity }}</p>
                </div>
              </div>
            </div>
            
            <!-- Attendance Summary -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
              <h4 class="text-lg font-semibold text-gray-900 mb-4">Attendance Summary</h4>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-green-50 p-4 rounded-lg">
                  <p class="text-sm text-green-600 font-medium">Present</p>
                  <p class="text-2xl font-bold text-green-800">{{ selectedFaculty.attendance.present }}</p>
                </div>
                <div class="bg-yellow-50 p-4 rounded-lg">
                  <p class="text-sm text-yellow-600 font-medium">Late</p>
                  <p class="text-2xl font-bold text-yellow-800">{{ selectedFaculty.attendance.late }}</p>
                </div>
                <div class="bg-red-50 p-4 rounded-lg">
                  <p class="text-sm text-red-600 font-medium">Absent</p>
                  <p class="text-2xl font-bold text-red-800">{{ selectedFaculty.attendance.absent }}</p>
                </div>
                <div class="bg-blue-50 p-4 rounded-lg">
                  <p class="text-sm text-blue-600 font-medium">Leave Days</p>
                  <p class="text-2xl font-bold text-blue-800">{{ selectedFaculty.attendance.leaveDays }}</p>
                </div>
              </div>
            </div>
            
            <!-- Leave History -->
            <div class="bg-white border border-gray-200 rounded-lg p-6">
              <h4 class="text-lg font-semibold text-gray-900 mb-4">Recent Leave History</h4>
              <div class="space-y-3">
                <div v-for="leave in selectedFaculty.recentLeaves" :key="leave.id" class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div class="flex items-center">
                    <div class="w-2 h-2 rounded-full mr-3" :class="getLeaveStatusClass(leave.status)"></div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ leave.type }}</p>
                      <p class="text-xs text-gray-500">{{ formatDateRange(leave.startDate, leave.endDate) }}</p>
                    </div>
                  </div>
                  <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getLeaveStatusBadgeClass(leave.status)">
                    {{ leave.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Faculty Modal -->
    <div v-if="showAddFacultyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 shadow-lg rounded-lg bg-white">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Add New Faculty</h3>
          <button @click="showAddFacultyModal = false" class="text-gray-400 hover:text-gray-600">
            <X class="w-6 h-6" />
          </button>
        </div>
        
        <form @submit.prevent="addFaculty" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
              <input
                v-model="newFaculty.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
              <input
                v-model="newFaculty.position"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
              <select
                v-model="newFaculty.department"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Department</option>
                <option value="Computer Science">Computer Science</option>
                <option value="Mathematics">Mathematics</option>
                <option value="English">English</option>
                <option value="Science">Science</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input
                v-model="newFaculty.email"
                type="email"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
              <input
                v-model="newFaculty.phone"
                type="tel"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
              <input
                v-model="newFaculty.employeeId"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>
          
          <div class="flex justify-end space-x-3 pt-4 border-t">
            <button
              type="button"
              @click="showAddFacultyModal = false"
              class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Add Faculty
            </button>
          </div>
        </form>
      </div>
    </div>
    </div>

    <!-- Faculty Personal Records View -->
    <div v-else>
      <!-- Personal Records Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h2 class="text-xl font-semibold text-gray-800">My Records</h2>
            <p class="text-sm text-gray-600 mt-1">View your personal profile and records</p>
          </div>
          
          <div class="flex gap-2">
            <button
              @click="exportPersonalRecords"
              class="px-4 py-2 text-sm bg-gray-600 text-white rounded-lg hover:bg-gray-700"
            >
              Export Records
            </button>
            <button
              @click="showEditProfileModal = true"
              class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Edit Profile
            </button>
          </div>
        </div>
      </div>

      <!-- Personal Profile Card -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <div class="w-24 h-24 bg-blue-500 rounded-full flex items-center justify-center text-white text-3xl font-semibold">
              {{ personalProfile.name.charAt(0) }}
            </div>
            
            <div class="flex-1">
              <h3 class="text-2xl font-semibold text-gray-900">{{ personalProfile.name }}</h3>
              <p class="text-lg text-gray-600">{{ personalProfile.position }}</p>
              <p class="text-sm text-gray-500 mt-1">{{ personalProfile.department }}</p>
              
              <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="flex items-center text-sm">
                  <Mail class="w-4 h-4 mr-2 text-gray-400" />
                  <span class="text-gray-600">{{ personalProfile.email }}</span>
                </div>
                <div class="flex items-center text-sm">
                  <Phone class="w-4 h-4 mr-2 text-gray-400" />
                  <span class="text-gray-600">{{ personalProfile.phone }}</span>
                </div>
                <div class="flex items-center text-sm">
                  <Calendar class="w-4 h-4 mr-2 text-gray-400" />
                  <span class="text-gray-600">Joined {{ personalProfile.joinDate }}</span>
                </div>
              </div>
            </div>
            
            <div class="flex items-center">
              <div
                class="w-3 h-3 rounded-full mr-2"
                :class="getStatusIndicatorClass(personalProfile.status)"
              ></div>
              <span class="text-sm font-medium text-gray-700">{{ personalProfile.status }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Leave Credits Section -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">My Leave Credits</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="text-center p-4 bg-red-50 rounded-lg">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <Heart class="w-6 h-6 text-red-600" />
            </div>
            <p class="text-2xl font-bold text-red-800">{{ personalProfile.leaveCredits.vacation }}</p>
            <p class="text-sm text-red-600">Vacation Leave</p>
          </div>
          
          <div class="text-center p-4 bg-blue-50 rounded-lg">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <Umbrella class="w-6 h-6 text-blue-600" />
            </div>
            <p class="text-2xl font-bold text-blue-800">{{ personalProfile.leaveCredits.sick }}</p>
            <p class="text-sm text-blue-600">Sick Leave</p>
          </div>
          
          <div class="text-center p-4 bg-green-50 rounded-lg">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <User class="w-6 h-6 text-green-600" />
            </div>
            <p class="text-2xl font-bold text-green-800">{{ personalProfile.leaveCredits.maternity }}</p>
            <p class="text-sm text-green-600">Maternity Leave</p>
          </div>
          
          <div class="text-center p-4 bg-purple-50 rounded-lg">
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <Baby class="w-6 h-6 text-purple-600" />
            </div>
            <p class="text-2xl font-bold text-purple-800">{{ personalProfile.leaveCredits.paternity }}</p>
            <p class="text-sm text-purple-600">Paternity Leave</p>
          </div>
        </div>
      </div>

      <!-- Recent Leave History -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">My Recent Leave History</h3>
        <div class="space-y-3">
          <div v-for="leave in personalProfile.recentLeaves" :key="leave.id" class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center">
              <div class="w-2 h-2 rounded-full mr-3" :class="getLeaveStatusClass(leave.status)"></div>
              <div>
                <p class="text-sm font-medium text-gray-900">{{ leave.type }}</p>
                <p class="text-xs text-gray-500">{{ formatDateRange(leave.startDate, leave.endDate) }}</p>
              </div>
            </div>
            <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getLeaveStatusBadgeClass(leave.status)">
              {{ leave.status }}
            </span>
          </div>
        </div>
      </div>

      <!-- Edit Profile Modal -->
      <div v-if="showEditProfileModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 shadow-lg rounded-lg bg-white">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Edit My Profile</h3>
            <button @click="showEditProfileModal = false" class="text-gray-400 hover:text-gray-600">
              <X class="w-6 h-6" />
            </button>
          </div>
          
          <form @submit.prevent="updateProfile" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                  v-model="personalProfile.email"
                  type="email"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input
                  v-model="personalProfile.phone"
                  type="tel"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-4 border-t">
              <button
                type="button"
                @click="showEditProfileModal = false"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
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

const userRole = ref<'ADAS' | 'Faculty'>(localStorage.getItem('userRole') === 'ADAS' ? 'ADAS' : 'Faculty')
const searchQuery = ref('')
const filterDepartment = ref('')
const filterStatus = ref('')
const selectedFaculty = ref<Faculty | null>(null)
const showAddFacultyModal = ref(false)
const showEditProfileModal = ref(false)

// Personal profile data for faculty
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
      startDate: 'May 15, 2024',
      endDate: 'May 17, 2024',
      status: 'Approved'
    },
    {
      id: '2',
      type: 'Sick Leave',
      startDate: 'April 5, 2024',
      endDate: 'April 6, 2024',
      status: 'Pending'
    },
    {
      id: '3',
      type: 'Personal Leave',
      startDate: 'March 10, 2024',
      endDate: 'March 10, 2024',
      status: 'Approved'
    }
  ]
})

const exportPersonalRecords = () => {
  // Export personal records functionality
  console.log('Exporting personal records...')
}

const updateProfile = () => {
  // Update profile functionality
  console.log('Updating profile...')
  showEditProfileModal.value = false
}

const newFaculty = ref({
  name: '',
  position: '',
  department: '',
  email: '',
  phone: '',
  employeeId: ''
})

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
      { id: '1', type: 'Sick Leave', startDate: '2024-01-15', endDate: '2024-01-16', status: 'Approved' },
      { id: '2', type: 'Vacation Leave', startDate: '2023-12-20', endDate: '2023-12-24', status: 'Approved' }
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
      { id: '3', type: 'Personal Leave', startDate: '2024-01-20', endDate: '2024-01-20', status: 'Pending' },
      { id: '4', type: 'Vacation Leave', startDate: '2024-01-10', endDate: '2024-01-12', status: 'Approved' }
    ]
  }
]

const filteredFaculty = computed(() => {
  return mockFaculty.filter(faculty => {
    const matchesSearch = searchQuery.value === '' || 
                         faculty.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         faculty.department.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         faculty.position.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesDept = filterDepartment.value === '' || 
                       faculty.department.toLowerCase().includes(filterDepartment.value.toLowerCase())
    
    const matchesStatus = filterStatus.value === '' || 
                         faculty.status === filterStatus.value
    
    return matchesSearch && matchesDept && matchesStatus
  })
})

const getStatusIndicatorClass = (status: string) => {
  const classes: Record<string, string> = {
    'active': 'bg-green-500',
    'on-leave': 'bg-yellow-500',
    'inactive': 'bg-red-500'
  }
  return classes[status] || 'bg-gray-500'
}

const getLeaveStatusClass = (status: string) => {
  const classes: Record<string, string> = {
    'Approved': 'bg-green-500',
    'Pending': 'bg-yellow-500',
    'Rejected': 'bg-red-500'
  }
  return classes[status] || 'bg-gray-500'
}

const getLeaveStatusBadgeClass = (status: string) => {
  const classes: Record<string, string> = {
    'Approved': 'bg-green-100 text-green-800',
    'Pending': 'bg-yellow-100 text-yellow-800',
    'Rejected': 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDateRange = (start: string, end: string) => {
  const startDate = new Date(start).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
  const endDate = new Date(end).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
  return `${startDate} - ${endDate}`
}

const viewFacultyDetails = (faculty: Faculty) => {
  selectedFaculty.value = faculty
}

const editFaculty = (faculty: Faculty) => {
  console.log('Edit faculty:', faculty.name)
}

const deleteFaculty = (faculty: Faculty) => {
  if (confirm(`Are you sure you want to delete ${faculty.name}?`)) {
    console.log('Delete faculty:', faculty.name)
  }
}

const addFaculty = () => {
  console.log('Add new faculty:', newFaculty.value)
  showAddFacultyModal.value = false
  
  // Reset form
  newFaculty.value = {
    name: '',
    position: '',
    department: '',
    email: '',
    phone: '',
    employeeId: ''
  }
}

const exportRecords = () => {
  console.log('Export faculty records')
}
</script>

