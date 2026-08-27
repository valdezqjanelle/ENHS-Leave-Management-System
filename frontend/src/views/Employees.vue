<template>
  <div>
    <div class="dashboard-shell max-w-7xl mx-auto space-y-6">
      <!-- ================= HEADER ================= -->
      <div class="neo-card p-6">
        <div class="flex justify-between items-center">
          <!-- LEFT: TITLE -->
          <div>
            <h2 class="text-2xl font-bold text-white">Employee Management</h2>

            <p class="text-white mt-1">Create and manage employee accounts.</p>
          </div>

          <!-- RIGHT: BUTTONS -->
          <div class="flex items-center gap-3">
            <button
              @click="openDeletedEmployees"
              class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-2 rounded-lg font-medium transition"
            >
              Deleted Employees
            </button>

            <button
              @click="showCreateModal = true"
              class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium transition"
            >
              + Create Employee
            </button>
          </div>
        </div>
      </div>

      <!-- ================= SEARCH ================= -->
      <div class="neo-card p-6">
        <input
          v-model="search"
          type="text"
          placeholder="Search employee..."
          class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-black"
        />
      </div>

      <!-- ================= EMPLOYEE TABLE ================= -->
      <div class="neo-card p-6 overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-100">
            <tr class="text-left text-black font-semibold">
              <th class="px-6 py-3 font-bold whitespace-nowrap">
                Employee Code
              </th>

              <th class="px-6 py-3 font-bold whitespace-nowrap">Employee</th>

              <th class="px-6 py-3 font-bold whitespace-nowrap">Email</th>

              <th class="px-6 py-3 font-bold whitespace-nowrap">Department</th>

              <th class="px-6 py-3 font-extrabold whitespace-nowrap">
                Position
              </th>

              <th class="px-6 py-3 font-bold whitespace-nowrap">Category</th>

              <th class="px-6 py-3 font-bold whitespace-nowrap">Status</th>

              <th class="px-6 py-3 font-bold whitespace-nowrap">Created By</th>

              <th class="px-6 py-3 font-bold text-center whitespace-nowrap">
                Action
              </th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="employee in filteredEmployees"
              :key="employee.employee_id"
              class="border-t hover:bg-gray-800 transition-colors duration-200"
            >
              <td class="px-6 py-4 text-white font-semibold whitespace-nowrap">
                {{ employee.employee_code }}
              </td>

              <td class="px-6 py-4 text-white font-medium whitespace-nowrap">
                {{ employee.last_name }},
                {{ employee.first_name }}
                {{ employee.middle_name }}
              </td>

              <td class="px-6 py-4 text-white whitespace-nowrap">
                {{ employee.user.email }}
              </td>

              <td class="px-6 py-4 text-white whitespace-nowrap">
                {{ employee.department }}
              </td>
              <td class="px-6 py-4 text-white whitespace-nowrap">
                {{ employee.position?.name || "-" }}
              </td>

              <td class="px-6 py-4 text-white whitespace-nowrap">
                {{ employee.employee_category }}
              </td>

              <td class="px-6 py-4 text-white whitespace-nowrap">
                <span
                  class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs"
                >
                  {{ employee.employment_status }}
                </span>
              </td>

              <td class="px-6 py-4 text-white whitespace-nowrap">
                {{ employee.created_by?.email || "-" }}
              </td>

              <!-- ================= ACTION BUTTONS ================= -->
              <td class="px-6 py-4">
                <div
                  class="flex items-center justify-center gap-2 whitespace-nowrap"
                >
                  <button
                    @click="viewEmployee(employee)"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200"
                  >
                    View
                  </button>

                  <button
                    @click="editEmployee(employee)"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors duration-200"
                  >
                    Edit
                  </button>

                  <button
                    @click="deleteEmployee(employee)"
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors duration-200"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>

            <!-- ================= EMPTY STATE ================= -->
            <tr v-if="filteredEmployees.length === 0">
              <td colspan="8" class="text-center py-10 text-white">
                No employees found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- CREATE EMPLOYEE MODAL -->
    <!-- ========================================================= -->

    <div
      v-if="showCreateModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div
        class="bg-white rounded-xl shadow-xl w-full max-w-4xl mx-4 overflow-hidden"
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
                  class="w-full border rounded-lg px-3 py-2 text-gray-800 focus:ring-2 focus:ring-blue-500"
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

            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  First Name
                </label>

                <input
                  v-model="form.first_name"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Middle Name
                </label>

                <input
                  v-model="form.middle_name"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Last Name
                </label>

                <input
                  v-model="form.last_name"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Sex
                </label>

                <select
                  v-model="form.sex"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
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
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>
            </div>
          </div>

          <!-- Employment Information -->
          <div>
            <h4 class="font-semibold text-gray-800 mb-4">
              Employment Information
            </h4>

            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Department
                </label>

                <input
                  v-model="form.department"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Position
                </label>

                <select
                  v-model="form.position_id"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
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

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Employee Category
                </label>

                <select
                  v-model="form.employee_category"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option value="">Select</option>

                  <option value="Teaching">Teaching</option>

                  <option value="Non-Teaching">Non-Teaching</option>
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
                  class="w-full border rounded-lg px-3 py-2 text-gray-800 bg-gray-100"
                />
              </div>

              <!-- Salary Step -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Salary Step
                </label>

                <select
                  v-model="form.salary_step"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
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
                  class="w-full border rounded-lg px-3 py-2 text-gray-800 bg-gray-100 font-semibold"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-100 px-6 py-4 flex justify-end gap-3">
          <button
            @click="showCreateModal = false"
            class="px-5 py-2 rounded-lg border"
          >
            Cancel
          </button>

          <button
            @click="saveEmployee"
            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg"
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
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
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
              class="bg-gray-100 rounded-lg px-3 py-2 text-black font-bold mt-1"
            >
              {{ generatedCredentials.email }}
            </div>
          </div>

          <div>
            <label class="text-sm text-black font-semibold">
              Temporary Password
            </label>

            <div
              class="bg-gray-100 rounded-lg px-3 py-2 text-black font-bold mt-1 font-mono"
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
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    >
      <div
        class="bg-white rounded-xl shadow-2xl w-full max-w-5xl mx-4 overflow-hidden"
      >
        <!-- Header -->
        <div
          class="bg-blue-600 text-white px-6 py-5 flex justify-between items-center"
        >
          <div>
            <h2 class="text-2xl font-bold">Employee Profile</h2>

            <p class="text-blue-100 text-sm">
              Employee information and employment details
            </p>
          </div>

          <button @click="showViewModal = false" class="text-white text-3xl">
            &times;
          </button>
        </div>

        <!-- Body -->
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- LEFT -->
          <div class="space-y-6">
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

                <div>
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
          <div class="space-y-6">
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
        </div>

        <!-- Employment -->
        <div class="px-6 pb-6">
          <h3 class="font-semibold text-blue-600 border-b pb-2">
            Employment Information
          </h3>

          <div class="grid md:grid-cols-4 gap-5 mt-5 text-black">
            <div>
              <span class="font-medium"> Department </span>

              <p class="mt-1">
                {{ selectedEmployee.department }}
              </p>
            </div>

            <div>
              <span class="font-medium"> Position </span>

              <p class="mt-1">
                {{ selectedEmployee.position?.name || "-" }}
              </p>
            </div>

            <div>
              <span class="font-medium"> Category </span>

              <p class="mt-1">
                {{ selectedEmployee.employee_category }}
              </p>
            </div>

            <div>
              <span class="font-medium"> Salary </span>

              <p class="mt-1">
                ₱{{ Number(selectedEmployee.salary || 0).toLocaleString() }}
              </p>
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
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div
        class="bg-white rounded-xl shadow-xl w-full max-w-4xl mx-4 overflow-hidden"
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
                  class="w-full border rounded-lg px-3 py-2 text-gray-800 focus:ring-2 focus:ring-yellow-500"
                />
              </div>
            </div>
          </div>

          <!-- Personal -->
          <div>
            <h4 class="font-semibold text-gray-700 mb-4">
              Personal Information
            </h4>

            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  First Name
                </label>

                <input
                  v-model="editForm.first_name"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Middle Name
                </label>

                <input
                  v-model="editForm.middle_name"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Last Name
                </label>

                <input
                  v-model="editForm.last_name"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Sex
                </label>

                <select
                  v-model="editForm.sex"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
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
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>
            </div>
          </div>

          <!-- Employment -->
          <div>
            <h4 class="font-semibold text-gray-700 mb-4">
              Employment Information
            </h4>

            <div class="grid md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Department
                </label>

                <input
                  v-model="editForm.department"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Position
                </label>

                <select
                  v-model="editForm.position_id"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
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

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Employee Category
                </label>

                <select
                  v-model="editForm.employee_category"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option value="Teaching">Teaching</option>

                  <option value="Non-Teaching">Non-Teaching</option>
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
                  class="w-full border rounded-lg px-3 py-2 text-gray-800 bg-gray-100"
                />
              </div>

              <!-- Salary Step -->
              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Salary Step
                </label>

                <select
                  v-model="editForm.salary_step"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
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
                  class="w-full border rounded-lg px-3 py-2 text-gray-800 bg-gray-100 font-semibold"
                />
              </div>

              <div>
                <label class="block mb-2 text-sm text-gray-800 font-medium">
                  Employment Status
                </label>

                <select
                  v-model="editForm.employment_status"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                >
                  <option value="active">Active</option>

                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-100 px-6 py-4 flex justify-end gap-3">
          <button
            @click="showEditModal = false"
            class="px-5 py-2 rounded-lg border"
          >
            Cancel
          </button>

          <button
            @click="updateEmployee"
            class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg"
          >
            Save Changes
          </button>
        </div>
      </div>
    </div>
    <div
      v-if="showDeletedModal"
      class="fixed inset-0 bg-black/60 flex items-center justify-center z-50"
    >
      <div
        class="bg-white rounded-xl shadow-2xl w-full max-w-6xl mx-4 overflow-hidden"
      >
        <!-- Header -->
        <div
          class="bg-gray-700 text-white px-6 py-5 flex justify-between items-center"
        >
          <div>
            <h2 class="text-2xl font-bold">Deleted Employees</h2>

            <p class="text-gray-200 text-sm mt-1">
              View and restore previously deleted employee records.
            </p>
          </div>

          <button
            @click="showDeletedModal = false"
            class="text-white text-3xl hover:text-gray-300"
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

            <p class="text-sm mt-1">
              Deleted employee records will appear here.
            </p>
          </div>

          <!-- Deleted Employees Table -->
          <div v-else class="overflow-x-auto border rounded-lg">
            <table class="min-w-full">
              <thead class="bg-gray-100">
                <tr class="text-left text-black font-semibold">
                  <th class="px-6 py-3">Employee Code</th>

                  <th class="px-6 py-3">Employee</th>

                  <th class="px-6 py-3">Email</th>

                  <th class="px-6 py-3">Department</th>

                  <th class="px-6 py-3">Position</th>

                  <th class="px-6 py-3">Deleted At</th>

                  <th class="px-6 py-3 text-center">Action</th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="employee in deletedEmployees"
                  :key="employee.employee_id"
                  class="border-t hover:bg-gray-50"
                >
                  <td class="px-6 py-4 text-gray-800 font-semibold">
                    {{ employee.employee_code }}
                  </td>

                  <td class="px-6 py-4 text-gray-800">
                    {{ employee.last_name }},
                    {{ employee.first_name }}
                    {{ employee.middle_name }}
                  </td>

                  <td class="px-6 py-4 text-gray-800">
                    {{ employee.user?.email || "-" }}
                  </td>

                  <td class="px-6 py-4 text-gray-800">
                    {{ employee.department }}
                  </td>

                  <td class="px-6 py-4 text-gray-800">
                    {{ employee.position?.name || "-" }}
                  </td>

                  <td class="px-6 py-4 text-gray-800">
                    {{ formatDate(employee.deleted_at) }}
                  </td>

                  <td class="px-6 py-4 text-center">
                    <button
                      @click="restoreEmployeeRecord(employee.employee_id)"
                      class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg"
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

  department: string;

  position_id: number;

  position?: {
    id: number;
    code: string;
    name: string;
    type: string;
    salary_grade: string | null;
  } | null;

  employee_category: string;
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
/* CATEGORY MAPPING */
/* ========================================================= */

