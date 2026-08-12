<template>
  <div v-if="leave" class="bg-gray-200 min-h-screen p-8">
    <!-- PRINT / DOWNLOAD BUTTONS -->
    <div class="flex justify-end gap-3 mb-4 print:hidden">
      <button
        @click="downloadPdf"
        :disabled="downloadingPdf"
        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg disabled:opacity-50"
      >
        {{ downloadingPdf ? "Downloading..." : "Download PDF" }}
      </button>

      <button
        @click="printForm"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg"
      >
        Print Form
      </button>
    </div>


    <div id="printArea" class="mx-auto bg-white border border-black text-black">

      <div class="text-center border-b-2 border-black py-3">
        <div>Civil Service Form No. 6</div>

        <div class="mb-2">Revised 2020</div>

        <div>Republic of the Philippines</div>

        <div>Department of Education</div>

        <div>Region II – Cagayan Valley</div>

        <div>Schools Division of Isabela</div>

        <h1 class="text-xl font-bold mt-4 tracking-wide">
          APPLICATION FOR LEAVE
        </h1>
      </div>

      <!-- ================= BASIC INFORMATION ================= -->

      <div class="grid grid-cols-12 border-b border-black">
        <!-- OFFICE -->

        <div class="col-span-4 border-r border-black p-2">
          <div class="font-bold">1. OFFICE / DEPARTMENT</div>

          <div class="border-b border-black h-7 mt-4 px-2">
            {{ leave.employee.department }}
          </div>
        </div>

        <!-- NAME -->

        <div class="col-span-8 p-2">
          <div class="font-bold mb-3">2. NAME</div>

          <div class="grid grid-cols-3 gap-3">
            <div>
              <div class="border-b border-black h-7 px-2">
                {{ leave.employee.last_name }}
              </div>

              <div class="text-center text-xs">(Last)</div>
            </div>

            <div>
              <div class="border-b border-black h-7 px-2">
                {{ leave.employee.first_name }}
              </div>

              <div class="text-center text-xs">(First)</div>
            </div>

            <div>
              <div class="border-b border-black h-7 px-2">
                {{ leave.employee.middle_name }}
              </div>

              <div class="text-center text-xs">(Middle)</div>
            </div>
          </div>
        </div>
      </div>

      <!-- SECOND ROW -->

      <div class="grid grid-cols-12 border-b-2 border-black">
        <div class="col-span-4 border-r border-black p-2">
          <div class="font-bold">3. DATE OF FILING</div>

          <div class="border-b border-black h-7 mt-4 px-2">
            {{ formatDate(leave.date_filed) }}
          </div>
        </div>

        <div class="col-span-4 border-r border-black p-2">
          <div class="font-bold">4. POSITION</div>

          <div class="border-b border-black h-7 mt-4 px-2">
            {{ leave.employee.position }}
          </div>
        </div>

        <div class="col-span-4 p-2">
          <div class="font-bold">5. SALARY</div>

          <div class="border-b border-black h-7 mt-4 px-2">
            ₱{{ leave.employee.salary }}
          </div>
        </div>
      </div>

      <!-- ================= DETAILS TITLE ================= -->

      <div class="text-center font-bold text-lg border-b-2 border-black py-1">
        6. DETAILS OF APPLICATION
      </div>

      <!-- ================= 6A + 6B CONTAINER ================= -->

      <div class="grid grid-cols-2 border-b border-black">
        <!-- ================= 6.A ================= -->

        <div class="border-r border-black">
          <div class="border-b border-black px-3 py-2 font-bold">
            6.A TYPE OF LEAVE TO BE AVAILED OF
          </div>

          <div class="p-3">
            <div
              v-for="type in leaveTypes"
              :key="type.leave_type_id"
              class="flex mb-2"
            >
              <div
                class="w-5 h-5 border border-black mr-2 flex items-center justify-center"
              >
                <span v-if="type.leave_type_id == leave.leave_type_id">
                  ✓
                </span>
              </div>

              <div>
                {{ type.leave_type_name }}
              </div>
            </div>

            <div class="mt-8">
              <div class="font-bold italic">Others:</div>

              <div class="border-b border-black h-7 mt-2">
                {{ leave.other_purpose }}
              </div>
            </div>
          </div>
        </div>

        <!-- ================= 6.B ================= -->

        <div class="p-3">
          <div class="text-center font-bold border-b border-black pb-2 mb-3">
            6.B DETAILS OF LEAVE
          </div>

          <div class="mb-4">
            <div class="font-bold">In case of Vacation Leave:</div>

            <div class="ml-4 mt-2">
              ☐ Within the Philippines

              <div class="border-b border-black inline-block w-40 ml-2">
                {{
                  leave.vacation_location_type === "within_philippines"
                    ? leave.vacation_location
                    : ""
                }}
              </div>
            </div>

            <div class="ml-4 mt-2">
              ☐ Abroad (Specify)

              <div class="border-b border-black inline-block w-40 ml-2">
                {{
                  leave.vacation_location_type === "abroad"
                    ? leave.vacation_location
                    : ""
                }}
              </div>
            </div>
          </div>
          <!-- ================= SICK LEAVE ================= -->

          <div class="mb-4">
            <div class="font-bold">In case of Sick Leave:</div>

            <div class="ml-4 mt-2">
              ☐ In Hospital

              <div class="border-b border-black inline-block w-40 ml-2">
                {{ leave.sick_type === "in_hospital" ? leave.illness : "" }}
              </div>
            </div>

            <div class="ml-4 mt-2">
              ☐ Out Patient

              <div class="border-b border-black inline-block w-40 ml-2">
                {{ leave.sick_type === "out_patient" ? leave.illness : "" }}
              </div>
            </div>
          </div>

          <!-- SPECIAL LEAVE -->

          <div class="mb-4">
            <div class="font-bold">
              In case of Special Leave Benefits for Women:
            </div>

            <div class="border-b border-black h-7 mt-2">
              {{ leave.special_leave_women || "" }}
            </div>
          </div>

          <!-- STUDY LEAVE -->

          <div class="mb-4">
            <div class="font-bold">In case of Study Leave:</div>

            <div class="ml-4 mt-2">
              {{ leave.masters_degree ? "☑" : "☐" }}

              Completion of Master's Degree
            </div>

            <div class="ml-4 mt-2">
              {{ leave.board_exam_review ? "☑" : "☐" }}

              BAR / Board Examination Review
            </div>
          </div>

          <!-- OTHER PURPOSE -->

          <div>
            <div class="font-bold">Other Purpose:</div>

            <div class="ml-4 mt-2">
              {{ leave.monetization ? "☑" : "☐" }}

              Monetization of Leave Credits
            </div>

            <div class="ml-4 mt-2">
              {{ leave.terminal_leave ? "☑" : "☐" }}

              Terminal Leave
            </div>

            <div class="border-b border-black h-7 mt-3">
              {{ leave.other_purpose || "" }}
            </div>
          </div>
        </div>
      </div>

      <!-- ================================================= -->
      <!-- 6.C + 6.D -->
      <!-- ================================================= -->

      <div class="grid grid-cols-2 border-b border-black">
        <!-- ================= 6.C ================= -->

        <div class="border-r border-black">
          <div class="border-b border-black px-3 py-2 font-bold">
            6.C NUMBER OF WORKING DAYS APPLIED FOR
          </div>

          <div class="p-4">
            <div class="border-b border-black h-7">
              {{ leave.number_of_days }}
            </div>

            <div class="text-center text-xs mt-2">Working Day(s)</div>

            <div class="font-bold mt-6">Inclusive Dates</div>

            <div class="border-b border-black h-7 mt-2">
              {{ formatDate(leave.start_date) }}

              -

              {{ formatDate(leave.end_date) }}
            </div>
          </div>
        </div>

        <!-- ================= 6.D ================= -->

        <div>
          <div class="border-b border-black px-3 py-2 font-bold">
            6.D COMMUTATION
          </div>

          <div class="p-4">
            <div class="mb-5">
              {{ leave.commutation === "requested" ? "☑" : "☐" }}

              Requested
            </div>

            <div>
              {{ leave.commutation === "not_requested" ? "☑" : "☐" }}

              Not Requested
            </div>

            <div class="mt-10 text-center">
              <div class="border-b border-black h-8"></div>

              <div class="text-xs mt-2">Signature of Applicant</div>
            </div>
          </div>
        </div>
      </div>

      <!-- ================================================= -->
      <!-- 7. DETAILS OF ACTION ON APPLICATION -->
      <!-- ================================================= -->

      <div class="text-center font-bold border-b-2 border-black py-2">
        7. DETAILS OF ACTION ON APPLICATION
      </div>

      <div class="grid grid-cols-2 border-b border-black">
        <!-- ================= 7.A ================= -->

        <div class="border-r border-black">
          <div class="font-bold border-b border-black p-2">
            7.A CERTIFICATION OF LEAVE CREDITS
          </div>

          <div class="p-3">
            <table class="w-full">
              <tr>
                <th></th>

                <th>Vacation Leave</th>

                <th>Sick Leave</th>
              </tr>

              <tr>
                <td>Total Earned</td>

                <td>
                  {{ leave.vacation_total_earned ?? "" }}
                </td>

                <td>
                  {{ leave.sick_total_earned ?? "" }}
                </td>
              </tr>

              <tr>
                <td>Less this application</td>

                <td>
                  {{ leave.vacation_less_application ?? "" }}
                </td>

                <td>
                  {{ leave.sick_less_application ?? "" }}
                </td>
              </tr>

              <tr>
                <td>Balance</td>

                <td>
                  {{ leave.vacation_balance ?? "" }}
                </td>

                <td>
                  {{ leave.sick_balance ?? "" }}
                </td>
              </tr>
            </table>

            <div class="mt-10 text-center">
              <div class="border-b border-black h-8"></div>

              <div class="font-bold mt-2">
                Human Resource Management Officer
              </div>
            </div>
          </div>
        </div>
        <!-- ================= 7.B ================= -->

        <div>
          <div class="font-bold border-b border-black p-2">
            7.B RECOMMENDATION
          </div>

          <div class="p-3">
            <div class="mb-3">
              {{ leave.recommendation_status === "approved" ? "☑" : "☐" }}

              For Approval
            </div>

            <div class="mb-4">
              {{ leave.recommendation_status === "disapproved" ? "☑" : "☐" }}

              For Disapproval due to:
            </div>

            <div class="border-b border-black h-7">
              {{ leave.recommendation_reason || "" }}
            </div>

            <div class="mt-10 text-center">
              <div class="border-b border-black h-8"></div>

              <div class="font-bold mt-2">Immediate Supervisor</div>
            </div>
          </div>

          <!-- ================= 7.C ================= -->

          <div class="border-t border-black">
            <div class="font-bold border-b border-black p-2">
              7.C APPROVED FOR
            </div>

            <div class="p-3">
              <div class="mb-3">
                ________

                {{ leave.days_with_pay ?? "" }}

                day(s) with pay
              </div>

              <div class="mb-3">
                ________

                {{ leave.days_without_pay ?? "" }}

                day(s) without pay
              </div>

              <div>
                Others:

                <span class="border-b border-black inline-block w-40">
                  {{ leave.other_approval || "" }}
                </span>
              </div>
            </div>
          </div>

          <!-- ================= 7.D ================= -->

          <div class="border-t border-black">
            <div class="font-bold border-b border-black p-2">
              7.D DISAPPROVED DUE TO
            </div>

            <div class="p-3">
              <div class="border-b border-black h-7">
                {{ leave.disapproval_reason || "" }}
              </div>

              <div class="mt-12 text-center">
                <div class="border-b border-black h-8"></div>

                <div class="font-bold mt-2">
                  Schools Division Superintendent / Approving Authority
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- END PRINT AREA -->
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from "vue";
import { useRoute } from "vue-router";


