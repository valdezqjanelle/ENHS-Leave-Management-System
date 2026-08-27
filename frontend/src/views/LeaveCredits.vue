<template>
  <div class="dashboard-shell p-8 min-h-screen space-y-8">

    <!-- ========================================================= -->
    <!-- HEADER -->
    <!-- ========================================================= -->
    <div class="neo-card p-6">

      <h2 class="text-2xl font-bold text-white">
        Leave Credits
      </h2>

      <p class="text-gray-400 mt-1">
        Record earned leave credits for employees.
      </p>

    </div>


    <!-- ========================================================= -->
    <!-- FORM -->
    <!-- ========================================================= -->
    <div class="neo-card p-6">

      <div
        class="grid grid-cols-1 md:grid-cols-2 gap-6"
      >

        <!-- Employee -->
        <div class="min-w-0">

          <label
            class="block text-sm font-medium text-white mb-2"
          >
            Employee
          </label>

          <select
            v-model="form.employee_id"
            class="form-control"
          >

            <option value="">
              Select Employee
            </option>

            <option
              v-for="employee in employees"
              :key="employee.employee_id"
              :value="employee.employee_id"
            >
              {{ employee.last_name }},
              {{ employee.first_name }}
            </option>

          </select>

        </div>


        <!-- Credit Type -->
        <div class="min-w-0">

          <label
            class="block text-sm font-medium text-white mb-2"
          >
            Credit Type
          </label>

          <select
            v-model="form.credit_type"
            class="form-control"
          >

            <option value="">
              Select Credit Type
            </option>

            <option value="Service Credits">
              Service Credits
            </option>

            <option value="Vacation">
              Vacation Leave
            </option>

            <option value="Sick">
              Sick Leave
            </option>

          </select>

        </div>


        <!-- Activity -->
        <div class="min-w-0">

          <label
            class="block text-sm font-medium text-white mb-2"
          >
            Activity Name
          </label>

          <input
            v-model="form.activity_name"
            type="text"
            class="form-control"
            placeholder="Example: Overtime Duty"
          />

        </div>


        <!-- Hours -->
        <div class="min-w-0">

          <label
            class="block text-sm font-medium text-white mb-2"
          >
            Hours Rendered
          </label>

          <input
            v-model="form.hours_rendered"
            type="number"
            min="0"
            step="0.25"
            class="form-control"
          />

        </div>


        <!-- Equivalent Leave Days -->
        <div class="min-w-0">

          <label
            class="block text-sm font-medium text-white mb-2"
          >
            Equivalent Leave Days
          </label>

          <input
            v-model="form.equivalent_leave_days"
            type="number"
            min="0"
            step="0.25"
            class="form-control"
          />

        </div>

      </div>


      <!-- Save Button -->
      <div class="mt-6">

        <button
          @click="saveCredit"
          type="button"
          class="primary-button"
        >
          Save Credit
        </button>

      </div>

    </div>

    <!-- Apply Credit Modal -->
    <div
      v-if="showApplyModal"
      class="modal-overlay"
      @click.self="closeApplyModal"
    >

        <h2 class="text-xl font-semibold text-white">
          Apply Leave Credit
        </h2>

        <p class="text-gray-300 mt-3">
          Choose where this credit should be applied. This will update the
          employee's leave balance.
        </p>

        <!-- Credit Details -->
        <div
          v-if="selectedCredit"
          class="credit-details"
        >

          <!-- Employee -->
          <div class="detail-row">

            <span class="detail-label">
              Employee:
            </span>

            <span class="detail-value">
              {{ selectedCredit.employee?.last_name }},
              {{ selectedCredit.employee?.first_name }}
            </span>

          </div>


          <!-- Credit Type -->
          <div class="detail-row">

            <span class="detail-label">
              Credit Type:
            </span>

            <span class="detail-value">
              {{ selectedCredit.credit_type }}
            </span>

          </div>


          <!-- Activity -->
          <div class="detail-row">

            <span class="detail-label">
              Activity:
            </span>

            <span class="detail-value">
              {{ selectedCredit.activity_name }}
            </span>

          </div>


          <!-- Equivalent Days -->
          <div class="detail-row">

            <span class="detail-label">
              Equivalent Days:
            </span>

            <span class="detail-value">
              {{ selectedCredit.equivalent_leave_days }}
            </span>

          </div>

          <div>
            <span class="font-medium text-gray-400">Available Credit:</span>
            {{ availableCreditDays.toFixed(2) }} days
          </div>
        </div>

        <div v-if="selectedCredit" class="mt-4 space-y-4 text-sm text-white">
          <template v-if="selectedCredit.credit_type === 'Service Credits'">
            <div>
              <label class="block font-medium text-white mb-2">Apply To</label>
              <select
                v-model="applyForm.leave_type"
                @change="handleApplyTypeChange"
                :disabled="applyForm.split"
                class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:opacity-60"
              >
                <option value="Service Credits">Service Credits</option>
                <option value="Vacation">Vacation Leave</option>
                <option value="Sick">Sick Leave</option>
              </select>
            </div>

            <label
              v-if="applyForm.leave_type !== 'Service Credits'"
              class="flex items-center gap-2 text-white"
            >
              <input
                v-model="applyForm.split"
                type="checkbox"
                class="rounded border-slate-700 bg-[#0B1420] text-blue-600 focus:ring-blue-500"
              >
              Split Application
            </label>

            <div v-if="!applyForm.split">
              <label class="block font-medium text-white mb-2">Days to Apply</label>
              <input
                v-model.number="applyForm.days"
                type="number"
                min="0"
                step="0.25"
                class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
            </div>

            <div v-else class="space-y-3">
              <div>
                <label class="block font-medium text-white mb-2">Vacation Leave Allocation</label>
                <input
                  v-model.number="applyForm.vacation_days"
                  type="number"
                  min="0"
                  step="0.25"
                  class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>

              <div>
                <label class="block font-medium text-white mb-2">Sick Leave Allocation</label>
                <input
                  v-model.number="applyForm.sick_days"
                  type="number"
                  min="0"
                  step="0.25"
                  class="w-full border border-slate-700 rounded-lg px-3 py-2 text-white bg-[#0B1420] focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
              </div>
            </div>

            <div class="border-t border-slate-800 pt-3 space-y-1">
              <div class="flex justify-between">
                <span class="text-gray-400">Total Applied:</span>
                <span>{{ totalApplied.toFixed(2) }} days</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Remaining:</span>
                <span>{{ remainingCredit.toFixed(2) }} days</span>
              </div>
            </div>
          </template>

          <template v-else>
            <div>
              <label class="block font-medium text-white mb-2">Apply To</label>
              <input
                :value="selectedCredit.credit_type === 'Vacation' ? 'Vacation Leave' : 'Sick Leave'"
                type="text"
                readonly
                class="w-full border border-slate-700 rounded-lg px-3 py-2 text-gray-400 bg-[#0B1420]"
              >
            </div>

            <div>
              <label class="block font-medium text-white mb-2">Days to Apply</label>
              <input
                :value="selectedCredit.equivalent_leave_days"
                type="number"
                readonly
                class="w-full border border-slate-700 rounded-lg px-3 py-2 text-gray-400 bg-[#0B1420]"
              >
            </div>
          </template>

          <p v-if="validationMessage" class="text-red-400" role="alert">
            {{ validationMessage }}
          </p>
        </div>


        <!-- Modal Buttons -->
        <div class="modal-actions">

          <button
            @click="closeApplyModal"
            type="button"
            class="cancel-button"
          >
            Cancel
          </button>

          <button
            @click="confirmApplyCredit"
            type="button"
            class="confirm-button"
          >
            Apply Credit
          </button>

        </div>

      </div>

    </div>
    

  
