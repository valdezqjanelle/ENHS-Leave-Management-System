<template>
  <div class="settings-shell p-8 min-h-screen space-y-6">

    <!-- ========================================================= -->
    <!-- SETTINGS NAVIGATION -->
    <!-- ========================================================= -->

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


    <!-- ========================================================= -->
    <!-- EMPLOYEE PROFILE TAB -->
    <!-- ========================================================= -->

    <div
      v-if="activeTab === 'profile'"
      class="neo-card p-6"
    >

      <div class="mb-6">
        <h2 class="text-xl font-semibold text-white">
          Employee Profile
        </h2>

        <p class="text-sm text-gray-400 mt-1">
          View your employee information and update your personal contact details.
        </p>
      </div>


      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- ===================================================== -->
        <!-- PROFILE SUMMARY -->
        <!-- ===================================================== -->

        <div class="lg:col-span-1">

          <div class="border border-[#1e293b] rounded-xl p-6 text-center">

            <div
              class="w-24 h-24 bg-blue-500 rounded-full flex items-center justify-center text-white text-3xl font-semibold mx-auto mb-4"
            >
              {{ initials }}
            </div>

            <h3 class="text-lg font-semibold text-white">
              {{ fullName || userProfile.first_name }}
            </h3>

            <p class="text-sm text-gray-400 mt-1">
              {{ userProfile.position || 'Employee' }}
            </p>

            <span
              v-if="userProfile.employment_status"
              class="inline-block mt-3 px-3 py-1 rounded-full text-xs bg-green-500/10 text-green-400"
            >
              {{ userProfile.employment_status }}
            </span>

            <div class="mt-5 pt-5 border-t border-[#1e293b]">
              <p class="text-xs text-gray-500">
                Employee Code
              </p>

              <p class="text-sm text-gray-300 font-mono mt-1">
                {{ userProfile.employeeId || '—' }}
              </p>
            </div>

          </div>

        </div>


        <!-- ===================================================== -->
        <!-- EMPLOYEE INFORMATION + CONTACT + EMERGENCY -->
        <!-- ===================================================== -->

        <div class="lg:col-span-2">

          <div class="space-y-6">

            <!-- HR CONTROLLED INFORMATION (read-only) -->

            <div>

              <div class="flex items-center gap-2 mb-4">

                <div
                  class="w-8 h-8 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center"
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


              <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 border border-[#1e293b] rounded-xl p-5">

                <div>
                  <p class="text-sm text-gray-400">Sex</p>
                  <p class="font-medium text-white mt-1">
                    {{ userProfile.gender || '—' }}
                  </p>
                </div>

                <div>
                  <p class="text-sm text-gray-400">Department</p>
                  <p class="font-medium text-white mt-1">
                    {{ userProfile.department_name || '—' }}
                  </p>
                </div>

                <div>
                  <p class="text-sm text-gray-400">Position</p>
                  <p class="font-medium text-white mt-1">
                    {{ userProfile.position || '—' }}
                  </p>
                </div>

                <div>
                  <p class="text-sm text-gray-400">Level</p>
                  <p class="font-medium text-white mt-1">
                    {{ userProfile.level || '—' }}
                  </p>
                </div>

              </div>

            </div>


            <!-- ================================================= -->
            <!-- PERSONAL CONTACT INFORMATION -->
            <!-- ================================================= -->

            <form
              @submit.prevent="updateProfile"
              class="border-t border-[#1e293b] pt-6"
            >

              <div class="flex items-center gap-2 mb-4">

                <div
                  class="w-8 h-8 bg-green-500/10 text-green-400 rounded-lg flex items-center justify-center"
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
                    v-model="userProfile.phone"
                    type="tel"
                    placeholder="Enter contact number"
                    class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />

                </div>


                <!-- Email -->

                <div>

                  <label class="block text-sm font-medium text-gray-400 mb-1">
                    Email
                  </label>

                  <input
                    :value="accountSettings.email.current"
                    type="email"
                    disabled
                    class="w-full px-3 py-2 border border-[#1e293b] rounded-lg bg-[#0a1119] text-gray-500 cursor-not-allowed"
                  />

                  <p class="text-xs text-gray-500 mt-1">
                    To change your email, use Account Settings.
                  </p>

                </div>


                <!-- Address -->

                <div class="md:col-span-2">

                  <label class="block text-sm font-medium text-gray-400 mb-1">
                    Address
                  </label>

                  <textarea
                    v-model="userProfile.address"
                    rows="3"
                    placeholder="Enter your complete address"
                    class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                  ></textarea>

                </div>

              </div>


              <!-- ================================================= -->
              <!-- EMERGENCY CONTACT -->
              <!-- ================================================= -->

              <div class="border-t border-[#1e293b] pt-6 mt-6">

                <div class="flex items-center gap-2 mb-4">

                  <div
                    class="w-8 h-8 bg-red-500/10 text-red-400 rounded-lg flex items-center justify-center"
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
                      v-model="userProfile.emergency_contact_name"
                      type="text"
                      placeholder="Enter emergency contact name"
                      class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />

                  </div>


                  <!-- Relationship -->

                  <div>

                    <label class="block text-sm font-medium text-gray-400 mb-1">
                      Relationship
                    </label>

                    <select
                      v-model="userProfile.emergency_contact_relationship"
                      class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >

                      <option value="">
                        Select relationship
                      </option>

                      <option value="Parent">
                        Parent
                      </option>

                      <option value="Spouse">
                        Spouse
                      </option>

                      <option value="Sibling">
                        Sibling
                      </option>

                      <option value="Child">
                        Child
                      </option>

                      <option value="Relative">
                        Relative
                      </option>

                      <option value="Friend">
                        Friend
                      </option>

                      <option value="Guardian">
                        Guardian
                      </option>

                      <option value="Other">
                        Other
                      </option>

                    </select>

                  </div>


                  <!-- Emergency Contact Number -->

                  <div class="md:col-span-2">

                    <label class="block text-sm font-medium text-gray-400 mb-1">
                      Contact Number
                    </label>

                    <input
                      v-model="userProfile.emergency_contact_number"
                      type="tel"
                      placeholder="Enter emergency contact number"
                      class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />

                  </div>

                </div>

              </div>


              <!-- SAVE PROFILE -->

              <div class="flex justify-end mt-6">

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

      </div>

    </div>


    <!-- ========================================================= -->
    <!-- ACCOUNT SETTINGS TAB -->
    <!-- ========================================================= -->

    <div
      v-if="activeTab === 'account'"
      class="neo-card p-6"
    >

      <h2 class="text-xl font-semibold text-white mb-6">
        Account Settings
      </h2>

      <div class="space-y-6">

        <!-- Email Settings -->

        <div>

          <h3 class="text-lg font-medium text-white mb-4">
            Email Settings
          </h3>

          <form
            @submit.prevent="updateEmail"
            class="space-y-4"
          >

            <div>

              <label class="block text-sm font-medium text-gray-400 mb-1">
                Current Email
              </label>

              <input
                v-model="accountSettings.email.current"
                type="email"
                disabled
                class="w-full px-3 py-2 border border-[#1e293b] rounded-lg bg-[#0a1119] text-gray-500"
              />

            </div>

            <div>

              <label class="block text-sm font-medium text-gray-400 mb-1">
                New Email
              </label>

              <input
                v-model="accountSettings.email.new"
                type="email"
                class="w-full px-3 py-2 text-white bg-[#0d1520] border border-[#1e293b] rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              />

            </div>

            <div>

              <label class="block text-sm font-medium text-gray-400 mb-1">
                Confirm New Email
              </label>

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

          <h3 class="text-lg font-medium text-white mb-4">
            Password Settings
          </h3>

          <form
            @submit.prevent="updatePassword"
            class="space-y-4"
          >

            <div>

              <label class="block text-sm font-medium text-gray-400 mb-1">
                Current Password
              </label>

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

              <label class="block text-sm font-medium text-gray-400 mb-1">
                New Password
              </label>

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

              <label class="block text-sm font-medium text-gray-400 mb-1">
                Confirm New Password
              </label>

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

      </div>

    </div>


    <!-- ========================================================= -->
    <!-- LEAVE RULES TAB -->
    <!-- ========================================================= -->

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


      <div
        v-if="leaveRulesLoading"
        class="text-center py-8 text-gray-400"
      >
        Loading leave rules...
      </div>


      <div
        v-else
        class="space-y-4"
      >

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


    <!-- ========================================================= -->
    <!-- HELP DESK TAB -->
    <!-- ========================================================= -->

    <div
      v-if="activeTab === 'help'"
      class="neo-card p-6"
    >

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


    <!-- ========================================================= -->
    <!-- ABOUT US TAB -->
    <!-- ========================================================= -->

    <div
      v-if="activeTab === 'about'"
      class="neo-card overflow-hidden"
    >

      <!-- Header -->

      <div class="neo-card p-6">

        <div
          class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4"
        >

          <div>

            <h2 class="text-xl font-semibold text-white">
              Settings
            </h2>

            <p class="text-sm text-gray-400 mt-1">
              Information about the ENHS Leave System
            </p>

          </div>


          <div>

            <h2 class="text-2xl font-bold text-white">
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

            <div
              class="w-9 h-9 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center"
            >
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

            <div
              class="flex gap-4 p-5 border border-[#1e293b] rounded-xl hover:border-blue-500/40 hover:bg-blue-500/5 transition"
            >

              <div
                class="w-10 h-10 shrink-0 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center"
              >
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


            <div
              class="flex gap-4 p-5 border border-[#1e293b] rounded-xl hover:border-blue-500/40 hover:bg-blue-500/5 transition"
            >

              <div
                class="w-10 h-10 shrink-0 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center"
              >
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


            <div
              class="flex gap-4 p-5 border border-[#1e293b] rounded-xl hover:border-blue-500/40 hover:bg-blue-500/5 transition"
            >

              <div
                class="w-10 h-10 shrink-0 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center"
              >
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


            <div
              class="flex gap-4 p-5 border border-[#1e293b] rounded-xl hover:border-blue-500/40 hover:bg-blue-500/5 transition"
            >

              <div
                class="w-10 h-10 shrink-0 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center"
              >
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


            <div
              class="flex gap-4 p-5 border border-[#1e293b] rounded-xl hover:border-blue-500/40 hover:bg-blue-500/5 transition"
            >

              <div
                class="w-10 h-10 shrink-0 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center"
              >
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


            <div
              class="flex gap-4 p-5 border border-[#1e293b] rounded-xl hover:border-blue-500/40 hover:bg-blue-500/5 transition"
            >

              <div
                class="w-10 h-10 shrink-0 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center"
              >
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

            <div class="border border-[#1e293b] rounded-xl p-6">

              <div class="flex items-center gap-3 mb-5">

                <div
                  class="w-10 h-10 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center"
                >
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


            <div class="border border-[#1e293b] rounded-xl p-6">

              <div class="flex items-center gap-3 mb-5">

                <div
                  class="w-10 h-10 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center"
                >
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

          <div
            class="border border-blue-500/20 bg-blue-500/10 rounded-xl p-6"
          >

            <div class="flex items-start gap-4">

              <div
                class="w-10 h-10 shrink-0 bg-[#111d2e] text-blue-400 rounded-lg flex items-center justify-center"
              >
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


    <!-- ========================================================= -->
    <!-- LOGOUT -->
    <!-- ========================================================= -->

    <div class="neo-card p-6">

      <div class="flex items-center justify-between">

        <div>

          <h3 class="text-lg font-medium text-white">
            Sign Out
          </h3>

          <p class="text-sm text-gray-400 mt-1">
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
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

import {
  User,
  Settings,
  HelpCircle,
  Info,
  ChevronDown,
  FileText,
  Eye,
  EyeOff,
  Building2,
  Phone,
  ShieldAlert
} from 'lucide-vue-next'

import {
  getMyProfile,
  updateMyProfile,
  updateEmail as updateEmailAPI,
  updatePassword as updatePasswordAPI,
  updatePhone as updatePhoneAPI
} from '@/services/employee'

import { getLeaveSettings } from '@/services/leaveSettings'

const router = useRouter()

const fullName = computed(() => {
  return [
    userProfile.value.first_name,
    userProfile.value.middle_name,
    userProfile.value.last_name
  ]
    .filter(Boolean)
    .join(' ')
})

const initials = computed(() => {
  return (
    userProfile.value.first_name.charAt(0) +
    userProfile.value.last_name.charAt(0)
  ).toUpperCase()
})

/* =========================================================
   SETTINGS NAVIGATION
========================================================= */

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

/* =========================================================
   DEPARTMENTS
========================================================= */

interface Department {
  department_id: number
  department_name: string
  level: string
}

const departments = ref<Department[]>([])

const loadDepartments = async () => {
  try {
    const response = await axios.get(
      'https://enhs-leave-management-system.onrender.com/api/departments',
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      }
    )

    departments.value = response.data
  } catch (error) {
    console.error('Failed to load departments:', error)
  }
}

