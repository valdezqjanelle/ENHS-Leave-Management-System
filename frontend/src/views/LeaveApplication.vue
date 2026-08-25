<template>
  <div class="dashboard-shell min-h-screen px-6 py-8">
    <!-- Main Form Card -->
    <div class="neo-card max-w-7xl mx-auto overflow-hidden">

      <!-- Header -->
      <div class="px-6 py-5 border-b border-slate-700">
        <h2 class="text-2xl font-semibold text-white">
          Leave Application Form
        </h2>

        <p class="text-sm text-slate-400 mt-1">
          Fill out the form below to apply for leave
        </p>
      </div>

      <!-- Form -->
      <form
        @submit.prevent="submitApplication"
        class="p-6 space-y-6"
      >

        <!-- =============================== -->
        <!-- PERSONAL INFORMATION -->
        <!-- =============================== -->

        <div>
          <h3 class="section-title">
            Personal Information
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            <!-- Full Name -->
            <div>
              <label class="form-label">
                Full Name <span class="text-red-400">*</span>
              </label>

              <input
                :value="fullName"
                type="text"
                readonly
                class="input-field"
                placeholder="Enter your full name"
              />
            </div>

            <!-- Department -->
            <div>
              <label class="form-label">
                Department <span class="text-red-400">*</span>
              </label>

              <input
                :value="employee.department"
                type="text"
                readonly
                class="input-field"
              />
            </div>

            <!-- Position -->
            <div>
              <label class="form-label">
                Position <span class="text-red-400">*</span>
              </label>

              <input
                :value="employee.position"
                type="text"
                readonly
                class="input-field"
                placeholder="e.g., Professor, Assistant Professor"
              />
            </div>

            <!-- Employee ID -->
            <div>
              <label class="form-label">
                Employee ID <span class="text-red-400">*</span>
              </label>

              <input
                :value="employee.employee_code"
                type="text"
                readonly
                class="input-field"
                placeholder="Enter your employee ID"
              />
            </div>

          </div>
        </div>


        <!-- =============================== -->
        <!-- LEAVE DETAILS -->
        <!-- =============================== -->

        <div class="form-section">

          <h3 class="section-title">
            Leave Details
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Leave Type -->
            <div>
              <label class="form-label">
                Leave Type <span class="text-red-400">*</span>
              </label>

              <select
                v-model="form.leave_type_id"
                required
                class="input-field"
              >
                <option value="">Select Leave Type</option>

                <option
                  v-for="type in leaveTypes"
                  :key="type.leave_type_id"
                  :value="type.leave_type_id"
                >
                  {{ type.leave_type_name }}
                </option>
              </select>
            </div>


            <!-- Vacation Details -->
            <div
              v-if="selectedLeaveName?.includes('Vacation')"
              class="detail-box"
            >
              <div>
                <label class="form-label">
                  Vacation Location Type
                </label>

                <select
                  v-model="form.vacation_location_type"
                  class="input-field"
                >
                  <option value="">Select</option>
                  <option value="within_philippines">
                    Within Philippines
                  </option>
                  <option value="abroad">
                    Abroad
                  </option>
                </select>
              </div>

              <div>
                <label class="form-label">
                  Specify Location
                </label>

                <input
                  v-model="form.vacation_location"
                  type="text"
                  class="input-field"
                />
              </div>
            </div>


            <!-- Sick Details -->
            <div
              v-if="selectedLeaveName?.includes('Sick')"
              class="detail-box"
            >
              <div>
                <label class="form-label">
                  Treatment
                </label>

                <select
                  v-model="form.sick_type"
                  class="input-field"
                >
                  <option value="">Select</option>
                  <option value="in_hospital">
                    In Hospital
                  </option>
                  <option value="out_patient">
                    Out Patient
                  </option>
                </select>
              </div>

              <div>
                <label class="form-label">
                  Illness
                </label>

                <input
                  v-model="form.illness"
                  type="text"
                  class="input-field"
                />
              </div>
            </div>


            <!-- Study Leave -->
            <div
              v-if="selectedLeaveName?.includes('Study')"
              class="option-box"
            >
              <label class="checkbox-label">
                <input
                  type="checkbox"
                  v-model="form.masters_degree"
                  class="checkbox-input"
                />

                <span>Masters Degree</span>
              </label>

              <label class="checkbox-label">
                <input
                  type="checkbox"
                  v-model="form.board_exam_review"
                  class="checkbox-input"
                />

                <span>Board Examination Review</span>
              </label>
            </div>


            <!-- Special / Mandatory -->
            <div
              v-if="
                selectedLeaveName?.includes('Special') ||
                selectedLeaveName?.includes('Mandatory')
              "
              class="option-box"
            >
              <label class="checkbox-label">
                <input
                  type="checkbox"
                  v-model="form.monetization"
                  class="checkbox-input"
                />

                <span>Monetization</span>
              </label>

              <label class="checkbox-label">
                <input
                  type="checkbox"
                  v-model="form.terminal_leave"
                  class="checkbox-input"
                />

                <span>Terminal Leave</span>
              </label>

              <input
                v-model="form.other_purpose"
                placeholder="Other purpose"
                class="input-field mt-3"
              />
            </div>


            <!-- Commutation -->
            <div>
              <label class="form-label">
                Commutation <span class="text-red-400">*</span>
              </label>

              <select
                v-model="form.commutation"
                class="input-field"
              >
                <option value="">Select</option>
                <option value="requested">
                  Requested
                </option>
                <option value="not requested">
                  Not Requested
                </option>
              </select>
            </div>


            <!-- Contact Number -->
            <div>
              <label class="form-label">
                Contact Number <span class="text-red-400">*</span>
              </label>

              <input
                :value="employee.contact_number"
                type="text"
                readonly
                class="input-field"
              />
            </div>

          </div>
        </div>


        <!-- =============================== -->
        <!-- LEAVE DATES -->
        <!-- =============================== -->

        <div class="form-section">

          <h3 class="section-title">
            Leave Dates
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Start Date -->
            <div>
              <label class="form-label">
                Start Date <span class="text-red-400">*</span>
              </label>

              <input
                v-model="form.startDate"
                type="date"
                required
                :min="minDate"
                class="input-field"
              />
            </div>


            <!-- End Date -->
            <div>
              <label class="form-label">
                End Date <span class="text-red-400">*</span>
              </label>

              <input
                v-model="form.endDate"
                type="date"
                required
                :min="form.startDate || minDate"
                class="input-field"
              />
            </div>


            <!-- Total Days -->
            <div>
              <label class="form-label">
                Total Days
              </label>

              <div class="input-field flex items-center">
                {{ calculateDays() }} days
              </div>
            </div>

          </div>
        </div>


        <!-- =============================== -->
        <!-- LEAVE CREDITS -->
        <!-- =============================== -->

        <div class="credit-box">

          <h3 class="text-base font-semibold text-blue-300 mb-4">
            Available Leave Credits
          </h3>

          

         <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

            <div class="credit-item">
              <span class="text-slate-400">
                Sick:
              </span>

              <span class="font-semibold text-blue-300">
                {{ leaveBalance.sick_balance }} Days
              </span>
            </div>

            <div class="credit-item">
              <span class="text-slate-400">
                Vacation:
              </span>

              <span class="font-semibold text-blue-300">
                {{ leaveBalance.vacation_balance }} Days
              </span>
            </div>

            <div class="credit-item">
              <span class="text-slate-400">
                Service Credits:
              </span>

              <span class="font-semibold text-blue-300">
                {{ leaveBalance.service_credits }} Days
              </span>
            </div>
            

          </div>
        </div>


        <!-- =============================== -->
        <!-- REASON -->
        <!-- =============================== -->

        <div class="form-section">

          <label class="form-label">
            Reason for Leave
            <span class="text-red-400">*</span>
          </label>

          <textarea
            v-model="form.reason"
            required
            rows="5"
            class="input-field resize-none"
            placeholder="Please provide a detailed reason for your leave application..."
          ></textarea>

        </div>


        <!-- =============================== -->
        <!-- SUPPORTING DOCUMENTS -->
        <!-- =============================== -->

        <div class="form-section">

          <label class="form-label">
            Supporting Documents
          </label>

          <div class="upload-box">

            <Upload
              class="mx-auto h-12 w-12 text-slate-500 mb-4"
            />

            <p class="text-sm text-slate-300 mb-2">
              Click to upload or drag and drop
            </p>

            <p class="text-xs text-slate-500">
              PDF, DOC, DOCX files up to 10MB
            </p>

            <input
              type="file"
              multiple
              accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
              @change="handleFileUpload"
              class="hidden"
              ref="fileInput"
            />

            <button
              type="button"
              @click="fileInput?.click()"
              class="mt-4 px-5 py-2.5 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-500 transition"
            >
              Select Files
            </button>

          </div>


          <!-- Uploaded Files -->
          <div
            v-if="form.attachments.length > 0"
            class="mt-4 space-y-3"
          >

            <div
              v-for="(file, index) in form.attachments"
              :key="index"
              class="flex items-center justify-between p-4 bg-slate-900/70 border border-slate-700 rounded-xl"
            >

              <div class="flex items-center min-w-0">

                <FileText
                  class="w-5 h-5 mr-3 text-slate-400 flex-shrink-0"
                />

                <span
                  class="text-sm text-slate-200 truncate"
                >
                  {{ file.name }}
                </span>

                <span
                  class="text-xs text-slate-500 ml-2 flex-shrink-0"
                >
                  ({{ formatFileSize(file.size) }})
                </span>

              </div>

              <button
                type="button"
                @click="removeFile(index)"
                class="text-red-400 hover:text-red-300 transition ml-4"
              >
                <X class="w-4 h-4" />
              </button>

            </div>

          </div>

        </div>

        <!-- =============================== -->
