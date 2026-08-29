<template>
  <div v-if="leave" class="bg-gray-100 min-h-screen p-3 sm:p-6 md:p-8">

    <!-- ========================================= -->
    <!-- ACTION BUTTONS (responsive: wraps + full width on mobile) -->
    <!-- ========================================= -->

    <div class="flex flex-wrap justify-end items-center gap-2 sm:gap-3 mb-4">

      <button
        @click="goBack"
        type="button"
        class="flex-1 sm:flex-none bg-gray-600 hover:bg-gray-700 text-white px-4 sm:px-6 py-2 rounded-lg transition text-sm sm:text-base"
      >
        Back
      </button>

      <button
        @click="downloadPdf"
        :disabled="downloadingPdf"
        type="button"
        class="flex-1 sm:flex-none bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-4 sm:px-6 py-2 rounded-lg transition text-sm sm:text-base"
      >
        {{ downloadingPdf ? "Downloading..." : "Download PDF" }}
      </button>

      <button
        @click="printForm"
        :disabled="printingPdf"
        type="button"
        class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-4 sm:px-6 py-2 rounded-lg transition text-sm sm:text-base"
      >
        {{ printingPdf ? "Preparing..." : "Print Form" }}
      </button>

    </div>

    <!-- ========================================= -->
    <!-- PDF PREVIEW (backend-generated CS Form 6) -->
    <!-- ========================================= -->

    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow overflow-hidden">

      <!-- Loading preview -->
      <div
        v-if="loadingPreview"
        class="flex items-center justify-center py-24 text-gray-500 text-sm sm:text-base"
      >
        Loading form preview...
      </div>

      <!-- Preview failed -->
      <div
        v-else-if="previewError"
        class="flex flex-col items-center justify-center gap-3 py-16 px-4 text-center"
      >
        <p class="text-red-600 font-medium text-sm sm:text-base">
          Couldn't load the form preview.
        </p>
        <p class="text-gray-500 text-xs sm:text-sm">
          You can still download or print it using the buttons above.
        </p>
        <button
          @click="loadPdfPreview"
          type="button"
          class="mt-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm"
        >
          Try Again
        </button>
      </div>

      <!-- Embedded PDF -->
      <template v-else-if="pdfUrl">
        <iframe
          :src="previewSrc"
          class="pdf-frame"
          title="CS Form No. 6 Preview"
        ></iframe>

        <!-- Fallback for browsers/webviews that can't render PDF inline -->
        <div class="text-center py-2 border-t bg-gray-50">
          <a
            :href="pdfUrl"
            target="_blank"
            rel="noopener"
            class="text-blue-600 hover:underline text-xs sm:text-sm"
          >
            Not showing correctly? Open the PDF in a new tab
          </a>
        </div>
      </template>

    </div>


    <!-- ========================================= -->
    <!-- ATTACHMENTS (responsive, no fixed mm sizing) -->
    <!-- ========================================= -->

    <div
      v-if="leave.attachments && leave.attachments.length > 0"
      class="max-w-4xl mx-auto bg-white rounded-lg shadow mt-6 p-4 sm:p-6"
    >

      <div class="border-b pb-3 mb-4">
        <h2 class="text-base sm:text-lg font-bold text-center mb-2">
          SUPPORTING DOCUMENTS
        </h2>

        <div class="text-xs sm:text-sm space-y-1">
          <div><strong>Leave Application ID:</strong> {{ leave.leave_id }}</div>
          <div>
            <strong>Employee:</strong>
            {{ leave.employee?.last_name }}, {{ leave.employee?.first_name }} {{ leave.employee?.middle_name }}
          </div>
          <div><strong>Leave Type:</strong> {{ currentLeaveTypeName }}</div>
          <div>
            <strong>Inclusive Dates:</strong>
            {{ formatDate(leave.start_date) }} - {{ formatDate(leave.end_date) }}
          </div>
        </div>
      </div>

      <div
        v-for="(attachment, index) in leave.attachments"
        :key="attachment.attachment_id || attachment.id || index"
        class="mb-6 last:mb-0"
      >

        <div class="flex flex-wrap justify-between items-center gap-2 border rounded-t-lg px-3 py-2 bg-gray-100 font-bold text-xs sm:text-sm">
          <span>Attachment {{ Number(index) + 1 }}</span>
          <span class="font-normal break-all">
            {{ attachment.file_name || attachment.name || attachment.original_name || "Supporting Document" }}
          </span>
        </div>

        <iframe
          v-if="isPdf(attachment)"
          :src="getAttachmentUrl(attachment)"
          class="attachment-frame"
          title="Attachment"
        ></iframe>

        <img
          v-else-if="isImage(attachment)"
          :src="getAttachmentUrl(attachment)"
          class="w-full h-auto max-h-[70vh] object-contain border border-t-0 rounded-b-lg"
          alt="Supporting document"
        />

        <div
          v-else
          class="flex flex-col sm:flex-row items-center gap-3 border border-t-0 rounded-b-lg p-6 text-center sm:text-left"
        >
          <div class="text-3xl sm:text-4xl">📄</div>
          <div>
            <div class="font-bold text-sm sm:text-base">
              {{ attachment.file_name || attachment.name || attachment.original_name || "Supporting Document" }}
            </div>
            <div class="text-gray-600 text-xs sm:text-sm mt-1">
              This document was submitted as a supporting attachment.
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>

  <!-- LOADING -->
  <!-- ========================================= -->

  <div
    v-else-if="loading"
    class="min-h-screen bg-gray-100 flex items-center justify-center px-4"
  >
    <div class="text-gray-700 text-base sm:text-lg text-center">
      Loading leave application...
    </div>
  </div>

  <!-- ========================================= -->
  <!-- ERROR -->
  <!-- ========================================= -->

  <div
    v-else
    class="min-h-screen bg-gray-100 flex items-center justify-center px-4"
  >
    <div class="bg-white p-6 sm:p-8 rounded-lg shadow text-center w-full max-w-sm">
      <h2 class="text-lg sm:text-xl font-bold text-red-600 mb-2">
        Unable to load leave application
      </h2>
      <p class="text-gray-600 mb-4 text-sm sm:text-base">
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
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { getLeave, getLeaveTypes, downloadLeavePdf } from "../services/leave";

