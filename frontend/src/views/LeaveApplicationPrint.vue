<template>
  <div v-if="leave" class="bg-gray-200 min-h-screen p-8">
    <!-- PRINT BUTTON -->
    <div class="flex justify-end mb-4 print:hidden">
      <button
        @click="printForm"
        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg"
      >
        Print Form
      </button>
    </div>

    <!-- ================= PRINT AREA ================= -->

    <div id="printArea" class="mx-auto bg-white text-black">
      <!-- ================= HEADER ================= -->

      <div class="relative mb-4 font-[Arial] text-[8px]">
  <div class="absolute left-0 top-0 font-bold italic leading-tight">
          <div><b>Civil Service Form No. 6</b></div>
          <div><b>Revised 2020</b></div>
        </div>

        <div class="text-center leading-tight">
          <div class="font-['Old_English_Text_MT'] text-[12px] font-bold">Republic of the Philippines</div>    
          <div class="font-['Old_English_Text_MT'] text-[18px] font-bold">Department of Education</div>
          <div class="font-['Times_New_Roman'] text-[12px] font-bold">Region 02-Cagayan Valley</div>
          <div class="font-['Times_New_Roman'] text-[12px] font-bold">SCHOOL DIVISION OFFICE OF ISABELA</div>

          <h1 class="text-xl font-bold mt-4 tracking-wide">
            APPLICATION FOR LEAVE
          </h1>
        </div>
      </div>

      <!-- ================= FORM BODY ================= -->

      <div class="form-body border-2 border-black">
        <!-- ================= 1. OFFICE / 2. NAME ================= -->

        <div class="border-b border-black p-2 field-row">
          <div class="flex items-baseline flex-wrap" style="column-gap: 2.5rem;">
            <span class="font-bold">1. OFFICE/DEPARTMENT</span>
            <span class="font-bold">2. NAME :</span>
            <span class="text-[10px]">(Last)</span>
            <span class="text-[10px]" style="margin-left: 6rem;">(First)</span>
            <span class="text-[10px]" style="margin-left: 6rem;">(Middle)</span>
          </div>
          <div class="mt-6">
            {{ leave.employee?.department }}
            <span v-if="leave.employee?.last_name || leave.employee?.first_name || leave.employee?.middle_name" class="ml-10">
              {{ leave.employee?.last_name }}&nbsp;&nbsp;&nbsp;&nbsp;{{ leave.employee?.first_name }}&nbsp;&nbsp;&nbsp;&nbsp;{{ leave.employee?.middle_name }}
            </span>
          </div>
        </div>

        <!-- ================= 3/4/5 ================= -->

        <div class="border-b border-black p-2 flex items-baseline whitespace-nowrap" style="gap: 14px;">
          <span>
            <span class="font-bold">3. DATE OF FILING</span>
            <span class="fill-underline" style="min-width: 95px;">{{ formatDate(leave.date_filed) }}</span>
          </span>
          <span>
            <span class="font-bold">4. POSITION</span>
            <span class="fill-underline" style="min-width: 170px;">{{ leave.employee?.position }}</span>
          </span>
          <span>
            <span class="font-bold">5. SALARY</span>
            <span class="fill-underline" style="min-width: 90px;">{{ leave.employee?.salary ? '₱' + leave.employee.salary : '' }}</span>
          </span>
        </div>

        <!-- ================= 6. DETAILS TITLE ================= -->

        <div class="text-center font-bold double-border-y font-[Arial] text-[12]">
          6. DETAILS OF APPLICATION
        </div>

        <!-- ================= 6.A + 6.B ================= -->

        <div class="grid grid-cols-2 border-b border-black">
          <!-- ================= 6.A ================= -->

          <div class="border-r border-black">
            <div class="px-2 py-1 font-bold">
              6.A TYPE OF LEAVE TO BE AVAILED OF
            </div>

            <div class="px-3 pb-2">
              <div class="px-3 pb-2">
  <div
    v-for="lt in leaveTypeList"
    :key="lt.key"
    class="flex items-start mb-1"
  >
    <!-- Smaller checkbox -->
    <span class="checkbox" style="margin-top: 1px;">
      <span v-if="isLeaveTypeSelected(lt)">&#10003;</span>
    </span>

    <!-- Label + citation beside each other -->
    <span class="ml-1 leading-tight">
      <span class="text-[10px] font-[Arial] font-bold">
        {{ lt.label }}
      </span>
      <span class="text-[8px] font-[Arial] italic text-gray-700 ml-1">
        ({{ lt.citation }})
      </span>
    </span>
  </div>
