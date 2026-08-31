<template>
  <div class="settings-shell p-6 min-h-screen space-y-6">
    <!-- Header -->

    <div class="neo-card p-6">
      <h1 class="text-2xl font-bold text-white">Admin Settings</h1>

      <p class="text-gray-400 mt-2">
        Manage system configuration and administration settings.
      </p>
    </div>

    <!-- Tabs -->

    <div class="neo-card">
      <div class="border-b border-[#1e293b] px-6">
        <nav class="flex flex-wrap gap-6">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-4 text-sm font-medium transition',
              activeTab === tab.id
                ? 'text-blue-400 border-b-2 border-blue-400'
                : 'text-gray-400 hover:text-gray-200',
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Account Settings -->

    <div v-if="activeTab === 'account'" class="neo-card p-6">
      <h2 class="text-xl font-semibold text-white mb-2">Account Settings</h2>

      <p class="text-gray-400 mb-6">
        Manage administrator account information and security.
      </p>

      <!-- Admin Profile -->
      <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
          <div>
            <h3 class="text-lg font-semibold text-white">
              Administrator Profile
            </h3>
            <p class="text-sm text-gray-400">
              Update your administrator information.
            </p>
          </div>

          <!-- Edit button -->
          <button
            v-if="!isEditingProfile"
            @click="startEditProfile"
            type="button"
            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg"
          >
            Edit Profile
          </button>

          <!-- Save / Cancel buttons -->
          <div v-else class="flex gap-2">
            <button
              @click="cancelEditProfile"
              type="button"
              class="px-4 py-2 border border-[#1e293b] text-gray-300 rounded-lg hover:bg-[#0b1420]"
            >
              Cancel
            </button>

            <button
              @click="saveAdminProfile"
              type="button"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
            >
              Save Changes
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- First Name -->
          <div>
            <label class="block text-sm font-medium text-gray-300">
              First Name
            </label>

            <input
              v-model="adminProfile.first_name"
              type="text"
              :readonly="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            />
          </div>

          <!-- Middle Name -->
          <div>
            <label class="block text-sm font-medium text-gray-300">
              Middle Name
            </label>

            <input
              v-model="adminProfile.middle_name"
              type="text"
              :readonly="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            />
          </div>

          <!-- Last Name -->
          <div>
            <label class="block text-sm font-medium text-gray-300">
              Last Name
            </label>

            <input
              v-model="adminProfile.last_name"
              type="text"
              :readonly="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            />
          </div>

          <!-- Sex -->
          <div>
            <label class="block text-sm font-medium text-gray-300"> Sex </label>

            <select
              v-model="adminProfile.sex"
              :disabled="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            >
              <option value="">Select Sex</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>
          </div>

          <!-- Extension Name -->
          <div>
            <label class="block text-sm font-medium text-gray-300"
              >Extension Name</label
            >
            <select
              v-model="adminProfile.extension_name"
              :disabled="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            >
              <option value="">None</option>
              <option value="Jr.">Jr.</option>
              <option value="Sr.">Sr.</option>
              <option value="II">II</option>
              <option value="III">III</option>
              <option value="IV">IV</option>
              <option value="V">V</option>
            </select>
          </div>

          <!-- Date of Birth -->
          <div>
            <label class="block text-sm font-medium text-gray-300"
              >Date of Birth</label
            >
            <input
              v-model="adminProfile.date_of_birth"
              type="date"
              :readonly="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            />
          </div>

          <!-- Civil Status -->
          <div>
            <label class="block text-sm font-medium text-gray-300"
              >Civil Status</label
            >
            <select
              v-model="adminProfile.civil_status"
              :disabled="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            >
              <option value="">Select</option>
              <option value="Single">Single</option>
              <option value="Married">Married</option>
              <option value="Widowed">Widowed</option>
              <option value="Separated">Separated</option>
              <option value="Divorced">Divorced</option>
            </select>
          </div>

          <!-- Nationality -->
          <div>
            <label class="block text-sm font-medium text-gray-300"
              >Nationality</label
            >
            <input
              v-model="adminProfile.nationality"
              type="text"
              :readonly="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            />
          </div>

          <!-- Personal Email -->
          <div>
            <label class="block text-sm font-medium text-gray-300"
              >Personal Email</label
            >
            <input
              v-model="adminProfile.personal_email"
              type="email"
              :readonly="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            />
          </div>

          <!-- Address -->
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-300"
              >Address</label
            >
            <textarea
              v-model="adminProfile.address"
              rows="2"
              :readonly="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2 resize-none',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            ></textarea>
          </div>

          <!-- Emergency Contact Name -->
          <div>
            <label class="block text-sm font-medium text-gray-300"
              >Emergency Contact Name</label
            >
            <input
              v-model="adminProfile.emergency_contact_name"
              type="text"
              :readonly="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            />
          </div>

          <!-- Emergency Contact Number -->
          <div>
            <label class="block text-sm font-medium text-gray-300"
              >Emergency Contact Number</label
            >
            <input
              v-model="adminProfile.emergency_contact_number"
              type="tel"
              inputmode="numeric"
              maxlength="11"
              :readonly="!isEditingProfile"
              @input="
                adminProfile.emergency_contact_number =
                  adminProfile.emergency_contact_number
                    .replace(/\D/g, '')
                    .slice(0, 11)
              "
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-300">Level</label>

            <select
              v-model="adminProfile.level"
              :disabled="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            >
              <option value="">Select Level</option>
              <option value="JHS">Junior High School (JHS)</option>
              <option value="SHS">Senior High School (SHS)</option>
              <option value="Non-Teaching">Non-Teaching</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-300"
              >Position</label
            >

            <select
              v-model="adminProfile.position_id"
              :disabled="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            >
              <option :value="null">Select Position</option>

              <option
                v-for="position in positions"
                :key="position.id"
                :value="Number(position.id)"
              >
                {{ position.name }}
              </option>
            </select>
          </div>

          <!-- Salary Grade (readonly, derived from position) -->
          <div>
            <label class="block text-sm font-medium text-gray-300"
              >Salary Grade</label
            >
            <input
              :value="selectedAdminSalaryGrade || '-'"
              type="text"
              readonly
              class="mt-1 w-full border rounded-lg px-3 py-2 field-readonly"
            />
          </div>

          <!-- Salary Step -->
          <div>
            <label class="block text-sm font-medium text-gray-300"
              >Salary Step</label
            >
            <select
              v-model="adminProfile.salary_step"
              :disabled="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            >
              <option :value="null">Select Step</option>
              <option v-for="step in 8" :key="step" :value="step">
                Step {{ step }}
              </option>
            </select>
          </div>

          <!-- Current Salary (readonly, auto-computed) -->
          <div>
            <label class="block text-sm font-medium text-gray-300"
              >Current Salary</label
            >
            <input
              :value="formattedAdminSalary"
              type="text"
              readonly
              class="mt-1 w-full border rounded-lg px-3 py-2 field-readonly font-semibold"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-300"
              >Department</label
            >

            <select
              v-model="adminProfile.department_id"
              :disabled="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            >
              <option :value="null">Select Department</option>

              <option
                v-for="dept in filteredAdminDepartments"
                :key="dept.department_id"
                :value="dept.department_id"
              >
                {{ dept.department_name }}
              </option>
            </select>
          </div>

          <!-- Contact Number -->
          <div>
            <label class="block text-sm font-medium text-gray-300">
              Contact Number
            </label>

            <input
              v-model="adminProfile.contact_number"
              type="text"
              :readonly="!isEditingProfile"
              :class="[
                'mt-1 w-full border rounded-lg px-3 py-2',
                isEditingProfile ? 'field-editable' : 'field-readonly',
              ]"
            />
          </div>
        </div>
      </div>

      <hr class="my-8 border-[#1e293b]" />

      <!-- Email -->

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-300">
            Admin Email
          </label>

          <input
            v-model="adminEmail"
            type="email"
            class="mt-1 w-full border rounded-lg px-3 py-2 field-editable"
          />
        </div>

        <button
          @click="updateEmail"
          class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700"
        >
          Update Email
        </button>
      </div>

      <hr class="my-8 border-[#1e293b]" />

      <!-- Password -->

      <h3 class="text-lg font-semibold text-white mb-4">Change Password</h3>

      <div class="space-y-4">
        <input
          v-model="password.current_password"
          type="password"
          placeholder="Current Password"
          class="w-full border rounded-lg px-3 py-2 field-editable"
        />

        <input
          v-model="password.new_password"
          type="password"
          placeholder="New Password"
          class="w-full border rounded-lg px-3 py-2 field-editable"
        />

        <input
          v-model="password.new_password_confirmation"
          type="password"
          placeholder="Confirm New Password"
          class="w-full border rounded-lg px-3 py-2 field-editable"
        />

        <button
          @click="updatePassword"
          class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700"
        >
          Update Password
        </button>
      </div>
    </div>

    <!-- Leave Settings -->

    <div v-if="activeTab === 'leave'" class="neo-card p-6">
      <h2 class="text-xl font-semibold text-white">Leave Settings</h2>

      <p class="text-gray-400 mt-1 mb-6">
        Manage leave types, requirements, and rules for employees.
      </p>
      <div class="inner-card p-5">
        <div class="flex items-center justify-between gap-4 mb-5">
          <div>
            <h3 class="text-lg font-semibold text-white">Leave Types</h3>

            <p class="text-sm text-gray-400">
              Manage the leave types available to employees.
            </p>
          </div>

          <button
            @click="openAddLeaveModal"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
          >
            Add Leave Type
          </button>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="border-b border-[#1e293b] text-left">
                <th class="px-4 py-3 text-gray-300">Code</th>

                <th class="px-4 py-3 text-gray-300">Leave Type</th>

                <th class="px-4 py-3 text-gray-300">
                  Legal Basis / Requirements
                </th>

                <th class="px-4 py-3 text-gray-300 text-right">Actions</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="leave in leaveTypes"
                :key="leave.leave_type_id"
                class="border-b border-[#1e293b]"
              >
                <td class="px-4 py-3">
                  <span
                    class="inline-flex px-2 py-1 rounded bg-blue-500/10 text-blue-400 font-semibold"
                  >
                    {{ leave.code }}
                  </span>
                </td>

                <td class="px-4 py-3 text-white font-medium">
                  {{ leave.leave_type_name }}
                </td>

                <td class="px-4 py-3 text-gray-400 max-w-xl">
                  {{
                    leave.legal_basis ||
                    "No legal basis or requirements provided."
                  }}
                </td>

                <td class="px-4 py-3">
                  <div class="flex justify-end gap-2">
                    <button
                      @click="openEditLeaveModal(leave)"
                      class="px-3 py-1.5 text-blue-400 border border-blue-500/40 rounded hover:bg-blue-500/10"
                    >
                      Edit
                    </button>

                    <button
                      @click="removeLeaveType(leave.leave_type_id)"
                      class="px-3 py-1.5 text-red-400 border border-red-500/40 rounded hover:bg-red-500/10"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="leaveTypes.length === 0">
                <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                  No leave types found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Leave Rules -->

      <div class="inner-card p-5 mt-6">
        <div class="flex items-center justify-between mb-5">
          <div>
            <h3 class="text-lg font-semibold text-white">Leave Rules</h3>

            <p class="text-sm text-gray-400">
              Configure general rules and requirements that employees should
              follow when filing leave.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <!-- Advance Filing -->

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              Minimum Filing Notice
            </label>

            <div class="flex items-center gap-3">
              <input
                v-model="leaveRules.minimum_notice_days"
                type="number"
                min="0"
                class="w-full border rounded-lg px-3 py-2 field-editable"
              />

              <span class="text-sm text-gray-400 whitespace-nowrap">
                day(s) before leave
              </span>
            </div>

            <p class="text-xs text-gray-500 mt-1">
              Number of days an employee should file leave in advance.
            </p>
          </div>

          <!-- Supporting Documents -->

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              Supporting Documents
            </label>

            <select
              v-model="leaveRules.require_documents"
              class="w-full border rounded-lg px-3 py-2 field-editable"
            >
              <option :value="false">Not Required</option>

              <option :value="true">Required</option>
            </select>

            <p class="text-xs text-gray-500 mt-1">
              Determines whether employees must provide supporting documents.
            </p>
          </div>

          <!-- Maximum Consecutive Days -->

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              Maximum Consecutive Leave Days
            </label>

            <input
              v-model="leaveRules.max_consecutive_days"
              type="number"
              min="0"
              class="w-full border rounded-lg px-3 py-2 field-editable"
              placeholder="Example: 10"
            />

            <p class="text-xs text-gray-500 mt-1">
              Maximum number of consecutive days allowed per application.
            </p>
          </div>

          <!-- General Rule -->

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              General Leave Policy
            </label>

            <textarea
              v-model="leaveRules.general_policy"
              rows="3"
              class="w-full border rounded-lg px-3 py-2 field-editable"
              placeholder="Enter general leave filing rules or instructions..."
            ></textarea>
          </div>
        </div>

        <div class="flex justify-end mt-5">
          <button
            @click="saveLeaveRules"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg"
          >
            Save Leave Rules
          </button>
        </div>
      </div>
    </div>

    <div v-if="activeTab === 'approval'" class="neo-card p-6">
      <h2 class="text-xl font-semibold text-white">Approval Settings</h2>

      <p class="text-gray-400 mt-1 mb-6">
        Configure the review, endorsement, and approval workflow for leave
        applications.
      </p>

      <div class="inner-card p-5">
        <div class="mb-5">
          <h3 class="text-lg font-semibold text-white">
            Leave Approval Workflow
          </h3>

          <p class="text-sm text-gray-400 mt-1">
            Manage the internal review and endorsement process for leave
            applications.
          </p>
        </div>

        <div class="space-y-5">
          <div class="flex items-center justify-between gap-4">
            <div>
              <h4 class="font-medium text-white">Admin Review</h4>

              <p class="text-sm text-gray-400">
                Require Admin Personnel to review submitted leave applications.
              </p>
            </div>

            <select
              v-model="approvalSettings.require_admin_review"
              class="border rounded-lg px-3 py-2 field-editable"
            >
              <option :value="true">Required</option>
              <option :value="false">Not Required</option>
            </select>
          </div>

          <div class="flex items-center justify-between gap-4">
            <div>
              <h4 class="font-medium text-white">Principal Endorsement</h4>

              <p class="text-sm text-gray-400">
                Require School Principal endorsement before the application is
                prepared for external submission.
              </p>
            </div>

            <select
              v-model="approvalSettings.require_principal_endorsement"
              class="border rounded-lg px-3 py-2 field-editable"
            >
              <option :value="true">Required</option>
              <option :value="false">Not Required</option>
            </select>
          </div>

          <div class="flex items-center justify-between gap-4">
            <div>
              <h4 class="font-medium text-white">Admin Remarks</h4>

              <p class="text-sm text-gray-400">
                Allow Admin Personnel to provide remarks during application
                review.
              </p>
            </div>

            <select
              v-model="approvalSettings.allow_admin_remarks"
              class="border rounded-lg px-3 py-2 field-editable"
            >
              <option :value="true">Allowed</option>
              <option :value="false">Not Allowed</option>
            </select>
          </div>
        </div>
      </div>

      <div class="inner-card p-5 mt-6">
        <div class="mb-5">
          <h3 class="text-lg font-semibold text-white">
            Division Office Result
          </h3>

          <p class="text-sm text-gray-400 mt-1">
            Configure how official Division Office results are recorded and
            processed in the system.
          </p>
        </div>

        <div class="space-y-5">
          <div class="flex items-center justify-between gap-4">
            <div>
              <h4 class="font-medium text-white">
                Automatically Update Application Status
              </h4>

              <p class="text-sm text-gray-400">
                Update the application status when an official Division Office
                result is recorded.
              </p>
            </div>

            <select
              v-model="approvalSettings.auto_update_status"
              class="border rounded-lg px-3 py-2 field-editable"
            >
              <option :value="true">Enabled</option>
              <option :value="false">Disabled</option>
            </select>
          </div>

          <div class="flex items-center justify-between gap-4">
            <div>
              <h4 class="font-medium text-white">
                Automatically Update Leave Balance
              </h4>

              <p class="text-sm text-gray-400">
                Update the employee's leave balance based on the official
                Division Office approved leave days.
              </p>
            </div>

            <select
              v-model="approvalSettings.auto_update_balance"
              class="border rounded-lg px-3 py-2 field-editable"
            >
              <option :value="true">Enabled</option>
              <option :value="false">Disabled</option>
            </select>
          </div>
        </div>
      </div>

      <div class="flex justify-end mt-6">
        <button
          @click="saveApprovalSettings"
          class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg"
        >
          Save Approval Settings
        </button>
      </div>
    </div>

    <!-- System Settings -->

    <div v-if="activeTab === 'system'" class="neo-card p-6">
      <h2 class="text-xl font-semibold text-white">System Settings</h2>

      <p class="text-gray-400 mt-1 mb-6">
        Manage general system information and configuration.
      </p>

      <!-- System Information -->

      <div class="inner-card p-5">
        <div class="mb-5">
          <h3 class="text-lg font-semibold text-white">System Information</h3>

          <p class="text-sm text-gray-400 mt-1">
            Configure the basic information displayed throughout the system.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <!-- System Name -->

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              System Name
            </label>

            <input
              v-model="systemSettings.system_name"
              type="text"
              class="w-full border rounded-lg px-3 py-2 field-editable"
              placeholder="Enter system name"
            />
          </div>

          <!-- School Name -->

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              School Name
            </label>

            <input
              v-model="systemSettings.school_name"
              type="text"
              class="w-full border rounded-lg px-3 py-2 field-editable"
              placeholder="Enter school name"
            />
          </div>

          <!-- System Version -->

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              System Version
            </label>

            <input
              v-model="systemSettings.system_version"
              type="text"
              class="w-full border rounded-lg px-3 py-2 field-editable"
              placeholder="Example: 1.0.0"
            />
          </div>

          <!-- Contact Email -->

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              Contact Email
            </label>

            <input
              v-model="systemSettings.contact_email"
              type="email"
              class="w-full border rounded-lg px-3 py-2 field-editable"
              placeholder="Enter contact email"
            />
          </div>

          <!-- Contact Number -->

          <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
              Contact Number
            </label>

            <input
              v-model="systemSettings.contact_number"
              type="text"
              class="w-full border rounded-lg px-3 py-2 field-editable"
              placeholder="Enter contact number"
            />
          </div>

          <!-- System Description -->

          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-300 mb-2">
              System Description
            </label>

            <textarea
              v-model="systemSettings.system_description"
              rows="3"
              class="w-full border rounded-lg px-3 py-2 field-editable"
              placeholder="Enter a short description of the system..."
            ></textarea>
          </div>
        </div>
      </div>

      <!-- About Us -->

      <div class="inner-card p-5 mt-6">
        <div class="mb-5">
          <h3 class="text-lg font-semibold text-white">About Us</h3>

          <p class="text-sm text-gray-400 mt-1">
            Information about the leave management system that may be displayed
            to employees.
          </p>
        </div>

        <textarea
          v-model="systemSettings.about_us"
          rows="5"
          class="w-full border rounded-lg px-3 py-2 field-editable"
          placeholder="Enter information about the system..."
        ></textarea>
      </div>

      <!-- Save -->

      <div class="flex justify-end mt-6">
        <button
          @click="saveSystemSettings"
          class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg"
        >
          Save System Settings
        </button>
      </div>
    </div>

    <div v-if="activeTab === 'audit'" class="neo-card p-6">
      <h2 class="text-xl font-semibold text-white">Audit Logs</h2>

      <p class="text-gray-400 mt-2">Audit trail of admin and login activity.</p>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
        <div class="inner-card p-4">
          <p class="text-sm text-gray-400">Logged events</p>
          <p class="text-2xl font-semibold text-white">{{ auditTotal }}</p>
        </div>

        <div class="inner-card p-4">
          <p class="text-sm text-gray-400">Showing</p>
          <p class="text-2xl font-semibold text-white">
            {{ auditLogs.length }}
          </p>
        </div>

        <div class="inner-card p-4">
          <p class="text-sm text-gray-400">Last event</p>
          <p class="text-2xl font-semibold text-white">
            {{ lastEventComputed }}
          </p>
        </div>
      </div>

      <div class="flex flex-wrap gap-3 mt-6">
        <select
          v-model="auditFilters.action"
          @change="loadAuditLogs(1)"
          class="border rounded-lg px-3 py-2 text-sm field-editable"
        >
          <option value="">All actions</option>
          <option v-for="a in auditActions" :key="a" :value="a">{{ a }}</option>
        </select>

        <input
          type="date"
          v-model="auditFilters.date_from"
          @change="loadAuditLogs(1)"
          class="border rounded-lg px-3 py-2 text-sm field-editable"
        />

        <input
          type="date"
          v-model="auditFilters.date_to"
          @change="loadAuditLogs(1)"
          class="border rounded-lg px-3 py-2 text-sm field-editable"
        />

        <button
          v-if="
            auditFilters.action ||
            auditFilters.date_from ||
            auditFilters.date_to
          "
          @click="clearAuditFilters"
          class="text-sm text-blue-400 hover:underline"
        >
          Clear filters
        </button>
      </div>

      <div class="mt-4 overflow-x-auto border border-[#1e293b] rounded-lg">
        <table class="min-w-full text-sm">
          <thead class="table-head">
            <tr>
              <th class="text-left px-4 py-3 font-medium text-gray-400">
                User
              </th>
              <th class="text-left px-4 py-3 font-medium text-gray-400">
                Action
              </th>
              <th class="text-left px-4 py-3 font-medium text-gray-400">
                Description
              </th>
              <th class="text-left px-4 py-3 font-medium text-gray-400">
                Date
              </th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="auditLoading">
              <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                Loading...
              </td>
            </tr>

            <tr v-else-if="auditLogs.length === 0">
              <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                No audit log entries yet.
              </td>
            </tr>

            <tr
              v-for="log in auditLogs"
              :key="log.log_id"
              class="border-t border-[#1e293b]"
            >
              <td class="px-4 py-3 text-white">
                {{ log.user?.email ?? "Unknown user" }}
              </td>

              <td class="px-4 py-3">
                <span
                  class="px-2 py-1 rounded-md text-xs font-medium"
                  :class="actionBadgeClass(log.action)"
                >
                  {{ log.action }}
                </span>
              </td>

              <td class="px-4 py-3 text-gray-300">{{ log.description }}</td>

              <td class="px-4 py-3 text-gray-400 whitespace-nowrap">
                {{ formatAuditDate(log.created_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between mt-4">
        <p class="text-sm text-gray-500">
          Showing {{ auditFrom }}-{{ auditTo }} of {{ auditTotal }}
        </p>

        <div class="flex gap-2">
          <button
            :disabled="auditPage <= 1"
            @click="loadAuditLogs(auditPage - 1)"
            class="px-3 py-1.5 border border-[#1e293b] text-gray-300 rounded-lg text-sm disabled:opacity-40 hover:bg-[#0b1420]"
          >
            Prev
          </button>

          <button
            :disabled="auditPage >= auditLastPage"
            @click="loadAuditLogs(auditPage + 1)"
            class="px-3 py-1.5 border border-[#1e293b] text-gray-300 rounded-lg text-sm disabled:opacity-40 hover:bg-[#0b1420]"
          >
            Next
          </button>
        </div>
      </div>
    </div>

    <!-- Backup & Recovery -->

    <div v-if="activeTab === 'backup'" class="neo-card p-6">
      <h2 class="text-xl font-semibold text-white">Backup & Recovery</h2>

      <p class="text-gray-400 mt-1 mb-6">
        Create, manage, and recover system database backups.
      </p>

      <!-- Database Backup -->

      <div class="inner-card p-5">
        <div
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
        >
          <div>
            <h3 class="text-lg font-semibold text-white">Database Backup</h3>

            <p class="text-sm text-gray-400 mt-1">
              Create a digital backup copy of the system database.
            </p>
          </div>

          <button
            @click="createBackup"
            :disabled="backupLoading"
            class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-5 py-2 rounded-lg"
          >
            {{ backupLoading ? "Creating Backup..." : "Create Backup" }}
          </button>
        </div>

        <!-- Last Backup -->

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
          <div class="inner-card-alt p-4">
            <p class="text-sm text-gray-400">Last Backup</p>

            <p class="text-lg font-semibold text-white mt-1">
              {{ lastBackup.date || "No backup yet" }}
            </p>
          </div>

          <div class="inner-card-alt p-4">
            <p class="text-sm text-gray-400">Backup Type</p>

            <p class="text-lg font-semibold text-white mt-1">
              {{ lastBackup.type || "—" }}
            </p>
          </div>

          <div class="inner-card-alt p-4">
            <p class="text-sm text-gray-400">Status</p>

            <p
              class="text-lg font-semibold mt-1"
              :class="
                lastBackup.status === 'Successful'
                  ? 'text-green-400'
                  : 'text-white'
              "
            >
              {{ lastBackup.status || "—" }}
            </p>
          </div>
        </div>
        <div class="flex justify-end mt-5">
          <button
            @click="downloadLatestBackup"
            :disabled="!lastBackup.id"
            class="bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white px-5 py-2 rounded-lg"
          >
            Download Latest Backup
          </button>
        </div>
      </div>

      <!-- Backup Methods -->

      <div class="inner-card p-5 mt-6">
        <div class="mb-5">
          <h3 class="text-lg font-semibold text-white">Backup Methods</h3>

          <p class="text-sm text-gray-400 mt-1">
            Recommended methods for protecting important system data.
          </p>
        </div>

        <div class="space-y-4">
          <!-- Printed -->

          <div class="border border-[#1e293b] rounded-lg p-4">
            <h4 class="font-semibold text-white">Printed Backup</h4>

            <p class="text-sm text-gray-400 mt-1">
              Important reports such as leave, attendance, and employee reports
              may be printed and stored as physical records.
            </p>
          </div>

          <!-- External -->

          <div class="border border-[#1e293b] rounded-lg p-4">
            <h4 class="font-semibold text-white">Digital / External Backup</h4>

            <p class="text-sm text-gray-400 mt-1">
              Downloaded database backups may be copied to USB drives, external
              hard drives, or other secure storage devices.
            </p>
          </div>

          <!-- Cloud -->

          <div class="border border-[#1e293b] rounded-lg p-4">
            <h4 class="font-semibold text-white">Cloud Backup</h4>

            <p class="text-sm text-gray-400 mt-1">
              Database backup files may be uploaded to an authorized cloud
              storage service for an additional backup copy.
            </p>
          </div>
        </div>
      </div>

      <!-- Recovery -->

      <div class="inner-card p-5 mt-6">
        <div class="mb-5">
          <h3 class="text-lg font-semibold text-white">
            Recovery / Failover Method
          </h3>

          <p class="text-sm text-gray-400 mt-1">
            Procedure for recovering the system when the primary database
            becomes unavailable.
          </p>
        </div>

        <div class="space-y-3">
          <div class="flex items-center gap-3">
            <span
              class="w-8 h-8 rounded-full bg-blue-500/15 text-blue-400 flex items-center justify-center font-semibold"
            >
              1
            </span>

            <span class="text-gray-300"> Identify the database failure. </span>
          </div>

          <div class="flex items-center gap-3">
            <span
              class="w-8 h-8 rounded-full bg-blue-500/15 text-blue-400 flex items-center justify-center font-semibold"
            >
              2
            </span>

            <span class="text-gray-300">
              Locate the latest valid database backup.
            </span>
          </div>

          <div class="flex items-center gap-3">
            <span
              class="w-8 h-8 rounded-full bg-blue-500/15 text-blue-400 flex items-center justify-center font-semibold"
            >
              3
            </span>

            <span class="text-gray-300">
              Restore the database from the backup.
            </span>
          </div>

          <div class="flex items-center gap-3">
            <span
              class="w-8 h-8 rounded-full bg-blue-500/15 text-blue-400 flex items-center justify-center font-semibold"
            >
              4
            </span>

            <span class="text-gray-300">
              Verify restored records and resume system operation.
            </span>
          </div>
        </div>

        <div
          class="mt-5 bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4"
        >
          <p class="text-sm font-medium text-yellow-400">Restore Database</p>

          <p class="text-sm text-yellow-500/80 mt-1 mb-4">
            Select a previously downloaded JSON database backup file to restore
            the system data.
          </p>

          <div class="flex flex-col sm:flex-row gap-3">
            <input
              ref="restoreFileInput"
              type="file"
              accept=".json,application/json"
              @change="handleRestoreFile"
              class="hidden"
            />

            <button
              @click="restoreFileInput?.click()"
              :disabled="restoreLoading"
              class="bg-yellow-600 hover:bg-yellow-700 disabled:opacity-50 text-white px-5 py-2 rounded-lg"
            >
              {{ restoreLoading ? "Restoring..." : "Select Backup & Restore" }}
            </button>
          </div>

          <p v-if="selectedRestoreFile" class="text-sm text-gray-400 mt-3">
            Selected file:
            <span class="text-white">
              {{ selectedRestoreFile.name }}
            </span>
          </p>

          <p class="text-xs text-red-400 mt-3">
            Warning: Restoring a backup will replace the current database
            records with the records contained in the selected backup.
          </p>
        </div>
      </div>
    </div>

    <div
      v-if="isLeaveModalOpen"
      class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4"
    >
      <div class="neo-card w-full max-w-lg p-6">
        <h2 class="text-xl font-semibold text-white mb-5">
          {{ isEditMode ? "Edit Leave Type" : "Add Leave Type" }}
        </h2>

        <div class="space-y-4">
          <div>
            <label class="block text-sm text-gray-300 mb-1"> Leave Code </label>

            <input
              v-model="leaveForm.code"
              type="text"
              maxlength="20"
              placeholder="Example: VL"
              class="w-full border rounded-lg px-3 py-2 field-editable uppercase"
              @input="
                leaveForm.code = leaveForm.code
                  .replace(/[^a-zA-Z0-9-]/g, '')
                  .toUpperCase()
              "
            />
          </div>

          <div>
            <label class="block text-sm text-gray-300 mb-1">
              Leave Type Name
            </label>

            <input
              v-model="leaveForm.leave_type_name"
              type="text"
              placeholder="Example: Vacation Leave"
              class="w-full border rounded-lg px-3 py-2 field-editable"
            />
          </div>

          <div>
            <label class="block text-sm text-gray-300 mb-1">
              Legal Basis / Requirements
            </label>

            <textarea
              v-model="leaveForm.legal_basis"
              rows="6"
              placeholder="Enter the applicable legal basis and requirements..."
              class="w-full border rounded-lg px-3 py-2 field-editable resize-none"
            ></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <button
            @click="closeLeaveModal"
            class="px-4 py-2 text-gray-300 border border-[#1e293b] rounded-lg hover:bg-[#0b1420]"
          >
            Cancel
          </button>

          <button
            @click="saveLeaveType"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg"
          >
            {{ isEditMode ? "Update" : "Add" }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref, computed, watch } from "vue";
import {
  getAdminProfile,
  getAdminPositions,
  getAdminDepartments,
  getAdminSalaryInfo,
  updateAdminEmail,
  updateAdminPassword,
  updateAdminProfile,
} from "@/services/admin";
import {
  getLeaveTypes,
  createLeaveType,
  updateLeaveType,
  deleteLeaveType,
} from "@/services/leaveType";
import { getAuditLogs, getAuditLogActions } from "@/services/auditLog";
import {
  getLeaveSettings,
  updateLeaveSettings,
} from "@/services/leaveSettings";

import {
  getApprovalSettings,
  updateApprovalSettings,
} from "@/services/approvalSettings";

import {
  getSystemSettings,
  updateSystemSettings,
} from "@/services/systemSettings";

import {
  createDatabaseBackup,
  getBackups,
  downloadBackup,
  restoreDatabaseBackup,
} from "@/services/backup";

const activeTab = ref("account");

// AUDIT LOGS
const auditLogs = ref<any[]>([]);
const auditActions = ref<string[]>([]);
const auditFilters = ref({ action: "", date_from: "", date_to: "" });
const auditPage = ref(1);
const auditLastPage = ref(1);
const auditTotal = ref(0);
const auditFrom = ref(0);
const auditTo = ref(0);
const auditLoading = ref(false);

const lastEventComputed = ref("—");

const loadAuditLogs = async (page = 1) => {
  auditLoading.value = true;
  try {
    const { data } = await getAuditLogs({
      page,
      action: auditFilters.value.action || undefined,
      date_from: auditFilters.value.date_from || undefined,
      date_to: auditFilters.value.date_to || undefined,
    });

    auditLogs.value = data.data;
    auditPage.value = data.current_page;
    auditLastPage.value = data.last_page;
    auditTotal.value = data.total;
    auditFrom.value = data.from ?? 0;
    auditTo.value = data.to ?? 0;

    lastEventComputed.value = auditLogs.value.length
      ? formatAuditDate(auditLogs.value[0].created_at)
      : "—";
  } catch (error) {
    console.log(error);
  } finally {
    auditLoading.value = false;
  }
};

const loadAuditActions = async () => {
  try {
    const { data } = await getAuditLogActions();
    auditActions.value = data;
  } catch (error) {
    console.log(error);
  }
};

const clearAuditFilters = () => {
  auditFilters.value = { action: "", date_from: "", date_to: "" };
  loadAuditLogs(1);
};

const actionBadgeClass = (action: string) => {
  const a = action.toLowerCase();
  if (a.includes("login")) return "bg-green-500/15 text-green-400";
  if (a.includes("approved")) return "bg-blue-500/15 text-blue-400";
  if (a.includes("rejected") || a.includes("deleted"))
    return "bg-red-500/15 text-red-400";
  if (a.includes("updated") || a.includes("created"))
    return "bg-amber-500/15 text-amber-400";
  return "bg-gray-500/15 text-gray-400";
};

const formatAuditDate = (dateStr: string) => {
  const d = new Date(dateStr);
  return d.toLocaleString("en-US", {
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
  });
};

const tabs = [
  {
    id: "account",
    name: "Account Settings",
  },

  {
    id: "leave",
    name: "Leave Settings",
  },

  {
    id: "approval",
    name: "Approval Settings",
  },

  {
    id: "system",
    name: "System Settings",
  },

  {
    id: "audit",
    name: "Audit Logs",
  },

  {
    id: "backup",
    name: "Backup",
  },
];

const adminEmail = ref("");
const adminProfile = ref({
  first_name: "",
  middle_name: "",
  last_name: "",
  extension_name: "",
  date_of_birth: "",
  sex: "",
  civil_status: "",
  nationality: "",
  address: "",
  personal_email: "",
  level: "",
  position_id: null as number | null,
  salary_step: null as number | null,
  salary: "" as string | number,
  department_id: null as number | null,
  contact_number: "",
  emergency_contact_name: "",
  emergency_contact_number: "",
});

const positions = ref<any[]>([]);

const departments = ref<any[]>([]);

const loadDepartments = async () => {
  try {
    const data = await getAdminDepartments();
    departments.value = Array.isArray(data) ? data : [];
  } catch (error: any) {
    console.error("Failed to load departments:", error.response?.data || error);
  }
};

const loadPositions = async () => {
  try {
    const data = await getAdminPositions();
    positions.value = Array.isArray(data) ? data : [];
  } catch (error: any) {
    console.error("Failed to load positions:", error.response?.data || error);
  }
};

// Snapshot used to restore values if the user cancels an edit
const adminProfileBackup = ref({ ...adminProfile.value });

const isEditingProfile = ref(false);

const startEditProfile = () => {
  adminProfileBackup.value = { ...adminProfile.value };
  isEditingProfile.value = true;
};

const cancelEditProfile = () => {
  adminProfile.value = { ...adminProfileBackup.value };
  isEditingProfile.value = false;
};

const saveAdminProfile = async () => {
  console.log(
    "ADMIN PROFILE PAYLOAD:",
    JSON.stringify(adminProfile.value, null, 2),
  );

  try {
    await updateAdminProfile(adminProfile.value);

    alert("Profile updated successfully");
    isEditingProfile.value = false;

    await loadAdmin();
  } catch (error: any) {
    console.error(
      "Failed to update admin profile:",
      error.response?.data || error,
    );

    console.error("VALIDATION ERRORS:", error.response?.data?.errors);

    alert(error.response?.data?.message || "Failed to update profile.");
  }
};

const password = ref({
  current_password: "",
  new_password: "",
  new_password_confirmation: "",
});

const loadAdmin = async () => {
  try {
    const data = await getAdminProfile();

    adminEmail.value = data.email;

    if (data.profile) {
      adminProfile.value = {
        first_name: data.profile.first_name || "",
        middle_name: data.profile.middle_name || "",
        last_name: data.profile.last_name || "",
        extension_name: data.profile.extension_name || "",
        date_of_birth: data.profile.date_of_birth
          ? data.profile.date_of_birth.substring(0, 10)
          : "",
        sex: data.profile.sex || "",
        civil_status: data.profile.civil_status || "",
        nationality: data.profile.nationality || "",
        address: data.profile.address || "",
        personal_email: data.profile.personal_email || "",
        level: data.profile.level || "",
        position_id: data.profile.position_id || null,
        salary_step: data.profile.salary_step || null,
        salary: data.profile.salary != null ? String(data.profile.salary) : "",
        department_id: data.profile.department_id || null,
        contact_number: data.profile.contact_number || "",
        emergency_contact_name: data.profile.emergency_contact_name || "",
        emergency_contact_number: data.profile.emergency_contact_number || "",
      };

      adminProfileBackup.value = { ...adminProfile.value };
    }
  } catch (error: any) {
    console.error(
      "Failed to load admin profile:",
      error.response?.data || error,
    );
  }
};

const selectedAdminSalaryGrade = computed(() => {
  const selected = positions.value.find(
    (position) =>
      Number(position.id) === Number(adminProfile.value.position_id),
  );

  return selected?.salary_grade ?? null;
});

const filteredAdminDepartments = computed(() => {
  if (!adminProfile.value.level) {
    return departments.value;
  }

  return departments.value.filter(
    (department) => department.level === adminProfile.value.level,
  );
});

const calculateAdminSalary = async () => {
  const positionId = Number(adminProfile.value.position_id);
  const salaryStep = Number(adminProfile.value.salary_step);

  if (!positionId || !salaryStep) {
    adminProfile.value.salary = "";
    return;
  }

  try {
    const data = await getAdminSalaryInfo(positionId, salaryStep);

    if (data.salary === null || data.salary === undefined) {
      adminProfile.value.salary = "";
      console.error("No salary schedule found:", data);
      return;
    }

    adminProfile.value.salary = Number(data.salary);
  } catch (error: any) {
    console.error(
      "Failed to calculate admin salary:",
      error.response?.data || error,
    );

    adminProfile.value.salary = "";
  }
};

watch(
  [() => adminProfile.value.position_id, () => adminProfile.value.salary_step],
  () => {
    calculateAdminSalary();
  },
);
const formattedAdminSalary = computed(() => {
  if (
    adminProfile.value.salary === "" ||
    adminProfile.value.salary === null ||
    adminProfile.value.salary === undefined
  ) {
    return "-";
  }

  return `₱${Number(adminProfile.value.salary).toLocaleString("en-PH", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
});
const updateEmail = async () => {
  try {
    await updateAdminEmail(adminEmail.value);

    alert("Email updated successfully");
  } catch (error) {
    console.log(error);

    alert("Email update failed");
  }
};

const updatePassword = async () => {
  try {
    await updateAdminPassword(password.value);

    alert("Password updated successfully");
  } catch (error) {
    console.log(error);

    alert("Password update failed");
  }
};

const leaveRules = ref({
  minimum_notice_days: 0,
  require_documents: false,
  max_consecutive_days: 0,
  general_policy: "",
});

const loadLeaveRules = async () => {
  try {
    const data = await getLeaveSettings();

    leaveRules.value = {
      minimum_notice_days: data.minimum_notice_days ?? 0,
      require_documents: data.require_documents ?? false,
      max_consecutive_days: data.max_consecutive_days ?? 0,
      general_policy: data.general_policy ?? "",
    };
  } catch (error) {
    console.error("Failed to load leave rules:", error);
  }
};

const saveLeaveRules = async () => {
  try {
    await updateLeaveSettings({
      minimum_notice_days: Number(leaveRules.value.minimum_notice_days),
      require_documents: Boolean(leaveRules.value.require_documents),
      max_consecutive_days: Number(leaveRules.value.max_consecutive_days),
      general_policy: leaveRules.value.general_policy,
    });

    alert("Leave rules saved successfully.");

    await loadLeaveRules();
  } catch (error: any) {
    console.error("Failed to save leave rules:", error.response?.data || error);

    alert(error.response?.data?.message || "Failed to save leave rules.");
  }
};
interface LeaveType {
  leave_type_id: number;
  code: string;
  leave_type_name: string;
  legal_basis: string | null;
}

const leaveTypes = ref<LeaveType[]>([]);

const isLeaveModalOpen = ref(false);
const isEditMode = ref(false);

const leaveForm = ref({
  leave_type_id: null as number | null,
  code: "",
  leave_type_name: "",
  legal_basis: "",
});

const loadLeaveTypes = async () => {
  try {
    const data = await getLeaveTypes();

    leaveTypes.value = data;
  } catch (error: any) {
    console.error("Failed to load leave types:", error.response?.data || error);
  }
};

const resetLeaveForm = () => {
  leaveForm.value = {
    leave_type_id: null,
    code: "",
    leave_type_name: "",
    legal_basis: "",
  };
};

const openAddLeaveModal = () => {
  isEditMode.value = false;
  resetLeaveForm();
  isLeaveModalOpen.value = true;
};

const openEditLeaveModal = (leave: LeaveType) => {
  isEditMode.value = true;

  leaveForm.value = {
    leave_type_id: leave.leave_type_id,
    code: leave.code || "",
    leave_type_name: leave.leave_type_name || "",
    legal_basis: leave.legal_basis || "",
  };

  isLeaveModalOpen.value = true;
};

const closeLeaveModal = () => {
  isLeaveModalOpen.value = false;
  isEditMode.value = false;
  resetLeaveForm();
};

const saveLeaveType = async () => {
  const code = leaveForm.value.code.trim().toUpperCase();
  const leaveTypeName = leaveForm.value.leave_type_name.trim();
  const legalBasis = leaveForm.value.legal_basis.trim();

  if (!code) {
    alert("Leave code is required.");
    return;
  }

  if (!leaveTypeName) {
    alert("Leave type name is required.");
    return;
  }

  const payload = {
    code,
    leave_type_name: leaveTypeName,
    legal_basis: legalBasis,
  };

  try {
    if (isEditMode.value && leaveForm.value.leave_type_id !== null) {
      await updateLeaveType(leaveForm.value.leave_type_id, payload);

      alert("Leave type updated successfully.");
    } else {
      await createLeaveType(payload);

      alert("Leave type created successfully.");
    }

    closeLeaveModal();
    await loadLeaveTypes();
  } catch (error: any) {
    console.error("Failed saving leave type:", error.response?.data || error);

    const errors = error.response?.data?.errors;

    if (errors) {
      const firstError = Object.values(errors).flat().at(0);

      alert(String(firstError || "Failed to save leave type."));

      return;
    }

    alert(error.response?.data?.message || "Failed to save leave type.");
  }
};

const removeLeaveType = async (id: number) => {
  const confirmed = confirm("Are you sure you want to delete this leave type?");

  if (!confirmed) return;

  try {
    await deleteLeaveType(id);

    alert("Leave type deleted successfully.");

    await loadLeaveTypes();
  } catch (error: any) {
    console.error(
      "Failed to delete leave type:",
      error.response?.data || error,
    );

    alert(error.response?.data?.message || "Failed to delete leave type.");
  }
};

const approvalSettings = ref({
  require_admin_review: true,
  require_principal_endorsement: true,
  allow_admin_remarks: true,
  auto_update_status: true,
  auto_update_balance: true,
});

const loadApprovalSettings = async () => {
  try {
    const data = await getApprovalSettings();

    approvalSettings.value = {
      require_admin_review: data.require_admin_review ?? true,
      require_principal_endorsement: data.require_principal_endorsement ?? true,
      allow_admin_remarks: data.allow_admin_remarks ?? true,
      auto_update_status: data.auto_update_status ?? true,
      auto_update_balance: data.auto_update_balance ?? true,
    };
  } catch (error) {
    console.error("Failed to load approval settings:", error);
  }
};

const saveApprovalSettings = async () => {
  try {
    await updateApprovalSettings({
      require_admin_review: Boolean(
        approvalSettings.value.require_admin_review,
      ),
      require_principal_endorsement: Boolean(
        approvalSettings.value.require_principal_endorsement,
      ),
      allow_admin_remarks: Boolean(approvalSettings.value.allow_admin_remarks),
      auto_update_status: Boolean(approvalSettings.value.auto_update_status),
      auto_update_balance: Boolean(approvalSettings.value.auto_update_balance),
    });

    alert("Approval settings saved successfully.");

    await loadApprovalSettings();
  } catch (error: any) {
    console.error(
      "Failed to save approval settings:",
      error.response?.data || error,
    );

    alert(error.response?.data?.message || "Failed to save approval settings.");
  }
};

const systemSettings = ref({
  system_name: "",
  school_name: "",
  system_description: "",
  about_us: "",
  contact_email: "",
  contact_number: "",
  system_version: "",
});

const loadSystemSettings = async () => {
  try {
    const data = await getSystemSettings();

    const settings: Record<string, string> = {};

    data.forEach((setting: any) => {
      settings[setting.setting_key] = setting.setting_value;
    });

    systemSettings.value = {
      system_name: settings.system_name || "",
      school_name: settings.school_name || "",
      system_description: settings.system_description || "",
      about_us: settings.about_us || "",
      contact_email: settings.contact_email || "",
      contact_number: settings.contact_number || "",
      system_version: settings.system_version || "",
    };
  } catch (error) {
    console.error("Failed to load system settings:", error);
  }
};
const saveSystemSettings = async () => {
  try {
    await updateSystemSettings(systemSettings.value);

    alert("System settings saved successfully.");

    await loadSystemSettings();
  } catch (error: any) {
    console.error(
      "Failed to save system settings:",
      error.response?.data || error,
    );

    alert(error.response?.data?.message || "Failed to save system settings.");
  }
};

const backupLoading = ref(false);
const restoreLoading = ref(false);

const selectedRestoreFile = ref<File | null>(null);

const restoreFileInput = ref<HTMLInputElement | null>(null);

const lastBackup = ref({
  id: null as number | null,
  file_name: "",
  date: "",
  type: "",
  status: "",
});

const loadBackups = async () => {
  try {
    const response = await getBackups();

    console.log("BACKUPS RESPONSE:", response);

    const backups = response.data?.data ?? response.data ?? [];

    if (Array.isArray(backups) && backups.length > 0) {
      const latestBackup = backups[0];

      lastBackup.value = {
        id: latestBackup.backup_id ?? latestBackup.id ?? null,
        file_name: latestBackup.file_name ?? "",
        date: latestBackup.created_at
          ? formatBackupDate(latestBackup.created_at)
          : "—",
        type: latestBackup.backup_type || "Full Database",
        status: latestBackup.status || "—",
      };
    } else {
      lastBackup.value = {
        id: null,
        file_name: "",
        date: "",
        type: "",
        status: "",
      };
    }
  } catch (error: any) {
    console.error("Failed to load backups:", error.response?.data || error);
  }
};

const createBackup = async () => {
  if (backupLoading.value) return;

  backupLoading.value = true;

  try {
    const response = await createDatabaseBackup();

    console.log("CREATE BACKUP RESPONSE:", response);

    const backup = response.data?.data ?? response.data;

    if (backup) {
      lastBackup.value = {
        id: backup.backup_id ?? backup.id ?? null,
        file_name: backup.file_name ?? "",
        date: backup.created_at
          ? formatBackupDate(backup.created_at)
          : new Date().toLocaleString(),
        type: backup.backup_type || "Full Database",
        status: backup.status || "Successful",
      };
    }

    alert("Database backup created successfully.");

    await loadBackups();
  } catch (error: any) {
    console.error(
      "Failed to create database backup:",
      error.response?.data || error,
    );

    alert(error.response?.data?.message || "Failed to create database backup.");
  } finally {
    backupLoading.value = false;
  }
};

const formatBackupDate = (dateStr: string) => {
  const date = new Date(dateStr);

  return date.toLocaleString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
  });
};

const downloadLatestBackup = async () => {
  if (!lastBackup.value.id) {
    alert("No backup available to download.");
    return;
  }

  try {
    const response = await downloadBackup(lastBackup.value.id);

    const blob = new Blob([response.data], {
      type: "application/octet-stream",
    });

    const url = window.URL.createObjectURL(blob);

    const link = document.createElement("a");
    link.href = url;

    link.download =
      lastBackup.value.file_name ||
      `els_database_backup_${lastBackup.value.id}.sql`;

    document.body.appendChild(link);
    link.click();

    link.remove();

    window.URL.revokeObjectURL(url);
  } catch (error: any) {
    console.error("Failed to download backup:", error.response?.data || error);

    alert(error.response?.data?.message || "Failed to download backup.");
  }
};

const handleRestoreFile = async (event: Event) => {
  const input = event.target as HTMLInputElement;

  if (!input.files || input.files.length === 0) {
    return;
  }

  const file = input.files.item(0);

  if (!file) {
    return;
  }

  // Make sure the file is JSON
  if (
    file.type !== "application/json" &&
    !file.name.toLowerCase().endsWith(".json")
  ) {
    alert("Please select a valid JSON backup file.");

    input.value = "";

    return;
  }

  selectedRestoreFile.value = file;

  const confirmed = confirm(
    `Are you sure you want to restore "${file.name}"?\n\n` +
      "This will replace the current database records with the records from this backup.",
  );

  if (!confirmed) {
    selectedRestoreFile.value = null;
    input.value = "";

    return;
  }

  restoreLoading.value = true;

  try {
    const response = await restoreDatabaseBackup(file);

    console.log("RESTORE RESPONSE:", response);

    alert(response.data?.message || "Database restored successfully.");

    selectedRestoreFile.value = null;

    // Reload backup information
    await loadBackups();

    // Reset file input so the same file can be selected again
    input.value = "";
  } catch (error: any) {
    console.error("FULL RESTORE ERROR:", error);
    console.error("STATUS:", error.response?.status);
    console.error("DATA:", error.response?.data);

    alert("RESTORE FAILED\n\n" + JSON.stringify(error.response?.data, null, 2));

    input.value = "";
  } finally {
    restoreLoading.value = false;
  }
};

onMounted(() => {
  loadAdmin();
  loadPositions();
  loadDepartments(); // idagdag ito
  loadLeaveTypes();
  loadLeaveRules();
  loadApprovalSettings();
  loadSystemSettings();
  loadAuditLogs();
  loadAuditActions();
  loadBackups();
});
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
  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;
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

/* Nested panels inside a neo-card, e.g. "Leave Types", "Leave Rules" */
.inner-card {
  background: #0b1420;
  border: 1px solid #1e293b;
  border-radius: 1.1rem;
}

/* Small stat tiles inside an inner-card, e.g. backup summary tiles */
.inner-card-alt {
  background: #0d1a2b;
  border: 1px solid #1e293b;
  border-radius: 0.9rem;
}

.table-head {
  background: #0b1420;
}

/* Editable form fields */
.field-editable {
  background: #0b1420;
  color: #ffffff;
  border-color: #233045;
}

.field-editable::placeholder {
  color: #64748b;
}

.field-editable:focus {
  outline: none;
  border-color: #3b82f6;
}

/* Readonly / disabled form fields */
.field-readonly {
  background: #0d1626;
  color: #64748b;
  border-color: #1e293b;
}
</style>