const mapPositionTypeToCategory = (type: string) => {
  if (type === "Non-Teaching") {
    return "Non-Teaching";
  }

  return "Teaching";
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

  department: "",

  position_id: null as number | null,

  employee_category: "",

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
    const response = await axios.get("http://127.0.0.1:8000/api/salary-info", {
      params: {
        position_id: form.value.position_id,
        salary_step: form.value.salary_step,
      },

      headers: {
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
    });

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
  (newId) => {
    const selected = positions.value.find((p) => p.id === newId);

    if (selected) {
      form.value.employee_category = mapPositionTypeToCategory(selected.type);
    }
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

  email: "",

  first_name: "",
  middle_name: "",
  last_name: "",

  sex: "",

  department: "",

  position_id: null as number | null,

  employee_category: "",

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
    const response = await axios.get("http://127.0.0.1:8000/api/salary-info", {
      params: {
        position_id: editForm.value.position_id,
        salary_step: editForm.value.salary_step,
      },

      headers: {
        Authorization: `Bearer ${localStorage.getItem("token")}`,
      },
    });

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
  (newId) => {
    const selected = positions.value.find((p) => p.id === newId);

    if (selected) {
      editForm.value.employee_category = mapPositionTypeToCategory(
        selected.type,
      );
    }
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

const normalizeEmploymentStatus = (
  status: string | undefined
) => {

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

    const department = employee.department?.toLowerCase() || "";

    const position = employee.position?.name?.toLowerCase() || "";

    const category =
      employee.employee_category?.toLowerCase() || "";

    return (

      firstName.includes(keyword) ||

      middleName.includes(keyword) ||

      lastName.includes(keyword) ||

      employeeCode.includes(keyword) ||

      email.includes(keyword) ||

      department.includes(keyword) ||

      position.includes(keyword) ||

      category.includes(keyword)

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

    department: employee.department || "",

    position_id: employee.position_id || null,

    employee_category:
      employee.employee_category || "",

    salary_step: employee.salary_step || null,

    salary: employee.salary != null ? String(employee.salary) : "",

    contact_number:
      employee.contact_number || "",

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
/* LOAD EMPLOYEES */
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

const loadEmployees = async () => {
  try {
    employees.value = await getEmployees();

    console.log("Employees:", employees.value);
  } catch (error) {
    console.error("Failed to load employees:", error);
  }
};

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

      department: "",

      position_id: null,

      employee_category: "",

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
  await Promise.all([loadEmployees(), loadPositions()]);
});
</script>

<style scoped>
.dashboard-shell {
  background: #080d14;

  min-height: 100vh;

  padding: 1.5rem;
}

.neo-card {
  background: #111d2e;

  border: 1px solid #1e293b;

  border-radius: 1.4rem;

  box-shadow:
    0 10px 22px rgba(15, 23, 42, 0.04);

  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;
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

/* Keep action buttons beside each other */

td:last-child {
  white-space: nowrap;
}
</style>