<!-- APPLICANT SIGNATURE -->
<!-- =============================== -->
<div class="form-section">
  <label class="form-label">
    Applicant Signature
    <span class="text-red-400">*</span>
  </label>

  <p class="text-xs text-slate-400 mb-3">
    Please draw your signature in the box below.
  </p>

  <div class="signature-box">
    <canvas
      ref="signatureCanvas"
      class="signature-canvas"
    ></canvas>
  </div>

  <div class="flex items-center justify-between mt-3">
    <span
      v-if="signatureData"
      class="text-xs text-green-400"
    >
      ✓ Signature provided
    </span>

    <span
      v-else
      class="text-xs text-slate-500"
    >
      Sign using your mouse, trackpad, touchscreen, or stylus.
    </span>

    <button
      type="button"
      @click="clearSignature"
      class="px-4 py-2 border border-slate-600 text-slate-300 rounded-lg hover:bg-slate-800 transition text-sm"
    >
      Clear Signature
    </button>
  </div>
</div>


        <!-- =============================== -->
        <!-- DECLARATION -->
        <!-- =============================== -->

        <div class="border-t border-slate-700 pt-6">

          <label class="flex items-start gap-3 cursor-pointer">

            <input
              v-model="form.declaration"
              type="checkbox"
              required
              class="checkbox-input mt-1"
            />

            <span class="text-sm text-slate-300 leading-relaxed">
              I hereby certify that the information provided in this leave
              application is true and correct. I understand that any false
              statement may result in disciplinary action. I agree to comply
              with all company policies and procedures regarding leave
              applications.
            </span>

          </label>

        </div>


        <!-- =============================== -->
        <!-- BUTTONS -->
        <!-- =============================== -->

        <div
          class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-slate-700"
        >

          <button
            type="button"
            @click="resetForm"
            class="w-full sm:w-auto px-6 py-2.5 border border-slate-600 text-slate-300 rounded-lg hover:bg-slate-800 transition text-sm"
          >
            Reset Form
          </button>

          <button
            type="submit"
            :disabled="isSubmitting"
            class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isSubmitting ? "Submitting..." : "Submit Application" }}
          </button>

        </div>

      </form>
    </div>


    <!-- =============================== -->
    <!-- SUCCESS MODAL -->
    <!-- =============================== -->

    <div
      v-if="showSuccessModal"
      class="fixed inset-0 bg-black/70 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-6"
    >

      <div
        class="relative w-full max-w-lg shadow-2xl rounded-2xl bg-[#111D2E] border border-slate-700 p-8"
      >

        <div class="text-center">

          <div
            class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-500/10 border border-green-500/20 mb-5"
          >
            <CheckCircle
              class="h-8 w-8 text-green-400"
            />
          </div>

          <h3
            class="text-xl font-semibold text-white mb-3"
          >
            Application Submitted Successfully!
          </h3>

          <p
            class="text-sm text-slate-400 mb-6 leading-relaxed"
          >
            Your leave application has been submitted to ADAS for review.
            You will receive a notification once there's an update.
          </p>

          <div class="flex flex-col sm:flex-row justify-center gap-3">

            <button
              @click="showSuccessModal = false"
              class="px-5 py-2.5 bg-slate-700 text-white rounded-lg hover:bg-slate-600 transition text-sm"
            >
              Close
            </button>

            <button
              @click="viewApplicationStatus"
              class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-500 transition text-sm"
            >
              View Application
            </button>

          </div>

        </div>

      </div>

    </div>

  </div>
