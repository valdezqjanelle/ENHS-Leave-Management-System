<template>
  <div v-if="leave" class="bg-gray-200 min-h-screen p-8">

    <!-- ========================================= -->
    <!-- ACTION BUTTONS -->
    <!-- ========================================= -->

    <div class="flex justify-end items-center gap-3 mb-4 print:hidden">

      <!-- BACK -->
      <button
        @click="goBack"
        type="button"
        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition"
      >
        Back
      </button>

      <!-- DOWNLOAD BACKEND PDF -->
      <button
        @click="downloadPdf"
        :disabled="downloadingPdf"
        type="button"
        class="bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg transition"
      >
        {{ downloadingPdf ? "Downloading..." : "Download PDF" }}
      </button>

      <!-- PRINT BACKEND PDF -->
      <button
        @click="printForm"
        :disabled="printingPdf"
        type="button"
        class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg transition"
      >
        {{ printingPdf ? "Preparing..." : "Print Form" }}
      </button>

      <!-- DOWNLOAD CIVIL SERVICE FORM -->
      <!-- <button
        @click="downloadForm"
        :disabled="downloadingForm"
        type="button"
        class="bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 py-2 rounded-lg transition"
      >
        {{
          downloadingForm
            ? "Downloading..."
            : "Download Civil Service Form"
        }}
      </button> -->
    </div>


    <!-- ========================================= -->
    <!-- PAGE 1 - APPLICATION FORM -->
    <!-- ========================================= -->

    <div id="printArea" class="mx-auto bg-white text-black">

      <!-- ================= HEADER ================= -->

      <div class="relative mb-4 font-[Arial] text-[8px]">

        <div class="absolute left-0 top-0 font-bold italic leading-tight">
          <div>
            <b>Civil Service Form No. 6</b>
          </div>

          <div>
            <b>Revised 2020</b>
          </div>
        </div>

        <div class="text-center leading-tight">

          <div
            class="font-['Old_English_Text_MT'] text-[12px] font-bold"
          >
            Republic of the Philippines
          </div>

          <div
            class="font-['Old_English_Text_MT'] text-[18px] font-bold"
          >
            Department of Education
          </div>

          <div
            class="font-['Times_New_Roman'] text-[12px] font-bold"
          >
            Region 02-Cagayan Valley
          </div>

          <div
            class="font-['Times_New_Roman'] text-[12px] font-bold"
          >
            SCHOOL DIVISION OFFICE OF ISABELA
          </div>

          <h1 class="text-xl font-bold mt-4 tracking-wide">
            APPLICATION FOR LEAVE
          </h1>

        </div>
      </div>


      <!-- ================= FORM BODY ================= -->

      <div class="form-body border-2 border-black">

        <!-- ========================================= -->
        <!-- 1. OFFICE / 2. NAME -->
        <!-- ========================================= -->

        <div class="border-b border-black p-2 field-row">

          <div
            class="flex items-baseline flex-wrap"
            style="column-gap: 2.5rem"
          >

            <span class="font-bold">
              1. OFFICE/DEPARTMENT
            </span>

            <span class="font-bold">
              2. NAME :
            </span>

            <span class="text-[10px]">
              (Last)
            </span>

            <span
              class="text-[10px]"
              style="margin-left: 6rem"
            >
              (First)
            </span>

            <span
              class="text-[10px]"
              style="margin-left: 6rem"
            >
              (Middle)
            </span>

          </div>

          <div class="mt-6">

            {{ leave.employee?.department }}

            <span
              v-if="
                leave.employee?.last_name ||
                leave.employee?.first_name ||
                leave.employee?.middle_name
              "
              class="ml-10"
            >
              {{ leave.employee?.last_name }}

              &nbsp;&nbsp;&nbsp;&nbsp;

              {{ leave.employee?.first_name }}

              &nbsp;&nbsp;&nbsp;&nbsp;

              {{ leave.employee?.middle_name }}
            </span>

          </div>

        </div>


        <!-- ========================================= -->
        <!-- 3 / 4 / 5 -->
        <!-- ========================================= -->

        <div
          class="border-b border-black p-2 flex items-baseline whitespace-nowrap"
          style="gap: 14px"
        >

          <span>

            <span class="font-bold">
              3. DATE OF FILING
            </span>

            <span
              class="fill-underline"
              style="min-width: 95px"
            >
              {{ formatDate(leave.date_filed) }}
            </span>

          </span>


          <span>

            <span class="font-bold">
              4. POSITION
            </span>

            <span
              class="fill-underline"
              style="min-width: 170px"
            >
              {{ leave.employee?.position }}
            </span>

          </span>


          <span>

            <span class="font-bold">
              5. SALARY
            </span>

            <span
              class="fill-underline"
              style="min-width: 90px"
            >
              {{
                leave.employee?.salary
                  ? "₱" + leave.employee.salary
                  : ""
              }}
            </span>

          </span>

        </div>


        <!-- ========================================= -->
        <!-- 6. DETAILS TITLE -->
        <!-- ========================================= -->

        <div
          class="text-center font-bold double-border-y font-[Arial] text-[12px]"
        >
          6. DETAILS OF APPLICATION
        </div>


        <!-- ========================================= -->
        <!-- 6.A + 6.B -->
        <!-- ========================================= -->

        <div class="grid grid-cols-2 border-b border-black">

          <!-- ================= 6.A ================= -->

          <div class="border-r border-black">

            <div class="px-2 py-1 font-bold">
              6.A TYPE OF LEAVE TO BE AVAILED OF
            </div>

            <div class="px-3 pb-2">

              <div
                v-for="lt in leaveTypeList"
                :key="lt.key"
                class="flex items-start mb-1"
              >

                <span
                  class="checkbox"
                  style="margin-top: 1px"
                >
                  <span v-if="isLeaveTypeSelected(lt)">
                    &#10003;
                  </span>
                </span>

                <span class="ml-1 leading-tight">

                  <span
                    class="text-[10px] font-[Arial] font-bold"
                  >
                    {{ lt.label }}
                  </span>

                  <span
                    class="text-[8px] font-[Arial] italic text-gray-700 ml-1"
                  >
                    ({{ lt.citation }})
                  </span>

                </span>

              </div>


              <div class="mt-3">

                <div class="italic">
                  Others:
                </div>

                <div class="fill-line">
                  {{ leave.other_purpose }}
                </div>

              </div>

            </div>

          </div>


          <!-- ================= 6.B ================= -->

          <div class="px-3 pb-3">

            <div class="font-bold py-1">
              6.B DETAILS OF LEAVE
            </div>


            <!-- Vacation -->

            <div class="mb-3">

              <div class="italic">
                In case of Vacation/Special Privilege Leave:
              </div>

              <div class="flex items-baseline mt-1">

                <span class="checkbox">

                  <span
                    v-if="
                      leave.vacation_location_type ===
                      'within_philippines'
                    "
                  >
                    &#10003;
                  </span>

                </span>

                <span>
                  Within the Philippines
                </span>

                <span class="fill-underline flex-1">
                  {{
                    leave.vacation_location_type ===
                    "within_philippines"
                      ? leave.vacation_location
                      : ""
                  }}
                </span>

              </div>


              <div class="flex items-baseline mt-1">

                <span class="checkbox">

                  <span
                    v-if="
                      leave.vacation_location_type ===
                      'abroad'
                    "
                  >
                    &#10003;
                  </span>

                </span>

                <span>
                  Abroad (Specify)
                </span>

                <span class="fill-underline flex-1">
                  {{
                    leave.vacation_location_type ===
                    "abroad"
                      ? leave.vacation_location
                      : ""
                  }}
                </span>

              </div>

            </div>


            <!-- Sick -->

            <div class="mb-3">

              <div class="italic">
                In case of Sick Leave:
              </div>

              <div class="flex items-baseline mt-1">

                <span class="checkbox">

                  <span
                    v-if="
                      leave.sick_type === 'in_hospital'
                    "
                  >
                    &#10003;
                  </span>

                </span>

                <span>
                  In Hospital (Specify Illness)
                </span>

                <span class="fill-underline flex-1">
                  {{
                    leave.sick_type === "in_hospital"
                      ? leave.illness
                      : ""
                  }}
                </span>

              </div>


              <div class="flex items-baseline mt-1">

                <span class="checkbox">

                  <span
                    v-if="
                      leave.sick_type === 'out_patient'
                    "
                  >
                    &#10003;
                  </span>

                </span>

                <span>
                  Out Patient (Specify Illness)
                </span>

                <span class="fill-underline flex-1">
                  {{
                    leave.sick_type === "out_patient"
                      ? leave.illness
                      : ""
                  }}
                </span>

              </div>

              <div class="fill-line mt-1">
                &nbsp;
              </div>

            </div>


            <!-- Women -->

            <div class="mb-3">

              <div class="italic">
                In case of Special Leave Benefits for Women:
              </div>

              <div>

                (Specify Illness)

                <span class="fill-underline">
                  {{ leave.special_leave_women || "" }}
                </span>

              </div>

              <div class="fill-line mt-1">
                &nbsp;
              </div>

            </div>


            <!-- Study -->

            <div class="mb-3">

              <div class="italic">
                In case of Study Leave:
              </div>

              <div class="flex items-center mt-1">

                <span class="checkbox">

                  <span v-if="leave.masters_degree">
                    &#10003;
                  </span>

                </span>

                <span>
                  Completion of Master's Degree
                </span>

              </div>


              <div class="flex items-center mt-1">

                <span class="checkbox">

                  <span v-if="leave.board_exam_review">
                    &#10003;
                  </span>

                </span>

                <span>
                  BAR/Board Examination Review
                </span>

              </div>

            </div>


            <!-- Other Purpose -->

            <div>

              <div class="italic">
                Other purpose:
              </div>

              <div class="flex items-center mt-1">

                <span class="checkbox">

                  <span v-if="leave.monetization">
                    &#10003;
                  </span>

                </span>

                <span>
                  Monetization of Leave Credits
                </span>

              </div>


              <div class="flex items-center mt-1">

                <span class="checkbox">

                  <span v-if="leave.terminal_leave">
                    &#10003;
                  </span>

                </span>

                <span>
                  Terminal Leave
                </span>

              </div>

            </div>

          </div>

        </div>


        <!-- ========================================= -->
        <!-- 6.C + 6.D -->
        <!-- ========================================= -->

        <div class="grid grid-cols-2 double-border-y no-top">

          <!-- ================= 6.C ================= -->

          <div class="border-r border-black p-3">

            <div class="font-bold mb-1">
              6.C NUMBER OF WORKING DAYS APPLIED FOR
            </div>

            <div class="fill-line">
              {{ leave.number_of_days }}
            </div>

            <div class="font-bold mt-4">
              INCLUSIVE DATES
            </div>

            <div class="fill-line">

              <span v-if="leave.start_date">

                {{ formatDate(leave.start_date) }}

                -

                {{ formatDate(leave.end_date) }}

              </span>

            </div>

          </div>


          <!-- ================= 6.D ================= -->

          <div class="p-3">

            <div class="font-bold mb-2">
              6.D COMMUTATION
            </div>


            <div class="flex items-center mb-2">

              <span class="checkbox">

                <span
                  v-if="
                    leave.commutation === 'not_requested' ||
                    leave.commutation === 'not requested'
                  "
                >
                  &#10003;
                </span>

              </span>

              <span>
                Not Requested
              </span>

            </div>


            <div class="flex items-center">

              <span class="checkbox">

                <span
                  v-if="
                    leave.commutation === 'requested'
                  "
                >
                  &#10003;
                </span>

              </span>

              <span>
                Requested
              </span>

            </div>


            <div class="mt-8 text-center">

              <img
                v-if="leave.applicants_signature"
                :src="leave.applicants_signature"
                class="applicant-signature"
                alt="Applicant signature"
              />

              <div class="signature-line"></div>

              <div class="text-xs mt-1">
                (Signature of Applicant)
              </div>

            </div>

          </div>

        </div>


        <!-- ========================================= -->
        <!-- 7. DETAILS OF ACTION -->
        <!-- ========================================= -->

        <div class="text-center font-bold py-2 double-border-y">
          7. DETAILS OF ACTION ON APPLICATION
        </div>


        <!-- ========================================= -->
        <!-- 7.A + 7.B -->
        <!-- ========================================= -->

        <div class="grid grid-cols-2 border-b border-black">

          <!-- ================= 7.A ================= -->

          <div class="border-r border-black">

            <div class="font-bold p-2">
              7.A CERTIFICATION OF LEAVE CREDITS
            </div>

            <div class="p-3">

              <div class="text-center mb-2">

                As of

                <span class="fill-underline">
                  {{ formatDate(leave.credits_as_of) }}
                </span>

              </div>


              <table
                class="text-xs mx-auto"
                style="width: 96%"
              >

                <tr>

                  <th></th>

                  <th class="whitespace-nowrap">
                    Vacation Leave
                  </th>

                  <th class="whitespace-nowrap">
                    Sick Leave
                  </th>

                </tr>


                <tr>

                  <td class="italic text-left">
                    Total Earned
                  </td>

                  <td>
                    {{ leave.vacation_total_earned ?? "" }}
                  </td>

                  <td>
                    {{ leave.sick_total_earned ?? "" }}
                  </td>

                </tr>


                <tr>

                  <td class="italic text-left">
                    Less this application
                  </td>

                  <td>
                    {{ leave.vacation_less_application ?? "" }}
                  </td>

                  <td>
                    {{ leave.sick_less_application ?? "" }}
                  </td>

                </tr>


                <tr>

                  <td class="italic text-left">
                    Balance
                  </td>

                  <td>
                    {{ leave.vacation_balance ?? "" }}
                  </td>

                  <td>
                    {{ leave.sick_balance ?? "" }}
                  </td>

                </tr>

              </table>


              <div class="mt-10 text-center">

                <div class="signature-line"></div>

                <div class="text-xs mt-1">
                  (Authorized Officer)
                </div>

              </div>

            </div>

          </div>


          <!-- ================= 7.B ================= -->

          <div>

            <div class="font-bold p-2">
              7.B RECOMMENDATION
            </div>

            <div class="p-3">

              <div class="flex items-center mb-2">

                <span class="checkbox">

                  <span
                    v-if="
                      leave.recommendation_status ===
                      'approved'
                    "
                  >
                    &#10003;
                  </span>

                </span>

                <span>
                  For approval
                </span>

              </div>


              <div class="flex items-baseline mb-2">

                <span class="checkbox">

                  <span
                    v-if="
                      leave.recommendation_status ===
                      'disapproved'
                    "
                  >
                    &#10003;
                  </span>

                </span>

                <span>
                  For disapproval due to
                </span>

                <span class="fill-underline flex-1">

                  {{
                    leave.recommendation_status ===
                    "disapproved"
                      ? leave.recommendation_reason
                      : ""
                  }}

                </span>

              </div>


              <div class="fill-line mb-1">
                &nbsp;
              </div>

              <div class="fill-line mb-1">
                &nbsp;
              </div>


              <div class="mt-8 text-center">

                <div class="signature-line"></div>

                <div class="text-xs mt-1">
              <b>Emily O. Benitez, EdD </b> 
            </div>

            <div class="text-xs mt-1">
              School Principal II
            </div>

              </div>

            </div>

          </div>

        </div>


        <!-- ========================================= -->
        <!-- 7.C + 7.D -->
        <!-- ========================================= -->

        <div>

          <div class="grid grid-cols-2">

            <!-- ================= 7.C ================= -->

            <div class="p-3">

              <div class="font-bold mb-2">
                7.C APPROVED FOR:
              </div>


              <div class="mb-1">

                <span
                  class="fill-underline"
                  style="min-width: 50px"
                >
                  {{ leave.days_with_pay ?? "" }}
                </span>

                days with pay

              </div>


              <div class="mb-1">

                <span
                  class="fill-underline"
                  style="min-width: 50px"
                >
                  {{ leave.days_without_pay ?? "" }}
                </span>

                days without pay

              </div>


              <div>

                <span
                  class="fill-underline"
                  style="min-width: 50px"
                >
                  {{ leave.other_approval || "" }}
                </span>

                others (Specify)

              </div>

            </div>


            <!-- ================= 7.D ================= -->

            <div class="p-3">

              <div class="font-bold mb-2">
                7.D DISAPPROVED DUE TO:
              </div>


              <div class="fill-line mb-1">

                {{ leave.disapproval_reason || "" }}

                &nbsp;

              </div>

              <div class="fill-line mb-1">
                &nbsp;
              </div>

              <div class="fill-line mb-1">
                &nbsp;
              </div>

            </div>

          </div>


          <!-- Authorized Official -->

          <div class="text-center pb-3 pt-10">

            <div
              class="signature-line inline-block"
              style="width: 280px"
            ></div>

            <div class="text-xs mt-1">
              (Authorized Official)
            </div>

          </div>

        </div>

      </div>
      <!-- END FORM BODY -->

    </div>
    <!-- END APPLICATION FORM -->


    <!-- ========================================= -->
    <!-- ATTACHMENTS - A4 PAGE(S) -->
    <!-- ========================================= -->

    <div
      v-if="
        leave.attachments &&
        leave.attachments.length > 0
      "
      class="attachment-pages mx-auto bg-white text-black"
    >

      <!-- ================= ATTACHMENT HEADER ================= -->

      <div class="attachment-header">

        <h2>
          SUPPORTING DOCUMENTS
        </h2>


        <div class="attachment-info">

          <strong>
            Leave Application ID:
          </strong>

          {{ leave.leave_id }}

        </div>


        <div class="attachment-info">

          <strong>
            Employee:
          </strong>

          {{ leave.employee?.last_name }},
          {{ leave.employee?.first_name }}
          {{ leave.employee?.middle_name }}

        </div>


        <div class="attachment-info">

          <strong>
            Leave Type:
          </strong>

          {{ currentLeaveTypeName }}

        </div>


        <div class="attachment-info">

          <strong>
            Inclusive Dates:
          </strong>

          {{ formatDate(leave.start_date) }}

          -

          {{ formatDate(leave.end_date) }}

        </div>

      </div>


      <!-- ================= ATTACHMENTS ================= -->

      <div
        v-for="(attachment, index) in leave.attachments"
        :key="
          attachment.attachment_id ||
          attachment.id ||
          index
        "
        class="attachment-item"
      >

        <!-- Attachment title -->

        <div class="attachment-title">

          <span>
            Attachment {{ Number(index) + 1 }}
          </span>

          <span class="attachment-name">

            {{
              attachment.file_name ||
              attachment.name ||
              attachment.original_name ||
              "Supporting Document"
            }}

          </span>

        </div>


        <!-- ================= PDF ================= -->

        <iframe
          v-if="isPdf(attachment)"
          :src="getAttachmentUrl(attachment)"
          class="attachment-preview"
          title="PDF Attachment"
        ></iframe>


        <!-- ================= IMAGE ================= -->

        <img
          v-else-if="isImage(attachment)"
          :src="getAttachmentUrl(attachment)"
          class="attachment-image"
          alt="Supporting document"
        />


        <!-- ================= OTHER DOCUMENT ================= -->

        <div
          v-else
          class="attachment-file"
        >

          <div class="attachment-icon">
            📄
          </div>

          <div>

            <div class="font-bold text-base">

              {{
                attachment.file_name ||
                attachment.name ||
                attachment.original_name ||
                "Supporting Document"
              }}

            </div>


            <div class="text-gray-600 mt-2">
              This document was submitted as a supporting attachment.
            </div>


            <div
              v-if="getAttachmentUrl(attachment)"
              class="mt-4 text-xs text-gray-500"
            >

              File type:
              {{ attachment.file_type || "Document" }}

            </div>

          </div>

        </div>

      </div>

    </div>
    <!-- END ATTACHMENTS -->

  </div>

  <!-- LOADING -->
  <div
    v-else-if="loading"
    class="min-h-screen bg-gray-200 flex items-center justify-center"
  >
    <div class="text-gray-700 text-lg">
      Loading leave application...
    </div>
  </div>

  <!-- ERROR -->
  <div
    v-else
    class="min-h-screen bg-gray-200 flex items-center justify-center"
  >
    <div class="bg-white p-8 rounded-lg shadow text-center">
      <h2 class="text-xl font-bold text-red-600 mb-2">
        Unable to load leave application
      </h2>

      <p class="text-gray-600 mb-4">
        The leave application could not be found.
      </p>

      <button
        @click="goBack"
        type="button"
        class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg"
      >
        Back
      </button>
    </div>
  </div>
