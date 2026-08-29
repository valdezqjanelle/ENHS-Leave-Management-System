<template>
  <div class="w-full min-h-screen">
    <div
      class="dashboard-shell w-full max-w-none mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6"
    >
      <!-- ================= HEADER ================= -->
      <div class="neo-card w-full p-6">
        <div
          class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4"
        >
          <!-- LEFT: TITLE -->
          <div class="min-w-0">
            <h2 class="text-2xl font-bold text-white">Employee Management</h2>

            <p class="text-white mt-1">Create and manage employee accounts.</p>
          </div>

          <!-- RIGHT: BUTTONS -->
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

      <!-- ================= SEARCH ================= -->
      <div class="neo-card w-full p-6">
        <input
          v-model="search"
          type="text"
          placeholder="Search employee..."
          class="w-full min-w-0 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-black"
        />
      </div>

      <!-- ================= EMPLOYEE TABLE ================= -->
      <div class="neo-card w-full p-6">
        <div class="table-wrapper">
          <table class="employee-table">
            <thead class="bg-gray-100">
              <tr class="text-left text-black font-semibold">
                <th class="px-2 sm:px-3 py-3 font-bold">Employee Code</th>

                <th class="px-2 sm:px-3 py-3 font-bold">Employee</th>

                <th class="px-2 sm:px-3 py-3 font-bold">Email</th>

                <th class="px-2 sm:px-3 py-3 font-bold">Department</th>

                <th class="px-2 sm:px-3 py-3 font-extrabold">Position</th>

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
                <td
                  class="px-2 sm:px-3 py-4 text-white font-semibold break-words"
                >
                  {{ employee.employee_code }}
                </td>

                <td
                  class="px-2 sm:px-3 py-4 text-white font-medium break-words"
                >
                  {{ employee.last_name }},
                  {{ employee.first_name }}
                  {{ employee.middle_name }}
                </td>

                <td class="px-2 sm:px-3 py-4 text-white break-all">
                  {{ employee.user.email }}
                </td>
                <td class="px-2 sm:px-3 py-4 text-white break-words">
                  {{
                    employee.department?.department_name ||
                    employee.department_name ||
                    "—"
                  }}
                </td>

                <td class="px-2 sm:px-3 py-4 text-white break-words">
                  {{ employee.position?.name || "-" }}
                </td>

                <td class="px-2 sm:px-3 py-4 text-white">
                  <span
                    class="inline-block bg-green-300 text-green-700 px-2 py-1 rounded-full text-xs whitespace-nowrap"
                  >
                    {{ employee.employment_status }}
                  </span>
                </td>

                <!-- ================= ACTION BUTTONS ================= -->
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

              <!-- ================= EMPTY STATE ================= -->
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

          <p class="text-blue-100 text-sm">Fill in the employee information.</p>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
          <!-- Account Information -->
          <div>
            <h4 class="font-semibold text-gray-700 mb-4">
              Account Information
            </h4>

            <div class="grid grid-cols-1">
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

          <!-- Personal Information -->
          <div>
            <h4 class="font-semibold text-gray-800 mb-4">
              Personal Information
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  First Name
                </label>

                <input
                  v-model="form.first_name"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Middle Name
                </label>

                <input
                  v-model="form.middle_name"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Last Name
                </label>

                <input
                  v-model="form.last_name"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

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

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Contact Number
                </label>

                <input
                  v-model="form.contact_number"
                  type="tel"
                  inputmode="numeric"
                  maxlength="11"
                  pattern="[0-9]{11}"
                  @input="
                    form.contact_number = form.contact_number
                      .replace(/\D/g, '')
                      .slice(0, 11)
                  "
                  class="text-black w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                  placeholder="Enter 11-digit contact number"
                />
              </div>
            </div>
          </div>

          <!-- Employment Information -->
          <div>
            <h4 class="font-semibold text-gray-800 mb-4">
              Employment Information
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium"
                  >Level</label
                >
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

              <div v-if="form.level">
                <label class="block mb-2 text-sm text-gray-800 font-medium"
                  >Department</label
                >
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

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium"
                  >Position</label
                >
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

              <!-- Salary Grade -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium"
                  >Salary Grade</label
                >
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
            class="bg-red-600 hover:bg-red-700 w-full sm:w-auto px-5 py-2 rounded-lg border text-black"
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
          <!-- LEFT -->
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

                  {{ selectedEmployee.user.email }}
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
                    class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm"
                  >
                    {{ selectedEmployee.employment_status }}
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

          <!-- RIGHT -->
          <div class="space-y-6 min-w-0">
            <div>
              <h3 class="font-semibold text-blue-600 border-b pb-2">
                Personal Information
              </h3>

              <div class="mt-4 space-y-3 text-black">
                <div>
                  <span class="font-medium"> First Name: </span>

                  <br />

                  {{ selectedEmployee.first_name }}
                </div>

                <div>
                  <span class="font-medium"> Middle Name: </span>

                  <br />

                  {{ selectedEmployee.middle_name || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Last Name: </span>

                  <br />

                  {{ selectedEmployee.last_name }}
                </div>

                <div>
                  <span class="font-medium"> Sex: </span>

                  <br />

                  {{ selectedEmployee.sex }}
                </div>

                <div>
                  <span class="font-medium"> Contact Number: </span>

                  <br />

                  {{ selectedEmployee.contact_number || "-" }}
                </div>
              </div>
            </div>
          </div>

          <!-- Employment -->
          <div class="md:col-span-2">
            <h3 class="font-semibold text-blue-600 border-b pb-2">
              Employment Information
            </h3>

            <div
              class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-5 text-black"
            >
              <div class="min-w-0">
                <span class="font-medium"> Department </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.department_name }}
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
                  {{ selectedEmployee.level }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Salary </span>

                <p class="mt-1">
                  ₱{{ Number(selectedEmployee.salary || 0).toLocaleString() }}
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
          <!-- Account -->
          <div>
            <h4 class="font-semibold text-gray-700 mb-4">
              Account Information
            </h4>

            <div class="grid grid-cols-1">
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

          <!-- Personal -->
          <div>
            <h4 class="font-semibold text-gray-700 mb-4">
              Personal Information
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  First Name
                </label>

                <input
                  v-model="editForm.first_name"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Middle Name
                </label>

                <input
                  v-model="editForm.middle_name"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Last Name
                </label>

                <input
                  v-model="editForm.last_name"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Sex
                </label>

                <select
                  v-model="editForm.sex"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option value="Male">Male</option>

                  <option value="Female">Female</option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Contact Number
                </label>

                <input
                  v-model="editForm.contact_number"
                  class="w-full min-w-0 border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>
            </div>
          </div>

          <!-- Employment -->
          <div>
            <h4 class="font-semibold text-gray-700 mb-4">
              Employment Information
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium"
                  >Level</label
                >
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

              <div v-if="editForm.level">
                <label class="block mb-2 text-sm text-gray-800 font-medium"
                  >Department</label
                >
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

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium"
                  >Position</label
                >
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
            </div>

            <!-- Salary Grade -->
            <div>
              <label class="block mb-2 text-sm text-gray-800 font-medium"
                >Salary Grade</label
              >
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
          class="w-full sm:w-auto px-5 py-2 rounded-lg border"
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
        <!-- Empty State -->
        <div
          v-if="deletedEmployees.length === 0"
          class="text-center py-12 text-gray-500"
        >
          <p class="text-lg font-semibold">No deleted employees found.</p>

          <p class="text-sm mt-1">Deleted employee records will appear here.</p>
        </div>

        <!-- Deleted Employees Table -->
        <div v-else class="border rounded-lg overflow-hidden">
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
                  {{ employee.middle_name }}
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
                  <button
                    @click="restoreEmployeeRecord(employee.employee_id)"
                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg whitespace-nowrap"
                  >
                    Restore
                  </button>
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
  getPositions,
} from "../services/employee";