</template>


<script setup lang="ts">
import {
  ref,
  computed,
  onMounted,
  onBeforeUnmount,
  nextTick
} from "vue";
import axios from "axios";
import SignaturePad from "signature_pad";
import {
  getLeaveTypes,
  submitLeave,
  getMyLeaves
} from "../services/leave";

import { useRouter } from "vue-router";

import {
  Upload,
  FileText,
  X,
  CheckCircle
} from "lucide-vue-next";

import { getMyProfile } from "../services/employee";



interface Attachment {
  name: string;
  size: number;
  file: File;
}


const router = useRouter();


const form = ref({
  leave_type_id: "",

  vacation_location_type: "",
  vacation_location: "",

  sick_type: "",
  illness: "",

  masters_degree: false,
  board_exam_review: false,

  monetization: false,
  terminal_leave: false,
  other_purpose: "",

  startDate: "",
  endDate: "",

  commutation: "",

  reason: "",

  attachments: [] as Attachment[],

  declaration: false,
});


const employee = ref({
  employee_code: "",
  first_name: "",
  middle_name: "",
  last_name: "",
  department: "",
  position: "",
  contact_number: "",
});


const fullName = computed(() => {
  return [
    employee.value.first_name,
    employee.value.middle_name,
    employee.value.last_name,
  ]
    .filter(Boolean)
    .join(" ");
});


