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
          <div class="min-w-0">
            <h2 class="text-2xl font-bold text-white">
              Teaching Personnel
            </h2>

            <p class="text-white mt-1">
              Manage teaching personnel records and teaching assignments.
            </p>
          </div>

          <button
            @click="openCreateModal"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium transition whitespace-nowrap"
          >
            + Add Teaching Personnel
          </button>
        </div>
      </div>

      <!-- ========================================================= -->
      <!-- SEARCH -->
      <!-- ========================================================= -->

      <div class="neo-card w-full p-6">
        <input
          v-model="search"
          type="text"
          placeholder="Search teaching personnel..."
          class="w-full min-w-0 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-black"
        />
      </div>

      <!-- ========================================================= -->
      <!-- TABLE -->
      <!-- ========================================================= -->

      <div class="neo-card w-full p-6">
        <div class="table-wrapper">
          <table class="teaching-table">
            <thead class="bg-gray-100">
              <tr class="text-left text-black font-semibold">
                <th class="px-3 py-3">Employee Code</th>
                <th class="px-3 py-3">Employee</th>
                <th class="px-3 py-3">Position</th>
                <th class="px-3 py-3">Specialization</th>
              
                <th class="px-3 py-3">Advisory Class</th>
                <th class="px-3 py-3">Teaching Hours</th>
                <th class="px-3 py-3 text-center">Action</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="record in filteredRecords"
                :key="record.teaching_record_id"
                class="border-t hover:bg-gray-800 transition-colors duration-200"
              >
                <td class="px-3 py-4 text-white font-semibold break-words">
                  {{ record.employee?.employee_code || "-" }}
                </td>

                <td class="px-3 py-4 text-white font-medium break-words">
                  {{ employeeName(record) }}
                </td>

                <td class="px-3 py-4 text-white break-words">
                  {{ record.employee?.position?.name || "-" }}
                </td>

                <td class="px-3 py-4 text-white break-words">
                  {{ record.subject_specialization || "-" }}
                </td>


                <td class="px-3 py-4 text-white break-words">
                  {{ record.advisory_class || "-" }}
                </td>

                <td class="px-3 py-4 text-white">
                  {{ record.teaching_hours ?? "-" }}
                </td>

                <td class="px-3 py-4">
                  <div
                    class="flex flex-wrap items-center justify-center gap-1"
                  >
                    <button
                      @click="viewRecord(record)"
                      class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded-full text-xs"
                    >
                      View
                    </button>

                    <button
                      @click="editRecord(record)"
                      class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded-full text-xs"
                    >
                      Edit
                    </button>

                    <button
                      @click="deleteRecord(record)"
                      class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded-full text-xs"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="filteredRecords.length === 0">
                <td colspan="8" class="text-center py-10 text-white">
                  No teaching personnel records found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- CREATE / EDIT MODAL -->
    <!-- ========================================================= -->

    <div
      v-if="showFormModal"
      class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-xl shadow-xl w-full max-w-3xl mx-auto overflow-hidden"
      >
        <!-- HEADER -->

        <div
          :class="
            editingRecord
              ? 'bg-yellow-500 text-white px-6 py-4'
              : 'bg-blue-600 text-white px-6 py-4'
          "
        >
          <div class="flex justify-between items-center">
            <div>
              <h3 class="text-xl font-semibold">
                {{
                  editingRecord
                    ? "Edit Teaching Personnel"
                    : "Add Teaching Personnel"
                }}
              </h3>

              <p class="text-sm mt-1">
                {{
                  editingRecord
                    ? "Update teaching personnel information."
                    : "Enter the teaching personnel information."
                }}
              </p>
            </div>

            <button
              @click="closeFormModal"
              class="text-white text-3xl"
            >
              &times;
            </button>
          </div>
        </div>

        <!-- BODY -->

        <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
          <!-- EMPLOYEE -->

          <div>
            <h4 class="font-semibold text-gray-800 mb-4">
              Employee
            </h4>

            <label class="block mb-2 text-sm text-gray-800 font-medium">
              Employee
            </label>

            <select
              v-model="form.employee_id"
              :disabled="editingRecord !== null"
              class="w-full border rounded-lg px-3 py-2 text-gray-800 disabled:bg-gray-100"
            >
              <option :value="null">
                Select Teaching Personnel
              </option>

              <option
                v-for="employee in teachingEmployees"
                :key="employee.employee_id"
                :value="employee.employee_id"
              >
                {{ employee.employee_code }} -
                {{ employee.last_name }},
                {{ employee.first_name }}
                {{ employee.middle_name || "" }}
              </option>
            </select>
          </div>

          <!-- TEACHING INFORMATION -->

          <div>
            <h4 class="font-semibold text-gray-800 mb-4">
              Teaching Information
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- SPECIALIZATION -->

              <div>
                <label
                  class="block mb-2 text-sm text-gray-800 font-medium"
                >
                  Subject Specialization
                </label>

                <input
                  v-model="form.subject_specialization"
                  type="text"
                  placeholder="e.g. Mathematics"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- GRADE LEVEL -->

              <div>
                <label
                  class="block mb-2 text-sm text-gray-800 font-medium"
                >
                  Grade Level Handled
                </label>

                <input
                  v-model="form.grade_level_handled"
                  type="text"
                  placeholder="e.g. Grade 7"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- ADVISORY -->

              <div>
                <label
                  class="block mb-2 text-sm text-gray-800 font-medium"
                >
                  Advisory Class
                </label>

                <input
                  v-model="form.advisory_class"
                  type="text"
                  placeholder="e.g. Grade 7 - Rizal"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <!-- TEACHING HOURS -->

              <div>
                <label
                  class="block mb-2 text-sm text-gray-800 font-medium"
                >
                  Teaching Hours
                </label>

                <input
                  v-model="form.teaching_hours"
                  type="number"
                  min="0"
                  max="99.99"
                  step="0.01"
                  placeholder="e.g. 6"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>
            </div>
          </div>

          <!-- TEACHING LOAD -->

          <div>
            <label
              class="block mb-2 text-sm text-gray-800 font-medium"
            >
              Teaching Load
            </label>

            <textarea
              v-model="form.teaching_load"
              rows="4"
              placeholder="Enter teaching load details..."
              class="w-full border rounded-lg px-3 py-2 text-gray-800 resize-none"
            ></textarea>
          </div>
        </div>

        <!-- FOOTER -->

        <div
          class="bg-gray-100 px-6 py-4 flex flex-col sm:flex-row justify-end gap-3"
        >
          <button
            @click="closeFormModal"
            class="w-full sm:w-auto px-5 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white"
          >
            Cancel
          </button>

          <button
            @click="saveRecord"
            :class="
              editingRecord
                ? 'bg-yellow-500 hover:bg-yellow-600'
                : 'bg-blue-600 hover:bg-blue-700'
            "
            class="w-full sm:w-auto text-white px-6 py-2 rounded-lg"
          >
            {{ editingRecord ? "Save Changes" : "Add Record" }}
          </button>
        </div>
      </div>
    </div>

    <!-- ========================================================= -->
    <!-- VIEW MODAL -->
    <!-- ========================================================= -->

    <div
      v-if="showViewModal && selectedRecord"
      class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-xl shadow-2xl w-full max-w-3xl mx-auto overflow-hidden"
      >
        <div
          class="bg-blue-600 text-white px-6 py-5 flex justify-between items-center"
        >
          <div>
            <h2 class="text-2xl font-bold">
              Teaching Personnel Profile
            </h2>

            <p class="text-blue-100 text-sm">
              Teaching assignment and personnel information
            </p>
          </div>

          <button
            @click="showViewModal = false"
            class="text-white text-3xl"
          >
            &times;
          </button>
        </div>

        <div
          class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 max-h-[75vh] overflow-y-auto text-black"
        >
          <div>
            <span class="font-semibold">Employee Code</span>
            <p class="mt-1">
              {{ selectedRecord.employee?.employee_code || "-" }}
            </p>
          </div>

          <div>
            <span class="font-semibold">Employee</span>
            <p class="mt-1">
              {{ employeeName(selectedRecord) }}
            </p>
          </div>

          <div>
            <span class="font-semibold">Position</span>
            <p class="mt-1">
              {{ selectedRecord.employee?.position?.name || "-" }}
            </p>
          </div>

          <div>
            <span class="font-semibold">Department</span>
            <p class="mt-1">
              {{
                selectedRecord.employee?.department?.department_name || "-"
              }}
            </p>
          </div>

          <div>
            <span class="font-semibold">Subject Specialization</span>
            <p class="mt-1 break-words">
              {{ selectedRecord.subject_specialization || "-" }}
            </p>
          </div>

          <div>
            <span class="font-semibold">Grade Level Handled</span>
            <p class="mt-1">
              {{ selectedRecord.grade_level_handled || "-" }}
            </p>
          </div>

          <div>
            <span class="font-semibold">Advisory Class</span>
            <p class="mt-1">
              {{ selectedRecord.advisory_class || "-" }}
            </p>
          </div>

          <div>
            <span class="font-semibold">Teaching Hours</span>
            <p class="mt-1">
              {{ selectedRecord.teaching_hours ?? "-" }}
            </p>
          </div>

          <div class="md:col-span-2">
            <span class="font-semibold">Teaching Load</span>

            <p class="mt-1 whitespace-pre-line break-words">
              {{ selectedRecord.teaching_load || "-" }}
            </p>
          </div>
        </div>

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
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";

