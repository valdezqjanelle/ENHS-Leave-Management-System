<template>
  <div class="records-shell p-8 min-h-screen space-y-6">
    <div class="neo-card p-8">
      <div class="flex flex-col sm:flex-row sm:items-center gap-6">
        <div
          class="w-28 h-28 bg-blue-600 rounded-full flex items-center justify-center text-white text-4xl font-semibold shrink-0"
        >
          {{ initials }}
        </div>

        <div class="flex-1 min-w-0">
          <h3 class="text-2xl font-semibold text-[var(--text)] break-words">
            {{ fullName }}
          </h3>

          <p class="text-sm text-[var(--text-muted)] mt-1">
            {{ employee.position || "Employee" }}
          </p>

          <span
            v-if="employee.employment_status"
            :class="
              normalizeEmploymentStatus(employee.employment_status) === 'active'
                ? 'bg-green-50 text-green-700'
                : 'bg-slate-100 text-slate-600'
            "
            class="inline-block mt-3 px-3 py-1 rounded-full text-xs"
          >
            {{ employee.employment_status }}
          </span>
        </div>

        <div
          class="sm:text-right sm:pl-6 sm:border-l border-t sm:border-t-0 border-[#cbd8e8] pt-5 sm:pt-0"
        >
          <p class="text-xs text-[var(--text-muted)]">
            Employee Code
          </p>

          <p class="text-sm text-[var(--text)] font-mono mt-1">
            {{ employee.employee_code || "—" }}
          </p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
      <div class="neo-card p-6">
        <div class="flex items-center gap-2 mb-5">
          <div
            class="w-8 h-8 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center shrink-0"
          >
            <Building2 class="w-4 h-4" />
          </div>

          <div>
            <h3 class="text-lg font-medium text-[var(--text)]">
              Employee Information
            </h3>

            <p class="text-xs text-[var(--text-muted)]">
              These details are managed by the administrator.
            </p>
          </div>
        </div>

        <div
          class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-5 border border-[#c8d8eb] rounded-xl p-5"
        >
          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Employee Code
            </p>

            <p class="font-medium text-[var(--text)] mt-1 break-words">
              {{ employee.employee_code || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Email
            </p>

            <p class="font-medium text-[var(--text)] mt-1 break-all">
              {{ employee.email || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Personnel Type
            </p>

            <p class="font-medium text-[var(--text)] mt-1 break-words">
              {{ employee.personnel_type || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Employment Category
            </p>

            <p class="font-medium text-[var(--text)] mt-1 break-words">
              {{ employee.employment_category || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Department
            </p>

            <p class="font-medium text-[var(--text)] mt-1 break-words">
              {{ employee.department_name || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Position
            </p>

            <p class="font-medium text-[var(--text)] mt-1 break-words">
              {{ employee.position || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Level
            </p>

            <p class="font-medium text-[var(--text)] mt-1">
              {{ employee.level || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Supervisor
            </p>

            <p class="font-medium text-[var(--text)] mt-1 break-words">
              {{ supervisorName }}
            </p>
          </div>

          <!-- FIX: use resolvedSalaryGrade instead of employee.salary_grade directly,
               so the value still shows even if the profile API omits it but returns position_id -->
          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Salary Grade
            </p>

            <p class="font-medium text-[var(--text)] mt-1">
              {{ resolvedSalaryGrade || "—" }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Salary Step
            </p>

            <p class="font-medium text-[var(--text)] mt-1">
              {{
                employee.salary_step
                  ? `Step ${employee.salary_step}`
                  : "—"
              }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Current Salary
            </p>

            <p class="font-medium text-[var(--text)] mt-1">
              {{ formattedSalary }}
            </p>
          </div>

          <div>
            <p class="text-sm text-[var(--text-muted)]">
              Date Hired
            </p>

            <p class="font-medium text-[var(--text)] mt-1">
              {{ formattedDateHired }}
            </p>
          </div>

          <div class="sm:col-span-2">
            <p class="text-sm text-[var(--text-muted)]">
              Employment Status
            </p>

            <span
              :class="
                normalizeEmploymentStatus(employee.employment_status) === 'active'
                  ? 'bg-green-50 text-green-700'
                  : 'bg-slate-100 text-slate-600'
              "
              class="inline-block mt-1 px-3 py-1 rounded-full text-xs"
            >
              {{ employee.employment_status || "—" }}
            </span>
          </div>
        </div>
      </div>

      <form
        @submit.prevent="updateProfile"
        class="neo-card p-6"
      >
        <div class="flex items-center justify-between gap-2 mb-5">
          <div class="flex items-center gap-2">
            <div
              class="w-8 h-8 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center shrink-0"
            >
              <User class="w-4 h-4" />
            </div>

            <h3 class="text-lg font-medium text-[var(--text)]">
              Personal Information
            </h3>
          </div>

          <button
            v-if="!isEditing"
            type="button"
            @click="startEdit"
            class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition"
          >
            <Pencil class="w-3.5 h-3.5" />
            Edit
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              First Name
            </label>

            <input
              v-if="isEditing"
              v-model="employee.first_name"
              type="text"
              placeholder="Enter first name"
              class="profile-input"
            />

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1 break-words"
            >
              {{ employee.first_name || "—" }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              Middle Name
            </label>

            <input
              v-if="isEditing"
              v-model="employee.middle_name"
              type="text"
              placeholder="Enter middle name"
              class="profile-input"
            />

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1 break-words"
            >
              {{ employee.middle_name || "—" }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              Last Name
            </label>

            <input
              v-if="isEditing"
              v-model="employee.last_name"
              type="text"
              placeholder="Enter last name"
              class="profile-input"
            />

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1 break-words"
            >
              {{ employee.last_name || "—" }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              Extension Name
            </label>

            <select
              v-if="isEditing"
              v-model="employee.extension_name"
              class="profile-input"
            >
              <option value="">None</option>
              <option value="Jr.">Jr.</option>
              <option value="Sr.">Sr.</option>
              <option value="II">II</option>
              <option value="III">III</option>
              <option value="IV">IV</option>
              <option value="V">V</option>
            </select>

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1"
            >
              {{ employee.extension_name || "—" }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              Date of Birth
            </label>

            <input
              v-if="isEditing"
              v-model="employee.date_of_birth"
              type="date"
              class="profile-input"
            />

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1"
            >
              {{ formattedDateOfBirth }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              Sex
            </label>

            <select
              v-if="isEditing"
              v-model="employee.sex"
              class="profile-input"
            >
              <option value="">Select</option>
              <option value="Male">Male</option>
              <option value="Female">Female</option>
            </select>

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1"
            >
              {{ employee.sex || "—" }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              Nationality
            </label>

            <input
              v-if="isEditing"
              v-model="employee.nationality"
              type="text"
              placeholder="e.g. Filipino"
              class="profile-input"
            />

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1 break-words"
            >
              {{ employee.nationality || "—" }}
            </p>
          </div>

          <div>
            <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
              Civil Status
            </label>

            <select
              v-if="isEditing"
              v-model="employee.civil_status"
              class="profile-input"
            >
              <option value="">Select</option>
              <option value="Single">Single</option>
              <option value="Married">Married</option>
              <option value="Widowed">Widowed</option>
              <option value="Separated">Separated</option>
              <option value="Divorced">Divorced</option>
            </select>

            <p
              v-else
              class="font-medium text-[var(--text)] mt-1"
            >
              {{ employee.civil_status || "—" }}
            </p>
          </div>
        </div>

        <div class="border-t border-[#cbd8e8] pt-6 mt-6">
          <div class="flex items-center gap-2 mb-5">
            <div
              class="w-8 h-8 bg-green-50 text-green-600 rounded-lg flex items-center justify-center shrink-0"
            >
              <Phone class="w-4 h-4" />
            </div>

            <div>
              <h3 class="text-lg font-medium text-[var(--text)]">
                Contact Information
              </h3>

              <p class="text-xs text-[var(--text-muted)]">
                You can update your personal contact information.
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Contact Number
              </label>

              <input
                v-if="isEditing"
                v-model="employee.contact_number"
                type="tel"
                inputmode="numeric"
                maxlength="11"
                @input="
                  employee.contact_number = employee.contact_number
                    .replace(/\D/g, '')
                    .slice(0, 11)
                "
                placeholder="09XXXXXXXXX"
                class="profile-input"
              />

              <p
                v-else
                class="font-medium text-[var(--text)] mt-1"
              >
                {{ employee.contact_number || "—" }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Email
              </label>

              <input
                :value="employee.email"
                type="email"
                disabled
                class="w-full px-3 py-2 border border-[#cbd8e8] rounded-lg bg-[#f3f7fc] text-[var(--text-muted)] cursor-not-allowed"
              />

              <p class="text-xs text-[var(--text-muted)] mt-1">
                To change your email, use Account Settings.
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Personal Email
              </label>

              <input
                v-if="isEditing"
                v-model="employee.personal_email"
                type="email"
                placeholder="personal@email.com"
                class="profile-input"
              />

              <p
                v-else
                class="font-medium text-[var(--text)] mt-1 break-all"
              >
                {{ employee.personal_email || "—" }}
              </p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Address
              </label>

              <textarea
                v-if="isEditing"
                v-model="employee.address"
                rows="3"
                placeholder="Enter your complete address"
                class="profile-input resize-none"
              ></textarea>

              <p
                v-else
                class="font-medium text-[var(--text)] mt-1 whitespace-pre-line break-words"
              >
                {{ employee.address || "—" }}
              </p>
            </div>
          </div>
        </div>

        <div class="border-t border-[#cbd8e8] pt-6 mt-6">
          <div class="flex items-center gap-2 mb-5">
            <div
              class="w-8 h-8 bg-red-50 text-red-600 rounded-lg flex items-center justify-center shrink-0"
            >
              <ShieldAlert class="w-4 h-4" />
            </div>

            <div>
              <h3 class="text-lg font-medium text-[var(--text)]">
                Emergency Contact
              </h3>

              <p class="text-xs text-[var(--text-muted)]">
                Provide someone who can be contacted in case of emergency.
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Contact Name
              </label>

              <input
                v-if="isEditing"
                v-model="employee.emergency_contact_name"
                type="text"
                placeholder="Enter emergency contact name"
                class="profile-input"
              />

              <p
                v-else
                class="font-medium text-[var(--text)] mt-1 break-words"
              >
                {{ employee.emergency_contact_name || "—" }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Relationship
              </label>

              <input
                v-if="isEditing"
                v-model="employee.emergency_contact_relationship"
                type="text"
                placeholder="e.g. Spouse, Parent, Sibling"
                class="profile-input"
              />

              <p
                v-else
                class="font-medium text-[var(--text)] mt-1 break-words"
              >
                {{ employee.emergency_contact_relationship || "—" }}
              </p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-[var(--text-muted)] mb-1">
                Contact Number
              </label>

              <input
                v-if="isEditing"
                v-model="employee.emergency_contact_number"
                type="tel"
                inputmode="numeric"
                maxlength="11"
                @input="
                  employee.emergency_contact_number =
                    employee.emergency_contact_number
                      .replace(/\D/g, '')
                      .slice(0, 11)
                "
                placeholder="09XXXXXXXXX"
                class="profile-input"
              />

              <p
                v-else
                class="font-medium text-[var(--text)] mt-1"
              >
                {{ employee.emergency_contact_number || "—" }}
              </p>
            </div>
          </div>
        </div>

        <div
          v-if="isEditing"
          class="flex flex-col sm:flex-row justify-end gap-3 mt-6"
        >
          <button
            type="button"
            @click="cancelEdit"
            class="w-full sm:w-auto px-6 py-2 text-[var(--text-muted)] bg-[#f3f7fc] border border-[#cbd8e8] rounded-lg hover:bg-[#e8f0f9] transition"
          >
            Cancel
          </button>

          <button
            type="submit"
            class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";

import {
  User,
  Building2,
  Phone,
  ShieldAlert,
  Pencil,
} from "lucide-vue-next";

import {
  getMyProfile,
  updateMyProfile,
} from "@/services/employee";

const employee = ref({
  employee_id: "",
  employee_code: "",
  email: "",
  role: "",

  first_name: "",
  middle_name: "",
  last_name: "",
  extension_name: "",

  date_of_birth: "",

  sex: "",
  nationality: "",
  civil_status: "",

  contact_number: "",
  personal_email: "",
  address: "",

  emergency_contact_name: "",
  emergency_contact_number: "",
  emergency_contact_relationship: "",

  personnel_type: "",
  employment_category: "",

  department_id: null as number | null,
  department_name: "",

  position_id: null as number | null,
  position: "",

  level: "",

  supervisor_id: null as number | null,
  supervisor: "",

  salary_grade: "",
  salary_step: null as number | null,

  salary: "" as string | number,

  employment_status: "",
  date_hired: "",
});

// NOTE: The /positions endpoint is admin-only on the backend (confirmed by
// a 403 for employee accounts), so it can't be used here as a fallback.
// The real fix is on the backend: the route behind getMyProfile() must
// include salary_grade (or an eager-loaded position.salary_grade) in its
// response for the logged-in employee. Until that's in place, this will
// show "—".
const resolvedSalaryGrade = computed(() => {
  return employee.value.salary_grade || "";
});

const isEditing = ref(false);

let editSnapshot: typeof employee.value | null = null;

const startEdit = () => {
  editSnapshot = JSON.parse(
    JSON.stringify(employee.value)
  );

  isEditing.value = true;
};

const cancelEdit = () => {
  if (editSnapshot) {
    employee.value = JSON.parse(
      JSON.stringify(editSnapshot)
    );
  }

  editSnapshot = null;
  isEditing.value = false;
};

const fullName = computed(() => {
  return [
    employee.value.first_name,
    employee.value.middle_name,
    employee.value.last_name,
    employee.value.extension_name,
  ]
    .filter(Boolean)
    .join(" ");
});

const initials = computed(() => {
  return (
    employee.value.first_name.charAt(0) +
    employee.value.last_name.charAt(0)
  ).toUpperCase();
});

const supervisorName = computed(() => {
  if (employee.value.supervisor) {
    return employee.value.supervisor;
  }

  return "—";
});

const normalizeEmploymentStatus = (
  status?: string
) => {
  return String(status || "")
    .trim()
    .toLowerCase();
};

const formatDate = (
  date?: string | null
) => {
  if (!date) {
    return "—";
  }

  const parsedDate = new Date(date);

  if (Number.isNaN(parsedDate.getTime())) {
    return "—";
  }

  const month = String(
    parsedDate.getUTCMonth() + 1
  ).padStart(2, "0");

  const day = String(
    parsedDate.getUTCDate()
  ).padStart(2, "0");

  const year = parsedDate.getUTCFullYear();

  return `${month}-${day}-${year}`;
};

const formattedDateOfBirth = computed(() => {
  return formatDate(
    employee.value.date_of_birth
  );
});

const formattedDateHired = computed(() => {
  return formatDate(
    employee.value.date_hired
  );
});

const formattedSalary = computed(() => {
  const raw = employee.value.salary;

  if (
    raw === "" ||
    raw === null ||
    raw === undefined
  ) {
    return "—";
  }

  const num =
    typeof raw === "number"
      ? raw
      : parseFloat(String(raw));

  if (Number.isNaN(num)) {
    return "—";
  }

  return `₱${num.toLocaleString("en-PH", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
});

const loadProfile = async () => {
  try {
    const data = await getMyProfile();

    console.log("Employee profile:", data);

    employee.value = {
      employee_id:
        data.employee_id ?? "",

      employee_code:
        data.employee_code ?? "",

      email:
        data.email ?? "",

      role:
        data.role ?? "",

      first_name:
        data.first_name ?? "",

      middle_name:
        data.middle_name ?? "",

      last_name:
        data.last_name ?? "",

      extension_name:
        data.extension_name ?? "",

      date_of_birth:
        data.date_of_birth ?? "",

      sex:
        data.sex ?? "",

      nationality:
        data.nationality ?? "",

      civil_status:
        data.civil_status ?? "",

      contact_number:
        data.contact_number ?? "",

      personal_email:
        data.personal_email ?? "",

      address:
        data.address ?? "",

      emergency_contact_name:
        data.emergency_contact_name ?? "",

      emergency_contact_number:
        data.emergency_contact_number ?? "",

      emergency_contact_relationship:
        data.emergency_contact_relationship ?? "",

      personnel_type:
        data.personnel_type ?? "",

      employment_category:
        data.employment_category ?? "",

      department_id:
        data.department_id ?? null,

      department_name:
        data.department_name ??
        data.department?.department_name ??
        "",

      position_id:
        data.position_id ??
        data.position?.id ??
        null,

      position:
        data.position ??
        data.position?.name ??
        "",

      level:
        data.level ?? "",

      supervisor_id:
        data.supervisor_id ??
        data.supervisor?.employee_id ??
        null,

      supervisor:
        typeof data.supervisor === "string"
          ? data.supervisor
          : data.supervisor
            ? `${data.supervisor.first_name ?? ""} ${
                data.supervisor.middle_name
                  ? data.supervisor.middle_name + " "
                  : ""
              }${data.supervisor.last_name ?? ""}`.trim()
            : "",

      salary_grade:
        data.salary_grade ??
        data.position?.salary_grade ??
        "",

      salary_step:
        data.salary_step ?? null,

      salary:
        data.salary ?? "",

      employment_status:
        data.employment_status ?? "",

      date_hired:
        data.date_hired ?? "",
    };
  } catch (error) {
    console.error(
      "Failed to load employee profile:",
      error
    );
  }
};

const updateProfile = async () => {
  try {
    await updateMyProfile({
      first_name:
        employee.value.first_name,

      middle_name:
        employee.value.middle_name,

      last_name:
        employee.value.last_name,

      extension_name:
        employee.value.extension_name,

      date_of_birth:
        employee.value.date_of_birth,

      sex:
        employee.value.sex,

      contact_number:
        employee.value.contact_number,

      nationality:
        employee.value.nationality,

      civil_status:
        employee.value.civil_status,

      address:
        employee.value.address,

      personal_email:
        employee.value.personal_email,

      emergency_contact_name:
        employee.value.emergency_contact_name,

      emergency_contact_number:
        employee.value.emergency_contact_number,

      emergency_contact_relationship:
        employee.value.emergency_contact_relationship,
    });

    alert(
      "Profile updated successfully."
    );

    await loadProfile();

    isEditing.value = false;

    editSnapshot = null;
  } catch (error: any) {
    console.error(
      "Profile update failed:",
      error
    );

    if (
      error.response?.data?.message
    ) {
      alert(
        error.response.data.message
      );
    } else if (
      error.response?.data?.errors
    ) {
      const errors =
        error.response.data.errors;

      const messages =
        Object.values(errors)
          .flat()
          .join("\n");

      alert(messages);
    } else {
      alert(
        "Failed to update profile."
      );
    }
  }
};

onMounted(async () => {
  await loadProfile();
});
</script>

<style scoped>
.records-shell {
  background: var(--app-bg);
  min-height: 100vh;
}

.neo-card {
  background: var(--surface);
  border: 1px solid #cbd8e8;
  border-radius: 1.4rem;
  box-shadow: 0 10px 22px rgba(23, 32, 51, 0.06);
  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;
}

.neo-card:hover {
  box-shadow: 0 14px 26px rgba(23, 32, 51, 0.09);
}

.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card button {
  letter-spacing: -0.01em;
}

.profile-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  color: var(--text);
  background: var(--surface-muted);
  border: 1px solid #c8d8eb;
  border-radius: 0.5rem;
  outline: none;
  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease;
}

.profile-input::placeholder {
  color: #94a3b8;
}

.profile-input:focus {
  border-color: #7aa7e8;
  box-shadow:
    0 0 0 3px
    rgba(37, 99, 235, 0.12);
}

.profile-input option {
  background: var(--surface);
  color: var(--text);
}

@media (max-width: 640px) {
  .records-shell {
    padding: 1rem;
  }

  .neo-card {
    border-radius: 1rem;
  }
}
</style>