</template>


<script setup lang="ts">
import {
  ref,
  computed,
  onMounted,
} from "vue";

import {
  useRoute,
  useRouter,
} from "vue-router";

import {
  getLeave,
  getLeaveTypes,
  downloadLeavePdf,
} from "../services/leave";

import html2pdf from "html2pdf.js";


/* =========================================
   ROUTER
========================================= */

const route = useRoute();
const router = useRouter();


/* =========================================
   STATE
========================================= */

const leave = ref<any>(null);

const leaveTypes = ref<any[]>([]);

const loading = ref(true);

const downloadingPdf = ref(false);

const downloadingForm = ref(false);

const printingPdf = ref(false);


/* =========================================
   GO BACK
========================================= */

const goBack = () => {
  router.push("/admin-applications");
};


/* =========================================
   LEAVE TYPE LIST
========================================= */

const leaveTypeList = [
  {
    key: "vacation",
    label: "Vacation Leave",
    citation:
      "Sec. 51, Rule XVI, Omnibus Rules Implementing E.O. No. 292",
    match: ["vacation"],
  },

  {
    key: "mandatory",
    label: "Mandatory/Forced Leave",
    citation:
      "Sec. 25, Rule XVI, Omnibus Rules Implementing E.O. No. 292",
    match: ["mandatory", "forced"],
  },

  {
    key: "sick",
    label: "Sick Leave",
    citation:
      "Sec. 43, Rule XVI, Omnibus Rules Implementing E.O. No. 292",
    match: ["sick"],
  },

  {
    key: "maternity",
    label: "Maternity Leave",
    citation:
      "R.A. No. 11210 / IRR issued by CSC, DOLE and SSS",
    match: ["maternity"],
  },

  {
    key: "paternity",
    label: "Paternity Leave",
    citation:
      "R.A. No. 8187 / CSC MC No. 71, s. 1998, as amended",
    match: ["paternity"],
  },

  {
    key: "special_privilege",
    label: "Special Privilege Leave",
    citation:
      "Sec. 21, Rule XVI, Omnibus Rules Implementing E.O. No. 292",
    match: ["special privilege"],
  },

  {
    key: "solo_parent",
    label: "Solo Parent Leave",
    citation:
      "RA No. 8972 / CSC MC No. 8, s. 2004",
    match: ["solo parent"],
  },

  {
    key: "study",
    label: "Study Leave",
    citation:
      "Sec. 68, Rule XVI, Omnibus Rules Implementing E.O. No. 292",
    match: ["study"],
  },

  {
    key: "vawc",
    label: "10-Day VAWC Leave",
    citation:
      "RA No. 9262 / CSC MC No. 15, s. 2005",
    match: ["vawc"],
  },

  {
    key: "rehabilitation",
    label: "Rehabilitation Privilege",
    citation:
      "Sec. 55, Rule XVI, Omnibus Rules Implementing E.O. No. 292",
    match: ["rehabilitation"],
  },

  {
    key: "women",
    label: "Special Leave Benefits for Women",
    citation:
      "RA No. 9710 / CSC MC No. 25, s. 2010",
    match: ["women"],
  },

  {
    key: "calamity",
    label: "Special Emergency (Calamity) Leave",
    citation:
      "CSC MC No. 2, s. 2012, as amended",
    match: ["calamity", "emergency"],
  },

  {
    key: "adoption",
    label: "Adoption Leave",
    citation:
      "R.A. No. 8552",
    match: ["adoption"],
  },
];