const isSubmitting = ref(false);

const leaveTypes = ref<any[]>([]);

const leaveBalance = ref({
  vacation_balance: 0,
  sick_balance: 0,
  used_leave: 0,
  service_credits: 0,
});

const loadLeaveBalance = async () => {
  const token = localStorage.getItem("token");

  const response = await axios.get(
    "http://127.0.0.1:8000/api/my-leave-balance",
    {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    },
  );

  leaveBalance.value = response.data;
};

onMounted(async () => {
  try {
    leaveTypes.value = await getLeaveTypes();
    employee.value = await getMyProfile();
    await loadLeaveBalance();
    await initializeSignaturePad();

    window.addEventListener(
      "resize",
      handleSignatureResize
    );
  } catch (error) {
    console.error(error);
  }
});
onBeforeUnmount(() => {
  window.removeEventListener(
    "resize",
    handleSignatureResize
  );

  signaturePad.value?.off();
  signaturePad.value = null;
});

const showSuccessModal = ref(false);

const submittedLeaveId = ref<number | null>(null);

const fileInput = ref<HTMLInputElement | null>(null);
const signatureCanvas = ref<HTMLCanvasElement | null>(null);
const signaturePad = ref<SignaturePad | null>(null);
const signatureData = ref<string | null>(null);
const handleSignatureResize = () => {
  resizeSignatureCanvas();
};

