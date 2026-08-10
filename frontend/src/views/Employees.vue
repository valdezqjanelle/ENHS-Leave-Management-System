<template>
  <div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow p-6">
      <div class="flex justify-between items-center">
        <div>
          <h2 class="text-2xl font-bold text-gray-800">Employee Management</h2>

          <p class="text-gray-500 mt-1">Create and manage employee accounts.</p>
        </div>

        <button
          @click="showCreateModal = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium"
        >
          + Create Employee
        </button>
      </div>
    </div>

    <!-- Search -->
    <div class="bg-white rounded-lg shadow p-5">
      <input
        v-model="search"
        type="text"
        placeholder="Search employee..."
        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
      />
    </div>

    <!-- Employee Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="min-w-full">
        <thead class="bg-gray-100">
          <tr class="text-left text-black font-semibold">
            <th class="px-6 py-3 font-bold">Employee Code</th>

            <th class="px-6 py-3 font-bold">Employee</th>

            <th class="px-6 py-3 font-bold">Email</th>

            <th class="px-6 py-3 font-bold">Department</th>

            <th class="px-6 py-3 font-extrabold">Position</th>

            <th class="px-6 py-3 font-bold">Category</th>

            <th class="px-6 py-3 font-bold">Status</th>

            <th class="px-6 py-3 font-bold text-center">Action</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="employee in filteredEmployees"
            :key="employee.employee_id"
            class="border-t hover:bg-yellow-50"
          >
            <td class="px-6 py-4 text-black font-semibold">
              {{ employee.employee_code }}
            </td>

            <td class="px-6 py-4 text-black font-medium">
              {{ employee.last_name }},
              {{ employee.first_name }}
              {{ employee.middle_name }}
            </td>

            <td class="px-6 py-4 text-black">
              {{ employee.user.email }}
            </td>

            <td class="px-6 py-4 text-black">
              {{ employee.department }}
            </td>

            <td class="px-6 py-4 text-black">
              {{ employee.position }}
            </td>

            <td class="px-6 py-4 text-black">
              {{ employee.employee_category }}
            </td>

            <td class="px-6 py-4 text-black">
              <span
                class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs"
              >
                {{ employee.employment_status }}
              </span>
            </td>

            <td class="px-6 py-4 text-center">
              <button
                @click="viewEmployee(employee)"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"
              >
                View
              </button>

              <button
                @click="editEmployee(employee)"
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded ml-2"
              >
                Edit
              </button>
            </td>
          </tr>

          <!-- Empty State -->

          <tr v-if="filteredEmployees.length === 0">
            <td colspan="8" class="text-center py-10 text-gray-500">
              No employees found.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <!-- ================= CREATE EMPLOYEE MODAL ================= -->

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
        <!-- Account -->

        <div>
          <h4 class="font-semibold text-gray-700 mb-4">Account Information</h4>

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

        <!-- Personal -->

        <div>
          <h4 class="font-semibold text-gray-800 mb-4">Personal Information</h4>

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

        <!-- Employment -->

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

              <input
                v-model="form.position"
                class="w-full border rounded-lg px-3 py-2 text-gray-800"
              />
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

            <div>
              <label class="block mb-2 text-sm text-gray-800 font-medium">
                Salary
              </label>

              <input
                v-model="form.salary"
                type="number"
                class="w-full border rounded-lg px-3 py-2 text-gray-800"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->

      <div class="bg-gray-100 px-6 py-4 flex justify-end gap-3">
        <button
          @click="showCreateModal = false"
          class="px-5 py-2 rounded-lg border text-black"
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

  <!-- ================= EMPLOYEE CREATED ================= -->

  <div
    v-if="showCredentialsModal"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
  >
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
      <div class="bg-green-600 text-white p-5">
        <h3 class="text-xl font-bold">Employee Created Successfully</h3>
      </div>

      <div class="p-6 space-y-4">
        <div>
          <p class="text-gray-900 text-sm">
            Give these login credentials to the employee.
          </p>
        </div>

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

  <!-- ================= VIEW EMPLOYEE MODAL ================= -->

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
                <span class="font-medium">Employee Code:</span><br />
                {{ selectedEmployee.employee_code }}
              </div>

              <div>
                <span class="font-medium">Email:</span><br />
                {{ selectedEmployee.user.email }}
              </div>

              <div>
                <span class="font-medium">Role:</span><br />
                Employee
              </div>

              <div>
                <span class="font-medium">Employment Status:</span><br />

                <span
                  class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm"
                >
                  {{ selectedEmployee.employment_status }}
                </span>
              </div>

              <div>
                <span class="font-medium">Date Hired:</span><br />
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
                <span class="font-medium">First Name:</span><br />
                {{ selectedEmployee.first_name }}
              </div>

              <div>
                <span class="font-medium">Middle Name:</span><br />
                {{ selectedEmployee.middle_name || "-" }}
              </div>

              <div>
                <span class="font-medium">Last Name:</span><br />
                {{ selectedEmployee.last_name }}
              </div>

              <div>
                <span class="font-medium">Sex:</span><br />
                {{ selectedEmployee.sex }}
              </div>

              <div>
                <span class="font-medium">Contact Number:</span><br />
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
              {{ selectedEmployee.position }}
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
  <!-- ================= EDIT EMPLOYEE ================= -->

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
          <h4 class="font-semibold text-gray-700 mb-4">Account Information</h4>

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
          <h4 class="font-semibold text-gray-700 mb-4">Personal Information</h4>

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

              <input
                v-model="editForm.position"
                class="w-full border rounded-lg px-3 py-2 text-gray-800"
              />
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

            <div>
              <label class="block mb-2 text-sm text-gray-800 font-medium">
                Salary
              </label>

              <input
                v-model="editForm.salary"
                type="number"
                class="w-full border rounded-lg px-3 py-2 text-gray-800"
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
</template>
<script setup lang="ts">
import { ref, computed, onMounted } from "vue";

