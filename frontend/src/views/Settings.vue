<template>
  <div class="space-y-6">
    <!-- Settings Navigation -->
    <div class="bg-white rounded-lg shadow">
      <div class="border-b border-gray-900/10 px-6 py-4">
        <nav class="flex -mb-px">
          <button
            v-for="tab in settingsTabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-4 px-6 text-sm font-medium border-b-2 transition-colors',
              activeTab === tab.id
                ? 'border-blue-500 text-blue-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            <component :is="tab.icon" class="w-4 h-4 mr-2 inline" />
            {{ tab.name }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Employee Profile Tab -->
    <div v-if="activeTab === 'profile'" class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-6">Employee Profile</h2>
      
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Picture -->
        <div class="lg:col-span-1">
          <div class="text-center">
            <div class="w-32 h-32 bg-blue-500 rounded-full flex items-center justify-center text-white text-4xl font-semibold mx-auto mb-4">
              {{ userProfile.first_name.charAt(0) }}
            </div>
            <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
              Change Photo
            </button>
          </div>
        </div>
        
        <!-- Profile Information -->
        <div class="lg:col-span-2">
          <form @submit.prevent="updateProfile" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input
                  v-model="userProfile.name"
                  type="text"
                  class="w-full px-3 py-2 text-black border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Employee ID</label>
                <input
                  v-model="userProfile.employeeId"
                  type="text"
                  disabled
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Position</label>
                <input
                  v-model="userProfile.position"
                  type="text"
                  class="w-full px-3 py-2 text-black border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                <select
                  v-model="userProfile.department"
                  class="w-full px-3 py-2 text-black border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="Computer Science">Computer Science</option>
                  <option value="Mathematics">Mathematics</option>
                  <option value="English">English</option>
                  <option value="Science">Science</option>
                </select>
              </div>
              
              
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gender</label>
                <select
                  v-model="userProfile.gender"
                  class="w-full px-3 py-2 text-black border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Other">Other</option>
                </select>
              </div>
            </div>
            
        
            
            <div class="flex justify-end">
              <button
                type="submit"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Account Settings Tab -->
    <div v-if="activeTab === 'account'" class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-6">Account Settings</h2>
      
      <div class="space-y-6">
        <!-- Email Settings -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Email Settings</h3>
          <form @submit.prevent="updateEmail" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Current Email</label>
              <input
                v-model="accountSettings.email.current"
                type="email"
                disabled
                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">New Email</label>
              <input
                v-model="accountSettings.email.new"
                type="email"
                class="w-full px-3 py-2 text-black border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Email</label>
              <input
                v-model="accountSettings.email.confirm"
                type="email"
                class="w-full px-3 py-2 text-black border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Update Email
            </button>
          </form>
        </div>

        <!-- Password Settings -->
        <div class="border-t pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Password Settings</h3>
          <form @submit.prevent="updatePassword" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-black mb-1">Current Password</label>
              <div class="relative">

<input
  v-model="accountSettings.password.current"
  :type="showCurrentPassword ? 'text' : 'password'"
  class="w-full px-3 py-2 pr-10 text-black border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
/>

<button
  type="button"
  @click="showCurrentPassword = !showCurrentPassword"
  class="absolute right-3 top-2.5"
>
  <Eye
    v-if="showCurrentPassword"
    class="w-4 h-4 text-gray-400"
  />

  <EyeOff
    v-else
    class="w-4 h-4 text-gray-400"
  />
</button>

</div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
              <input
                v-model="accountSettings.password.new"
              :type="showNewPassword ? 'text' : 'password'"

                class="w-full px-3 py-2 text-black border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <button
  type="button"
  @click="showNewPassword = !showNewPassword"
  class="absolute right-3 top-2.5"
>
  <Eye
    v-if="showNewPassword"
    class="w-4 h-4 text-gray-400"
  />

  <EyeOff
    v-else
    class="w-4 h-4 text-gray-400"
  />
</button>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
              <input
                v-model="accountSettings.password.confirm"
                type="password"
                class="w-full px-3 py-2 text-black border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <button
  type="button"
  @click="showConfirmPassword = !showConfirmPassword"
  class="absolute right-3 top-2.5"
>
  <Eye
    v-if="showConfirmPassword"
    class="w-4 h-4 text-gray-400"
  />

  <EyeOff
    v-else
    class="w-4 h-4 text-gray-400"
  />
</button>
              
              
            </div>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <p class="text-sm text-blue-800">
                <strong>Password Requirements:</strong><br>
                • At least 8 characters long<br>
                • Contains uppercase and lowercase letters<br>
                • Contains at least one number<br>
                • Contains at least one special character
              </p>
            </div>
            
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Update Password
            </button>
          </form>
        </div>

        <!-- Phone Settings -->
        <div class="border-t pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Phone Settings</h3>
          <form @submit.prevent="updatePhone" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Current Phone Number</label>
              <input
                v-model="accountSettings.phone.current"
                type="tel"
                disabled
                class="w-full px-3 py-2  border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">New Phone Number</label>
              <input
                v-model="accountSettings.phone.new"
                type="tel"
                class="w-full px-3 py-2 text-black border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Update Phone
            </button>
          </form>
        </div>

        <!-- Position Settings -->
        <div class="border-t pt-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Position Settings</h3>
          <form @submit.prevent="updatePosition" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Current Position</label>
              <input
                v-model="accountSettings.position.current"
                type="text"
                disabled
                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">New Position</label>
              <input
                v-model="accountSettings.position.new"
                type="text"
                disabled
                class="w-full px-3 py-2 text-black border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                
            </input>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Change</label>
              <textarea
                v-model="accountSettings.position.reason"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Please provide reason for position change"
              ></textarea>
            </div>
            
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Request Position Change
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Help Desk Tab -->
    <div v-if="activeTab === 'help'" class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-6">Help Desk</h2>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- FAQ Section -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Frequently Asked Questions</h3>
          <div class="space-y-4">
            <div v-for="faq in faqs" :key="faq.id" class="border border-gray-200 rounded-lg">
              <button
                @click="toggleFAQ(faq.id)"
                class="w-full px-4 py-3 text-left flex justify-between items-center hover:bg-gray-50"
              >
                <span class="font-medium text-gray-900">{{ faq.question }}</span>
                <ChevronDown
                  :class="['w-4 h-4 transition-transform', expandedFAQ === faq.id ? 'rotate-180' : '']"
                />
              </button>
              <div v-if="expandedFAQ === faq.id" class="px-4 py-3 border-t border-gray-200">
                <p class="text-gray-600">{{ faq.answer }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Support Ticket -->
        <div>
          <h3 class="text-lg font-medium text-gray-900 mb-4">Submit Support Ticket</h3>
          <form @submit.prevent="submitTicket" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Issue Type</label>
              <select
                v-model="ticket.type"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select Issue Type</option>
                <option value="technical">Technical Issue</option>
                <option value="account">Account Problem</option>
                <option value="leave">Leave Application Issue</option>
                <option value="attendance">Attendance Issue</option>
                <option value="other">Other</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
              <input
                v-model="ticket.subject"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="ticket.description"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Please describe your issue in detail"
              ></textarea>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
              <select
                v-model="ticket.priority"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
            
            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
              Submit Ticket
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- About Us Tab -->
    <div v-if="activeTab === 'about'" class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-6">About EL System</h2>
      
      <div class="prose max-w-none">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">System Information</h3>
            <div class="space-y-4">
              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Version Information</h4>
                <p class="text-sm text-gray-600">EL System v2.1.0</p>
                <p class="text-sm text-gray-600">Last Updated: January 15, 2024</p>
              </div>
              
              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">System Features</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                  <li>• Online Leave Application</li>
                  <li>• Attendance Tracking</li>
                  <li>• Faculty Records Management</li>
                  <li>• Report Generation</li>
                  <li>• Multi-level Approval System</li>
                </ul>
              </div>
              
              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">System Requirements</h4>
                <ul class="text-sm text-gray-600 space-y-1">
                  <li>• Modern web browser (Chrome, Firefox, Safari, Edge)</li>
                  <li>• Internet connection</li>
                  <li>• Minimum screen resolution: 1024x768</li>
                </ul>
              </div>
            </div>
          </div>
          
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
            <div class="space-y-4">
              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Technical Support</h4>
                <p class="text-sm text-gray-600">Email: support@elsystem.edu</p>
                <p class="text-sm text-gray-600">Phone: (123) 456-7890</p>
                <p class="text-sm text-gray-600">Hours: Monday - Friday, 8:00 AM - 5:00 PM</p>
              </div>
              
              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">ADAS Office</h4>
                <p class="text-sm text-gray-600">Email: adas@university.edu</p>
                <p class="text-sm text-gray-600">Phone: (123) 456-7891</p>
                <p class="text-sm text-gray-600">Location: Admin Building, Room 201</p>
              </div>
              
              <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-900 mb-2">Development Team</h4>
                <p class="text-sm text-gray-600">Lead Developer: John Developer</p>
                <p class="text-sm text-gray-600">UI/UX Designer: Sarah Designer</p>
                <p class="text-sm text-gray-600">System Administrator: Mike Admin</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="mt-8 border-t pt-8">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Privacy & Security</h3>
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <p class="text-sm text-blue-800">
              <strong>Privacy Policy:</strong> EL System is committed to protecting your privacy. 
              All personal information is stored securely and used only for official university purposes. 
              We comply with all applicable data protection regulations and maintain strict security protocols 
              to safeguard your data.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Logout Button -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-medium text-gray-900">Sign Out</h3>
          <p class="text-sm text-gray-600 mt-1">Sign out of your account and return to login page</p>
        </div>
        <button
          @click="logout"
          class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
        >
          Logout
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

import { onMounted } from 'vue'
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

const loadProfile = async()=>{

    try{

        const data = await getMyProfile()


        userProfile.value = {

            first_name:data.first_name,
            middle_name:data.middle_name,
            last_name:data.last_name,

            name:
            `${data.first_name} ${data.last_name}`,

            employeeId:data.employee_code,

            position:data.position,

            department:data.department,

            gender:data.sex,

            phone:data.contact_number

        }


        accountSettings.value.email.current = data.email

        accountSettings.value.phone.current =
            data.contact_number

        accountSettings.value.position.current =
            data.position


    }catch(error){

        console.log(error)

    }

}


const router = useRouter()
const activeTab = ref('profile')

const settingsTabs = [
  { id: 'profile', name: 'Employee Profile', icon: User },
  { id: 'account', name: 'Account', icon: Settings },
  { id: 'help', name: 'Help Desk', icon: HelpCircle },
  { id: 'about', name: 'About Us', icon: Info }
]

const userProfile = ref({

  first_name:'',
  middle_name:'',
  last_name:'',

  name:'',
  employeeId:'',
  position:'',
  department:'',
  gender:'',
  phone:''

})
const accountSettings = ref({
  email: {
    current: 'john.smith@university.edu',
    new: '',
    confirm: ''
  },
  password: {
    current: '',
    new: '',
    confirm: ''
  },
  phone: {
    current: '+1 234-567-8901',
    new: ''
  },
  position: {
    current: 'Professor',
    new: '',
    reason: ''
  }
})

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

onMounted(()=>{
    loadProfile()
})

const expandedFAQ = ref<number | null>(null)

const faqs = [
  {
    id: 1,
    question: 'How do I apply for leave?',
    answer: 'Navigate to the Leave Application page from the sidebar menu. Fill out the form with your leave details, attach any required documents, and submit it for ADAS approval.'
  },
  {
    id: 2,
    question: 'What is the leave approval process?',
    answer: 'Leave applications go through a multi-step approval process: Faculty submits → ADAS reviews → Principal approves → ADAS processes → Division final approval.'
  },
  {
    id: 3,
    question: 'How many leave credits do I have?',
    answer: 'You can view your available leave credits on your Dashboard or in the Faculty Records section. Credits vary by type (sick, vacation, personal, etc.).'
  },
  {
    id: 4,
    question: 'How do I check my attendance record?',
    answer: 'Go to the Attendance page from the sidebar menu. You can view your daily attendance, monthly summaries, and annual attendance records.'
  }
]

const ticket = ref({
  type: '',
  subject: '',
  description: '',
  priority: 'medium'
})

const toggleFAQ = (id: number) => {
  expandedFAQ.value = expandedFAQ.value === id ? null : id
}

const updateProfile = async()=>{

try{


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


alert("Profile updated successfully")


}catch(error){

console.log(error)

alert("Update failed")

}

}


const updateEmail = async()=>{

try{

await updateEmailAPI(
    accountSettings.value.email.new
)

alert("Email updated successfully")


}catch(error){

console.log(error)

alert("Email update failed")

}

}

const updatePassword = async()=>{

    try {

        await updatePasswordAPI({

            current_password:
            accountSettings.value.password.current,

            new_password:
            accountSettings.value.password.new,

            new_password_confirmation:
            accountSettings.value.password.confirm

        })


        alert("Password updated successfully")


    } catch(error) {

        console.log(error)

        alert("Password update failed")

    }

}

const updatePosition = () => {
  console.log('Update position:', accountSettings.value.position)
}

const updatePhone = async()=>{

try{

await updatePhoneAPI(
    accountSettings.value.phone.new
)

alert("Phone updated successfully")


}catch(error){

console.log(error)

alert("Phone update failed")

}

}



const submitTicket = () => {
  console.log('Submit support ticket:', ticket.value)
}

const logout = () => {
  router.push('/login')
}
</script>