/* =========================================================
   EMPLOYEE PROFILE
   Only employee-editable personal/contact information
========================================================= */

const userProfile = ref({
  first_name: '',
  middle_name: '',
  last_name: '',

  employeeId: '',

  position: '',
  department_id: null as number | null,
  department_name:'',
  level: '',
  gender: '',
  employment_status: '',

  phone: '',

  address: '',
  emergency_contact_name: '',
  emergency_contact_number: '',
  emergency_contact_relationship: ''
})

/* =========================================================
   LOAD PROFILE
========================================================= */

const loadProfile = async () => {
  try {
    const data = await getMyProfile()

    userProfile.value = {
      first_name: data.first_name ?? '',
      middle_name: data.middle_name ?? '',
      last_name: data.last_name ?? '',

      employeeId: data.employee_code ?? '',

      /*
       * These are displayed for reference.
       * They should not be modified by the employee.
       */
      position: data.position ?? '',
      department_id: data.department_id ?? null,
      department_name: data.department_name ?? '',
      level: data.level ?? '',
      gender: data.sex ?? '',
      employment_status: data.employment_status ?? '',

      phone: data.contact_number ?? '',

      address: data.address ?? '',
      emergency_contact_name:
        data.emergency_contact_name ?? '',
      emergency_contact_number:
        data.emergency_contact_number ?? '',
      emergency_contact_relationship:
        data.emergency_contact_relationship ?? ''
    }

    accountSettings.value.email.current =
      data.email ?? ''

    accountSettings.value.phone.current =
      data.contact_number ?? ''

    accountSettings.value.position.current =
      data.position ?? ''

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
      first_name: userProfile.value.first_name,
      middle_name: userProfile.value.middle_name,
      last_name: userProfile.value.last_name,

      sex: userProfile.value.gender,

      contact_number:
        userProfile.value.phone,

      address:
        userProfile.value.address,

      emergency_contact_name:
        userProfile.value.emergency_contact_name,

      emergency_contact_number:
        userProfile.value.emergency_contact_number,

      emergency_contact_relationship:
        userProfile.value.emergency_contact_relationship
    })

    alert('Profile updated successfully.')

    await loadProfile()

  } catch (error: any) {
    console.error('Profile update failed:', error)

    if (error.response?.data?.message) {
      alert(error.response.data.message)
    } else if (error.response?.data?.errors) {
      const errors = error.response.data.errors

      const messages = Object.values(errors)
        .flat()
        .join('\n')

      alert(messages)
    } else {
      alert('Failed to update profile.')
    }
  }
}

