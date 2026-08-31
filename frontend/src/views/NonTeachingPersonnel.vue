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
              Non-Teaching Personnel
            </h2>

            <p class="text-white mt-1">
              Manage non-teaching personnel records and job assignments.
            </p>
          </div>

          <button
            @click="openCreateModal"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-medium transition whitespace-nowrap"
          >
            + Add Non-Teaching Personnel
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
          placeholder="Search non-teaching personnel..."
          class="w-full min-w-0 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-black"
        />
      </div>

      <!-- ========================================================= -->
      <!-- TABLE -->
      <!-- ========================================================= -->

      <div class="neo-card w-full p-6">
        <div class="table-wrapper">
          <table class="non-teaching-table">
            <thead class="bg-gray-100">
              <tr class="text-left text-black font-semibold">
                <th class="px-3 py-3">Employee Code</th>
                <th class="px-3 py-3">Employee</th>
                <th class="px-3 py-3">Position</th>
                <th class="px-3 py-3">Department</th>
                <th class="px-3 py-3">Office Assignment</th>
                <th class="px-3 py-3">Job Assignment</th>
                <th class="px-3 py-3 text-center">Action</th>
              </tr>
            </thead>

            <tbody>
              <tr
                v-for="record in filteredRecords"
                :key="record.non_teaching_record_id"
                class="border-t hover:bg-gray-800 transition-colors duration-200"
              >
                <td class="px-3 py-4 text-white font-semibold">
                  {{ record.employee?.employee_code || "-" }}
                </td>

                <td class="px-3 py-4 text-white font-medium">
                  {{ employeeName(record) }}
                </td>

                <td class="px-3 py-4 text-white">
                  {{ record.employee?.position?.name || "-" }}
                </td>

                <td class="px-3 py-4 text-white">
                  {{ record.employee?.department?.department_name || "-" }}
                </td>

                <td class="px-3 py-4 text-white">
                  {{ record.office_assignment || "-" }}
                </td>

                <td class="px-3 py-4 text-white">
                  {{ record.job_assignment || "-" }}
                </td>

                <td class="px-3 py-4">
                  <div
                    class="action-buttons flex flex-nowrap items-center justify-center gap-1"
                  >
                    <button
                      @click="viewRecord(record)"
                      class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded-full text-xs whitespace-nowrap"
                    >
                      View
                    </button>

                    <button
                      @click="editRecord(record)"
                      class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded-full text-xs whitespace-nowrap"
                    >
                      Edit
                    </button>

                    <button
                      @click="deleteRecord(record)"
                      class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded-full text-xs whitespace-nowrap"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="filteredRecords.length === 0">
                <td colspan="7" class="text-center py-10 text-white">
                  No non-teaching personnel records found.
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
                    ? "Edit Non-Teaching Personnel"
                    : "Add Non-Teaching Personnel"
                }}
              </h3>

              <p class="text-sm mt-1">
                {{
                  editingRecord
                    ? "Update non-teaching personnel information."
                    : "Enter the non-teaching personnel information."
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
                Select Non-Teaching Personnel
              </option>

              <option
                v-for="employee in nonTeachingEmployees"
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

          <!-- JOB INFORMATION -->

          <div>
            <h4 class="font-semibold text-gray-800 mb-4">
              Job Information
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label
                  class="block mb-2 text-sm text-gray-800 font-medium"
                >
                  Office Assignment
                </label>

                <input
                  v-model="form.office_assignment"
                  type="text"
                  placeholder="e.g. School Principal's Office"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>

              <div>
                <label
                  class="block mb-2 text-sm text-gray-800 font-medium"
                >
                  Job Assignment
                </label>

                <input
                  v-model="form.job_assignment"
                  type="text"
                  placeholder="e.g. Administrative Assistant"
                  class="w-full border rounded-lg px-3 py-2 text-gray-800"
                />
              </div>
            </div>
          </div>

          <!-- JOB DESCRIPTION -->

          <div>
            <label
              class="block mb-2 text-sm text-gray-800 font-medium"
            >
              Job Description
            </label>

            <textarea
              v-model="form.job_description"
              rows="5"
              placeholder="Enter job description and responsibilities..."
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
              Non-Teaching Personnel Profile
            </h2>

            <p class="text-blue-100 text-sm">
              Personnel and job assignment information
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
            <span class="font-semibold">Office Assignment</span>

            <p class="mt-1 break-words">
              {{ selectedRecord.office_assignment || "-" }}
            </p>
          </div>

          <div>
            <span class="font-semibold">Job Assignment</span>

            <p class="mt-1 break-words">
              {{ selectedRecord.job_assignment || "-" }}
            </p>
          </div>

          <div class="md:col-span-2">
            <span class="font-semibold">Job Description</span>

            <p class="mt-1 whitespace-pre-line break-words">
              {{ selectedRecord.job_description || "-" }}
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
  getNonTeachingPersonnelRecords,
  createNonTeachingPersonnelRecord,
  updateNonTeachingPersonnelRecord,
  deleteNonTeachingPersonnelRecord,
} from "../services/nonTeaching";

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

interface NonTeachingRecord {
  non_teaching_record_id: number;
  employee_id: number;
  office_assignment: string;
  job_assignment?: string | null;
  job_description?: string | null;
  employee?: Employee;
}

/* ========================================================= */
/* STATE */
/* ========================================================= */

const records = ref<NonTeachingRecord[]>([]);
const employees = ref<Employee[]>([]);

const search = ref("");

const showFormModal = ref(false);
const showViewModal = ref(false);

const selectedRecord = ref<NonTeachingRecord | null>(null);
const editingRecord = ref<NonTeachingRecord | null>(null);

/* ========================================================= */
/* FORM */
/* ========================================================= */

const form = ref({
  employee_id: null as number | null,
  office_assignment: "",
  job_assignment: "",
  job_description: "",
});

/* ========================================================= */
/* NON-TEACHING EMPLOYEES */
/* ========================================================= */

const nonTeachingEmployees = computed(() => {
  return employees.value.filter(
    (employee) => employee.personnel_type === "Non-Teaching"
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
      ? `${employee.first_name} ${
          employee.middle_name || ""
        } ${employee.last_name}`.toLowerCase()
      : "";

    const code =
      employee?.employee_code?.toLowerCase() || "";

    const position =
      employee?.position?.name?.toLowerCase() || "";

    const department =
      employee?.department?.department_name?.toLowerCase() || "";

    const office =
      record.office_assignment?.toLowerCase() || "";

    const job =
      record.job_assignment?.toLowerCase() || "";

    return (
      name.includes(keyword) ||
      code.includes(keyword) ||
      position.includes(keyword) ||
      department.includes(keyword) ||
      office.includes(keyword) ||
      job.includes(keyword)
    );
  });
});

