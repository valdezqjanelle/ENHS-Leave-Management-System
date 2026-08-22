<template>
  <div class="settings-shell p-8 min-h-screen space-y-6">
    <!-- Settings Navigation -->
    <div class="neo-card">
      <div class="border-b border-[#1e293b] px-6 py-4">
        <nav class="flex -mb-px flex-wrap">
          <button
            v-for="tab in settingsTabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-4 px-6 text-sm font-medium border-b-2 transition-colors',
              activeTab === tab.id
                ? 'border-blue-400 text-blue-400'
                : 'border-transparent text-gray-400 hover:text-gray-200 hover:border-[#334155]'
            ]"
          >
            <component :is="tab.icon" class="w-4 h-4 mr-2 inline" />
            {{ tab.name }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Employee Profile Tab -->
    <div v-if="activeTab === 'profile'" class="neo-card p-6">
      <h2 class="text-xl font-semibold text-white mb-6">Employee Profile</h2>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Picture -->
        <div class="lg:col-span-1">
          <div class="text-center">
            <div class="w-32 h-32 bg-blue-500 rounded-full flex items-center justify-center text-white text-4xl font-semibold mx-auto mb-4">
              {{ userProfile.first_name.charAt(0) }}
            </div>
            <button class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-500">
              Change Photo
            </button>
          </div>
        </div>

        <!-- Profile Information -->
        <div class="lg:col-span-2">
          <form @submit.prevent="updateProfile" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Full Name</label>
                <input
                  v-model="userProfile.name"
                  type="text"
                  class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Employee ID</label>
                <input
                  v-model="userProfile.employeeId"
                  type="text"
                  disabled
                  class="w-full px-3 py-2 border border-[#1e293b] rounded-lg bg-[#0a1119] text-gray-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Position</label>
                <input
                  v-model="userProfile.position"
                  type="text"
                  class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Department</label>
                <select
                  v-model="userProfile.department"
                  class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="Computer Science">Computer Science</option>
                  <option value="Mathematics">Mathematics</option>
                  <option value="English">English</option>
                  <option value="Science">Science</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-400 mb-1">Gender</label>
                <select
                  v-model="userProfile.gender"
                  class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500"
              >
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Account Settings Tab -->
    <div v-if="activeTab === 'account'" class="neo-card p-6">
      <h2 class="text-xl font-semibold text-white mb-6">Account Settings</h2>

      <div class="space-y-6">
        <!-- Email Settings -->
        <div>
          <h3 class="text-lg font-medium text-white mb-4">Email Settings</h3>
          <form @submit.prevent="updateEmail" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Current Email</label>
              <input
                v-model="accountSettings.email.current"
                type="email"
                disabled
                class="w-full px-3 py-2 border border-[#1e293b] rounded-lg bg-[#0a1119] text-gray-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">New Email</label>
              <input
                v-model="accountSettings.email.new"
                type="email"
                class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Confirm New Email</label>
              <input
                v-model="accountSettings.email.confirm"
                type="email"
                class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500"
            >
              Update Email
            </button>
          </form>
        </div>

        <!-- Password Settings -->
        <div class="border-t border-[#1e293b] pt-6">
          <h3 class="text-lg font-medium text-white mb-4">Password Settings</h3>
          <form @submit.prevent="updatePassword" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Current Password</label>
              <div class="relative">
                <input
                  v-model="accountSettings.password.current"
                  :type="showCurrentPassword ? 'text' : 'password'"
                  class="w-full px-3 py-2 pr-10 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />

                <button
                  type="button"
                  @click="showCurrentPassword = !showCurrentPassword"
                  class="absolute right-3 top-2.5"
                >
                  <Eye
                    v-if="showCurrentPassword"
                    class="w-4 h-4 text-gray-500"
                  />

                  <EyeOff
                    v-else
                    class="w-4 h-4 text-gray-500"
                  />
                </button>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">New Password</label>
              <div class="relative">
                <input
                  v-model="accountSettings.password.new"
                  :type="showNewPassword ? 'text' : 'password'"
                  class="w-full px-3 py-2 pr-10 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button
                  type="button"
                  @click="showNewPassword = !showNewPassword"
                  class="absolute right-3 top-2.5"
                >
                  <Eye
                    v-if="showNewPassword"
                    class="w-4 h-4 text-gray-500"
                  />

                  <EyeOff
                    v-else
                    class="w-4 h-4 text-gray-500"
                  />
                </button>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Confirm New Password</label>
              <div class="relative">
                <input
                  v-model="accountSettings.password.confirm"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  class="w-full px-3 py-2 pr-10 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button
                  type="button"
                  @click="showConfirmPassword = !showConfirmPassword"
                  class="absolute right-3 top-2.5"
                >
                  <Eye
                    v-if="showConfirmPassword"
                    class="w-4 h-4 text-gray-500"
                  />

                  <EyeOff
                    v-else
                    class="w-4 h-4 text-gray-500"
                  />
                </button>
              </div>
            </div>

            <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
              <p class="text-sm text-blue-300">
                <strong>Password Requirements:</strong><br>
                • At least 8 characters long<br>
                • Contains uppercase and lowercase letters<br>
                • Contains at least one number<br>
                • Contains at least one special character
              </p>
            </div>

            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500"
            >
              Update Password
            </button>
          </form>
        </div>

        <!-- Phone Settings -->
        <div class="border-t border-[#1e293b] pt-6">
          <h3 class="text-lg font-medium text-white mb-4">Phone Settings</h3>
          <form @submit.prevent="updatePhone" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Current Phone Number</label>
              <input
                v-model="accountSettings.phone.current"
                type="tel"
                disabled
                class="w-full px-3 py-2 border border-[#1e293b] rounded-lg bg-[#0a1119] text-gray-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">New Phone Number</label>
              <input
                v-model="accountSettings.phone.new"
                type="tel"
                class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500"
            >
              Update Phone
            </button>
          </form>
        </div>

        <!-- Position Settings -->
        <div class="border-t border-[#1e293b] pt-6">
          <h3 class="text-lg font-medium text-white mb-4">Position Settings</h3>
          <form @submit.prevent="updatePosition" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Current Position</label>
              <input
                v-model="accountSettings.position.current"
                type="text"
                disabled
                class="w-full px-3 py-2 border border-[#1e293b] rounded-lg bg-[#0a1119] text-gray-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">New Position</label>
              <input
                v-model="accountSettings.position.new"
                type="text"
                disabled
                class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-400 mb-1">Reason for Change</label>
              <textarea
                v-model="accountSettings.position.reason"
                rows="3"
                class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Please provide reason for position change"
              ></textarea>
            </div>

            <button
              type="submit"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500"
            >
              Request Position Change
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Leave Rules Tab -->
    <div
      v-if="activeTab === 'leave-rules'"
      class="neo-card p-6"
    >
      <h2 class="text-xl font-semibold text-white mb-2">
        Leave Rules
      </h2>

      <p class="text-gray-400 mb-6">
        View the leave rules configured by the administrator.
      </p>

      <!-- Loading -->
      <div
        v-if="leaveRulesLoading"
        class="text-center py-8 text-gray-400"
      >
        Loading leave rules...
      </div>

      <!-- Rules -->
      <div v-else class="space-y-4">

        <!-- Minimum Filing Notice -->
        <div class="border border-[#1e293b] rounded-xl p-5">
          <h3 class="text-lg font-semibold text-white">
            Minimum Filing Notice
          </h3>

          <p class="text-sm text-gray-400 mt-2">
            Employees should file their leave at least
            <strong class="text-white">
              {{ leaveRules.minimum_notice_days }} day(s)
            </strong>
            before the intended leave date.
          </p>
        </div>

        <!-- Supporting Documents -->
        <div class="border border-[#1e293b] rounded-xl p-5">
          <h3 class="text-lg font-semibold text-white">
            Supporting Documents
          </h3>

          <p class="text-sm text-gray-400 mt-2">
            Supporting documents are
            <strong class="text-white">
              {{ leaveRules.require_documents ? 'required' : 'not required' }}
            </strong>
            when filing leave.
          </p>
        </div>

        <!-- Maximum Consecutive Days -->
        <div class="border border-[#1e293b] rounded-xl p-5">
          <h3 class="text-lg font-semibold text-white">
            Maximum Consecutive Leave Days
          </h3>

          <p class="text-sm text-gray-400 mt-2">
            Employees may file up to
            <strong class="text-white">
              {{ leaveRules.max_consecutive_days }} day(s)
            </strong>
            of consecutive leave per application.
          </p>
        </div>

        <!-- General Policy -->
        <div class="border border-[#1e293b] rounded-xl p-5">
          <h3 class="text-lg font-semibold text-white">
            General Leave Policy
          </h3>

          <p class="text-sm text-gray-400 mt-2 whitespace-pre-line">
            {{ leaveRules.general_policy || 'No general leave policy has been provided.' }}
          </p>
        </div>

      </div>
    </div>

    <!-- Help Desk Tab -->
    <div v-if="activeTab === 'help'" class="neo-card p-6">
      <h2 class="text-xl font-semibold text-white mb-2">
        Help & Frequently Asked Questions
      </h2>

      <p class="text-gray-400 mb-6">
        Find answers to common questions about using the ENHS Leave System.
      </p>

      <div class="space-y-4">
        <div
          v-for="faq in faqs"
          :key="faq.id"
          class="border border-[#1e293b] rounded-lg"
        >
          <button
            @click="toggleFAQ(faq.id)"
            class="w-full px-4 py-3 text-left flex justify-between items-center hover:bg-[#0d1520]"
          >
            <span class="font-medium text-white">
              {{ faq.question }}
            </span>

            <ChevronDown
              :class="[
                'w-4 h-4 transition-transform text-gray-400',
                expandedFAQ === faq.id ? 'rotate-180' : ''
              ]"
            />
          </button>

          <div
            v-if="expandedFAQ === faq.id"
            class="px-4 py-3 border-t border-[#1e293b]"
          >
            <p class="text-gray-400">
              {{ faq.answer }}
            </p>
          </div>
        </div>
      </div>
    </div>


    <!-- About Us Tab -->
    <div
      v-if="activeTab === 'about'"
      class="neo-card overflow-hidden"
    >
      <!-- Header -->
      <div class="neo-card p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h2 class="text-xl font-semibold text-white">Settings</h2>
            <p class="text-sm text-gray-400 mt-1">Generate and view various reports</p>
          </div>

          <div>
            <h2 class="text-2xl font-bold">
              ENHS Leave System
            </h2>

            <p class="text-blue-100 mt-1 text-sm">
              Echague National High School Leave Management System
            </p>
          </div>
        </div>

        <p class="mt-5 max-w-3xl text-sm leading-6 text-blue-50">
          A web-based system designed to help Echague National High School
          manage employee leave applications, attendance records, leave credits,
          and related employee information in a more organized and efficient way.
        </p>
      </div>


      <!-- Main Content -->
      <div class="p-8">

        <!-- About the System -->
        <section class="mb-10">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center">
              <Info class="w-5 h-5" />
            </div>

            <div>
              <h3 class="text-lg font-semibold text-white">
                About the System
              </h3>

              <p class="text-sm text-gray-400">
                Overview of the ENHS Leave System
              </p>
            </div>
          </div>

          <div class="bg-[#0d1520] border border-[#1e293b] rounded-xl p-5">
            <p class="text-sm text-gray-400 leading-7">
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


        <!-- Features -->
        <section class="mb-10">
          <div class="mb-5">
            <h3 class="text-lg font-semibold text-white">
              System Features
            </h3>

            <p class="text-sm text-gray-400 mt-1">
              Main functions available in the ENHS Leave System
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Feature -->
            <div class="flex gap-4 p-5 border border-[#1e293b] rounded-xl hover:border-blue-500/40 hover:bg-blue-500/5 transition">
              <div class="w-10 h-10 shrink-0 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center">
                <User class="w-5 h-5" />
              </div>

              <div>
                <h4 class="font-semibold text-white">
                  Employee Account Management
                </h4>

                <p class="text-sm text-gray-400 mt-1 leading-6">
                  Manage employee accounts and authorized access to the system.
                </p>
              </div>
            </div>


            <div class="flex gap-4 p-5 border border-[#1e293b] rounded-xl hover:border-blue-500/40 hover:bg-blue-500/5 transition">
              <div class="w-10 h-10 shrink-0 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center">
                <FileText class="w-5 h-5" />
              </div>

              <div>
                <h4 class="font-semibold text-white">
                  Online Leave Application
                </h4>

                <p class="text-sm text-gray-400 mt-1 leading-6">
                  Submit and monitor leave applications through the system.
                </p>
              </div>
            </div>


            <div class="flex gap-4 p-5 border border-[#1e293b] rounded-xl hover:border-blue-500/40 hover:bg-blue-500/5 transition">
              <div class="w-10 h-10 shrink-0 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center">
                <FileText class="w-5 h-5" />
              </div>

              <div>
                <h4 class="font-semibold text-white">
                  Leave Credit Management
                </h4>

                <p class="text-sm text-gray-400 mt-1 leading-6">
                  View and manage employee leave credits and balances.
                </p>
              </div>
            </div>


            <div class="flex gap-4 p-5 border border-[#1e293b] rounded-xl hover:border-blue-500/40 hover:bg-blue-500/5 transition">
              <div class="w-10 h-10 shrink-0 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center">
                <FileText class="w-5 h-5" />
              </div>

              <div>
                <h4 class="font-semibold text-white">
                  Attendance Records
                </h4>

                <p class="text-sm text-gray-400 mt-1 leading-6">
                  Maintain and access employee attendance information.
                </p>
              </div>
            </div>


            <div class="flex gap-4 p-5 border border-[#1e293b] rounded-xl hover:border-blue-500/40 hover:bg-blue-500/5 transition">
              <div class="w-10 h-10 shrink-0 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center">
                <FileText class="w-5 h-5" />
              </div>

              <div>
                <h4 class="font-semibold text-white">
                  Reports
                </h4>

                <p class="text-sm text-gray-400 mt-1 leading-6">
                  Generate organized reports based on available system records.
                </p>
              </div>
            </div>


            <div class="flex gap-4 p-5 border border-[#1e293b] rounded-xl hover:border-blue-500/40 hover:bg-blue-500/5 transition">
              <div class="w-10 h-10 shrink-0 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center">
                <Settings class="w-5 h-5" />
              </div>

              <div>
                <h4 class="font-semibold text-white">
                  Activity Monitoring
                </h4>

                <p class="text-sm text-gray-400 mt-1 leading-6">
                  Monitor relevant system activities and records for accountability.
                </p>
              </div>
            </div>

          </div>
        </section>


        <!-- Organization + Users -->
        <section class="mb-10">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Organization -->
            <div class="border border-[#1e293b] rounded-xl p-6">
              <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center">
                  <Info class="w-5 h-5" />
                </div>

                <div>
                  <h3 class="font-semibold text-white">
                    Organization
                  </h3>

                  <p class="text-sm text-gray-400">
                    Intended school environment
                  </p>
                </div>
              </div>

              <h4 class="font-medium text-white">
                Echague National High School
              </h4>

              <p class="text-sm text-gray-400 leading-6 mt-2">
                The system is intended to support the school's employee
                leave and attendance management processes by providing
                a centralized and organized digital platform.
              </p>
            </div>


            <!-- System Users -->
            <div class="border border-[#1e293b] rounded-xl p-6">
              <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center">
                  <User class="w-5 h-5" />
                </div>

                <div>
                  <h3 class="font-semibold text-white">
                    System Users
                  </h3>

                  <p class="text-sm text-gray-400">
                    Authorized users of the system
                  </p>
                </div>
              </div>

              <div class="space-y-3">

                <div class="flex items-center gap-3">
                  <span class="w-2 h-2 bg-blue-400 rounded-full"></span>
                  <span class="text-sm text-gray-300">
                    Administrator / ADAS
                  </span>
                </div>

                <div class="flex items-center gap-3">
                  <span class="w-2 h-2 bg-blue-400 rounded-full"></span>
                  <span class="text-sm text-gray-300">
                    Teaching Employees
                  </span>
                </div>

                <div class="flex items-center gap-3">
                  <span class="w-2 h-2 bg-blue-400 rounded-full"></span>
                  <span class="text-sm text-gray-300">
                    Non-Teaching Employees
                  </span>
                </div>

              </div>
            </div>

          </div>
        </section>


        <!-- Purpose -->
        <section class="mb-10">
          <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-2">
              Purpose of the System
            </h3>

            <p class="text-sm text-gray-300 leading-7">
              The ENHS Leave System aims to provide a centralized platform
              for managing employee leave applications, attendance records,
              leave credits, and reports. By organizing these processes
              digitally, the system helps reduce manual record-keeping
              and improves the accessibility, accuracy, and organization
              of employee information.
            </p>
          </div>
        </section>


        <!-- System Details -->
        <section class="mb-10">
          <h3 class="text-lg font-semibold text-white mb-1">
            System Details
          </h3>

          <p class="text-sm text-gray-400 mb-5">
            Basic information about the system
          </p>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div class="border border-[#1e293b] rounded-xl p-5">
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                System Name
              </p>

              <p class="font-semibold text-white mt-2">
                ENHS Leave System
              </p>
            </div>

            <div class="border border-[#1e293b] rounded-xl p-5">
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                System Type
              </p>

              <p class="font-semibold text-white mt-2">
                Web-Based Leave Management System
              </p>
            </div>

            <div class="border border-[#1e293b] rounded-xl p-5">
              <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                Primary Users
              </p>

              <p class="font-semibold text-white mt-2">
                Administrators and Employees
              </p>
            </div>

          </div>
        </section>


        <!-- Privacy & Security -->
        <section>
          <div class="border border-blue-500/20 bg-blue-500/10 rounded-xl p-6">

            <div class="flex items-start gap-4">

              <div class="w-10 h-10 shrink-0 bg-[#111d2e] text-blue-400 rounded-lg flex items-center justify-center">
                <Settings class="w-5 h-5" />
              </div>

              <div>
                <h3 class="text-lg font-semibold text-white">
                  Privacy & Security
                </h3>

                <p class="text-sm text-gray-300 leading-7 mt-2">
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

    <!-- Logout Button -->
    <div class="neo-card p-6">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-lg font-medium text-white">Sign Out</h3>
          <p class="text-sm text-gray-400 mt-1">Sign out of your account and return to login page</p>
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
  FileText,
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