import {
  getTeachingPersonnelRecords,
  createTeachingPersonnelRecord,
  updateTeachingPersonnelRecord,
  deleteTeachingPersonnelRecord,
} from "../services/teachingPersonnel";

import { getEmployees } from "../services/employee";

/* ========================================================= */
/* INTERFACES */
/* ========================================================= */

interface Employee {
  employee_id: number;
  employee_code: string;
  first_name: string;
  middle_name?: string;
  last_name: string;

  personnel_type?: string;

  user?: {
    email: string;
  };

  position?: {
    id: number;
    name: string;
  };

  department?: {
    department_id: number;
    department_name: string;
  };
}

interface TeachingRecord {
  teaching_record_id: number; 

  employee_id: number;

  subject_specialization: string;
  grade_level_handled: string;
  advisory_class?: string | null;
  teaching_load?: string | null;
  teaching_hours?: number | null;

  employee?: Employee;
}

/* ========================================================= */
/* STATE */
/* ========================================================= */

const records = ref<TeachingRecord[]>([]);

const employees = ref<Employee[]>([]);

const search = ref("");

const showFormModal = ref(false);

const showViewModal = ref(false);

const selectedRecord = ref<TeachingRecord | null>(null);

const editingRecord = ref<TeachingRecord | null>(null);