/* =========================================================
   ACCOUNT SETTINGS
========================================================= */

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

/* =========================================================
   EMAIL
========================================================= */

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

    await loadProfile()

  } catch (error: any) {
    console.error('Email update failed:', error)

    if (error.response?.data?.message) {
      alert(error.response.data.message)
    } else {
      alert('Email update failed.')
    }
  }
}

/* =========================================================
   PASSWORD
========================================================= */

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

/* =========================================================
   PHONE
========================================================= */

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

    await loadProfile()

  } catch (error: any) {

    console.error('Phone update failed:', error)

    if (error.response?.data?.message) {
      alert(error.response.data.message)
    } else {
      alert('Phone update failed.')
    }
  }
}

/* =========================================================
   POSITION REQUEST
========================================================= */

const updatePosition = () => {

  /*
   * Position is controlled by the administrator.
   * Employees cannot directly change their position.
   *
   * This currently acts as a placeholder for a future
   * position-change request workflow.
   */

  alert(
    'Position changes must be requested through the administrator.'
  )
}

/* =========================================================
   LEAVE RULES
========================================================= */

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

/* =========================================================
   FAQ
========================================================= */

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

/* =========================================================
   LOGOUT
========================================================= */

const logout = () => {

  localStorage.removeItem('token')

  router.push('/login')

}

/* =========================================================
   INITIAL LOAD
========================================================= */

onMounted(async () => {

  await Promise.all([
    loadProfile(),
    loadLeaveRules(),
    loadDepartments()
  ])

})
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