</div>

              <div class="mt-3">
                <div class="italic">Others:</div>
                <div class="fill-line">{{ leave.other_purpose }}</div>
              </div>
            </div>
          </div>

          <!-- ================= 6.B ================= -->

          <div class="px-3 pb-3">
            <div class="font-bold py-1">6.B DETAILS OF LEAVE</div>

            <div class="mb-3">
              <div class="italic">In case of Vacation/Special Privilege Leave:</div>

              <div class="flex items-baseline mt-1">
                <span class="checkbox"></span>
                <span>Within the Philippines</span>
                <span class="fill-underline flex-1">{{
                  leave.vacation_location_type === 'within_philippines' ? leave.vacation_location : ''
                }}</span>
              </div>

              <div class="flex items-baseline mt-1">
                <span class="checkbox"></span>
                <span>Abroad (Specify)</span>
                <span class="fill-underline flex-1">{{
                  leave.vacation_location_type === 'abroad' ? leave.vacation_location : ''
                }}</span>
              </div>
            </div>

            <div class="mb-3">
              <div class="italic">In case of Sick Leave:</div>

              <div class="flex items-baseline mt-1">
                <span class="checkbox"></span>
                <span>In Hospital (Specify Illness)</span>
                <span class="fill-underline flex-1">{{
                  leave.sick_type === 'in_hospital' ? leave.illness : ''
                }}</span>
              </div>

              <div class="flex items-baseline mt-1">
                <span class="checkbox"></span>
                <span>Out Patient (Specify Illness)</span>
                <span class="fill-underline flex-1">{{
                  leave.sick_type === 'out_patient' ? leave.illness : ''
                }}</span>
              </div>

              <div class="fill-line mt-1">&nbsp;</div>
            </div>

            <div class="mb-3">
              <div class="italic">In case of Special Leave Benefits for Women:</div>
              <div>
                (Specify Illness)
                <span class="fill-underline">{{ leave.special_leave_women || '' }}</span>
              </div>
              <div class="fill-line mt-1">&nbsp;</div>
            </div>

            <div class="mb-3">
              <div class="italic">In case of Study Leave:</div>

              <div class="flex items-center mt-1">
                <span class="checkbox">
                  <span v-if="leave.masters_degree">&#10003;</span>
                </span>
                <span>Completion of Master's Degree</span>
              </div>

              <div class="flex items-center mt-1">
                <span class="checkbox">
                  <span v-if="leave.board_exam_review">&#10003;</span>
                </span>
                <span>BAR/Board Examination Review</span>
              </div>
            </div>

            <div>
              <div class="italic">Other purpose:</div>

              <div class="flex items-center mt-1">
                <span class="checkbox">
                  <span v-if="leave.monetization">&#10003;</span>
                </span>
                <span>Monetization of Leave Credits</span>
              </div>

              <div class="flex items-center mt-1">
                <span class="checkbox">
                  <span v-if="leave.terminal_leave">&#10003;</span>
                </span>
                <span>Terminal Leave</span>
              </div>
            </div>
          </div>
        </div>

        <!-- ================= 6.C + 6.D ================= -->

        <div class="grid grid-cols-2 double-border-y no-top">
          <!-- ================= 6.C ================= -->

          <div class="border-r border-black p-3">
            <div class="font-bold mb-1">6.C NUMBER OF WORKING DAYS APPLIED FOR</div>
            <div class="fill-line">{{ leave.number_of_days }}</div>

            <div class="font-bold mt-4">INCLUSIVE DATES</div>
            <div class="fill-line">
              <span v-if="leave.start_date">{{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}</span>
            </div>
          </div>

          <!-- ================= 6.D ================= -->

          <div class="p-3">
            <div class="font-bold mb-2">6.D COMMUTATION</div>

            <div class="flex items-center mb-2">
              <span class="checkbox">
                <span v-if="leave.commutation === 'not_requested'">&#10003;</span>
              </span>
              <span>Not Requested</span>
            </div>

            <div class="flex items-center">
              <span class="checkbox">
                <span v-if="leave.commutation === 'requested'">&#10003;</span>
              </span>
              <span>Requested</span>
            </div>

            <div class="mt-8 text-center">
              <div class="signature-line"></div>
              <div class="text-xs mt-1">(Signature of Applicant)</div>
            </div>
          </div>
        </div>

        <!-- ================= 7. DETAILS OF ACTION ================= -->

        <div class="text-center font-bold py-2 double-border-y">
          7. DETAILS OF ACTION ON APPLICATION
        </div>

        <!-- ================= 7.A + 7.B ================= -->

        <div class="grid grid-cols-2 border-b border-black">
          <!-- ================= 7.A ================= -->

          <div class="border-r border-black">
            <div class="font-bold p-2">7.A CERTIFICATION OF LEAVE CREDITS</div>

            <div class="p-3">
              <div class="text-center mb-2">
                As of
                <span class="fill-underline">{{ formatDate(leave.credits_as_of) }}</span>
              </div>

              <table class="text-xs mx-auto" style="width: 96%;">
                <tr>
                  <th></th>
                  <th class="whitespace-nowrap">Vacation Leave</th>
                  <th class="whitespace-nowrap">Sick Leave</th>
                </tr>
                <tr>
                  <td class="italic text-left">Total Earned</td>
                  <td>{{ leave.vacation_total_earned ?? '' }}</td>
                  <td>{{ leave.sick_total_earned ?? '' }}</td>
                </tr>
                <tr>
                  <td class="italic text-left">Less this application</td>
                  <td>{{ leave.vacation_less_application ?? '' }}</td>
                  <td>{{ leave.sick_less_application ?? '' }}</td>
                </tr>
                <tr>
                  <td class="italic text-left">Balance</td>
                  <td>{{ leave.vacation_balance ?? '' }}</td>
                  <td>{{ leave.sick_balance ?? '' }}</td>
                </tr>
              </table>

              <div class="mt-10 text-center">
                <div class="signature-line"></div>
                <div class="text-xs mt-1">(Authorized Officer)</div>
              </div>
            </div>
          </div>

          <!-- ================= 7.B ================= -->

          <div>
            <div class="font-bold p-2">7.B RECOMMENDATION</div>

            <div class="p-3">
              <div class="flex items-center mb-2">
                <span class="checkbox">
                  <span v-if="leave.recommendation_status === 'approved'">&#10003;</span>
                </span>
                <span>For approval</span>
              </div>

              <div class="flex items-baseline mb-2">
                <span class="checkbox"></span>
                <span>For disapproval due to</span>
                <span class="fill-underline flex-1">{{
                  leave.recommendation_status === 'disapproved' ? leave.recommendation_reason : ''
                }}</span>
              </div>

              <div class="fill-line mb-1">&nbsp;</div>
              <div class="fill-line mb-1">&nbsp;</div>

              <div class="mt-8 text-center">
                <div class="signature-line"></div>
                <div class="text-xs mt-1">(Authorized Officer)</div>
              </div>
            </div>
          </div>
        </div>

        <!-- ================= 7.C + 7.D ================= -->

        <div>
          <div class="grid grid-cols-2">
            <!-- ================= 7.C ================= -->

            <div class="p-3">
              <div class="font-bold mb-2">7.C APPROVED FOR:</div>

              <div class="mb-1">
                <span class="fill-underline" style="min-width: 50px;">{{ leave.days_with_pay ?? '' }}</span>
                days with pay
              </div>

              <div class="mb-1">
                <span class="fill-underline" style="min-width: 50px;">{{ leave.days_without_pay ?? '' }}</span>
                days without pay
              </div>

              <div>
                <span class="fill-underline" style="min-width: 50px;">{{ leave.other_approval || '' }}</span>
                others (Specify)
              </div>
            </div>

            <!-- ================= 7.D ================= -->

            <div class="p-3">
              <div class="font-bold mb-2">7.D DISAPPROVED DUE TO:</div>

              <div class="fill-line mb-1">{{ leave.disapproval_reason || '' }}&nbsp;</div>
              <div class="fill-line mb-1">&nbsp;</div>
              <div class="fill-line mb-1">&nbsp;</div>
            </div>
          </div>

          <!-- Authorized Official signature spans full width beneath 7.C/7.D -->
          <div class="text-center pb-3 pt-10">
            <div class="signature-line inline-block" style="width: 280px;"></div>
            <div class="text-xs mt-1">(Authorized Official)</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";

import { getLeave, getLeaveTypes } from "../services/leave";

const route = useRoute();

const leave = ref<any>(null);
const leaveTypes = ref<any[]>([]);
const loading = ref(true);

// Static reference list matching CS Form No. 6, with legal citations.
// `match` values are used to test against leave_type_name returned by the API.
const leaveTypeList = [
  { key: "vacation", label: "Vacation Leave", citation: "Sec. 51, Rule XVI, Omnibus Rules Implementing E.O. No. 292", match: ["vacation"] },
  { key: "mandatory", label: "Mandatory/Forced Leave", citation: "Sec. 25, Rule XVI, Omnibus Rules Implementing E.O. No. 292", match: ["mandatory", "forced"] },
  { key: "sick", label: "Sick Leave", citation: "Sec. 43, Rule XVI, Omnibus Rules Implementing E.O. No. 292", match: ["sick"] },
  { key: "maternity", label: "Maternity Leave", citation: "R.A. No. 11210 / IRR issued by CSC, DOLE and SSS", match: ["maternity"] },
  { key: "paternity", label: "Paternity Leave", citation: "R.A. No. 8187 / CSC MC No. 71, s. 1998, as amended", match: ["paternity"] },
  { key: "special_privilege", label: "Special Privilege Leave", citation: "Sec. 21, Rule XVI, Omnibus Rules Implementing E.O. No. 292", match: ["special privilege"] },
  { key: "solo_parent", label: "Solo Parent Leave", citation: "RA No. 8972 / CSC MC No. 8, s. 2004", match: ["solo parent"] },
  { key: "study", label: "Study Leave", citation: "Sec. 68, Rule XVI, Omnibus Rules Implementing E.O. No. 292", match: ["study"] },
  { key: "vawc", label: "10-Day VAWC Leave", citation: "RA No. 9262 / CSC MC No. 15, s. 2005", match: ["vawc"] },
  { key: "rehabilitation", label: "Rehabilitation Privilege", citation: "Sec. 55, Rule XVI, Omnibus Rules Implementing E.O. No. 292", match: ["rehabilitation"] },
  { key: "women", label: "Special Leave Benefits for Women", citation: "RA No. 9710 / CSC MC No. 25, s. 2010", match: ["women"] },
  { key: "calamity", label: "Special Emergency (Calamity) Leave", citation: "CSC MC No. 2, s. 2012, as amended", match: ["calamity", "emergency"] },
  { key: "adoption", label: "Adoption Leave", citation: "R.A. No. 8552", match: ["adoption"] },
];

const isLeaveTypeSelected = (lt: { match: string[] }) => {
  const current = leaveTypes.value.find((t) => t.leave_type_id == leave.value?.leave_type_id);
  const name = (current?.leave_type_name || "").toLowerCase();
  if (!name) return false;
  return lt.match.some((m) => name.includes(m));
};

const formatDate = (date: string | null) => {
  if (!date) return "";
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

const printForm = () => {
  window.print();
};

const loadLeave = async () => {
  try {
    leave.value = await getLeave(Number(route.params.id));
    leaveTypes.value = await getLeaveTypes();
  } catch (error) {
    console.error("Failed loading leave", error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadLeave();
});
</script>

<style scoped>
#printArea {
  width: 8.27in;
  min-height: 11.69in;
  padding: 0.5in 1in 1in 0.5in; /* top right bottom left */
  background: white;
  color: black !important;
  font-family: Arial, Helvetica, sans-serif;
  font-size: 11px;
}

#printArea * {
  color: black !important;
}

.field-row {
  min-height: 60px;
}

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

.fill-line {
  border-bottom: 1px solid black;
  min-height: 16px;
  padding: 0 4px;
}

.fill-underline {
  border-bottom: 1px solid black;
  display: inline-block;
  min-width: 120px;
  padding: 0 4px;
  margin-left: 4px;
}

.signature-line {
  border-bottom: 1px solid black;
  height: 26px;
}

.double-border-y {
  border-top: 3px double black;
  border-bottom: 3px double black;
}

.double-border-y.no-top {
  border-top-width: 0;
}

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

@page {
  size: A4;
  margin-top: 0.5in;
  margin-right: 1in;
  margin-bottom: 1in;
  margin-left: 0.5in;
}

@media print {
  body {
    background: white;
  }

  .print\:hidden {
    display: none !important;
  }

  #printArea {
    width: 100% !important;
    min-height: auto !important;
    margin: 0 !important;
    padding: 0 !important;
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