import { getLeave, getLeaveTypes, downloadLeavePdf } from "../services/leave";

const route = useRoute();

const leave = ref<any>(null);

const leaveTypes = ref<any[]>([]);

const loading = ref(true);

const formatDate = (date: string | null) => {
  if (!date) return "-";

  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

const downloadingPdf = ref(false);

const downloadPdf = async () => {
  if (!leave.value) return;

  downloadingPdf.value = true;

  try {
    const blob = await downloadLeavePdf(leave.value.leave_id);

    const url = window.URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;

    const first = leave.value.employee?.first_name ?? "";
    const last = leave.value.employee?.last_name ?? "";
    link.download = `Leave_Application_${first}_${last}_${leave.value.leave_id}.pdf`;

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error("Failed to download PDF", error);
    alert("Failed to download PDF. Please try again.");
  } finally {
    downloadingPdf.value = false;
  }
};

const printForm = () => {
  window.print();
};

const originalTitle = document.title;

const loadLeave = async () => {
  try {
    leave.value = await getLeave(Number(route.params.id));

    leaveTypes.value = await getLeaveTypes();

    if (leave.value?.employee) {
      const first = leave.value.employee.first_name ?? "";
      const last = leave.value.employee.last_name ?? "";
      const safeName = `${first}_${last}`.replace(/\s+/g, "_");

      document.title = `Leave_Application_${safeName}_${leave.value.leave_id}`;
    }
  } catch (error) {
    console.error("Failed loading leave", error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadLeave();
});

onBeforeUnmount(() => {
  document.title = originalTitle;
});

</script>
<style scoped>
#printArea {
  box-sizing: border-box;

  width: 210mm;

  min-height: 297mm;

  padding: 10mm;

  background: white;

  color: black !important;

  font-family: Arial, Helvetica, sans-serif;

  font-size: 11px;
}

#printArea * {
  color: black !important;
}

table,
th,
td {
  border: 1px solid black;

  border-collapse: collapse;
}

th,
td {
  padding: 5px;

  text-align: center;
}

@page {
  size: A4;

  margin: 0;
}

@media print {
  body {
    background: white;
  }

  .print\:hidden {
    display: none !important;
  }

  #printArea {
    box-sizing: border-box !important;

    width: 210mm !important;

    min-height: 297mm !important;

    margin: 0 !important;

    padding: 10mm !important;

    border: none !important;
  }

  #printArea,
  #printArea * {
    color: black !important;

    -webkit-print-color-adjust: exact;

    print-color-adjust: exact;
  }
}
</style>