/* ========================================================= */
/* INTERFACE */
/* ========================================================= */

interface Employee {
  employee_id: number;
  employee_code: string;

  deleted_at?: string;

  first_name: string;
  middle_name: string;
  last_name: string;

  department_id: number | null;
  department_name?: string;

  department?: {
    department_id: number;
    department_name: string;
    level: string;
  } | null;

  position_id: number;

  position?: {
    id: number;
    code: string;
    name: string;
    type: string;
    salary_grade: string | null;
  } | null;

  level: string;
  salary_step: number | null;
  salary: number;

  contact_number: string;
  sex: string;

  employment_status: string;
  date_hired?: string;

  user: {
    email: string;
  };

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

const positions = ref<
  {
    id: number;
    name: string;
    type: string;
    salary_grade: string | null;
  }[]
>([]);

const departments = ref<
  {
    department_id: number;
    department_name: string;
    level: string;
  }[]
>([]);

const filteredDepartmentsForCreate = computed(() =>
  (departments.value ?? []).filter((d) => d.level === form.value.level),
);

const filteredDepartmentsForEdit = computed(() =>
  (departments.value ?? []).filter((d) => d.level === editForm.value.level),
);
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

    departments.value = response.data;

    console.log("Departments:", departments.value);
  } catch (error) {
    console.error("Failed to load departments:", error);
  }
};
/* ========================================================= */
/* CATEGORY MAPPING */
/* ========================================================= */