/* =========================================
   SELECTED LEAVE TYPE
========================================= */

const currentLeaveTypeName = computed(() => {

  const current = leaveTypes.value.find(
    (type) =>
      type.leave_type_id ==
      leave.value?.leave_type_id
  );

  return current?.leave_type_name || "";
});


/* =========================================
   CHECK LEAVE TYPE
========================================= */

const isLeaveTypeSelected = (
  lt: { match: string[] }
) => {

  const current = leaveTypes.value.find(
    (type) =>
      type.leave_type_id ==
      leave.value?.leave_type_id
  );

  const name = (
    current?.leave_type_name || ""
  ).toLowerCase();

  if (!name) {
    return false;
  }

  return lt.match.some(
    (match) =>
      name.includes(match)
  );
};


/* =========================================
   FORMAT DATE
========================================= */

const formatDate = (
  date: string | null
) => {

  if (!date) {
    return "";
  }

  return new Date(date).toLocaleDateString(
    "en-US",
    {
      year: "numeric",
      month: "long",
      day: "numeric",
    }
  );
};


/* =========================================
   GET ATTACHMENT URL
========================================= */

const getAttachmentUrl = (
  attachment: any
) => {

  if (attachment.file_url) {
    return attachment.file_url;
  }

  if (attachment.url) {
    return attachment.url;
  }

  if (attachment.download_url) {
    return attachment.download_url;
  }

  if (attachment.file_path) {
    return `https://enhs-leave-management-system.onrender.com/storage/${attachment.file_path}`;
    
  }

  return "";
};