import { getLeaveSettings } from '@/services/leaveSettings'

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
  { id: 'leave-rules', name: 'Leave Rules', icon: FileText },
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
      minimum_notice_days: data.minimum_notice_days ?? 0,
      require_documents: data.require_documents ?? false,
      max_consecutive_days: data.max_consecutive_days ?? 0,
      general_policy: data.general_policy ?? ''
    }

  } catch (error) {
    console.log('Failed to load leave rules:', error)
  } finally {
    leaveRulesLoading.value = false
  }
}

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

onMounted(()=>{
    loadProfile()
    loadLeaveRules()
})

const expandedFAQ = ref<number | null>(null)

const faqs = [
  {
    id: 1,
    question: 'How do I apply for leave?',
    answer: 'Navigate to the Leave Application page from the sidebar menu. Fill out the form with your leave details, attach any required documents, and submit the application for review.'
  },
  {
    id: 2,
    question: 'What is the leave approval process?',
    answer: 'After an employee submits a leave application, it is reviewed and processed by the designated approving personnel according to the school’s leave approval procedure.'
  },
  {
    id: 3,
    question: 'How many leave credits do I have?',
    answer: 'You can check your available leave credits by going to the Records page from the sidebar menu. Your leave credits and related leave records are displayed there.'
  },
  {
    id: 4,
    question: 'How do I check my attendance record?',
    answer: 'Go to the Attendance page from the sidebar menu to view your attendance records and attendance information.'
  }
]

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





const logout = () => {
  router.push('/login')
}
</script>

<style scoped>
.settings-shell {
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