import {
  getEmployees,
  createEmployee as createEmployeeAPI,
  updateEmployee as updateEmployeeAPI,
} from "@/services/employee";

interface Employee {
  employee_id: number;

  employee_code: string;

  first_name: string;

  middle_name: string;

  last_name: string;

  department: string;

  position: string;

  employee_category: string;

  employment_status: string;

  salary: number;

  contact_number: string;

  sex: string;

  user: {
    email: string;
  };
}

const employees = ref<Employee[]>([]);

const search = ref("");

const showCreateModal = ref(false);

const showViewModal = ref(false);

const selectedEmployee = ref<any>(null);

const form = ref({
  email: "",

  first_name: "",

  middle_name: "",

  last_name: "",

  sex: "",

  department: "",

  position: "",

  employee_category: "",

  salary: "",

  contact_number: "",
});
const formatDate = (date: string) => {
  if (!date) return "-";

  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

const showCredentialsModal = ref(false);

const generatedCredentials = ref({
  email: "",
  password: "",
});

const viewEmployee = (employee: any) => {
  selectedEmployee.value = employee;

  showViewModal.value = true;
};
const filteredEmployees = computed(() => {
  return employees.value.filter((employee) => {
    const keyword = search.value.toLowerCase();

    return (
      employee.first_name.toLowerCase().includes(keyword) ||
      employee.last_name.toLowerCase().includes(keyword) ||
      employee.employee_code.toLowerCase().includes(keyword) ||
      employee.user.email.toLowerCase().includes(keyword)
    );
  });
});

const showEditModal = ref(false);

const editForm = ref({
  employee_id: 0,
  email: "",
  first_name: "",
  middle_name: "",
  last_name: "",
  sex: "",
  department: "",
  position: "",
  employee_category: "",
  salary: "",
  contact_number: "",
  employment_status: "",
});

const editEmployee = (employee: any) => {
  editForm.value = {
    employee_id: Number(employee.employee_id),
    email: employee.user?.email || "",
    first_name: employee.first_name,
    middle_name: employee.middle_name,
    last_name: employee.last_name,
    sex: employee.sex,
    department: employee.department,
    position: employee.position,
    employee_category: employee.employee_category,
    salary: employee.salary,
    contact_number: employee.contact_number,
    employment_status: employee.employment_status,
  };

  showEditModal.value = true;
};

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
const loadEmployees = async () => {
  try {
    employees.value = await getEmployees();

    console.log(employees.value);
  } catch (error) {
    console.error(error);
  }
};

const saveEmployee = async () => {
  try {
    const response = await createEmployeeAPI(form.value);

    generatedCredentials.value = {
      email: response.email,
      password: response.password,
    };

    showCredentialsModal.value = true;

    showCreateModal.value = false;

    await loadEmployees();

    form.value = {
      email: "",
      first_name: "",
      middle_name: "",
      last_name: "",
      sex: "",
      department: "",
      position: "",
      employee_category: "",
      salary: "",
      contact_number: "",
    };
  } catch (error) {
    console.error(error);
    alert("Unable to create employee.");
  }
};

onMounted(() => {
  loadEmployees();
});
</script>