/* =========================================
   ATTACHMENT NAME
========================================= */

const getAttachmentName = (
  attachment: any
) => {

  return (
    attachment.file_name ||
    attachment.name ||
    attachment.original_name ||
    ""
  ).toLowerCase();
};


/* =========================================
   CHECK PDF
========================================= */

const isPdf = (
  attachment: any
) => {

  const name =
    getAttachmentName(attachment);

  return (
    attachment.file_type ===
      "application/pdf" ||
    name.endsWith(".pdf")
  );
};


/* =========================================
   CHECK IMAGE
========================================= */

const isImage = (
  attachment: any
) => {

  const name =
    getAttachmentName(attachment);

  return (
    attachment.file_type?.startsWith(
      "image/"
    ) ||
    /\.(jpg|jpeg|png|gif|webp)$/i.test(
      name
    )
  );
};


/* =========================================
   DOWNLOAD BACKEND PDF
========================================= */

const downloadPdf = async () => {

  if (!leave.value?.leave_id) {
    alert("Leave application not found.");
    return;
  }

  downloadingPdf.value = true;

  try {

    const blob =
      await downloadLeavePdf(
        leave.value.leave_id
      );

    if (!blob || blob.size === 0) {
      throw new Error(
        "No PDF file was returned."
      );
    }

    const url =
      window.URL.createObjectURL(blob);

    const link =
      document.createElement("a");

    link.href = url;

    link.download =
      `Leave_Application_${employeeName}_${leave.value.leave_id}.pdf`;

    link.style.display = "none";

    document.body.appendChild(link);

    link.click();

    document.body.removeChild(link);

    setTimeout(() => {
      window.URL.revokeObjectURL(url);
    }, 1000);

  } catch (error) {

    console.error(
      "Failed to download PDF:",
      error
    );

    alert(
      "Failed to download PDF. Please try again."
    );

  } finally {

    downloadingPdf.value = false;

  }
};