</template>


<script setup lang="ts">
import { computed, ref, onMounted } from "vue";

import {
  getEmployees,
  getLeaveCredits,
  addLeaveCredit,
  applyLeaveCredit
} from "@/services/leaveCredit";

/* ============================================================
   TYPES
   ============================================================ */

interface Employee {
  employee_id: number;
  first_name: string;
  last_name: string;
}

interface LeaveCredit {
  credits_id: number;
  employee_id: number;
  credit_type: string;
  status: string;
  activity_name: string;
  hours_rendered: number;
  equivalent_leave_days: number;
  date_recorded: string;
  employee: Employee;
}

/* ============================================================
   DATA
   ============================================================ */

const employees = ref<Employee[]>([]);
const credits = ref<LeaveCredit[]>([]);

const showApplyModal = ref(false);
const selectedCredit = ref<LeaveCredit | null>(null);

const applyForm = ref({
  leave_type: "Vacation",
  days: 0,
  split: false,
  vacation_days: 0,
  sick_days: 0
});

const validationMessage = ref("");

const form = ref({
  employee_id: "",
  activity_name: "",
  hours_rendered: "",
  equivalent_leave_days: "",
  credit_type: ""
});

/* ============================================================
   APPLY FORM HELPERS
   ============================================================ */

const resetApplyForm = () => {
  applyForm.value = {
    leave_type: "Vacation",
    days: 0,
    split: false,
    vacation_days: 0,
    sick_days: 0
  };
  validationMessage.value = "";
};