/* ========================================================= */
/* FORM */
/* ========================================================= */

const form = ref({
  employee_id: null as number | null,

  subject_specialization: "",

  grade_level_handled: "",

  advisory_class: "",

  teaching_load: "",

  teaching_hours: null as number | null,
});

/* ========================================================= */
/* TEACHING EMPLOYEES */
/* ========================================================= */

const teachingEmployees = computed(() => {
  return employees.value.filter(
    (employee) => employee.personnel_type === "Teaching"
  );
});

/* ========================================================= */
/* SEARCH */
/* ========================================================= */

const filteredRecords = computed(() => {
  const keyword = search.value.toLowerCase().trim();

  if (!keyword) {
    return records.value;
  }

  return records.value.filter((record) => {
    const employee = record.employee;

    const name = employee
      ? `${employee.first_name} ${employee.middle_name || ""} ${employee.last_name}`
          .toLowerCase()
      : "";

    const code = employee?.employee_code?.toLowerCase() || "";

    const position =
      employee?.position?.name?.toLowerCase() || "";

    const specialization =
      record.subject_specialization?.toLowerCase() || "";

    const grade =
      record.grade_level_handled?.toLowerCase() || "";

    const advisory =
      record.advisory_class?.toLowerCase() || "";

    return (
      name.includes(keyword) ||
      code.includes(keyword) ||
      position.includes(keyword) ||
      specialization.includes(keyword) ||
      grade.includes(keyword) ||
      advisory.includes(keyword)
    );
  });
});

