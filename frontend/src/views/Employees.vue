<template>
  <div class="w-full min-h-screen">
    <div
      class="dashboard-shell w-full max-w-none mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6"
    >
      <!-- ========================================================= -->
      <!-- HEADER -->
      <!-- ========================================================= -->
      <div class="neo-card w-full p-6">
        <div
          class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4"
        >
          <!-- LEFT -->
          <div class="min-w-0">
            <h2 class="text-2xl font-bold text-white">Employee Management</h2>

            <p class="text-white mt-1">Create and manage employee accounts.</p>
          </div>

          <!-- RIGHT -->
          <div
            class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3"
          >
            <button
              @click="openDeletedEmployees"
              class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-2 rounded-lg font-medium transition whitespace-nowrap"
            >
              Deleted Employees
            </button>

            <button
              @click="showCreateModal = true"
              class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium transition whitespace-nowrap"
            >
              + Create Employee
            </button>
          </div>
        </div>
      </div>

      <!-- ========================================================= -->
      <!-- SEARCH -->
      <!-- ========================================================= -->
      <div class="neo-card w-full p-6">
        <input
          v-model="search"
          type="text"
          placeholder="Search employee..."
          class="w-full min-w-0 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-black"
        />
      </div>

      <!-- ========================================================= -->
      <!-- EMPLOYEE TABLE -->
      <!-- ========================================================= -->
      <div class="neo-card w-full p-6">
        <div class="table-wrapper">
          <table class="employee-table">
            <thead class="bg-gray-100">
              <tr class="text-left text-black font-semibold">
                <th class="px-2 sm:px-3 py-3 font-bold">Employee Code</th>

                <th class="px-2 sm:px-3 py-3 font-bold">Employee</th>

                <th class="px-2 sm:px-3 py-3 font-bold">Email</th>

                <th class="px-2 sm:px-3 py-3 font-bold">Department</th>

                <th class="px-2 sm:px-3 py-3 font-bold">Position</th>

                <th class="px-2 sm:px-3 py-3 font-bold">Status</th>

                <th class="px-2 sm:px-3 py-3 font-bold text-center">Action</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="employee in filteredEmployees"
                :key="employee.employee_id"
                class="border-t hover:bg-gray-800 transition-colors duration-200"
              >
                <!-- Employee Code -->
                <td
                  class="px-2 sm:px-3 py-4 text-white font-semibold break-words"
                >
                  {{ employee.employee_code }}
                </td>

                <!-- Employee -->
                <td
                  class="px-2 sm:px-3 py-4 text-white font-medium break-words"
                >
                  {{ employee.last_name }},
                  {{ employee.first_name }}
                  {{ employee.middle_name || "" }}
                  {{ employee.extension_name || "" }}
                </td>

                <!-- Email -->
                <td class="px-2 sm:px-3 py-4 text-white break-all">
                  {{ employee.user?.email || "-" }}
                </td>

                <!-- Department -->
                <td class="px-2 sm:px-3 py-4 text-white break-words">
                  {{
                    employee.department?.department_name ||
                    employee.department_name ||
                    "—"
                  }}
                </td>

                <!-- Position -->
                <td class="px-2 sm:px-3 py-4 text-white break-words">
                  {{ employee.position?.name || "-" }}
                </td>

                <!-- Status -->
                <td class="px-2 sm:px-3 py-4 text-white">
                  <span
                    :class="
                      normalizeEmploymentStatus(employee.employment_status) ===
                      'active'
                        ? 'bg-green-300 text-green-700'
                        : 'bg-gray-300 text-gray-700'
                    "
                    class="inline-block px-2 py-1 rounded-full text-xs whitespace-nowrap"
                  >
                    {{ employee.employment_status || "Inactive" }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="px-2 sm:px-3 py-4">
                  <div class="flex flex-wrap items-center justify-center gap-1">
                    <button
                      @click="viewEmployee(employee)"
                      class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded-full text-xs transition-colors duration-200"
                    >
                      View
                    </button>

                    <button
                      @click="editEmployee(employee)"
                      class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded-full text-xs transition-colors duration-200"
                    >
                      Edit
                    </button>

                    <button
                      @click="deleteEmployee(employee)"
                      class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded-full text-xs transition-colors duration-200"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty -->
              <tr v-if="filteredEmployees.length === 0">
                <td colspan="7" class="text-center py-10 text-white">
                  No employees found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- CREATE EMPLOYEE MODAL -->
    <!-- ========================================================= -->
    <div
      v-if="showCreateModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-xl shadow-xl w-full max-w-4xl mx-auto overflow-hidden"
      >
        <!-- Header -->
        <div class="bg-blue-600 text-white px-6 py-4">
          <h3 class="text-xl font-semibold">Create Employee</h3>

          <p class="text-blue-100 text-sm">
            Fill in the employee's official information.
          </p>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
          <!-- ===================================================== -->
          <!-- ACCOUNT INFORMATION -->
          <!-- ===================================================== -->
          <div>
            <h4 class="font-semibold text-gray-700 mb-4">
              Account Information
            </h4>

            <div class="grid grid-cols-1 gap-4">
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Email
                </label>

                <input
                  v-model="form.email"
                  type="email"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800 focus:ring-2 focus:ring-blue-500"
                  placeholder="employee@email.com"
                />
              </div>
            </div>
          </div>

          <!-- ===================================================== -->
          <!-- PERSONAL INFORMATION -->
          <!-- ===================================================== -->
          <div>
            <h4 class="font-semibold text-gray-800 mb-1">
              Personal Information
            </h4>

            <p class="text-sm text-gray-500 mb-4">
              Official basic information should be entered by the Admin.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- First Name -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  First Name
                </label>

                <input
                  v-model="form.first_name"
                  type="text"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- Middle Name -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Middle Name
                </label>

                <input
                  v-model="form.middle_name"
                  type="text"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- Last Name -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Last Name
                </label>

                <input
                  v-model="form.last_name"
                  type="text"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- Extension Name -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Extension Name
                </label>

                <select
                  v-model="form.extension_name"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
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
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Date of Birth
                </label>

                <input
                  v-model="form.date_of_birth"
                  type="date"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- Sex -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Sex
                </label>

                <select
                  v-model="form.sex"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option value="">Select</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
              </div>

              <!-- ================================================= -->
              <!-- EMPLOYEE-OWNED PERSONAL INFORMATION -->
              <!-- ================================================= -->

              <!-- Civil Status -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Civil Status
                  <span class="text-gray-400 font-normal">(Optional)</span>
                </label>

                <select
                  v-model="form.civil_status"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
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
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Nationality
                  <span class="text-gray-400 font-normal">(Optional)</span>
                </label>

                <input
                  v-model="form.nationality"
                  type="text"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                  placeholder="e.g. Filipino"
                />
              </div>

              <!-- Contact Number -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Contact Number
                  <span class="text-gray-400 font-normal">(Optional)</span>
                </label>

                <input
                  v-model="form.contact_number"
                  type="tel"
                  inputmode="numeric"
                  maxlength="11"
                  @input="
                    form.contact_number = form.contact_number
                      .replace(/\D/g, '')
                      .slice(0, 11)
                  "
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                  placeholder="09XXXXXXXXX"
                />
              </div>

              <!-- Personal Email -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Personal Email
                  <span class="text-gray-400 font-normal">(Optional)</span>
                </label>

                <input
                  v-model="form.personal_email"
                  type="email"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                  placeholder="personal@email.com"
                />
              </div>

              <!-- Address -->
              <div class="md:col-span-2">
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Address
                  <span class="text-gray-400 font-normal">(Optional)</span>
                </label>

                <textarea
                  v-model="form.address"
                  rows="3"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800 resize-none"
                  placeholder="Complete residential address"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- ===================================================== -->
          <!-- EMERGENCY CONTACT -->
          <!-- ===================================================== -->
          <div>
            <h4 class="font-semibold text-gray-800 mb-1">Emergency Contact</h4>

            <p class="text-sm text-gray-500 mb-4">
              These details may be completed later by the employee.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Emergency Contact Name -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Emergency Contact Name
                  <span class="text-gray-400 font-normal">(Optional)</span>
                </label>

                <input
                  v-model="form.emergency_contact_name"
                  type="text"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                  placeholder="Full name"
                />
              </div>

              <!-- Emergency Contact Number -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Emergency Contact Number
                  <span class="text-gray-400 font-normal">(Optional)</span>
                </label>

                <input
                  v-model="form.emergency_contact_number"
                  type="tel"
                  inputmode="numeric"
                  maxlength="11"
                  @input="
                    form.emergency_contact_number =
                      form.emergency_contact_number
                        .replace(/\D/g, '')
                        .slice(0, 11)
                  "
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                  placeholder="09XXXXXXXXX"
                />
              </div>
            </div>
          </div>

          <!-- ===================================================== -->
          <!-- EMPLOYMENT INFORMATION -->
          <!-- ===================================================== -->
          <div>
            <h4 class="font-semibold text-gray-800 mb-4">
              Employment Information
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Personnel Type -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Personnel Type
                </label>

                <select
                  v-model="form.personnel_type"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option value="">Select</option>
                  <option value="Teaching">Teaching</option>
                  <option value="Non-Teaching">Non-Teaching</option>
                  <option value="School Head">School Head</option>
                </select>
              </div>

              <!-- Employment Category -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Employment Category
                </label>

                <select
                  v-model="form.employment_category"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option value="">Select</option>
                  <option value="Permanent">Permanent</option>
                  <option value="Probationary">Probationary</option>
                  <option value="Contractual">Contractual</option>
                  <option value="Casual">Casual</option>
                  <option value="Temporary">Temporary</option>
                  <option value="Contract of Service">
                    Contract of Service
                  </option>
                  <option value="Job Order">Job Order</option>
                </select>
              </div>

              <!-- Date Hired -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Date Hired
                </label>

                <input
                  v-model="form.date_hired"
                  type="date"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- Employment Status -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Employment Status
                </label>

                <select
                  v-model="form.employment_status"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>

              <!-- Level -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Level
                </label>

                <select
                  v-model="form.level"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option value="">Select</option>
                  <option value="JHS">JHS</option>
                  <option value="SHS">SHS</option>
                  <option value="Non-Teaching">Non-Teaching</option>
                </select>
              </div>

              <!-- Department -->
              <div v-if="form.level">
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Department
                </label>

                <select
                  v-model="form.department_id"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option :value="null">Select Department</option>

                  <option
                    v-for="dept in filteredDepartmentsForCreate"
                    :key="dept.department_id"
                    :value="dept.department_id"
                  >
                    {{ dept.department_name }}
                  </option>
                </select>
              </div>

              <!-- Position -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Position
                </label>

                <select
                  v-model="form.position_id"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option :value="null">Select</option>

                  <option
                    v-for="pos in positions"
                    :key="pos.id"
                    :value="pos.id"
                  >
                    {{ pos.name }}
                  </option>
                </select>
              </div>

              <!-- Supervisor -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Supervisor
                </label>

                <select
                  v-model="form.supervisor_id"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option :value="null">None</option>

                  <option
                    v-for="employee in employees"
                    :key="employee.employee_id"
                    :value="employee.employee_id"
                  >
                    {{ employee.first_name }}
                    {{ employee.middle_name ? employee.middle_name + " " : "" }}
                    {{ employee.last_name }}
                  </option>
                </select>
              </div>

              <!-- Salary Grade -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Salary Grade
                </label>

                <input
                  :value="selectedCreateSalaryGrade || '-'"
                  type="text"
                  readonly
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800 bg-gray-100"
                />
              </div>

              <!-- Salary Step -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Salary Step
                </label>

                <select
                  v-model="form.salary_step"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option :value="null">Select Step</option>

                  <option v-for="step in 8" :key="step" :value="step">
                    Step {{ step }}
                  </option>
                </select>
              </div>

              <!-- Current Salary -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Current Salary
                </label>

                <input
                  :value="formattedCreateSalary"
                  type="text"
                  readonly
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800 bg-gray-100 font-semibold"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div
          class="bg-gray-100 px-6 py-4 flex flex-col sm:flex-row justify-end gap-3"
        >
          <button
            @click="showCreateModal = false"
            class="bg-red-600 hover:bg-red-700 w-full sm:w-auto px-5 py-2 rounded-lg text-white"
          >
            Cancel
          </button>

          <button
            @click="saveEmployee"
            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg"
          >
            Create Employee
          </button>
        </div>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- EMPLOYEE CREATED CREDENTIALS MODAL -->
    <!-- ========================================================= -->
    <div
      v-if="showCredentialsModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-xl shadow-xl w-full max-w-md mx-auto overflow-hidden"
      >
        <div class="bg-green-600 text-white p-5">
          <h3 class="text-xl font-bold">Employee Created Successfully</h3>
        </div>

        <div class="p-6 space-y-4">
          <p class="text-gray-900 text-sm">
            Give these login credentials to the employee.
          </p>

          <div>
            <label class="text-sm text-black font-semibold"> Email </label>

            <div
              class="bg-gray-100 rounded-lg px-3 py-2 text-black font-bold mt-1 break-all"
            >
              {{ generatedCredentials.email }}
            </div>
          </div>

          <div>
            <label class="text-sm text-black font-semibold">
              Temporary Password
            </label>

            <div
              class="bg-gray-100 rounded-lg px-3 py-2 text-black font-bold mt-1 font-mono break-all"
            >
              {{ generatedCredentials.password }}
            </div>
          </div>

          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
            <p class="text-sm text-yellow-800">
              Keep these credentials safe. The employee will use them to log in.
            </p>
          </div>
        </div>

        <div class="bg-gray-100 p-4 flex justify-end">
          <button
            @click="showCredentialsModal = false"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- VIEW EMPLOYEE MODAL -->
    <!-- ========================================================= -->
    <div
      v-if="showViewModal && selectedEmployee"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-xl shadow-2xl w-full max-w-5xl mx-auto overflow-hidden"
      >
        <!-- Header -->
        <div
          class="bg-blue-600 text-white px-6 py-5 flex justify-between items-center"
        >
          <div class="min-w-0">
            <h2 class="text-2xl font-bold">Employee Profile</h2>

            <p class="text-blue-100 text-sm">
              Employee information and employment details
            </p>
          </div>

          <button
            @click="showViewModal = false"
            class="text-white text-3xl ml-4 flex-shrink-0"
          >
            &times;
          </button>
        </div>

        <!-- Body -->
        <div
          class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8 max-h-[75vh] overflow-y-auto"
        >
          <!-- ===================================================== -->
          <!-- ACCOUNT INFORMATION -->
          <!-- ===================================================== -->
          <div class="space-y-6 min-w-0">
            <div>
              <h3 class="font-semibold text-blue-600 border-b pb-2">
                Account Information
              </h3>

              <div class="mt-4 space-y-3 text-black">
                <div>
                  <span class="font-medium"> Employee Code: </span>

                  <br />

                  {{ selectedEmployee.employee_code }}
                </div>

                <div class="break-all">
                  <span class="font-medium"> Email: </span>

                  <br />

                  {{ selectedEmployee.user?.email || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Role: </span>

                  <br />

                  Employee
                </div>

                <div>
                  <span class="font-medium"> Employment Status: </span>

                  <br />

                  <span
                    :class="
                      normalizeEmploymentStatus(
                        selectedEmployee.employment_status,
                      ) === 'active'
                        ? 'bg-green-100 text-green-700'
                        : 'bg-gray-100 text-gray-700'
                    "
                    class="px-3 py-1 rounded-full text-sm"
                  >
                    {{ selectedEmployee.employment_status || "-" }}
                  </span>
                </div>

                <div>
                  <span class="font-medium"> Date Hired: </span>

                  <br />

                  {{ formatDate(selectedEmployee.date_hired) }}
                </div>
              </div>
            </div>
          </div>

          <!-- ===================================================== -->
          <!-- PERSONAL INFORMATION -->
          <!-- ===================================================== -->
          <div class="space-y-6 min-w-0">
            <div>
              <h3 class="font-semibold text-blue-600 border-b pb-2">
                Personal Information
              </h3>

              <div class="mt-4 space-y-3 text-black">
                <div>
                  <span class="font-medium"> First Name: </span>

                  <br />

                  {{ selectedEmployee.first_name || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Middle Name: </span>

                  <br />

                  {{ selectedEmployee.middle_name || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Last Name: </span>

                  <br />

                  {{ selectedEmployee.last_name || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Extension Name: </span>

                  <br />

                  {{ selectedEmployee.extension_name || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Date of Birth: </span>

                  <br />

                  {{ formatDate(selectedEmployee.date_of_birth) }}
                </div>

                <div>
                  <span class="font-medium"> Sex: </span>

                  <br />

                  {{ selectedEmployee.sex || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Civil Status: </span>

                  <br />

                  {{ selectedEmployee.civil_status || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Nationality: </span>

                  <br />

                  {{ selectedEmployee.nationality || "-" }}
                </div>
              </div>
            </div>
          </div>

          <!-- ===================================================== -->
          <!-- CONTACT INFORMATION -->
          <!-- ===================================================== -->
          <div class="md:col-span-2">
            <h3 class="font-semibold text-blue-600 border-b pb-2">
              Contact Information
            </h3>

            <div
              class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mt-5 text-black"
            >
              <div class="min-w-0">
                <span class="font-medium"> Contact Number </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.contact_number || "-" }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Personal Email </span>

                <p class="mt-1 break-all">
                  {{ selectedEmployee.personal_email || "-" }}
                </p>
              </div>

              <div class="min-w-0 lg:col-span-1">
                <span class="font-medium"> Address </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.address || "-" }}
                </p>
              </div>
            </div>
          </div>

          <!-- ===================================================== -->
          <!-- EMERGENCY CONTACT -->
          <!-- ===================================================== -->
          <div class="md:col-span-2">
            <h3 class="font-semibold text-blue-600 border-b pb-2">
              Emergency Contact
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-5 text-black">
              <div>
                <span class="font-medium"> Emergency Contact Name </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.emergency_contact_name || "-" }}
                </p>
              </div>

              <div>
                <span class="font-medium"> Emergency Contact Number </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.emergency_contact_number || "-" }}
                </p>
              </div>
            </div>
          </div>

          <!-- ===================================================== -->
          <!-- EMPLOYMENT INFORMATION -->
          <!-- ===================================================== -->
          <div class="md:col-span-2">
            <h3 class="font-semibold text-blue-600 border-b pb-2">
              Employment Information
            </h3>

            <div
              class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-5 text-black"
            >
              <div class="min-w-0">
                <span class="font-medium"> Personnel Type </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.personnel_type || "-" }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Employment Category </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.employment_category || "-" }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Department </span>

                <p class="mt-1 break-words">
                  {{
                    selectedEmployee.department?.department_name ||
                    selectedEmployee.department_name ||
                    "-"
                  }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Position </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.position?.name || "-" }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Level </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.level || "-" }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Supervisor </span>

                <p class="mt-1 break-words">
                  {{
                    selectedEmployee.supervisor
                      ? `${selectedEmployee.supervisor.first_name} ${
                          selectedEmployee.supervisor.middle_name
                            ? selectedEmployee.supervisor.middle_name + " "
                            : ""
                        }${selectedEmployee.supervisor.last_name}`
                      : "-"
                  }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Salary Grade </span>

                <p class="mt-1">
                  {{ selectedEmployee.position?.salary_grade || "-" }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Salary Step </span>

                <p class="mt-1">
                  {{
                    selectedEmployee.salary_step
                      ? `Step ${selectedEmployee.salary_step}`
                      : "-"
                  }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Current Salary </span>

                <p class="mt-1">
                  ₱{{
                    Number(selectedEmployee.salary || 0).toLocaleString(
                      "en-PH",
                      {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                      },
                    )
                  }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-100 px-6 py-4 flex justify-end">
          <button
            @click="showViewModal = false"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg"
          >
            Close
          </button>
        </div>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- EDIT EMPLOYEE MODAL -->
    <!-- ========================================================= -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-xl shadow-xl w-full max-w-4xl mx-auto overflow-hidden"
      >
        <!-- Header -->
        <div class="bg-yellow-500 text-white px-6 py-4">
          <h3 class="text-xl font-semibold">Edit Employee</h3>

          <p class="text-yellow-100 text-sm">Update employee information.</p>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
          <!-- ===================================================== -->
          <!-- ACCOUNT -->
          <!-- ===================================================== -->
          <div>
            <h4 class="font-semibold text-gray-700 mb-4">
              Account Information
            </h4>

            <div class="grid grid-cols-1 gap-4">
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Email
                </label>

                <input
                  v-model="editForm.email"
                  type="email"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800 focus:ring-2 focus:ring-yellow-500"
                />
              </div>
            </div>
          </div>

          <!-- ===================================================== -->
          <!-- PERSONAL -->
          <!-- ===================================================== -->
          <div>
            <h4 class="font-semibold text-gray-700 mb-4">
              Personal Information
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- First Name -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  First Name
                </label>

                <input
                  v-model="editForm.first_name"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- Middle Name -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Middle Name
                </label>

                <input
                  v-model="editForm.middle_name"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- Last Name -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Last Name
                </label>

                <input
                  v-model="editForm.last_name"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- Extension -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Extension Name
                </label>

                <select
                  v-model="editForm.extension_name"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
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
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Date of Birth
                </label>

                <input
                  v-model="editForm.date_of_birth"
                  type="date"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- Sex -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Sex
                </label>

                <select
                  v-model="editForm.sex"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option value="">Select</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
              </div>

              <!-- Civil Status -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Civil Status
                </label>

                <select
                  v-model="editForm.civil_status"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
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
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Nationality
                </label>

                <input
                  v-model="editForm.nationality"
                  type="text"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- Contact -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Contact Number
                </label>

                <input
                  v-model="editForm.contact_number"
                  type="tel"
                  inputmode="numeric"
                  maxlength="11"
                  @input="
                    editForm.contact_number = editForm.contact_number
                      .replace(/\D/g, '')
                      .slice(0, 11)
                  "
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- Personal Email -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Personal Email
                </label>

                <input
                  v-model="editForm.personal_email"
                  type="email"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- Address -->
              <div class="md:col-span-2">
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Address
                </label>

                <textarea
                  v-model="editForm.address"
                  rows="3"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800 resize-none"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- ===================================================== -->
          <!-- EMERGENCY -->
          <!-- ===================================================== -->
          <div>
            <h4 class="font-semibold text-gray-700 mb-4">Emergency Contact</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Emergency Contact Name
                </label>

                <input
                  v-model="editForm.emergency_contact_name"
                  type="text"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Emergency Contact Number
                </label>

                <input
                  v-model="editForm.emergency_contact_number"
                  type="tel"
                  inputmode="numeric"
                  maxlength="11"
                  @input="
                    editForm.emergency_contact_number =
                      editForm.emergency_contact_number
                        .replace(/\D/g, '')
                        .slice(0, 11)
                  "
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>
            </div>
          </div>

          <!-- ===================================================== -->
          <!-- EMPLOYMENT -->
          <!-- ===================================================== -->
          <div>
            <h4 class="font-semibold text-gray-700 mb-4">
              Employment Information
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Personnel Type -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Personnel Type
                </label>

                <select
                  v-model="editForm.personnel_type"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option value="">Select</option>
                  <option value="Teaching">Teaching</option>
                  <option value="Non-Teaching">Non-Teaching</option>
                  <option value="School Head">School Head</option>
                </select>
              </div>

              <!-- Employment Category -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Employment Category
                </label>

                <select
                  v-model="editForm.employment_category"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option value="">Select</option>
                  <option value="Permanent">Permanent</option>
                  <option value="Probationary">Probationary</option>
                  <option value="Contractual">Contractual</option>
                  <option value="Casual">Casual</option>
                  <option value="Temporary">Temporary</option>
                  <option value="Contract of Service">
                    Contract of Service
                  </option>
                  <option value="Job Order">Job Order</option>
                </select>
              </div>

              <!-- Date Hired -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Date Hired
                </label>

                <input
                  v-model="editForm.date_hired"
                  type="date"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- Level -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Level
                </label>

                <select
                  v-model="editForm.level"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option value="">Select</option>
                  <option value="JHS">JHS</option>
                  <option value="SHS">SHS</option>
                  <option value="Non-Teaching">Non-Teaching</option>
                </select>
              </div>

              <!-- Department -->
              <div v-if="editForm.level">
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Department
                </label>

                <select
                  v-model="editForm.department_id"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option :value="null">Select Department</option>

                  <option
                    v-for="dept in filteredDepartmentsForEdit"
                    :key="dept.department_id"
                    :value="dept.department_id"
                  >
                    {{ dept.department_name }}
                  </option>
                </select>
              </div>

              <!-- Position -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Position
                </label>

                <select
                  v-model="editForm.position_id"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option :value="null">Select</option>

                  <option
                    v-for="pos in positions"
                    :key="pos.id"
                    :value="pos.id"
                  >
                    {{ pos.name }}
                  </option>
                </select>
              </div>

              <!-- Supervisor -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Supervisor
                </label>

                <select
                  v-model="editForm.supervisor_id"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option :value="null">None</option>

                  <option
                    v-for="employee in employees"
                    :key="employee.employee_id"
                    :value="employee.employee_id"
                  >
                    {{ employee.first_name }}
                    {{ employee.middle_name ? employee.middle_name + " " : "" }}
                    {{ employee.last_name }}
                  </option>
                </select>
              </div>

              <!-- Salary Grade -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Salary Grade
                </label>

                <input
                  :value="selectedSalaryGrade || '-'"
                  type="text"
                  readonly
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800 bg-gray-100"
                />
              </div>

              <!-- Salary Step -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Salary Step
                </label>

                <select
                  v-model="editForm.salary_step"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option :value="null">Select Step</option>

                  <option v-for="step in 8" :key="step" :value="step">
                    Step {{ step }}
                  </option>
                </select>
              </div>

              <!-- Current Salary -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Current Salary
                </label>

                <input
                  :value="formattedSalary"
                  type="text"
                  readonly
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800 bg-gray-100 font-semibold"
                />
              </div>

              <!-- Employment Status -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Employment Status
                </label>

                <select
                  v-model="editForm.employment_status"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option value="active">Active</option>

                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div
          class="bg-gray-100 px-6 py-4 flex flex-col sm:flex-row justify-end gap-3"
        >
          <button
            @click="showEditModal = false"
            class="w-full sm:w-auto px-5 py-2 rounded-lg border text-gray-800 bg-white hover:bg-gray-50"
          >
            Cancel
          </button>

          <button
            @click="updateEmployee"
            class="w-full sm:w-auto bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg"
          >
            Save Changes
          </button>
        </div>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- DELETED EMPLOYEES MODAL -->
    <!-- ========================================================= -->
    <div
      v-if="showDeletedModal"
      class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-xl shadow-2xl w-full max-w-6xl mx-auto overflow-hidden"
      >
        <!-- Header -->
        <div
          class="bg-gray-700 text-white px-6 py-5 flex justify-between items-center"
        >
          <div class="min-w-0">
            <h2 class="text-2xl font-bold">Deleted Employees</h2>

            <p class="text-gray-200 text-sm mt-1">
              View and restore previously deleted employee records.
            </p>
          </div>

          <button
            @click="showDeletedModal = false"
            class="text-white text-3xl hover:text-gray-300 ml-4 flex-shrink-0"
          >
            &times;
          </button>
        </div>

        <!-- Body -->
        <div class="p-6 max-h-[70vh] overflow-y-auto">
          <!-- Empty -->
          <div
            v-if="deletedEmployees.length === 0"
            class="text-center py-12 text-gray-500"
          >
            <p class="text-lg font-semibold">No deleted employees found.</p>

            <p class="text-sm mt-1">
              Deleted employee records will appear here.
            </p>
          </div>

          <!-- Table -->
          <div v-else class="table-wrapper border rounded-lg">
            <table class="deleted-table">
              <thead class="bg-gray-100">
                <tr class="text-left text-black font-semibold">
                  <th class="px-2 sm:px-4 py-3">Employee Code</th>

                  <th class="px-2 sm:px-4 py-3">Employee</th>

                  <th class="px-2 sm:px-4 py-3">Email</th>

                  <th class="px-2 sm:px-4 py-3">Department</th>

                  <th class="px-2 sm:px-4 py-3">Position</th>

                  <th class="px-2 sm:px-4 py-3">Deleted At</th>

                  <th class="px-2 sm:px-4 py-3 text-center">Action</th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="employee in deletedEmployees"
                  :key="employee.employee_id"
                  class="border-t hover:bg-gray-50"
                >
                  <td
                    class="px-2 sm:px-4 py-4 text-gray-800 font-semibold break-words"
                  >
                    {{ employee.employee_code }}
                  </td>

                  <td class="px-2 sm:px-4 py-4 text-gray-800 break-words">
                    {{ employee.last_name }},
                    {{ employee.first_name }}
                    {{ employee.middle_name || "" }}
                    {{ employee.extension_name || "" }}
                  </td>

                  <td class="px-2 sm:px-4 py-4 text-gray-800 break-all">
                    {{ employee.user?.email || "-" }}
                  </td>

                  <td class="px-2 sm:px-3 py-4 text-gray-800 break-words">
                    {{
                      employee.department?.department_name ||
                      employee.department_name ||
                      "—"
                    }}
                  </td>

                  <td class="px-2 sm:px-4 py-4 text-gray-800 break-words">
                    {{ employee.position?.name || "-" }}
                  </td>

                  <td class="px-2 sm:px-4 py-4 text-gray-800 break-words">
                    {{ formatDate(employee.deleted_at) }}
                  </td>

                  <td class="px-2 sm:px-4 py-4 text-center">
                    <div
                      class="flex items-center justify-center gap-1 flex-nowrap"
                    >
                      <button
                        @click="restoreEmployeeRecord(employee.employee_id)"
                        class="bg-green-600 hover:bg-green-700 text-white px-2 py-2 rounded-lg whitespace-nowrap text-xs"
                      >
                        Restore
                      </button>

                      <button
                        @click="permanentlyDeleteEmployeeRecord(employee)"
                        class="bg-red-700 hover:bg-red-800 text-white px-2 py-2 rounded-lg whitespace-nowrap text-xs"
                      >
                        Delete Permanently
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-100 px-6 py-4 flex justify-end">
          <button
            @click="showDeletedModal = false"
            class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from "vue";
import axios from "axios";

import {
  getEmployees,
  createEmployee as createEmployeeAPI,
  updateEmployee as updateEmployeeAPI,
  deleteEmployee as deleteEmployeeAPI,
  getDeletedEmployees,
  restoreEmployee,
  permanentlyDeleteEmployee,
  getPositions,
} from "../services/employee";

/* ========================================================= */
/* INTERFACE */
/* ========================================================= */

interface Employee {
  employee_id: number;
  employee_code: string;
  deleted_at?: string;

  /* ======================================================= */
  /* PERSONAL INFORMATION */
  /* ======================================================= */

  first_name: string;
  middle_name: string;
  last_name: string;
  extension_name?: string | null;
  date_of_birth?: string | null;
  sex: string;
  civil_status?: string | null;
  nationality?: string | null;
  address?: string | null;
  contact_number?: string | null;
  personal_email?: string | null;

  /* ======================================================= */
  /* EMERGENCY CONTACT */
  /* ======================================================= */

  emergency_contact_name?: string | null;
  emergency_contact_number?: string | null;

  /* ======================================================= */
  /* EMPLOYMENT */
  /* ======================================================= */

  personnel_type: string;
  employment_status: string;
  employment_category?: string | null;
  date_hired?: string | null;
  years_of_service?: number | null;

  department_id: number | null;

  department_name?: string;

  department?: {
    department_id: number;
    department_name: string;
    level: string;
  } | null;

  position_id: number | null;

  position?: {
    id: number;
    code: string;
    name: string;
    type: string;
    salary_grade: string | null;
  } | null;

  supervisor_id?: number | null;

  supervisor?: {
    employee_id: number;
    first_name: string;
    middle_name?: string | null;
    last_name: string;
  } | null;

  level: string;
  salary_step: number | null;
  salary: number;

  user?: {
    email: string;
  } | null;

  created_by?: {
    user_id: number;
    email: string;
  } | null;
}

/* ========================================================= */
/* STATE */
/* ========================================================= */

const employees = ref<Employee[]>([]);

const deletedEmployees = ref<Employee[]>([]);

const showDeletedModal = ref(false);

const search = ref("");

const showCreateModal = ref(false);

const showViewModal = ref(false);

const showEditModal = ref(false);

const showCredentialsModal = ref(false);

const selectedEmployee = ref<Employee | null>(null);

/* ========================================================= */
/* POSITIONS */
/* ========================================================= */

const positions = ref<
  {
    id: number;
    name: string;
    type: string;
    salary_grade: string | null;
  }[]
>([]);

/* ========================================================= */
/* DEPARTMENTS */
/* ========================================================= */

const departments = ref<
  {
    department_id: number;
    department_name: string;
    level: string;
  }[]
>([]);

/* ========================================================= */
/* FILTERED DEPARTMENTS - CREATE */
/* ========================================================= */

const filteredDepartmentsForCreate = computed(() => {
  return (departments.value ?? []).filter(
    (department) => department.level === form.value.level,
  );
});

/* ========================================================= */
/* FILTERED DEPARTMENTS - EDIT */
/* ========================================================= */

const filteredDepartmentsForEdit = computed(() => {
  return (departments.value ?? []).filter(
    (department) => department.level === editForm.value.level,
  );
});

/* ========================================================= */
/* LOAD POSITIONS */
/* ========================================================= */

const loadPositions = async () => {
  try {
    positions.value = await getPositions();
  } catch (error) {
    console.error("Failed to load positions:", error);
  }
};

/* ========================================================= */
/* LOAD DEPARTMENTS */
/* ========================================================= */

const loadDepartments = async () => {
  try {
    const response = await axios.get(
      "https://enhs-leave-management-system.onrender.com/api/departments",
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      },
    );

    departments.value = Array.isArray(response.data)
      ? response.data
      : (response.data?.data ?? []);

    console.log("Departments:", departments.value);
  } catch (error) {
    console.error("Failed to load departments:", error);
  }
};

/* ========================================================= */
/* CREATE FORM */
/* ========================================================= */

const form = ref({
  /* Account */
  email: "",

  /* Personal */
  first_name: "",
  middle_name: "",
  last_name: "",
  extension_name: "",
  date_of_birth: "",
  sex: "",
  civil_status: "",
  nationality: "",
  address: "",
  contact_number: "",
  personal_email: "",

  /* Emergency */
  emergency_contact_name: "",
  emergency_contact_number: "",

  /* Employment */
  personnel_type: "",
  employment_status: "active",
  employment_category: "",
  date_hired: "",

  department_id: null as number | null,
  level: "",
  position_id: null as number | null,
  supervisor_id: null as number | null,

  salary_step: null as number | null,
  salary: "",
});

/* ========================================================= */
/* POSITION -> LEVEL */
/* ========================================================= */

const watchPositionForLevel = (
  newId: number | null,
  formRef: { value: { level: string } },
) => {
  const selected = positions.value.find((position) => position.id === newId);

  if (selected?.type === "Non-Teaching") {
    formRef.value.level = "Non-Teaching";
  }
};

/* ========================================================= */
/* CREATE SALARY GRADE */
/* ========================================================= */

const selectedCreateSalaryGrade = computed(() => {
  const selected = positions.value.find(
    (position) => position.id === form.value.position_id,
  );

  return selected?.salary_grade || null;
});

/* ========================================================= */
/* CREATE SALARY CALCULATION */
/* ========================================================= */

const calculateSalary = async () => {
  if (!form.value.position_id || !form.value.salary_step) {
    form.value.salary = "";
    return;
  }

  try {
    const response = await axios.get(
      "https://enhs-leave-management-system.onrender.com/api/salary-info",
      {
        params: {
          position_id: form.value.position_id,
          salary_step: form.value.salary_step,
        },

        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      },
    );

    form.value.salary = response.data.salary ?? "";
  } catch (error) {
    console.error("Failed to calculate salary:", error);

    form.value.salary = "";
  }
};

/* ========================================================= */
/* WATCH CREATE SALARY */
/* ========================================================= */

watch([() => form.value.position_id, () => form.value.salary_step], () => {
  calculateSalary();
});

/* ========================================================= */
/* CREATE FORMATTED SALARY */
/* ========================================================= */

const formattedCreateSalary = computed(() => {
  if (
    form.value.salary === "" ||
    form.value.salary === null ||
    form.value.salary === undefined
  ) {
    return "-";
  }

  return `₱${Number(form.value.salary).toLocaleString("en-PH", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
});

/* ========================================================= */
/* AUTO CATEGORY - CREATE */
/* ========================================================= */

watch(
  () => form.value.position_id,
  (newId) => {
    watchPositionForLevel(newId, form);
  },
);

/* ========================================================= */
/* RESET DEPARTMENT WHEN CREATE LEVEL CHANGES */
/* ========================================================= */

watch(
  () => form.value.level,
  () => {
    form.value.department_id = null;
  },
);

/* ========================================================= */
/* GENERATED CREDENTIALS */
/* ========================================================= */

const generatedCredentials = ref({
  email: "",
  password: "",
});

/* ========================================================= */
/* EDIT FORM */
/* ========================================================= */

const editForm = ref({
  employee_id: 0,

  /* Account */
  email: "",

  /* Personal */
  first_name: "",
  middle_name: "",
  last_name: "",
  extension_name: "",
  date_of_birth: "",
  sex: "",
  civil_status: "",
  nationality: "",
  address: "",
  contact_number: "",
  personal_email: "",

  /* Emergency */
  emergency_contact_name: "",
  emergency_contact_number: "",

  /* Employment */
  personnel_type: "",
  employment_status: "active",
  employment_category: "",
  date_hired: "",

  department_id: null as number | null,
  level: "",
  position_id: null as number | null,
  supervisor_id: null as number | null,

  salary_step: null as number | null,
  salary: "",
});

/* ========================================================= */
/* EDIT SALARY GRADE */
/* ========================================================= */

const selectedSalaryGrade = computed(() => {
  const selected = positions.value.find(
    (position) => position.id === editForm.value.position_id,
  );

  return selected?.salary_grade || null;
});

/* ========================================================= */
/* EDIT FORMATTED SALARY */
/* ========================================================= */

const formattedSalary = computed(() => {
  if (
    editForm.value.salary === "" ||
    editForm.value.salary === null ||
    editForm.value.salary === undefined
  ) {
    return "-";
  }

  return `₱${Number(editForm.value.salary).toLocaleString("en-PH", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
});

/* ========================================================= */
/* EDIT SALARY CALCULATION */
/* ========================================================= */

const calculateEditSalary = async () => {
  if (!editForm.value.position_id || !editForm.value.salary_step) {
    editForm.value.salary = "";
    return;
  }

  try {
    const response = await axios.get(
      "https://enhs-leave-management-system.onrender.com/api/salary-info",
      {
        params: {
          position_id: editForm.value.position_id,
          salary_step: editForm.value.salary_step,
        },

        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      },
    );

    editForm.value.salary = response.data.salary ?? "";
  } catch (error) {
    console.error("Failed to calculate edit salary:", error);

    editForm.value.salary = "";
  }
};

/* ========================================================= */
/* WATCH EDIT SALARY */
/* ========================================================= */

watch(
  [() => editForm.value.position_id, () => editForm.value.salary_step],
  () => {
    calculateEditSalary();
  },
);

/* ========================================================= */
/* AUTO CATEGORY - EDIT */
/* ========================================================= */

watch(
  () => editForm.value.position_id,
  (newId) => {
    watchPositionForLevel(newId, editForm);
  },
);

/* ========================================================= */
/* RESET DEPARTMENT WHEN EDIT LEVEL CHANGES */
/* ========================================================= */

watch(
  () => editForm.value.level,
  () => {
    editForm.value.department_id = null;
  },
);

/* ========================================================= */
/* FORMAT DATE */
/* ========================================================= */

const formatDate = (date?: string | null) => {
  if (!date) {
    return "-";
  }

  const parsedDate = new Date(date);

  if (Number.isNaN(parsedDate.getTime())) {
    return "-";
  }

  return parsedDate.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

/* ========================================================= */
/* NORMALIZE EMPLOYMENT STATUS */
/* ========================================================= */

const normalizeEmploymentStatus = (status: string | undefined) => {
  return String(status || "")
    .trim()
    .toLowerCase();
};

/* ========================================================= */
/* VIEW EMPLOYEE */
/* ========================================================= */

const viewEmployee = (employee: Employee) => {
  selectedEmployee.value = employee;

  showViewModal.value = true;
};

/* ========================================================= */
/* SEARCH / FILTER */
/* ========================================================= */

const filteredEmployees = computed(() => {
  const keyword = search.value.toLowerCase().trim();

  if (!keyword) {
    return employees.value;
  }

  return employees.value.filter((employee) => {
    const firstName = employee.first_name?.toLowerCase() || "";

    const lastName = employee.last_name?.toLowerCase() || "";

    const middleName = employee.middle_name?.toLowerCase() || "";

    const extensionName = employee.extension_name?.toLowerCase() || "";

    const employeeCode = employee.employee_code?.toLowerCase() || "";

    const email = employee.user?.email?.toLowerCase() || "";

    const department = (
      employee.department?.department_name ||
      employee.department_name ||
      ""
    ).toLowerCase();

    const position = employee.position?.name?.toLowerCase() || "";

    const level = employee.level?.toLowerCase() || "";

    const personnelType = employee.personnel_type?.toLowerCase() || "";

    return (
      firstName.includes(keyword) ||
      middleName.includes(keyword) ||
      lastName.includes(keyword) ||
      extensionName.includes(keyword) ||
      employeeCode.includes(keyword) ||
      email.includes(keyword) ||
      department.includes(keyword) ||
      position.includes(keyword) ||
      level.includes(keyword) ||
      personnelType.includes(keyword)
    );
  });
});

/* ========================================================= */
/* EDIT EMPLOYEE */
/* ========================================================= */

const editEmployee = (employee: Employee) => {
  editForm.value = {
    employee_id: Number(employee.employee_id),

    /* Account */
    email: employee.user?.email || "",

    /* Personal */
    first_name: employee.first_name || "",

    middle_name: employee.middle_name || "",

    last_name: employee.last_name || "",

    extension_name: employee.extension_name || "",

    date_of_birth: employee.date_of_birth
      ? employee.date_of_birth.substring(0, 10)
      : "",

    sex: employee.sex || "",

    civil_status: employee.civil_status || "",

    nationality: employee.nationality || "",

    address: employee.address || "",

    contact_number: employee.contact_number || "",

    personal_email: employee.personal_email || "",

    /* Emergency */
    emergency_contact_name: employee.emergency_contact_name || "",

    emergency_contact_number: employee.emergency_contact_number || "",

    /* Employment */
    personnel_type: employee.personnel_type || "",

    employment_status: employee.employment_status || "active",

    employment_category: employee.employment_category || "",

    date_hired: employee.date_hired ? employee.date_hired.substring(0, 10) : "",

    department_id: employee.department_id || null,

    level: employee.level || "",

    position_id: employee.position_id || null,

    supervisor_id: employee.supervisor_id || null,

    salary_step: employee.salary_step || null,

    salary: employee.salary != null ? String(employee.salary) : "",
  };

  showEditModal.value = true;
};

/* ========================================================= */
/* UPDATE EMPLOYEE */
/* ========================================================= */

const updateEmployee = async () => {
  if (!editForm.value.employee_id) {
    alert("Invalid employee.");
    return;
  }

  try {
    await updateEmployeeAPI(editForm.value.employee_id, editForm.value);

    alert("Employee updated successfully!");

    showEditModal.value = false;

    await loadEmployees();
  } catch (error) {
    console.error("Failed to update employee:", error);

    alert("Unable to update employee.");
  }
};

/* ========================================================= */
/* DELETE EMPLOYEE */
/* ========================================================= */

const deleteEmployee = async (employee: Employee) => {
  const confirmed = confirm(
    `Are you sure you want to delete ${employee.first_name} ${employee.last_name}?`,
  );

  if (!confirmed) {
    return;
  }

  try {
    await deleteEmployeeAPI(employee.employee_id);

    alert("Employee deleted successfully.");

    await loadEmployees();
  } catch (error) {
    console.error("Failed to delete employee:", error);

    alert("Unable to delete employee.");
  }
};

/* ========================================================= */
/* LOAD EMPLOYEES */
/* ========================================================= */

const loadEmployees = async () => {
  try {
    const result = await getEmployees();

    employees.value = Array.isArray(result) ? result : [];

    console.log("Employees:", employees.value);
  } catch (error) {
    console.error("Failed to load employees:", error);
  }
};

/* ========================================================= */
/* LOAD DELETED EMPLOYEES */
/* ========================================================= */

const loadDeletedEmployees = async () => {
  try {
    const result = await getDeletedEmployees();

    deletedEmployees.value = Array.isArray(result) ? result : [];

    console.log("Deleted Employees:", deletedEmployees.value);
  } catch (error) {
    console.error("Failed to load deleted employees:", error);

    alert("Unable to load deleted employees.");
  }
};

/* ========================================================= */
/* OPEN DELETED EMPLOYEES */
/* ========================================================= */

const openDeletedEmployees = async () => {
  showDeletedModal.value = true;

  await loadDeletedEmployees();
};

/* ========================================================= */
/* RESTORE EMPLOYEE */
/* ========================================================= */

const restoreEmployeeRecord = async (id: number) => {
  if (!confirm("Are you sure you want to restore this employee?")) {
    return;
  }

  try {
    await restoreEmployee(id);

    alert("Employee restored successfully.");

    await Promise.all([loadEmployees(), loadDeletedEmployees()]);
  } catch (error) {
    console.error("Failed to restore employee:", error);

    alert("Unable to restore employee.");
  }
};

const permanentlyDeleteEmployeeRecord = async (employee: Employee) => {
  const confirmed = confirm(
    `Are you sure you want to PERMANENTLY delete ${employee.first_name} ${employee.last_name}? This action cannot be undone and will remove all related records.`,
  );

  if (!confirmed) {
    return;
  }

  try {
    await permanentlyDeleteEmployee(employee.employee_id);

    alert("Employee permanently deleted.");

    await loadDeletedEmployees();
  } catch (error) {
    console.error("Failed to permanently delete employee:", error);

    alert("Unable to permanently delete employee.");
  }
};

/* ========================================================= */
/* RESET CREATE FORM */
/* ========================================================= */

const resetCreateForm = () => {
  form.value = {
    /* Account */
    email: "",

    /* Personal */
    first_name: "",
    middle_name: "",
    last_name: "",
    extension_name: "",
    date_of_birth: "",
    sex: "",
    civil_status: "",
    nationality: "",
    address: "",
    contact_number: "",
    personal_email: "",

    /* Emergency */
    emergency_contact_name: "",
    emergency_contact_number: "",

    /* Employment */
    personnel_type: "",
    employment_status: "active",
    employment_category: "",
    date_hired: "",

    department_id: null,
    level: "",
    position_id: null,
    supervisor_id: null,

    salary_step: null,
    salary: "",
  };
};

/* ========================================================= */
/* CREATE EMPLOYEE */
/* ========================================================= */

const saveEmployee = async () => {
  try {
    const response = await createEmployeeAPI(form.value);

    generatedCredentials.value = {
      email: response?.email || form.value.email,

      password: response?.password || "",
    };

    showCreateModal.value = false;

    showCredentialsModal.value = true;

    resetCreateForm();

    await loadEmployees();
  } catch (error: any) {
    console.error("Failed to create employee:", error);

    const message =
      error?.response?.data?.message || "Unable to create employee.";

    alert(message);
  }
};

/* ========================================================= */
/* INITIAL LOAD */
/* ========================================================= */

onMounted(async () => {
  await Promise.all([loadEmployees(), loadPositions(), loadDepartments()]);
});
</script>

<style scoped>
.dashboard-shell {
  background: #080d14;
  min-height: 100vh;
  width: 100%;
  max-width: none;
  box-sizing: border-box;
}

.neo-card {
  background: #111d2e;
  border: 1px solid #1e293b;
  border-radius: 1.4rem;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;

  width: 100%;
  max-width: none;
  min-width: 0;
  box-sizing: border-box;
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

/* ========================================================= */
/* TABLE WRAPPER                                              */
/* ========================================================= */

.table-wrapper {
  width: 100%;
  max-width: 100%;
}

/* ========================================================= */
/* EMPLOYEE TABLE - DESKTOP DESIGN STAYS THE SAME            */
/* ========================================================= */

.employee-table {
  width: 100%;
  max-width: 100%;
  table-layout: auto;
  border-collapse: collapse;
}

.employee-table th,
.employee-table td {
  white-space: normal;
  word-break: normal;
  overflow-wrap: break-word;
}

/* Desktop column widths */
.employee-table th:nth-child(1),
.employee-table td:nth-child(1) {
  width: 11%;
}

.employee-table th:nth-child(2),
.employee-table td:nth-child(2) {
  width: 17%;
}

.employee-table th:nth-child(3),
.employee-table td:nth-child(3) {
  width: 19%;
}

.employee-table th:nth-child(4),
.employee-table td:nth-child(4) {
  width: 14%;
}

.employee-table th:nth-child(5),
.employee-table td:nth-child(5) {
  width: 14%;
}

.employee-table th:nth-child(6),
.employee-table td:nth-child(6) {
  width: 10%;
}

.employee-table th:nth-child(7),
.employee-table td:nth-child(7) {
  width: 15%;
}

/* ========================================================= */
/* DELETED EMPLOYEES TABLE                                   */
/* ========================================================= */

.deleted-table {
  width: 100%;
  max-width: 100%;
  table-layout: auto;
  border-collapse: collapse;
}

.deleted-table th,
.deleted-table td {
  white-space: normal;
  word-break: normal;
  overflow-wrap: break-word;
}

.deleted-table th:nth-child(1),
.deleted-table td:nth-child(1) {
  width: 12%;
}

.deleted-table th:nth-child(2),
.deleted-table td:nth-child(2) {
  width: 17%;
}

.deleted-table th:nth-child(3),
.deleted-table td:nth-child(3) {
  width: 19%;
}

.deleted-table th:nth-child(4),
.deleted-table td:nth-child(4) {
  width: 13%;
}

.deleted-table th:nth-child(5),
.deleted-table td:nth-child(5) {
  width: 13%;
}

.deleted-table th:nth-child(6),
.deleted-table td:nth-child(6) {
  width: 13%;
}

.deleted-table th:nth-child(7),
.deleted-table td:nth-child(7) {
  width: 18%;
}

/* ========================================================= */
/* FLEX / GRID CHILDREN                                      */
/* ========================================================= */

.dashboard-shell *,
.neo-card * {
  min-width: 0;
}

/* ========================================================= */
/* TABLET                                                    */
/* ========================================================= */

@media (max-width: 1024px) {
  .employee-table th,
  .employee-table td {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
  }

  .employee-table {
    font-size: 0.875rem;
  }

  .employee-table button {
    font-size: 0.7rem;
    padding-left: 0.45rem;
    padding-right: 0.45rem;
  }
}

/* ========================================================= */
/* MOBILE - HORIZONTAL TABLE SCROLL                          */
/* ========================================================= */

@media (max-width: 768px) {
  .dashboard-shell {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }

  .neo-card {
    border-radius: 1rem;
  }

  /* Keep the card inside the phone screen while letting only
     the table itself scroll sideways. */
  .table-wrapper {
    display: block;
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    overscroll-behavior-x: contain;
  }

  /* Do NOT squeeze seven columns into the phone width. */
  .employee-table {
    width: 900px;
    min-width: 900px;
    max-width: none;
    table-layout: auto;
    font-size: 0.875rem;
  }

  .deleted-table {
    width: 980px;
    min-width: 980px;
    max-width: none;
    table-layout: auto;
    font-size: 0.875rem;
  }

  /* Prevent the letter-by-letter wrapping shown in the screenshot. */
  .employee-table th,
  .employee-table td,
  .deleted-table th,
  .deleted-table td {
    white-space: nowrap;
    word-break: normal;
    overflow-wrap: normal;
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }

  /* Name can use two lines naturally when needed. */
  .employee-table td:nth-child(2),
  .deleted-table td:nth-child(2) {
    white-space: normal;
    min-width: 170px;
  }

  /* Email can wrap at sensible points instead of crushing the column. */
  .employee-table td:nth-child(3),
  .deleted-table td:nth-child(3) {
    white-space: normal;
    overflow-wrap: anywhere;
    min-width: 210px;
  }

  .employee-table th:nth-child(1),
  .employee-table td:nth-child(1) {
    min-width: 120px;
  }

  .employee-table th:nth-child(4),
  .employee-table td:nth-child(4) {
    min-width: 140px;
  }

  .employee-table th:nth-child(5),
  .employee-table td:nth-child(5) {
    min-width: 140px;
  }

  .employee-table th:nth-child(6),
  .employee-table td:nth-child(6) {
    min-width: 100px;
  }

  .employee-table th:nth-child(7),
  .employee-table td:nth-child(7) {
    min-width: 180px;
  }

  .employee-table td:last-child > div {
    flex-wrap: nowrap;
  }

  .employee-table button,
  .deleted-table button {
    font-size: 0.75rem;
    padding: 0.4rem 0.65rem;
    white-space: nowrap;
  }
}

/* ========================================================= */
/* SMALL PHONES                                              */
/* ========================================================= */

@media (max-width: 480px) {
  .dashboard-shell {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
  }

  .neo-card {
    border-radius: 0.9rem;
  }

  .employee-table {
    width: 880px;
    min-width: 880px;
  }

  .deleted-table {
    width: 950px;
    min-width: 950px;
  }
}
</style>