const resizeSignatureCanvas = () => {
  const canvas = signatureCanvas.value;
  const pad = signaturePad.value;

  if (!canvas || !pad) {
    return;
  }

  const ratio = Math.max(window.devicePixelRatio || 1, 1);

  const width = canvas.offsetWidth;
  const height = canvas.offsetHeight;

  if (width === 0 || height === 0) {
    return;
  }

  const existingSignature = pad.isEmpty()
    ? null
    : pad.toData();

  canvas.width = width * ratio;
  canvas.height = height * ratio;

  const context = canvas.getContext("2d");

  if (context) {
    context.scale(ratio, ratio);
  }

  pad.clear();

  if (existingSignature) {
    pad.fromData(existingSignature);
  }
};

const initializeSignaturePad = async () => {
  await nextTick();

  const canvas = signatureCanvas.value;

  if (!canvas) {
    console.error("Signature canvas not found.");
    return;
  }

  const ratio = Math.max(window.devicePixelRatio || 1, 1);

  const width = canvas.offsetWidth;
  const height = canvas.offsetHeight;

  canvas.width = width * ratio;
  canvas.height = height * ratio;

  const context = canvas.getContext("2d");

  if (context) {
    context.scale(ratio, ratio);
  }

  signaturePad.value = new SignaturePad(canvas, {
    backgroundColor: "rgb(15, 26, 42)",
    penColor: "rgb(248, 250, 252)",
    minWidth: 0.8,
    maxWidth: 2.2,
  });

  signaturePad.value.addEventListener("endStroke", () => {
    if (
      signaturePad.value &&
      !signaturePad.value.isEmpty()
    ) {
      signatureData.value =
        signaturePad.value.toDataURL("image/png");
    }
  });
};

const clearSignature = () => {
  signaturePad.value?.clear();
  signatureData.value = null;
};


const minDate = computed(() => {
  const today = new Date();

  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, "0");
  const day = String(today.getDate()).padStart(2, "0");

  return `${year}-${month}-${day}`;
});


const calculateDays = () => {
  if (!form.value.startDate || !form.value.endDate) {
    return 0;
  }

  const start = new Date(form.value.startDate);
  const end = new Date(form.value.endDate);

  if (end < start) {
    return 0;
  }

  const diffTime = end.getTime() - start.getTime();

  return (
    Math.ceil(
      diffTime / (1000 * 60 * 60 * 24)
    ) + 1
  );
};


const handleFileUpload = (event: Event) => {

  const target =
    event.target as HTMLInputElement;

  const files = target.files;

  if (files) {

    for (let i = 0; i < files.length; i++) {

      const file = files[i];

      if (
        file &&
        file.size <= 10 * 1024 * 1024
      ) {

        form.value.attachments.push({
          name: file.name,
          size: file.size,
          file: file,
        });

      }

    }

  }
};


const removeFile = (index: number) => {
  form.value.attachments.splice(index, 1);
};


const formatFileSize = (bytes: number) => {

  if (bytes === 0) {
    return "0 Bytes";
  }

  const k = 1024;

  const sizes = [
    "Bytes",
    "KB",
    "MB",
    "GB"
  ];

  const i =
    Math.floor(
      Math.log(bytes) / Math.log(k)
    );

  return (
    parseFloat(
      (
        bytes / Math.pow(k, i)
      ).toFixed(2)
    ) +
    " " +
    sizes[i]
  );
};