/* =========================================
   PRINT BACKEND PDF
========================================= */

const printForm = async () => {

  if (!leave.value?.leave_id) {
    alert("Leave application not found.");
    return;
  }

  printingPdf.value = true;

  try {

    /*
     * Get the PDF from Laravel.
     *
     * IMPORTANT:
     * There is only ONE `url` declaration here.
     */

    const blob =
      await downloadLeavePdf(
        leave.value.leave_id
      );

    if (!blob || blob.size === 0) {
      throw new Error(
        "Empty PDF response."
      );
    }

    const url =
      window.URL.createObjectURL(blob);

    const iframe =
      document.createElement("iframe");

    iframe.style.position = "fixed";
    iframe.style.right = "0";
    iframe.style.bottom = "0";
    iframe.style.width = "1px";
    iframe.style.height = "1px";
    iframe.style.border = "0";
    iframe.style.opacity = "0";

    iframe.src = url;

    document.body.appendChild(iframe);

    iframe.onload = () => {

      setTimeout(() => {

        try {

          iframe.contentWindow?.focus();

          iframe.contentWindow?.print();

        } catch (error) {

          console.error(
            "Print window error:",
            error
          );

        }

      }, 500);
    };


    /*
     * Clean up iframe and object URL later.
     */

    setTimeout(() => {

      window.URL.revokeObjectURL(url);

      if (iframe.parentNode) {
        iframe.parentNode.removeChild(iframe);
      }

    }, 60000);

  } catch (error) {

    console.error(
      "Failed to print PDF:",
      error
    );

    alert(
      "Failed to prepare the PDF for printing. Please try again."
    );

  } finally {

    printingPdf.value = false;

  }
};