const handleApplyTypeChange = () => {
  if (applyForm.value.leave_type === "Service Credits") {
    applyForm.value.split = false;
  }
};

const availableCreditDays = computed(() =>
  Number(selectedCredit.value?.equivalent_leave_days ?? 0)
);

const totalApplied = computed(() => {
  if (!selectedCredit.value) return 0;
  if (selectedCredit.value.credit_type !== "Service Credits") {
    return availableCreditDays.value;
  }

  return applyForm.value.split
    ? Number(applyForm.value.vacation_days || 0) + Number(applyForm.value.sick_days || 0)
    : Number(applyForm.value.days || 0);
});

const remainingCredit = computed(() =>
  availableCreditDays.value - totalApplied.value
);

/* ============================================================
   LOAD EMPLOYEES
   ============================================================ */

const loadEmployees = async () => {
  try {
    employees.value = await getEmployees();
  } catch (error) {
    console.error("Failed to load employees.", error);
  }
};

/* ============================================================
   LOAD CREDITS
   ============================================================ */

const loadCredits = async () => {
  try {
    credits.value = await getLeaveCredits();
  } catch (error) {
    console.error("Failed to load leave credits.", error);
  }
};

/* ============================================================
   SAVE CREDIT
   ============================================================ */

const saveCredit = async () => {
  try {
    console.log("Sending credit data:", form.value);

    await addLeaveCredit(form.value);

    alert("Leave credit added successfully!");

    form.value = {
      employee_id: "",
      activity_name: "",
      hours_rendered: "",
      equivalent_leave_days: "",
      credit_type: ""
    };

    await loadCredits();
  } catch (error: any) {
    console.error("STATUS:", error.response?.status);
    console.error("SERVER RESPONSE:", error.response?.data);

    alert(
      error.response?.data?.message ??
      JSON.stringify(
        error.response?.data?.errors ??
        "Unable to save leave credit."
      )
    );
  }
};

/* ============================================================
   OPEN APPLY MODAL
   ============================================================ */

const applyCredit = (id: number) => {
  const credit = credits.value.find((credit) => credit.credits_id === id);

  if (!credit) {
    return;
  }

  selectedCredit.value = credit;
  resetApplyForm();

  if (credit.credit_type === "Vacation" || credit.credit_type === "Sick") {
    applyForm.value.leave_type = credit.credit_type;
    applyForm.value.days = credit.equivalent_leave_days;
  }

  showApplyModal.value = true;
};

/* ============================================================
   CLOSE APPLY MODAL
   ============================================================ */

const closeApplyModal = () => {
  showApplyModal.value = false;
  selectedCredit.value = null;
  resetApplyForm();
};

/* ============================================================
   CONFIRM APPLY
   ============================================================ */

const confirmApplyCredit = async () => {
  if (!selectedCredit.value) {
    return;
  }

  const total = totalApplied.value;

  if (!Number.isFinite(total) || total <= 0) {
    validationMessage.value = "Total applied days must be greater than 0.";
    return;
  }

  if (total > availableCreditDays.value) {
    validationMessage.value = "Total applied days cannot exceed the available credit.";
    return;
  }

  try {
    await applyLeaveCredit({
      credits_id: selectedCredit.value.credits_id,
      leave_type: applyForm.value.leave_type,
      days: applyForm.value.split ? total : applyForm.value.days,
      split: applyForm.value.split,
      vacation_days: applyForm.value.vacation_days,
      sick_days: applyForm.value.sick_days
    });

    alert("Leave credit applied successfully!");

    closeApplyModal();

    await loadCredits();
  } catch (error) {
    console.error(error);
    alert("Unable to apply leave credit.");
  }
};

/* ============================================================
   FORMAT DATE
   ============================================================ */

const formatDate = (date: string) => {
  if (!date) {
    return "—";
  }

  const parsedDate = new Date(date);

  if (Number.isNaN(parsedDate.getTime())) {
    return date;
  }

  return parsedDate.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric"
  });
};

/* ============================================================
   INITIAL LOAD
   ============================================================ */

onMounted(() => {
  loadEmployees();
  loadCredits();
});
</script>


<style scoped>

