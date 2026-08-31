
<template>
  <div class="records-shell p-8 min-h-screen space-y-6">

    <!-- ===================================================== -->
    <!-- PROFILE SUMMARY -->
    <!-- ===================================================== -->

    <div class="neo-card p-8">

      <div class="flex flex-col sm:flex-row sm:items-center gap-6">

        <!-- Initials -->
        <div
          class="w-28 h-28 bg-blue-500 rounded-full flex items-center justify-center text-white text-4xl font-semibold shrink-0"
        >
          {{ initials }}
        </div>

        <!-- Name / Position -->
        <div class="flex-1 min-w-0">

          <h3 class="text-2xl font-semibold text-white break-words">
            {{ fullName }}
          </h3>

          <p class="text-sm text-gray-400 mt-1">
            {{ employee.position || "Employee" }}
          </p>

          <span
            v-if="employee.employment_status"
            :class="
              normalizeEmploymentStatus(employee.employment_status) === 'active'
                ? 'bg-green-500/10 text-green-400'
                : 'bg-gray-500/10 text-gray-400'
            "
            class="inline-block mt-3 px-3 py-1 rounded-full text-xs"
          >
            {{ employee.employment_status }}
          </span>

        </div>

        <!-- Employee Code -->
        <div
          class="sm:text-right sm:pl-6 sm:border-l border-t sm:border-t-0 border-[#1e293b] pt-5 sm:pt-0"
        >

          <p class="text-xs text-gray-500">
            Employee Code
          </p>

          <p class="text-sm text-gray-300 font-mono mt-1">
            {{ employee.employee_code || "—" }}
          </p>

        </div>

      </div>

    </div>


    <!-- ===================================================== -->
    <!-- EMPLOYEE INFORMATION + PERSONAL INFORMATION -->
    <!-- ===================================================== -->

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

      <!-- =================================================== -->
      <!-- EMPLOYEE / HR INFORMATION -->
      <!-- =================================================== -->

      <div class="neo-card p-6">

        <div class="flex items-center gap-2 mb-5">

          <div
            class="w-8 h-8 bg-blue-500/10 text-blue-400 rounded-lg flex items-center justify-center shrink-0"
          >
            <Building2 class="w-4 h-4" />
          </div>

          <div>
            <h3 class="text-lg font-medium text-white">
              Employee Information
            </h3>

            <p class="text-xs text-gray-500">
              These details are managed by the administrator.
            </p>
          </div>

        </div>


        <div
          class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-5 border border-[#1e293b] rounded-xl p-5"
        >

          <!-- Employee Code -->
          <div>
            <p class="text-sm text-gray-400">
              Employee Code
            </p>

            <p class="font-medium text-white mt-1 break-words">
              {{ employee.employee_code || "—" }}
            </p>
          </div>


          <!-- Email -->
          <div>
            <p class="text-sm text-gray-400">
              Email
            </p>

            <p class="font-medium text-white mt-1 break-all">
              {{ employee.email || "—" }}
            </p>
          </div>


          <!-- Personnel Type -->
          <div>
            <p class="text-sm text-gray-400">
              Personnel Type
            </p>

            <p class="font-medium text-white mt-1 break-words">
              {{ employee.personnel_type || "—" }}
            </p>
          </div>


          <!-- Employment Category -->
          <div>
            <p class="text-sm text-gray-400">
              Employment Category
            </p>

            <p class="font-medium text-white mt-1 break-words">
              {{ employee.employment_category || "—" }}
            </p>
          </div>


          <!-- Department -->
          <div>
            <p class="text-sm text-gray-400">
              Department
            </p>

            <p class="font-medium text-white mt-1 break-words">
              {{ employee.department_name || "—" }}
            </p>
          </div>


          <!-- Position -->
          <div>
            <p class="text-sm text-gray-400">
              Position
            </p>

            <p class="font-medium text-white mt-1 break-words">
              {{ employee.position || "—" }}
            </p>
          </div>


          <!-- Level -->
          <div>
            <p class="text-sm text-gray-400">
              Level
            </p>

            <p class="font-medium text-white mt-1">
              {{ employee.level || "—" }}
            </p>
          </div>


          <!-- Supervisor -->
          <div>
            <p class="text-sm text-gray-400">
              Supervisor
            </p>

            <p class="font-medium text-white mt-1 break-words">
              {{ supervisorName }}
            </p>
          </div>


          <!-- Salary Grade -->
          <div>
            <p class="text-sm text-gray-400">
              Salary Grade
            </p>

            <p class="font-medium text-white mt-1">
              {{ employee.salary_grade || "—" }}
            </p>
          </div>


          <!-- Salary Step -->
          <div>
            <p class="text-sm text-gray-400">
              Salary Step
            </p>

            <p class="font-medium text-white mt-1">
              {{
                employee.salary_step
                  ? `Step ${employee.salary_step}`
                  : "—"
              }}
            </p>
          </div>


          <!-- Salary -->
          <div>
            <p class="text-sm text-gray-400">
              Current Salary
            </p>

            <p class="font-medium text-white mt-1">
              {{ formattedSalary }}
            </p>
          </div>


          <!-- Date Hired -->
          <div>
            <p class="text-sm text-gray-400">
              Date Hired
            </p>

            <p class="font-medium text-white mt-1">
              {{ formattedDateHired }}
            </p>
          </div>


          <!-- Employment Status -->
          <div class="sm:col-span-2">
            <p class="text-sm text-gray-400">
              Employment Status
            </p>

            <span
              :class="
                normalizeEmploymentStatus(employee.employment_status) === 'active'
                  ? 'bg-green-500/10 text-green-400'
                  : 'bg-gray-500/10 text-gray-400'
              "
              class="inline-block mt-1 px-3 py-1 rounded-full text-xs"
            >
              {{ employee.employment_status || "—" }}
            </span>
          </div>

        </div>

      </div>


      <!-- =================================================== -->
      <!-- PERSONAL + CONTACT + EMERGENCY -->
      <!-- =================================================== -->

      <form
        @submit.prevent="updateProfile"
        class="neo-card p-6"
      >

        <!-- ================================================= -->
        <!-- PERSONAL INFORMATION -->
        <!-- ================================================= -->

        <div class="flex items-center justify-between gap-2 mb-5">

          <div class="flex items-center gap-2">

            <div
              class="w-8 h-8 bg-yellow-500/10 text-yellow-400 rounded-lg flex items-center justify-center shrink-0"
            >
              <User class="w-4 h-4" />
            </div>

            <h3 class="text-lg font-medium text-white">
              Personal Information
            </h3>

          </div>


          <!-- EDIT -->
          <button
            v-if="!isEditing"
            type="button"
            @click="startEdit"
            class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-blue-400 bg-blue-500/10 rounded-lg hover:bg-blue-500/20 transition"
          >
            <Pencil class="w-3.5 h-3.5" />
            Edit
          </button>

        </div>


        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

          <!-- First Name -->
          <div>

            <label class="block text-sm font-medium text-gray-400 mb-1">
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
              class="font-medium text-white mt-1 break-words"
            >
              {{ employee.first_name || "—" }}
            </p>

          </div>


          <!-- Middle Name -->
          <div>

            <label class="block text-sm font-medium text-gray-400 mb-1">
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
              class="font-medium text-white mt-1 break-words"
            >
              {{ employee.middle_name || "—" }}
            </p>

          </div>


          <!-- Last Name -->
          <div>

            <label class="block text-sm font-medium text-gray-400 mb-1">
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
              class="font-medium text-white mt-1 break-words"
            >
              {{ employee.last_name || "—" }}
            </p>

          </div>


          <!-- Extension Name -->
          <div>

            <label class="block text-sm font-medium text-gray-400 mb-1">
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
              class="font-medium text-white mt-1"
            >
              {{ employee.extension_name || "—" }}
            </p>

          </div>


          <!-- Date of Birth -->
          <div>

            <label class="block text-sm font-medium text-gray-400 mb-1">
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
              class="font-medium text-white mt-1"
            >
              {{ formattedDateOfBirth }}
            </p>

          </div>


          <!-- Sex -->
          <div>

            <label class="block text-sm font-medium text-gray-400 mb-1">
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
              class="font-medium text-white mt-1"
            >
              {{ employee.sex || "—" }}
            </p>

          </div>


          <!-- Nationality -->
          <div>

            <label class="block text-sm font-medium text-gray-400 mb-1">
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
              class="font-medium text-white mt-1 break-words"
            >
              {{ employee.nationality || "—" }}
            </p>

          </div>


          <!-- Civil Status -->
          <div>

            <label class="block text-sm font-medium text-gray-400 mb-1">
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
              class="font-medium text-white mt-1"
            >
              {{ employee.civil_status || "—" }}
            </p>

          </div>

        </div>


        <!-- ================================================= -->
        <!-- CONTACT INFORMATION -->
        <!-- ================================================= -->

        <div class="border-t border-[#1e293b] pt-6 mt-6">

          <div class="flex items-center gap-2 mb-5">

            <div
              class="w-8 h-8 bg-green-500/10 text-green-400 rounded-lg flex items-center justify-center shrink-0"
            >
              <Phone class="w-4 h-4" />
            </div>

            <div>

              <h3 class="text-lg font-medium text-white">
                Contact Information
              </h3>

              <p class="text-xs text-gray-500">
                You can update your personal contact information.
              </p>

            </div>

          </div>


          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Contact Number -->
            <div>

              <label class="block text-sm font-medium text-gray-400 mb-1">
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
                class="font-medium text-white mt-1"
              >
                {{ employee.contact_number || "—" }}
              </p>

            </div>


            <!-- Email -->
            <div>

              <label class="block text-sm font-medium text-gray-400 mb-1">
                Email
              </label>

              <input
                :value="employee.email"
                type="email"
                disabled
                class="w-full px-3 py-2 border border-[#1e293b] rounded-lg bg-[#0a1119] text-gray-500 cursor-not-allowed"
              />

              <p class="text-xs text-gray-500 mt-1">
                To change your email, use Account Settings.
              </p>

            </div>


            <!-- Personal Email -->
            <div>

              <label class="block text-sm font-medium text-gray-400 mb-1">
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
                class="font-medium text-white mt-1 break-all"
              >
                {{ employee.personal_email || "—" }}
              </p>

            </div>


            <!-- Address -->
            <div class="md:col-span-2">

              <label class="block text-sm font-medium text-gray-400 mb-1">
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
                class="font-medium text-white mt-1 whitespace-pre-line break-words"
              >
                {{ employee.address || "—" }}
              </p>

            </div>

          </div>

        </div>


        <!-- ================================================= -->
        <!-- EMERGENCY CONTACT -->
        <!-- ================================================= -->

        <div class="border-t border-[#1e293b] pt-6 mt-6">

          <div class="flex items-center gap-2 mb-5">

            <div
              class="w-8 h-8 bg-red-500/10 text-red-400 rounded-lg flex items-center justify-center shrink-0"
            >
              <ShieldAlert class="w-4 h-4" />
            </div>

            <div>

              <h3 class="text-lg font-medium text-white">
                Emergency Contact
              </h3>

              <p class="text-xs text-gray-500">
                Provide someone who can be contacted in case of emergency.
              </p>

            </div>

          </div>


          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Emergency Contact Name -->
            <div>

              <label class="block text-sm font-medium text-gray-400 mb-1">
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
                class="font-medium text-white mt-1 break-words"
              >
                {{ employee.emergency_contact_name || "—" }}
              </p>

            </div>


            <!-- Relationship -->
            <div>

              <label class="block text-sm font-medium text-gray-400 mb-1">
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
                class="font-medium text-white mt-1 break-words"
              >
                {{ employee.emergency_contact_relationship || "—" }}
              </p>

            </div>


            <!-- Emergency Contact Number -->
            <div class="md:col-span-2">

              <label class="block text-sm font-medium text-gray-400 mb-1">
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
                class="font-medium text-white mt-1"
              >
                {{ employee.emergency_contact_number || "—" }}
              </p>

            </div>

          </div>

        </div>


        <!-- ================================================= -->
        <!-- SAVE / CANCEL -->
        <!-- ================================================= -->

        <div
          v-if="isEditing"
          class="flex flex-col sm:flex-row justify-end gap-3 mt-6"
        >

          <button
            type="button"
            @click="cancelEdit"
            class="w-full sm:w-auto px-6 py-2 text-gray-300 bg-[#0d1520] border border-[#1e293b] rounded-lg hover:bg-[#111d2e] transition"
          >
            Cancel
          </button>

          <button
            type="submit"
            class="w-full sm:w-auto px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition"
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


/* =========================================================
   EMPLOYEE PROFILE
========================================================= */

const employee = ref({

  /* =======================================================
     ACCOUNT
  ======================================================= */

  employee_id: "",
  employee_code: "",
  email: "",
  role: "",


  /* =======================================================
     PERSONAL INFORMATION
  ======================================================= */

  first_name: "",
  middle_name: "",
  last_name: "",
  extension_name: "",

  date_of_birth: "",

  sex: "",
  nationality: "",
  civil_status: "",


  /* =======================================================
     CONTACT INFORMATION
  ======================================================= */

  contact_number: "",
  personal_email: "",
  address: "",


  /* =======================================================
     EMERGENCY CONTACT
  ======================================================= */

  emergency_contact_name: "",
  emergency_contact_number: "",
  emergency_contact_relationship: "",


  /* =======================================================
     EMPLOYMENT INFORMATION
  ======================================================= */

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


/* =========================================================
   EDIT MODE
========================================================= */

const isEditing = ref(false);

let editSnapshot: typeof employee.value | null = null;


/* =========================================================
   START EDIT
========================================================= */

const startEdit = () => {

  editSnapshot = JSON.parse(
    JSON.stringify(employee.value)
  );

  isEditing.value = true;
};


/* =========================================================
   CANCEL EDIT
========================================================= */

const cancelEdit = () => {

  if (editSnapshot) {

    employee.value = JSON.parse(
      JSON.stringify(editSnapshot)
    );

  }

  editSnapshot = null;

  isEditing.value = false;
};


/* =========================================================
   FULL NAME
========================================================= */

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


/* =========================================================
   INITIALS
========================================================= */

const initials = computed(() => {

  return (
    employee.value.first_name.charAt(0) +
    employee.value.last_name.charAt(0)
  ).toUpperCase();

});


/* =========================================================
   SUPERVISOR NAME
========================================================= */

const supervisorName = computed(() => {

  if (employee.value.supervisor) {
    return employee.value.supervisor;
  }

  return "—";

});


/* =========================================================
   NORMALIZE EMPLOYMENT STATUS
========================================================= */

const normalizeEmploymentStatus = (
  status?: string
) => {

  return String(status || "")
    .trim()
    .toLowerCase();

};


/* =========================================================
   FORMAT DATE
========================================================= */

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


/* =========================================================
   FORMATTED DATE OF BIRTH
========================================================= */

const formattedDateOfBirth = computed(() => {

  return formatDate(
    employee.value.date_of_birth
  );

});


/* =========================================================
   FORMATTED DATE HIRED
========================================================= */

const formattedDateHired = computed(() => {

  return formatDate(
    employee.value.date_hired
  );

});


/* =========================================================
   FORMATTED SALARY
========================================================= */

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


/* =========================================================
   LOAD PROFILE
========================================================= */

const loadProfile = async () => {

  try {

    const data = await getMyProfile();

    console.log("Employee profile:", data);


    employee.value = {

      /* Account */

      employee_id:
        data.employee_id ?? "",

      employee_code:
        data.employee_code ?? "",

      email:
        data.email ?? "",

      role:
        data.role ?? "",


      /* Personal */

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


      /* Contact */

      contact_number:
        data.contact_number ?? "",

      personal_email:
        data.personal_email ?? "",

      address:
        data.address ?? "",


      /* Emergency */

      emergency_contact_name:
        data.emergency_contact_name ?? "",

      emergency_contact_number:
        data.emergency_contact_number ?? "",

      emergency_contact_relationship:
        data.emergency_contact_relationship ?? "",


      /* Employment */

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
        data.supervisor ??
        (
          data.supervisor
            ? `${data.supervisor.first_name ?? ""} ${
                data.supervisor.middle_name
                  ? data.supervisor.middle_name + " "
                  : ""
              }${data.supervisor.last_name ?? ""}`
            : ""
        ),

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


/* =========================================================
   UPDATE EMPLOYEE PROFILE
========================================================= */

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


/* =========================================================
   INITIAL LOAD
========================================================= */

onMounted(async () => {

  await loadProfile();

});

</script>


<style scoped>

.records-shell {
  background: #080d14;
  min-height: 100vh;
}


/* =========================================================
   CARD
========================================================= */

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

  box-shadow:
    0 14px 26px rgba(15, 23, 42, 0.06);

}


/* =========================================================
   TYPOGRAPHY
========================================================= */

.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card button {
  letter-spacing: -0.01em;
}


/* =========================================================
   PROFILE INPUT
========================================================= */

.profile-input {

  width: 100%;

  padding:
    0.5rem 0.75rem;

  color: white;

  background: #0d1520;

  border:
    1px solid #1e293b;

  border-radius:
    0.5rem;

  outline: none;

  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease;

}


.profile-input:focus {

  border-color:
    #3b82f6;

  box-shadow:
    0 0 0 2px
    rgba(59, 130, 246, 0.35);

}


/* =========================================================
   SELECT OPTIONS
========================================================= */

.profile-input option {

  background: #0d1520;

  color: white;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 640px) {

  .records-shell {

    padding:
      1rem;

  }

  .neo-card {

    border-radius:
      1rem;

  }

}
</style>