/* =========================================
   DOWNLOAD CIVIL SERVICE FORM
   USING HTML2PDF
========================================= */

const downloadForm = async () => {

  if (!leave.value?.leave_id) {
    alert(
      "Leave application not found."
    );

    return;
  }

  downloadingForm.value = true;

  try {

    const element =
      document.getElementById(
        "printArea"
      );

    if (!element) {
      throw new Error(
        "Leave application form not found."
      );
    }


    const employeeName = [
      leave.value.employee?.last_name,
      leave.value.employee?.first_name,
    ]
      .filter(Boolean)
      .join("_") ||
      "leave_application";


    const filename =
      `Civil_Service_Form_6_${employeeName}_${leave.value.leave_id}.pdf`;


    await html2pdf()
      .set({

        margin: 0,

        filename: filename,

        image: {
          type: "jpeg",
          quality: 0.98,
        },

        html2canvas: {

          scale: 2,

          useCORS: true,

          backgroundColor: "#ffffff",

          scrollX: 0,

          scrollY: 0,

          windowWidth:
            element.scrollWidth,
        },

        jsPDF: {

          unit: "mm",

          format: "a4",

          orientation: "portrait",

        },

        pagebreak: {
          mode: [
            "avoid-all",
            "css",
            "legacy",
          ],
        },

      })
      .from(element)
      .save();

  } catch (error) {

    console.error(
      "Failed to download Civil Service Form:",
      error
    );

    alert(
      "Failed to download Civil Service Form. Please try again."
    );

  } finally {

    downloadingForm.value = false;

  }
};