const submitApplication = async () => {
  if (
    !signaturePad.value ||
    signaturePad.value.isEmpty()
  ) {
    alert("Please provide your signature before submitting.");
    return;
  }

  signatureData.value =
    signaturePad.value.toDataURL("image/png");

  isSubmitting.value = true;

  try {
    const data = new FormData();

    data.append(
      "leave_type_id",
      String(form.value.leave_type_id)
    );

    data.append(
      "date_filed",
      new Date().toISOString().split("T")[0]
    );

    data.append(
      "start_date",
      form.value.startDate
    );

    data.append(
      "end_date",
      form.value.endDate
    );

    data.append(
      "number_of_days",
      String(calculateDays())
    );

    data.append(
      "reason",
      form.value.reason
    );

    // Applicant Signature
    data.append(
      "applicants_signature",
      signatureData.value
    );

    // Additional Leave Details
    data.append(
      "vacation_location_type",
      form.value.vacation_location_type
    );

    data.append(
      "vacation_location",
      form.value.vacation_location
    );

    data.append(
      "sick_type",
      form.value.sick_type
    );

    data.append(
      "illness",
      form.value.illness
    );

    data.append(
      "masters_degree",
      form.value.masters_degree ? "1" : "0"
    );

    data.append(
      "board_exam_review",
      form.value.board_exam_review ? "1" : "0"
    );

    data.append(
      "monetization",
      form.value.monetization ? "1" : "0"
    );

    data.append(
      "terminal_leave",
      form.value.terminal_leave ? "1" : "0"
    );

    data.append(
      "other_purpose",
      form.value.other_purpose
    );

    data.append(
      "commutation",
      form.value.commutation
    );

    // Attachments
    form.value.attachments.forEach((file) => {
      data.append(
        "attachments[]",
        file.file
      );
    });

    const response = await submitLeave(data);

    console.log("SUBMIT RESPONSE:", response);

    const applications = await getMyLeaves();

    const latestLeave = applications[0];

    if (!latestLeave) {
      throw new Error(
        "Leave application was submitted but could not be retrieved."
      );
    }

    submittedLeaveId.value =
      latestLeave.leave_id;

    console.log(
      "SUBMITTED LEAVE ID:",
      submittedLeaveId.value
    );

    showSuccessModal.value = true;

    resetForm();

  } catch (error) {
    console.error(
      "SUBMIT LEAVE ERROR:",
      error
    );

    alert(
      "Failed to submit leave application. Please try again."
    );

  } finally {
    isSubmitting.value = false;
  }
};


const viewApplicationStatus = () => {

  if (!submittedLeaveId.value) {

    console.error(
      "No leave ID available"
    );

    return;
  }

  router.push({
    name: "LeaveApplicationPrint",

    params: {
      id: submittedLeaveId.value
    }
  });

};


const selectedLeaveName = computed(() => {

  const leave =
    leaveTypes.value.find(
      (l) =>
        l.leave_type_id ==
        form.value.leave_type_id
    );

  return leave?.leave_type_name ?? "";

});


const resetForm = () => {

  form.value = {

    leave_type_id: "",

    vacation_location_type: "",
    vacation_location: "",

    sick_type: "",
    illness: "",

    masters_degree: false,
    board_exam_review: false,

    monetization: false,
    terminal_leave: false,
    other_purpose: "",

    startDate: "",
    endDate: "",

    commutation: "",

    reason: "",

    attachments: [],

    declaration: false,

  };
  clearSignature();

};

</script>


<style scoped>

/* =========================================
   DASHBOARD THEME
========================================= */

.dashboard-shell {
  background: #080D14;
}


/* =========================================
   MAIN CARD
========================================= */

.neo-card {
  background: #111D2E;

  border: 1px solid #1E293B;

  border-radius: 1.4rem;

  box-shadow:
    0 10px 22px rgba(15, 23, 42, 0.18);

  transition:
    box-shadow 0.2s ease,
    transform 0.2s ease;
}