const watchPositionForLevel = (
  newId: number | null,
  formRef: { value: { level: string } },
) => {
  const selected = positions.value.find((p) => p.id === newId);
  if (selected?.type === "Non-Teaching") {
    formRef.value.level = "Non-Teaching";
  }
};

/* ========================================================= */
/* CREATE FORM */
/* ========================================================= */

const form = ref({
  email: "",

  first_name: "",
  middle_name: "",
  last_name: "",

  sex: "",

  department_id: null as number | null,

  level: "",

  position_id: null as number | null,

  salary_step: null as number | null,

  salary: "",

  contact_number: "",
});

/* ========================================================= */
/* CREATE SALARY GRADE */
/* ========================================================= */

const selectedCreateSalaryGrade = computed(() => {
  const selected = positions.value.find((p) => p.id === form.value.position_id);

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

watch([() => form.value.position_id, () => form.value.salary_step], () => {
  calculateSalary();
});

/* ========================================================= */
/* CREATE FORMATTED SALARY */
/* ========================================================= */

const formattedCreateSalary = computed(() => {
  if (!form.value.salary) {
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
  (newId) => watchPositionForLevel(newId, form),
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

  email: "",

  first_name: "",
  middle_name: "",
  last_name: "",

  sex: "",

  department_id: null as number | null,

  level: "",

  position_id: null as number | null,

  salary_step: null as number | null,

  salary: "",

  contact_number: "",

  employment_status: "",
});

/* ========================================================= */
/* EDIT SALARY GRADE */
/* ========================================================= */

const selectedSalaryGrade = computed(() => {
  const selected = positions.value.find(
    (p) => p.id === editForm.value.position_id,
  );

  return selected?.salary_grade || null;
});

/* ========================================================= */
/* EDIT FORMATTED SALARY */
/* ========================================================= */

const formattedSalary = computed(() => {
  if (!editForm.value.salary) {
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
  (newId) => watchPositionForLevel(newId, editForm),
);

watch(
  () => form.value.level,
  () => {
    form.value.department_id = null;
  },
);
watch(
  () => editForm.value.level,
  () => {
    editForm.value.department_id = null;
  },
);

/* ========================================================= */
/* FORMAT DATE */
/* ========================================================= */

const formatDate = (date?: string) => {
  if (!date) {
    return "-";
  }

  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

/* ========================================================= */
/* STATUS */
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

    const employeeCode = employee.employee_code?.toLowerCase() || "";

    const email = employee.user?.email?.toLowerCase() || "";

    const department = employee.department_name?.toLowerCase() || "";

    const position = employee.position?.name?.toLowerCase() || "";

    const level = employee.level?.toLowerCase() || "";

    return (
      firstName.includes(keyword) ||
      middleName.includes(keyword) ||
      lastName.includes(keyword) ||
      employeeCode.includes(keyword) ||
      email.includes(keyword) ||
      department.includes(keyword) ||
      position.includes(keyword) ||
      level.includes(keyword)
    );
  });
});

/* ========================================================= */
/* EDIT EMPLOYEE */
/* ========================================================= */

const editEmployee = (employee: Employee) => {
  editForm.value = {
    employee_id: Number(employee.employee_id),

    email: employee.user?.email || "",

    first_name: employee.first_name || "",

    middle_name: employee.middle_name || "",

    last_name: employee.last_name || "",

    sex: employee.sex || "",

    department_id: employee.department_id || null,

    level: employee.level || "",

    position_id: employee.position_id || null,

    salary_step: employee.salary_step || null,

    salary: employee.salary != null ? String(employee.salary) : "",

    contact_number: employee.contact_number || "",

    employment_status: employee.employment_status || "",
  };

  showEditModal.value = true;
};

/* ========================================================= */
/* UPDATE EMPLOYEE */
/* ========================================================= */

const updateEmployee = async () => {
  try {
    await updateEmployeeAPI(editForm.value.employee_id, editForm.value);

    alert("Employee updated successfully!");

    showEditModal.value = false;

    await loadEmployees();
  } catch (error) {
    console.error(error);

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
    employees.value = await getEmployees();

    console.log("Employees:", employees.value);
  } catch (error) {
    console.error("Failed to load employees:", error);
  }
};

/* ========================================================= */
/* DELETED EMPLOYEES */
/* ========================================================= */

const openDeletedEmployees = async () => {
  showDeletedModal.value = true;

  await loadDeletedEmployees();
};

const loadDeletedEmployees = async () => {
  try {
    deletedEmployees.value = await getDeletedEmployees();

    console.log("Deleted Employees:", deletedEmployees.value);
  } catch (error) {
    console.error("Failed to load deleted employees:", error);

    alert("Unable to load deleted employees.");
  }
};

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

/* ========================================================= */
/* CREATE EMPLOYEE */
/* ========================================================= */

const saveEmployee = async () => {
  try {
    const response = await createEmployeeAPI(form.value);

    generatedCredentials.value = {
      email: response.email,

      password: response.password,
    };

    showCreateModal.value = false;

    showCredentialsModal.value = true;

    await loadEmployees();

    /* RESET FORM */
    form.value = {
      email: "",

      first_name: "",

      middle_name: "",

      last_name: "",

      sex: "",

      department_id: null,

      level: "",

      position_id: null,

      salary_step: null,

      salary: "",

      contact_number: "",
    };
  } catch (error) {
    console.error(error);

    alert("Unable to create employee.");
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
/* IMPORTANT: TABLE DOES NOT SCROLL */
/* ========================================================= */

.table-wrapper {
  width: 100%;
  max-width: 100%;
  overflow: visible;
}

.employee-table {
  width: 100%;
  max-width: 100%;
  table-layout: fixed;
  border-collapse: collapse;
}

/*
 * Allow text to wrap instead of forcing
 * the table wider than the screen.
 */

.employee-table th,
.employee-table td {
  overflow-wrap: anywhere;
  word-break: break-word;
}

/* ========================================================= */
/* TABLE COLUMN WIDTHS */
/* ========================================================= */

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
/* DELETED EMPLOYEES TABLE */
/* ========================================================= */

.deleted-table {
  width: 100%;
  max-width: 100%;
  table-layout: fixed;
  border-collapse: collapse;
}

.deleted-table th,
.deleted-table td {
  overflow-wrap: anywhere;
  word-break: break-word;
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
  width: 13%;
}

/* ========================================================= */
/* FLEX / GRID CHILDREN */
/* ========================================================= */

.dashboard-shell *,
.neo-card * {
  min-width: 0;
}

/* ========================================================= */
/* SMALLER SCREENS */
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

@media (max-width: 768px) {
  .dashboard-shell {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }

  .neo-card {
    border-radius: 1rem;
  }

  .employee-table,
  .deleted-table {
    font-size: 0.75rem;
  }

  .employee-table th,
  .employee-table td,
  .deleted-table th,
  .deleted-table td {
    padding-left: 0.35rem;
    padding-right: 0.35rem;
  }

  .employee-table button,
  .deleted-table button {
    font-size: 0.65rem;
    padding: 0.25rem 0.4rem;
  }
}

@media (max-width: 640px) {
  .neo-card {
    border-radius: 0.9rem;
  }

  .employee-table,
  .deleted-table {
    font-size: 0.7rem;
  }

  .employee-table th,
  .employee-table td,
  .deleted-table th,
  .deleted-table td {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    padding-left: 0.25rem;
    padding-right: 0.25rem;
  }
}
</style>