/* =========================================
   LOAD LEAVE
========================================= */

const loadLeave = async () => {

  try {

    leave.value =
      await getLeave(
        Number(route.params.id)
      );

    console.log(
      "LOADED LEAVE:",
      leave.value
    );

    console.log(
      "ATTACHMENTS:",
      leave.value?.attachments
    );


    leaveTypes.value =
      await getLeaveTypes();

  } catch (error) {

    console.error(
      "Failed loading leave:",
      error
    );

  } finally {

    loading.value = false;

  }
};


/* =========================================
   ON MOUNT
========================================= */

onMounted(() => {
  loadLeave();
});

</script>


<style scoped>

/* =========================================
   A4 PAGE - APPLICATION FORM
========================================= */

#printArea {

  width: 210mm;

  min-height: 297mm;

  padding:
    12.7mm
    25.4mm
    25.4mm
    12.7mm;

  margin: 0 auto;

  background: white;

  color: black !important;

  font-family:
    Arial,
    Helvetica,
    sans-serif;

  font-size: 11px;

  box-sizing: border-box;

}


#printArea * {
  color: black !important;
}


/* =========================================
   FIELD ROW
========================================= */

.field-row {
  min-height: 60px;
}


/* =========================================
   CHECKBOX
========================================= */

.checkbox {

  width: 9px;

  height: 9px;

  min-width: 9px;

  border: 1.4px solid black;

  display: inline-flex;

  align-items: center;

  justify-content: center;

  margin-right: 1px;

  margin-left: -7px;

  font-size: 10px;

  line-height: 1;

  flex-shrink: 0;

}


