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
                  {{ assignmentSummary(record, "subject") }}
                </td>

                <td class="px-3 py-4 text-white">
                  {{ assignmentSummary(record, "class") }}
                </td>

                <td class="px-3 py-4 text-white">
                  {{ record.teaching_hours ?? "-" }}
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
              <div class="md:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                  <label class="text-sm font-semibold text-gray-800">Teaching Assignments</label>
                  <button type="button" @click="addAssignment" class="px-4 py-2 rounded-lg bg-blue-100 text-blue-700 font-medium">+ Add Assignment</button>
                </div>

                <div v-for="(assignment,index) in form.assignments" :key="index" class="grid grid-cols-1 md:grid-cols-2 gap-3 border rounded-xl p-4 bg-gray-50">
                  <select v-model.number="assignment.grade_level_id" @change="assignment.section_id=null" class="w-full border rounded-lg px-3 py-2 text-gray-800">
                    <option :value="null">Select grade level</option>
                    <option v-for="grade in activeGradeLevels" :key="grade.grade_level_id" :value="grade.grade_level_id">{{ grade.grade_name }} ({{ grade.level }})</option>
                  </select>
                  <select v-model.number="assignment.section_id" class="w-full border rounded-lg px-3 py-2 text-gray-800">
                    <option :value="null">No section / Select section</option>
                    <option v-for="section in sectionsFor(assignment.grade_level_id)" :key="section.section_id" :value="section.section_id">{{ section.section_name }}</option>
                  </select>
                  <select v-model.number="assignment.subject_id" class="w-full border rounded-lg px-3 py-2 text-gray-800">
                    <option :value="null">Select subject / specialization</option>
                    <option v-for="subject in subjectsForSelectedEmployee" :key="subject.subject_id" :value="subject.subject_id">{{ subject.subject_name }}</option>
                  </select>
                  <input v-model.trim="assignment.school_year" placeholder="2026-2027" pattern="[0-9]{4}-[0-9]{4}" class="w-full border rounded-lg px-3 py-2 text-gray-800" />
                  <label class="flex items-center gap-2 text-sm text-gray-700"><input v-model="assignment.is_advisory" type="checkbox" /> Advisory class</label>
                  <input v-model.number="assignment.teaching_hours" type="number" min="0" max="99.99" step="0.01" placeholder="Hours" class="w-full border rounded-lg px-3 py-2 text-gray-800" />
                  <button v-if="form.assignments.length>1" type="button" @click="form.assignments.splice(index,1)" class="text-left text-sm font-medium text-red-600">Remove assignment</button>
                </div>
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

          <div class="md:col-span-2">
            <span class="font-semibold">Teaching Assignments</span>
            <div v-if="selectedRecord.assignments?.length" class="mt-2 space-y-2">
              <div v-for="assignment in selectedRecord.assignments" :key="assignment.assignment_id" class="rounded-lg border border-slate-600 p-3">
                <p>{{ assignment.subject?.subject_name || "Subject" }} — {{ assignment.grade_level?.grade_name || "Grade" }}<span v-if="assignment.section">, {{ assignment.section.section_name }}</span></p>
                <p class="text-xs opacity-80">S.Y. {{ assignment.school_year }}<span v-if="assignment.is_advisory"> · Advisory class</span><span v-if="assignment.teaching_hours !== null"> · {{ assignment.teaching_hours }} hour(s)</span></p>
              </div>
            </div>
            <p v-else class="mt-1">{{ selectedRecord.subject_specialization || "-" }} — {{ selectedRecord.grade_level_handled || "-" }}</p>
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
import { getTeachingSetup } from "../services/teachingSetup";

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
  assignments?: any[];

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
const teachingSetup = ref<any>({ grade_levels: [], subjects: [] });

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
  assignments: [] as any[],
});

const currentSchoolYear = () => {
  const now = new Date();
  const start = now.getMonth() + 1 >= 6 ? now.getFullYear() : now.getFullYear() - 1;
  return `${start}-${start + 1}`;
};