const route = useRoute();
const router = useRouter();

/* STATE */
const leave = ref<any>(null);
const leaveTypes = ref<any[]>([]);
const loading = ref(true);

const downloadingPdf = ref(false);
const printingPdf = ref(false);

const loadingPreview = ref(true);
const previewError = ref(false);
const pdfBlob = ref<Blob | null>(null);
const pdfUrl = ref<string | null>(null);

/* Shared filename used everywhere a PDF is saved (preview, toolbar, custom buttons) */
const buildFileName = () => {
  const employeeName = [
    leave.value?.employee?.last_name,
    leave.value?.employee?.first_name,
  ].filter(Boolean).join("_") || "leave_application";

  return `Leave_Application_${employeeName}_${formatDateForFilename(leave.value?.date_filed)}.pdf`;
};

const goBack = () => {

  /*
   * ADMIN
   * If this page was opened from an admin route,
   * return to the Admin Applications page.
   */

  const routeName =
    String(route.name || "").toLowerCase();

  const currentPath =
    route.path.toLowerCase();

  const isAdmin =
    routeName.includes("admin") ||
    currentPath.includes("/admin") ||
    currentPath.includes("admin-applications");

  if (isAdmin) {

    router.push("/admin-applications");

    return;

  }

  /*
   * EMPLOYEE
   * Employees should return to Apply Leave.
   */

  router.push("/leave-application");

};

/*
 * Appends standard PDF "open parameters" to hide the built-in viewer chrome
 * on desktop (toolbar, side nav, scrollbar). Chrome/Edge honor these on
 * blob: URLs; Firefox partially supports them; Safari ignores them and
 * keeps its own toolbar (harmless — our own buttons still work everywhere).
 */
const previewSrc = computed(() => {
  if (!pdfUrl.value) return "";
  return `${pdfUrl.value}#toolbar=0&navpanes=0&scrollbar=0`;
});

const currentLeaveTypeName = computed(() => {
  const current = leaveTypes.value.find(
    (type) => type.leave_type_id == leave.value?.leave_type_id
  );
  return current?.leave_type_name || "";

});