/* ============================================================
   PAGE
   ============================================================ */

.dashboard-shell {

  background: #080d14;

  width: 100%;

  min-height: 100vh;

}


/* ============================================================
   CARDS
   ============================================================ */

.neo-card {

  background: #111d2e;

  border: 1px solid #1e293b;

  border-radius: 1.4rem;

  box-shadow:
    0 10px 22px
    rgba(15, 23, 42, 0.04);

  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;

  min-width: 0;

}


.neo-card:hover {

  box-shadow:
    0 14px 26px
    rgba(15, 23, 42, 0.08);

}


/* ============================================================
   FORM CONTROLS
   ============================================================ */

.form-control {

  width: 100%;

  min-width: 0;

  border: 1px solid #334155;

  border-radius: 0.5rem;

  padding:
    0.5rem
    0.75rem;

  color: white;

  background: #0b1420;

  outline: none;

  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease;

}


.form-control::placeholder {

  color: #6b7280;

}


.form-control:focus {

  border-color: #3b82f6;

  box-shadow:
    0 0 0 2px
    rgba(59, 130, 246, 0.25);

}


.form-control option {

  background: #0b1420;

  color: white;

}


/* ============================================================
   BUTTONS
   ============================================================ */

.primary-button {

  display: inline-flex;

  align-items: center;

  justify-content: center;

  min-height: 40px;

  padding:
    0.5rem
    1.5rem;

  background: #2563eb;

  color: white;

  border-radius: 0.5rem;

  font-weight: 600;

  white-space: nowrap;

  transition:
    background-color 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;

}


.primary-button:hover {

  background: #1d4ed8;

  transform: translateY(-1px);

}


/* ============================================================
   TABLE WRAPPER
   ============================================================ */

.table-wrapper {

  width: 100%;

  max-width: 100%;

  overflow-x: auto;

  overflow-y: hidden;

  -webkit-overflow-scrolling: touch;

}


/* ============================================================
   TABLE
   ============================================================ */

.credit-table {

  width: 100%;

  min-width: 850px;

  border-collapse: collapse;

  table-layout: auto;

}


/* ============================================================
   TABLE HEADER
   ============================================================ */

.credit-table thead {

  background: #0b1420;

}


.credit-table th {

  padding:
    0.75rem
    1rem;

  color: white;

  font-size: 0.875rem;

  font-weight: 600;

  text-align: left;

  white-space: nowrap;

  border-bottom:
    1px solid #1e293b;

}


/* ============================================================
   TABLE BODY
   ============================================================ */

.credit-table tbody tr {

  border-top:
    1px solid #1e293b;

  transition:
    background-color 0.2s ease,
    border-color 0.2s ease;

}


.credit-table tbody tr:hover {

  background: rgba(
    255,
    255,
    255,
    0.05
  );

}


.credit-table td {

  padding:
    0.75rem
    1rem;

  vertical-align: middle;

  color: white;

  font-size: 0.875rem;

}


/* ============================================================
   EMPLOYEE
   ============================================================ */

.employee-cell {

  min-width: 150px;

  max-width: 220px;

}


.employee-name {

  display: block;

  color: white;

  font-weight: 500;

  overflow-wrap: anywhere;

}


/* ============================================================
   TABLE TEXT
   ============================================================ */

.table-primary {

  color: white;

  overflow-wrap: anywhere;

}


/* ============================================================
   CREDIT TYPES
   ============================================================ */

.credit-service {

  color: #c084fc;

  font-weight: 600;

  white-space: nowrap;

}


.credit-vacation {

  color: #60a5fa;

  font-weight: 600;

  white-space: nowrap;

}


.credit-sick {

  color: #4ade80;

  font-weight: 600;

  white-space: nowrap;

}


.credit-other {

  color: #9ca3af;

  font-weight: 600;

}


/* ============================================================
   STATUS
   ============================================================ */

.status-pending {

  display: inline-flex;

  align-items: center;

  justify-content: center;

  padding:
    0.25rem
    0.75rem;

  border-radius: 9999px;

  background:
    rgba(234, 179, 8, 0.20);

  color: #facc15;

  font-size: 0.75rem;

  white-space: nowrap;

}


.status-applied {

  display: inline-flex;

  align-items: center;

  justify-content: center;

  padding:
    0.25rem
    0.75rem;

  border-radius: 9999px;

  background:
    rgba(34, 197, 94, 0.20);

  color: #4ade80;

  font-size: 0.75rem;

  white-space: nowrap;

}