const emptyAssignment = () => ({ grade_level_id: null, section_id: null, subject_id: null, school_year: currentSchoolYear(), is_advisory: false, teaching_hours: null });
const activeGradeLevels = computed(() => teachingSetup.value.grade_levels.filter((g:any)=>g.is_active));
const selectedEmployee = computed(() => employees.value.find(e=>e.employee_id===form.value.employee_id));
const subjectsForSelectedEmployee = computed(() => {
  const departmentId = selectedEmployee.value?.department?.department_id;
  return teachingSetup.value.subjects.filter((s:any)=>s.is_active && (!s.department_id || s.department_id===departmentId));
});
const sectionsFor = (gradeId:number|null) => teachingSetup.value.grade_levels.find((g:any)=>g.grade_level_id===gradeId)?.sections?.filter((s:any)=>s.is_active) ?? [];
const addAssignment = () => form.value.assignments.push(emptyAssignment());

const assignmentSummary = (record: TeachingRecord, type: "subject" | "class") => {
  if (!record.assignments?.length) {
    return type === "subject"
      ? record.subject_specialization || "-"
      : record.advisory_class || record.grade_level_handled || "-";
  }

  return record.assignments
    .map((assignment: any) => {
      if (type === "subject") {
        return assignment.subject?.subject_name;
      }

      const grade = assignment.grade_level?.grade_name || "";
      const section = assignment.section?.section_name || "";
      return [grade, section].filter(Boolean).join(" - ");
    })
    .filter(Boolean)
    .filter((value: string, index: number, values: string[]) => values.indexOf(value) === index)
    .join(", ") || "-";
};

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

    const assignments = (record.assignments || [])
      .map((assignment: any) => [
        assignment.subject?.subject_name,
        assignment.grade_level?.grade_name,
        assignment.section?.section_name,
        assignment.school_year,
      ].filter(Boolean).join(" "))
      .join(" ")
      .toLowerCase();

    return (
      name.includes(keyword) ||
      code.includes(keyword) ||
      position.includes(keyword) ||
      specialization.includes(keyword) ||
      grade.includes(keyword) ||
      advisory.includes(keyword) ||
      assignments.includes(keyword)
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

    console.log(
      "Teaching Personnel Records:",
      records.value
    );
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
    console.error(
      "Failed to load employees:",
      error
    );
  }
};