const formatDate = (date: string | null) => {
  if (!date) return "";
  return new Date(date).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

/* YYYY-MM-DD, safe to use inside a filename */
const formatDateForFilename = (date: string | null) => {
  if (!date) return "no-date";
  const d = new Date(date);
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const dd = String(d.getDate()).padStart(2, "0");
  return `${yyyy}-${mm}-${dd}`;
};

/* ATTACHMENT HELPERS (unchanged) */
const getAttachmentUrl = (attachment: any) => {
  if (attachment.file_url) return attachment.file_url;
  if (attachment.url) return attachment.url;
  if (attachment.download_url) return attachment.download_url;

  if (attachment.file_path) {
    return `https://enhs-leave-management-system.onrender.com/storage/${attachment.file_path}`;
  }

  return "";
};
const getAttachmentName = (attachment: any) => {
  return (attachment.file_name || attachment.name || attachment.original_name || "").toLowerCase();
};

const isPdf = (attachment: any) => {
  const name = getAttachmentName(attachment);
  return attachment.file_type === "application/pdf" || name.endsWith(".pdf");
};

const isImage = (attachment: any) => {
  const name = getAttachmentName(attachment);
  return attachment.file_type?.startsWith("image/") || /\.(jpg|jpeg|png|gif|webp)$/i.test(name);
};

/* PDF PREVIEW - fetch once, reuse for download + print */
const loadPdfPreview = async () => {
  if (!leave.value?.leave_id) return;

  loadingPreview.value = true;
  previewError.value = false;

  try {
    const blob = await downloadLeavePdf(leave.value.leave_id);
    if (!blob || blob.size === 0) throw new Error("Empty PDF response.");

    const namedFile = new File([blob], buildFileName(), { type: "application/pdf" });
    pdfBlob.value = namedFile;

    if (pdfUrl.value) window.URL.revokeObjectURL(pdfUrl.value);
    pdfUrl.value = window.URL.createObjectURL(namedFile);
  } catch (error) {
    console.error("Failed to load PDF preview:", error);
    previewError.value = true;
  } finally {
    loadingPreview.value = false;
  }
};

const ensurePdfBlob = async (): Promise<Blob> => {
  if (pdfBlob.value) return pdfBlob.value;
  const blob = await downloadLeavePdf(leave.value.leave_id);
  if (!blob || blob.size === 0) throw new Error("Empty PDF response.");
  const namedFile = new File([blob], buildFileName(), { type: "application/pdf" });
  pdfBlob.value = namedFile;
  return namedFile;
};

const downloadPdf = async () => {
  if (!leave.value?.leave_id) {

    alert(
      "Leave application not found."
    );

    return;

  }

  downloadingPdf.value = true;

  try {
    const blob = await ensurePdfBlob();
    const url = window.URL.createObjectURL(blob);

    const link = document.createElement("a");
    link.href = url;
    link.download = buildFileName();
    link.style.display = "none";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    setTimeout(() => window.URL.revokeObjectURL(url), 1000);
  } catch (error) {
    console.error("Failed to download PDF:", error);
    alert("Failed to download PDF. Please try again.");
  } finally {
    downloadingPdf.value = false;
  }

};

const printForm = async () => {
  if (!leave.value?.leave_id) {

    alert(
      "Leave application not found."
    );

    return;

  }

  printingPdf.value = true;

  try {
    const blob = await ensurePdfBlob();
    const url = window.URL.createObjectURL(blob);

    const iframe = document.createElement("iframe");
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
          console.error("Print window error:", error);
        }
      }, 500);

    };

    setTimeout(() => {
      window.URL.revokeObjectURL(url);
      if (iframe.parentNode) iframe.parentNode.removeChild(iframe);
    }, 60000);
  } catch (error) {
    console.error("Failed to print PDF:", error);
    alert("Failed to prepare the PDF for printing. Please try again.");
  } finally {
    printingPdf.value = false;
  }

};

/* LOAD LEAVE */
const loadLeave = async () => {
  try {
    leave.value = await getLeave(Number(route.params.id));
    leaveTypes.value = await getLeaveTypes();
  } catch (error) {
    console.error("Failed loading leave:", error);
  } finally {
    loading.value = false;
  }

};

onMounted(async () => {
  await loadLeave();
  if (leave.value) {
    loadPdfPreview();
  }
});

onUnmounted(() => {
  if (pdfUrl.value) window.URL.revokeObjectURL(pdfUrl.value);
});
</script>

<style scoped>
.pdf-frame {
  width: 100%;
  height: 75vh;
  min-height: 400px;
  border: none;
  display: block;
  background: #525659; /* matches typical PDF viewer chrome */
}

@media (min-width: 768px) {
  .pdf-frame {
    height: 85vh;
  }
}

.attachment-frame {
  width: 100%;
  height: 60vh;
  min-height: 300px;
  border: 1px solid #000;
  border-top: none;
  display: block;
}

@media (min-width: 768px) {
  .attachment-frame {
    height: 75vh;
  }
}
</style>