.neo-card:hover {
  box-shadow:
    0 14px 26px rgba(15, 23, 42, 0.28);
}


/* =========================================
   FORM SECTIONS
========================================= */

.form-section {
  padding-top: 0.5rem;
}


.section-title {
  color: #f8fafc;

  font-size: 1rem;

  font-weight: 600;

  margin-bottom: 1.25rem;
}


/* =========================================
   LABELS
========================================= */

.form-label {
  display: block;

  color: #cbd5e1;

  font-size: 0.875rem;

  font-weight: 500;

  margin-bottom: 0.5rem;
}


/* =========================================
   INPUTS
========================================= */

.input-field {
  width: 100%;

  border: 1px solid #334155;

  border-radius: 0.65rem;

  padding: 0.7rem 0.85rem;

  background: #0f1a2a;

  color: #f8fafc;

  font-size: 0.875rem;

  outline: none;

  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease,
    background 0.2s ease;
}

.input-field:focus {
  border-color: #3b82f6;

  box-shadow:
    0 0 0 2px rgba(59, 130, 246, 0.15);
}

.input-field::placeholder {
  color: #64748b;
}

.input-field:read-only {
  color: #cbd5e1;

  background: #0b1524;
}

select.input-field {
  color: #f8fafc;
}

select.input-field option {
  color: #f8fafc;

  background: #111D2E;
}


/* =========================================
   CONDITIONAL DETAIL BOXES
========================================= */

.detail-box {
  display: grid;

  grid-template-columns: 1fr 1fr;

  gap: 1rem;

  padding: 1rem;

  background: rgba(15, 26, 42, 0.7);

  border: 1px solid #334155;

  border-radius: 0.8rem;
}


/* =========================================
   CHECKBOX OPTIONS
========================================= */

.option-box {
  padding: 1rem;

  background: rgba(15, 26, 42, 0.7);

  border: 1px solid #334155;

  border-radius: 0.8rem;
}

.checkbox-label {
  display: flex;

  align-items: center;

  gap: 0.6rem;

  color: #cbd5e1;

  font-size: 0.875rem;

  margin-bottom: 0.75rem;

  cursor: pointer;
}

.checkbox-label:last-child {
  margin-bottom: 0;
}

.checkbox-input {
  width: 1rem;

  height: 1rem;

  accent-color: #2563eb;

  cursor: pointer;
}


/* =========================================
   LEAVE CREDITS
========================================= */

.credit-box {
  background:
    rgba(30, 64, 175, 0.12);

  border: 1px solid rgba(59, 130, 246, 0.25);

  border-radius: 1rem;

  padding: 1.25rem;
}

.credit-item {
  display: flex;

  align-items: center;

  gap: 0.35rem;

  font-size: 0.875rem;
}


/* =========================================
   UPLOAD AREA
========================================= */

.upload-box {
  border: 2px dashed #334155;

  border-radius: 1rem;

  padding: 2.5rem 1.5rem;

  text-align: center;

  background: rgba(15, 26, 42, 0.45);

  transition:
    border-color 0.2s ease,
    background 0.2s ease;
}

.upload-box:hover {
  border-color: #3b82f6;

  background:
    rgba(30, 64, 175, 0.08);
}


/* =========================================
   FILES
========================================= */

.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card button {
  letter-spacing: -0.01em;
}


/* =========================================
   DATE INPUT
========================================= */

input[type="date"]::-webkit-calendar-picker-indicator {
  filter: invert(1);

  opacity: 0.7;

  cursor: pointer;
}


/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 768px) {

  .detail-box {
    grid-template-columns: 1fr;
  }

}

.signature-box {
  width: 100%;
  height: 180px;
  border: 1px solid #334155;
  border-radius: 0.75rem;
  background: #0f1a2a;
  overflow: hidden;
}

.signature-canvas {
  width: 100%;
  height: 100%;
  display: block;
  cursor: crosshair;
  touch-action: none;
}

</style>