/* ========================================================= */
/* EMPLOYEE NAME */
/* ========================================================= */

const employeeName = (record: NonTeachingRecord) => {
  const employee = record.employee;

  if (!employee) {
    return "-";
  }

  return `${employee.last_name}, ${employee.first_name}${
    employee.middle_name
      ? ` ${employee.middle_name}`
      : ""
  }`;
};

/* ========================================================= */
/* LOAD RECORDS */
/* ========================================================= */

const loadRecords = async () => {
  try {
    records.value =
      await getNonTeachingPersonnelRecords();

    console.log(
      "Non-Teaching Personnel Records:",
      records.value
    );
  } catch (error) {
    console.error(
      "Failed to load non-teaching personnel records:",
      error
    );

    alert(
      "Unable to load non-teaching personnel records."
    );
  }
};

/* ========================================================= */
/* LOAD EMPLOYEES */
/* ========================================================= */

const loadEmployees = async () => {
  try {
    employees.value = await getEmployees();

    console.log(
      "Employees:",
      employees.value
    );
  } catch (error) {
    console.error(
      "Failed to load employees:",
      error
    );
  }
};

/* ========================================================= */
/* RESET FORM */
/* ========================================================= */

const resetForm = () => {
  form.value = {
    employee_id: null,
    office_assignment: "",
    job_assignment: "",
    job_description: "",
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

const editRecord = (
  record: NonTeachingRecord
) => {
  editingRecord.value = record;

  form.value = {
    employee_id: record.employee_id,

    office_assignment:
      record.office_assignment || "",

    job_assignment:
      record.job_assignment || "",

    job_description:
      record.job_description || "",
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

    if (!form.value.office_assignment.trim()) {
      alert("Please enter the office assignment.");

      return;
    }

    const payload = {
      employee_id: form.value.employee_id,

      office_assignment:
        form.value.office_assignment,

      job_assignment:
        form.value.job_assignment || null,

      job_description:
        form.value.job_description || null,
    };

    if (editingRecord.value) {
      await updateNonTeachingPersonnelRecord(
        editingRecord.value.non_teaching_record_id,
        payload
      );

      alert(
        "Non-teaching personnel record updated successfully."
      );
    } else {
      await createNonTeachingPersonnelRecord(
        payload
      );

      alert(
        "Non-teaching personnel record created successfully."
      );
    }

    closeFormModal();

    await loadRecords();
  } catch (error: any) {
    console.error(
      "Failed to save non-teaching personnel record:",
      error
    );

    const message =
      error?.response?.data?.message ||
      "Unable to save non-teaching personnel record.";

    alert(message);
  }
};

/* ========================================================= */
/* VIEW */
/* ========================================================= */

const viewRecord = (
  record: NonTeachingRecord
) => {
  selectedRecord.value = record;

  showViewModal.value = true;
};

/* ========================================================= */
/* DELETE */
/* ========================================================= */

const deleteRecord = async (
  record: NonTeachingRecord
) => {
  const name = employeeName(record);

  const confirmed = confirm(
    `Are you sure you want to delete the non-teaching personnel record for ${name}?`
  );

  if (!confirmed) {
    return;
  }

  try {
    await deleteNonTeachingPersonnelRecord(
      record.non_teaching_record_id
    );

    alert(
      "Non-teaching personnel record deleted successfully."
    );

    await loadRecords();
  } catch (error) {
    console.error(
      "Failed to delete non-teaching personnel record:",
      error
    );

    alert(
      "Unable to delete non-teaching personnel record."
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
/* ========================================================= */
/* MAIN PAGE                                                  */
/* ========================================================= */

.dashboard-shell {
  background: #080d14;
  min-height: 100vh;
  width: 100%;
  box-sizing: border-box;
}

/* ========================================================= */
/* CARDS                                                      */
/* ========================================================= */

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

/* ========================================================= */
/* TABLE WRAPPER                                               */
/* ========================================================= */

.table-wrapper {
  width: 100%;
  max-width: 100%;

  overflow-x: visible;
  overflow-y: hidden;

  box-sizing: border-box;
}

/* ========================================================= */
/* NON-TEACHING TABLE - DESKTOP                               */
/* ========================================================= */

.non-teaching-table {
  width: 100%;
  max-width: 100%;

  table-layout: fixed;
  border-collapse: collapse;
}

.non-teaching-table th,
.non-teaching-table td {
  overflow-wrap: normal;
  word-break: normal;
}

/* Keep existing desktop sizing */

.non-teaching-table th:nth-child(1),
.non-teaching-table td:nth-child(1) {
  width: 10%;
}

.non-teaching-table th:nth-child(2),
.non-teaching-table td:nth-child(2) {
  width: 16%;
}

.non-teaching-table th:nth-child(3),
.non-teaching-table td:nth-child(3) {
  width: 13%;
}

.non-teaching-table th:nth-child(4),
.non-teaching-table td:nth-child(4) {
  width: 14%;
}

.non-teaching-table th:nth-child(5),
.non-teaching-table td:nth-child(5) {
  width: 17%;
}

.non-teaching-table th:nth-child(6),
.non-teaching-table td:nth-child(6) {
  width: 16%;
}

.non-teaching-table th:nth-child(7),
.non-teaching-table td:nth-child(7) {
  width: 14%;
}

/* ========================================================= */
/* GLOBAL MIN-WIDTH FIX                                       */
/* ========================================================= */

.dashboard-shell *,
.neo-card * {
  min-width: 0;
}

.non-teaching-table {
  min-width: 0;
}

/* ========================================================= */
/* TABLET                                                     */
/* ========================================================= */

@media (max-width: 1024px) {
  .non-teaching-table th,
  .non-teaching-table td {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
  }

  .non-teaching-table {
    font-size: 0.875rem;
  }

  .non-teaching-table button {
    font-size: 0.7rem;

    padding-left: 0.45rem;
    padding-right: 0.45rem;
  }
}

/* ========================================================= */
/* MOBILE TABLE                                               */
/* ========================================================= */

@media (max-width: 768px) {
  /*
   * The card remains responsive.
   * The table becomes the horizontally scrollable
   * element.
   */

  .table-wrapper {
    width: 100%;
    max-width: 100%;

    overflow-x: auto;
    overflow-y: hidden;

    -webkit-overflow-scrolling: touch;
    overscroll-behavior-x: contain;

    scrollbar-width: thin;

    padding-bottom: 6px;
  }

  /*
   * Make the table wider than the phone.
   */

  .non-teaching-table {
    width: 980px;
    min-width: 980px;
    max-width: none;

    table-layout: fixed;

    font-size: 0.8rem;
  }

  /*
   * Mobile column widths.
   */

  .non-teaching-table th:nth-child(1),
  .non-teaching-table td:nth-child(1) {
    width: 110px;
  }

  .non-teaching-table th:nth-child(2),
  .non-teaching-table td:nth-child(2) {
    width: 180px;
  }

  .non-teaching-table th:nth-child(3),
  .non-teaching-table td:nth-child(3) {
    width: 130px;
  }

  .non-teaching-table th:nth-child(4),
  .non-teaching-table td:nth-child(4) {
    width: 140px;
  }

  .non-teaching-table th:nth-child(5),
  .non-teaching-table td:nth-child(5) {
    width: 180px;
  }

  .non-teaching-table th:nth-child(6),
  .non-teaching-table td:nth-child(6) {
    width: 160px;
  }

  .non-teaching-table th:nth-child(7),
  .non-teaching-table td:nth-child(7) {
    width: 120px;
  }

  /*
   * IMPORTANT:
   * Prevent words from being broken into individual
   * characters.
   */

  .non-teaching-table th,
  .non-teaching-table td {
    white-space: nowrap;

    word-break: normal;
    overflow-wrap: normal;

    padding-left: 0.65rem;
    padding-right: 0.65rem;
  }

  /*
   * Names can use multiple lines if needed.
   */

  .non-teaching-table td:nth-child(2) {
    white-space: normal;
    overflow-wrap: normal;
  }

  /*
   * Keep View / Edit / Delete in one row.
   */

  .non-teaching-table .action-buttons {
    display: flex;
    flex-wrap: nowrap;

    align-items: center;
    justify-content: center;

    gap: 0.35rem;

    white-space: nowrap;
  }

  .non-teaching-table button {
    flex-shrink: 0;

    font-size: 0.7rem;

    padding: 0.3rem 0.5rem;

    white-space: nowrap;
  }
}

/* ========================================================= */
/* SMALL PHONES                                               */
/* ========================================================= */

@media (max-width: 640px) {
  .table-wrapper {
    margin-left: 0;
    margin-right: 0;
  }

  .non-teaching-table {
    width: 980px;
    min-width: 980px;

    font-size: 0.75rem;
  }

  .non-teaching-table th,
  .non-teaching-table td {
    padding-top: 0.6rem;
    padding-bottom: 0.6rem;

    padding-left: 0.55rem;
    padding-right: 0.55rem;
  }

  .non-teaching-table button {
    font-size: 0.7rem;

    padding: 0.3rem 0.5rem;
  }
}

/* ========================================================= */
/* VERY SMALL PHONES                                          */
/* ========================================================= */

@media (max-width: 400px) {
  .non-teaching-table {
    width: 980px;
    min-width: 980px;
  }

  .table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
}
</style>