/* ============================================================
   ACTION
   ============================================================ */

.action-column {

  text-align: center !important;

}


.action-cell {

  text-align: center;

  white-space: nowrap;

}


.apply-button {

  display: inline-flex;

  align-items: center;

  justify-content: center;

  padding:
    0.5rem
    1rem;

  background: #2563eb;

  color: white;

  border-radius: 0.5rem;

  font-size: 0.875rem;

  font-weight: 600;

  white-space: nowrap;

  transition:
    background-color 0.2s ease,
    transform 0.2s ease;

}


.apply-button:hover {

  background: #1d4ed8;

  transform: translateY(-1px);

}


.applied-text {

  color: #4ade80;

  font-weight: 600;

  white-space: nowrap;

}


/* ============================================================
   EMPTY STATE
   ============================================================ */

.empty-state {

  text-align: center;

  color: #9ca3af;

}


/* ============================================================
   MODAL
   ============================================================ */

.modal-overlay {

  position: fixed;

  inset: 0;

  z-index: 50;

  display: flex;

  align-items: center;

  justify-content: center;

  padding: 1rem;

  background:
    rgba(0, 0, 0, 0.60);

  overflow-y: auto;

}


.modal-card {

  width: 100%;

  max-width: 28rem;

  max-height: calc(100vh - 2rem);

  overflow-y: auto;

  padding: 1.5rem;

  background: #111d2e;

  border: 1px solid #1e293b;

  border-radius: 1.4rem;

  box-shadow:
    0 20px 40px
    rgba(0, 0, 0, 0.35);

}


/* ============================================================
   CREDIT DETAILS
   ============================================================ */

.credit-details {

  margin-top: 1rem;

  padding: 1rem;

  background: #0b1420;

  border:
    1px solid #1e293b;

  border-radius: 0.5rem;

}


.detail-row {

  display: flex;

  justify-content: space-between;

  align-items: flex-start;

  gap: 1rem;

  padding:
    0.25rem 0;

}


.detail-label {

  color: #9ca3af;

  font-weight: 500;

  flex-shrink: 0;

}


.detail-value {

  color: white;

  text-align: right;

  overflow-wrap: anywhere;

}


/* ============================================================
   MODAL ACTIONS
   ============================================================ */

.modal-actions {

  display: flex;

  justify-content: flex-end;

  gap: 0.75rem;

  margin-top: 1.5rem;

  flex-wrap: wrap;

}


.cancel-button {

  padding:
    0.5rem
    1rem;

  background: #334155;

  color: white;

  border-radius: 0.5rem;

  white-space: nowrap;

  transition:
    background-color 0.2s ease,
    transform 0.2s ease;

}


.cancel-button:hover {

  background: #475569;

  transform: translateY(-1px);

}


.confirm-button {

  padding:
    0.5rem
    1rem;

  background: #2563eb;

  color: white;

  border-radius: 0.5rem;

  white-space: nowrap;

  transition:
    background-color 0.2s ease,
    transform 0.2s ease;

}


.confirm-button:hover {

  background: #1d4ed8;

  transform: translateY(-1px);

}


/* ============================================================
   GENERAL TEXT BEHAVIOR
   ============================================================ */

.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card button {

  letter-spacing: -0.01em;

}


button {

  transition:
    background-color 0.2s ease,
    transform 0.2s ease,
    box-shadow 0.2s ease;

}


/* ============================================================
   ZOOM / SMALL VIEWPORT BEHAVIOR
   ============================================================ */

/*
  Browser zoom changes the CSS viewport width.

  These rules make the page behave more like the Dashboard
  when the viewport becomes narrow because of zoom.
*/


@media (max-width: 768px) {

  .dashboard-shell {

    padding:
      1.5rem;

  }


  .neo-card {

    border-radius:
      1.1rem;

  }


  .neo-card.p-6 {

    padding:
      1.25rem;

  }


  .modal-card {

    padding:
      1.25rem;

  }

}


/* ============================================================
   VERY NARROW VIEWPORT
   ============================================================ */

@media (max-width: 640px) {

  .dashboard-shell {

    padding:
      1rem;

  }


  .neo-card.p-6 {

    padding:
      1rem;

  }


  .primary-button {

    width: 100%;

  }


  .modal-actions {

    flex-direction: column;

  }


  .modal-actions button {

    width: 100%;

  }


  .detail-row {

    flex-direction: column;

    gap: 0.15rem;

  }


  .detail-value {

    text-align: left;

  }

}

</style>