/* ========================================================= */
/* EMPLOYEE NAME */
/* ========================================================= */

const employeeName = (record: TeachingRecord) => {
  const employee = record.employee;

  if (!employee) {
    return "-";
  }

  return `${employee.last_name}, ${employee.first_name}${
    employee.middle_name ? ` ${employee.middle_name}` : ""
  }`;
};

/* ========================================================= */
/* LOAD RECORDS */
/* ========================================================= */

const loadRecords = async () => {
  try {
    records.value = await getTeachingPersonnelRecords();

    console.log("Teaching Personnel Records:", records.value);
  } catch (error) {
    console.error(
      "Failed to load teaching personnel records:",
      error
    );

    alert("Unable to load teaching personnel records.");
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
/* RESET FORM */
/* ========================================================= */

const resetForm = () => {
  form.value = {
    employee_id: null,
    subject_specialization: "",
    grade_level_handled: "",
    advisory_class: "",
    teaching_load: "",
    teaching_hours: null,
  };
};

/* ========================================================= */
/* CREATE MODAL */
/* ========================================================= */

const openCreateModal = () => {
  editingRecord.value = null;

  resetForm();

  showFormModal.value = true;
};

/* ========================================================= */
/* EDIT */
/* ========================================================= */

const editRecord = (record: TeachingRecord) => {
  editingRecord.value = record;

  form.value = {
    employee_id: record.employee_id,

    subject_specialization:
      record.subject_specialization || "",

    grade_level_handled:
      record.grade_level_handled || "",

    advisory_class:
      record.advisory_class || "",

    teaching_load:
      record.teaching_load || "",

    teaching_hours:
      record.teaching_hours ?? null,
  };

  showFormModal.value = true;
};

/* ========================================================= */
/* CLOSE FORM */
/* ========================================================= */

const closeFormModal = () => {
  showFormModal.value = false;

  editingRecord.value = null;

  resetForm();
};

/* ========================================================= */
/* SAVE */
/* ========================================================= */

const saveRecord = async () => {
  try {
    if (!form.value.employee_id) {
      alert("Please select an employee.");

      return;
    }

    if (!form.value.subject_specialization.trim()) {
      alert("Please enter the subject specialization.");

      return;
    }

    if (!form.value.grade_level_handled.trim()) {
      alert("Please enter the grade level handled.");

      return;
    }

    const payload = {
      employee_id: form.value.employee_id,

      subject_specialization:
        form.value.subject_specialization,

      grade_level_handled:
        form.value.grade_level_handled,

      advisory_class:
        form.value.advisory_class || null,

      teaching_load:
        form.value.teaching_load || null,

      teaching_hours:
        form.value.teaching_hours,
    };

    if (editingRecord.value) {
      await updateTeachingPersonnelRecord(
        editingRecord.value.teaching_record_id,
        payload
      );

      alert(
        "Teaching personnel record updated successfully."
      );
    } else {
      await createTeachingPersonnelRecord(payload);

      alert(
        "Teaching personnel record created successfully."
      );
    }

    closeFormModal();

    await loadRecords();
  } catch (error: any) {
    console.error(
      "Failed to save teaching personnel record:",
      error
    );

    const message =
      error?.response?.data?.message ||
      "Unable to save teaching personnel record.";

    alert(message);
  }
};

/* ========================================================= */
/* VIEW */
/* ========================================================= */

const viewRecord = (record: TeachingRecord) => {
  selectedRecord.value = record;

  showViewModal.value = true;
};

/* ========================================================= */
/* DELETE */
/* ========================================================= */

const deleteRecord = async (record: TeachingRecord) => {
  const name = employeeName(record);

  const confirmed = confirm(
    `Are you sure you want to delete the teaching personnel record for ${name}?`
  );

  if (!confirmed) {
    return;
  }

  try {
    await deleteTeachingPersonnelRecord(record.teaching_record_id);

    alert(
      "Teaching personnel record deleted successfully."
    );

    await loadRecords();
  } catch (error) {
    console.error(
      "Failed to delete teaching personnel record:",
      error
    );

    alert(
      "Unable to delete teaching personnel record."
    );
  }
};

/* ========================================================= */
/* INITIAL LOAD */
/* ========================================================= */

onMounted(async () => {
  await Promise.all([
    loadRecords(),
    loadEmployees(),
  ]);
});
</script>

<style scoped>
.dashboard-shell {
  background: #080d14;
  min-height: 100vh;
  width: 100%;
  box-sizing: border-box;
}

.neo-card {
  background: #111d2e;
  border: 1px solid #1e293b;
  border-radius: 1.4rem;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
  width: 100%;
  max-width: none;
  min-width: 0;
  box-sizing: border-box;
}

.neo-card:hover {
  box-shadow: 0 14px 26px rgba(15, 23, 42, 0.06);
}

.table-wrapper {
  width: 100%;
  max-width: 100%;
  overflow: visible;
}

.teaching-table {
  width: 100%;
  max-width: 100%;
  table-layout: fixed;
  border-collapse: collapse;
}

.teaching-table th,
.teaching-table td {
  overflow-wrap: anywhere;
  word-break: break-word;
}

.teaching-table th:nth-child(1),
.teaching-table td:nth-child(1) {
  width: 10%;
}

.teaching-table th:nth-child(2),
.teaching-table td:nth-child(2) {
  width: 15%;
}

.teaching-table th:nth-child(3),
.teaching-table td:nth-child(3) {
  width: 12%;
}

.teaching-table th:nth-child(4),
.teaching-table td:nth-child(4) {
  width: 15%;
}

.teaching-table th:nth-child(5),
.teaching-table td:nth-child(5) {
  width: 12%;
}

.teaching-table th:nth-child(6),
.teaching-table td:nth-child(6) {
  width: 13%;
}

.teaching-table th:nth-child(7),
.teaching-table td:nth-child(7) {
  width: 9%;
}

.teaching-table th:nth-child(8),
.teaching-table td:nth-child(8) {
  width: 14%;
}

.dashboard-shell *,
.neo-card * {
  min-width: 0;
}

@media (max-width: 1024px) {
  .teaching-table th,
  .teaching-table td {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
  }

  .teaching-table {
    font-size: 0.875rem;
  }

  .teaching-table button {
    font-size: 0.7rem;
    padding-left: 0.45rem;
    padding-right: 0.45rem;
  }
}

@media (max-width: 768px) {
  .teaching-table {
    font-size: 0.75rem;
  }

  .teaching-table th,
  .teaching-table td {
    padding-left: 0.35rem;
    padding-right: 0.35rem;
  }

  .teaching-table button {
    font-size: 0.65rem;
    padding: 0.25rem 0.4rem;
  }
}

@media (max-width: 640px) {
  .teaching-table {
    font-size: 0.7rem;
  }

  .teaching-table th,
  .teaching-table td {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    padding-left: 0.25rem;
    padding-right: 0.25rem;
  }
}
</style>