const loadTeachingSetup = async () => {
  try { teachingSetup.value = await getTeachingSetup(); }
  catch (error) { console.error("Failed to load teaching setup:", error); }
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
    assignments: [emptyAssignment()],
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
    assignments: record.assignments?.length
      ? record.assignments.map((a:any)=>({
          grade_level_id:a.grade_level_id, section_id:a.section_id,
          subject_id:a.subject_id, school_year:a.school_year,
          is_advisory:Boolean(a.is_advisory), teaching_hours:a.teaching_hours === null ? null : Number(a.teaching_hours),
        }))
      : [emptyAssignment()],
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

    if (!form.value.assignments.length || form.value.assignments.some((a:any)=>!a.grade_level_id || !a.subject_id || !/^\d{4}-\d{4}$/.test(a.school_year))) {
      alert("Complete the grade level, subject, and school year for every assignment."); return;
    }

    const first = form.value.assignments[0];
    const grade = teachingSetup.value.grade_levels.find((g:any)=>g.grade_level_id===first.grade_level_id);
    const section = sectionsFor(first.grade_level_id).find((s:any)=>s.section_id===first.section_id);
    const subject = teachingSetup.value.subjects.find((s:any)=>s.subject_id===first.subject_id);

    const payload = {
      employee_id: form.value.employee_id,

      subject_specialization:
        subject?.subject_name || "Teaching Assignment",

      grade_level_handled:
        grade?.grade_name || "Assigned Grade",

      advisory_class:
        first.is_advisory && section ? `${grade?.grade_name} - ${section.section_name}` : null,

      teaching_load:
        form.value.teaching_load || null,

      teaching_hours:
        form.value.teaching_hours,
      assignments: form.value.assignments,
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
    await deleteTeachingPersonnelRecord(
      record.teaching_record_id
    );

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
    loadTeachingSetup(),
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

/*
 * IMPORTANT:
 * Desktop remains unchanged.
 *
 * On mobile the wrapper becomes the horizontal
 * scrolling area instead of forcing the table
 * to squeeze into the phone width.
 */

.table-wrapper {
  width: 100%;
  max-width: 100%;

  overflow-x: visible;
  overflow-y: hidden;

  box-sizing: border-box;
}

/* ========================================================= */
/* TEACHING TABLE - DESKTOP                                   */
/* ========================================================= */

.teaching-table {
  width: 100%;
  max-width: 100%;

  table-layout: fixed;
  border-collapse: collapse;
}

.teaching-table th,
.teaching-table td {
  overflow-wrap: normal;
  word-break: normal;
}

/* Keep existing desktop sizing */

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

/* ========================================================= */
/* GLOBAL MIN-WIDTH FIX                                       */
/* ========================================================= */

.dashboard-shell *,
.neo-card * {
  min-width: 0;
}

/*
 * Do NOT allow the table itself to shrink on mobile.
 */

.teaching-table {
  min-width: 0;
}

/* ========================================================= */
/* TABLET                                                     */
/* ========================================================= */

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

/* ========================================================= */
/* MOBILE TABLE                                               */
/* ========================================================= */

@media (max-width: 768px) {
  /*
   * The card stays within the phone.
   * Only the table becomes wider and scrolls.
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
   * Give the table enough room for all columns.
   * This prevents headers from becoming individual
   * letters.
   */

  .teaching-table {
    width: 980px;
    min-width: 980px;
    max-width: none;

    table-layout: fixed;

    font-size: 0.8rem;
  }

  /*
   * Mobile column widths.
   *
   * These are deliberately wider than the phone.
   * The user can swipe horizontally.
   */

  .teaching-table th:nth-child(1),
  .teaching-table td:nth-child(1) {
    width: 110px;
  }

  .teaching-table th:nth-child(2),
  .teaching-table td:nth-child(2) {
    width: 180px;
  }

  .teaching-table th:nth-child(3),
  .teaching-table td:nth-child(3) {
    width: 130px;
  }

  .teaching-table th:nth-child(4),
  .teaching-table td:nth-child(4) {
    width: 180px;
  }

  .teaching-table th:nth-child(5),
  .teaching-table td:nth-child(5) {
    width: 140px;
  }

  .teaching-table th:nth-child(6),
  .teaching-table td:nth-child(6) {
    width: 120px;
  }

  .teaching-table th:nth-child(7),
  .teaching-table td:nth-child(7) {
    width: 120px;
  }

  /*
   * Never break headers or normal table values
   * into individual characters.
   */

  .teaching-table th,
  .teaching-table td {
    white-space: nowrap;

    word-break: normal;
    overflow-wrap: normal;

    padding-left: 0.65rem;
    padding-right: 0.65rem;
  }

  /*
   * Employee names can still be long, but they
   * remain inside their proper column.
   */

  .teaching-table td:nth-child(2) {
    white-space: normal;
    overflow-wrap: normal;
  }

  /*
   * Keep View / Edit / Delete together.
   */

  .teaching-table .action-buttons {
    display: flex;
    flex-wrap: nowrap;

    align-items: center;
    justify-content: center;

    gap: 0.35rem;

    white-space: nowrap;
  }

  .teaching-table button {
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

  .teaching-table {
    width: 980px;
    min-width: 980px;

    font-size: 0.75rem;
  }

  .teaching-table th,
  .teaching-table td {
    padding-top: 0.6rem;
    padding-bottom: 0.6rem;

    padding-left: 0.55rem;
    padding-right: 0.55rem;
  }

  .teaching-table button {
    font-size: 0.7rem;

    padding: 0.3rem 0.5rem;
  }
}

/* ========================================================= */
/* VERY SMALL PHONES                                          */
/* ========================================================= */

@media (max-width: 400px) {
  .teaching-table {
    width: 980px;
    min-width: 980px;
  }

  .table-wrapper {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
}
</style>