/* =========================================
   FILL LINE
========================================= */

.fill-line {

  border-bottom: 1px solid black;

  min-height: 16px;

  padding: 0 4px;

}


/* =========================================
   FILL UNDERLINE
========================================= */

.fill-underline {

  border-bottom: 1px solid black;

  display: inline-block;

  min-width: 120px;

  padding: 0 4px;

  margin-left: 4px;

}


/* =========================================
   SIGNATURE
========================================= */

.signature-line {

  border-bottom: 1px solid black;

  height: 26px;

}


.applicant-signature {

  display: block;

  width: 150px;

  height: 42px;

  object-fit: contain;

  margin: 0 auto -16px;

}


/* =========================================
   DOUBLE BORDER
========================================= */

.double-border-y {

  border-top: 3px double black;

  border-bottom: 3px double black;

}


.double-border-y.no-top {
  border-top-width: 0;
}


/* =========================================
   TABLE
========================================= */

table,
th,
td {

  border: 1px solid black;

  border-collapse: collapse;

}


th,
td {

  padding: 3px 6px;

  text-align: center;

}


/* =========================================
   ATTACHMENT PAGE
========================================= */

.attachment-pages {

  width: 210mm;

  min-height: 297mm;

  padding: 20mm;

  margin: 24px auto 0;

  background: white;

  color: black;

  box-sizing: border-box;

}


/* =========================================
   ATTACHMENT HEADER
========================================= */

.attachment-header {

  border-bottom: 2px solid black;

  padding-bottom: 10px;

  margin-bottom: 20px;

}


.attachment-header h2 {

  text-align: center;

  font-size: 16px;

  font-weight: bold;

  margin-bottom: 12px;

}


.attachment-info {

  font-size: 11px;

  margin-top: 4px;

}


/* =========================================
   ATTACHMENT ITEM
========================================= */

.attachment-item {

  margin-bottom: 20px;

  break-inside: avoid;

  page-break-inside: avoid;

}


/* =========================================
   ATTACHMENT TITLE
========================================= */

.attachment-title {

  display: flex;

  justify-content: space-between;

  align-items: center;

  border: 1px solid black;

  padding: 8px;

  font-size: 11px;

  font-weight: bold;

  background: #f3f4f6;

}


.attachment-name {

  font-weight: normal;

  margin-left: 15px;

  word-break: break-all;

}


/* =========================================
   PDF ATTACHMENT
========================================= */

.attachment-preview {

  width: 100%;

  height: 240mm;

  border: 1px solid black;

  margin-top: 8px;

  display: block;

}


/* =========================================
   IMAGE ATTACHMENT
========================================= */

.attachment-image {

  display: block;

  max-width: 100%;

  max-height: 240mm;

  margin: 10px auto;

  object-fit: contain;

  border: 1px solid black;

}


/* =========================================
   OTHER DOCUMENT
========================================= */

.attachment-file {

  min-height: 100mm;

  border: 1px solid #999;

  display: flex;

  align-items: center;

  justify-content: center;

  gap: 15px;

  margin-top: 8px;

  padding: 30px;

  text-align: center;

}


.attachment-icon {
  font-size: 40px;
}


/* =========================================
   PAGE SETUP
========================================= */

@page {

  size: A4 portrait;

  margin: 0;

}


/* =========================================
   PRINT
========================================= */

@media print {

  body {

    background: white !important;

    margin: 0 !important;

    padding: 0 !important;

  }


  .print\:hidden {

    display: none !important;

  }


  /* PAGE 1 */

  #printArea {

    width: 210mm !important;

    min-height: 297mm !important;

    margin: 0 !important;

    padding:
      12.7mm
      25.4mm
      25.4mm
      12.7mm !important;

    box-sizing: border-box;

    page-break-after: always;

    break-after: page;

  }


  /* ATTACHMENT PAGE */

  .attachment-pages {

    width: 210mm !important;

    min-height: 297mm !important;

    margin: 0 !important;

    padding: 20mm !important;

    box-sizing: border-box;

    page-break-before: always;

    break-before: page;

  }


  .attachment-item {

    break-inside: avoid;

    page-break-inside: avoid;

  }


  .attachment-preview {

    width: 100% !important;

    height: 240mm !important;

  }


  .attachment-image {

    max-width: 100% !important;

    max-height: 240mm !important;

  }


  #printArea,
  #printArea *,
  .attachment-pages,
  .attachment-pages * {

    color: black !important;

    -webkit-print-color-adjust: exact;

    print-color-adjust: exact;

  }

}

</style>