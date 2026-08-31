<template>
  <div v-if="leave" class="bg-gray-100 min-h-screen p-3 sm:p-6 md:p-8">

    <!-- ========================================= -->
    <!-- ACTION BUTTONS (responsive: wraps + full width on mobile) -->
    <!-- ========================================= -->

    <div class="flex flex-wrap justify-end items-center gap-2 sm:gap-3 mb-4">

      <button @click="goBack" type="button"
        class="flex-1 sm:flex-none bg-gray-600 hover:bg-gray-700 text-white px-4 sm:px-6 py-2 rounded-lg transition text-sm sm:text-base">
        Back
      </button>

      <button @click="downloadPdf" :disabled="downloadingPdf" type="button"
        class="flex-1 sm:flex-none bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-4 sm:px-6 py-2 rounded-lg transition text-sm sm:text-base">
        {{ downloadingPdf ? "Downloading..." : "Download PDF" }}
      </button>

      <button @click="printForm" :disabled="printingPdf" type="button"
        class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-4 sm:px-6 py-2 rounded-lg transition text-sm sm:text-base">
        {{ printingPdf ? "Preparing..." : "Print Form" }}
      </button>

    </div>

    <!-- ========================================= -->
    <!-- PDF PREVIEW (backend-generated CS Form 6) -->
    <!-- ========================================= -->

    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow overflow-hidden">

      <!-- Loading preview -->
      <div v-if="loadingPreview"
        class="flex flex-col items-center justify-center gap-2 py-24 text-gray-500 text-sm sm:text-base text-center px-4">
        <span>
          {{ previewRetryAttempt > 0
            ? "Waking up the server — this can take up to a minute on the first load..."
            : "Loading form preview..." }}
        </span>
        <span v-if="previewRetryAttempt > 0" class="text-xs text-gray-400">
          Attempt {{ previewRetryAttempt }} of {{ MAX_RETRIES + 1 }}
        </span>
      </div>

      <!-- Preview failed -->
      <div v-else-if="previewError" class="flex flex-col items-center justify-center gap-3 py-16 px-4 text-center">
        <p class="text-red-600 font-medium text-sm sm:text-base">
          Couldn't load the form preview.
        </p>
        <p class="text-gray-500 text-xs sm:text-sm">
          You can still download or print it using the buttons above.
        </p>
        <button @click="loadPdfPreview" type="button"
          class="mt-2 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm">
          Try Again
        </button>
      </div>

      <!-- Embedded PDF -->
      <template v-else-if="pdfUrl">
        <iframe :src="previewSrc" class="pdf-frame" title="CS Form No. 6 Preview"></iframe>

        <!-- Fallback for browsers/webviews that can't render PDF inline -->
        <div class="text-center py-2 border-t bg-gray-50">
          <a :href="pdfUrl" target="_blank" rel="noopener" class="text-blue-600 hover:underline text-xs sm:text-sm">
            Not showing correctly? Open the PDF in a new tab
          </a>
        </div>
      </template>

    </div>


    <!-- ========================================= -->
    <!-- ATTACHMENTS (responsive, no fixed mm sizing) -->
    <!-- ========================================= -->

    <div v-if="leave.attachments && leave.attachments.length > 0"
      class="max-w-4xl mx-auto bg-white rounded-lg shadow mt-6 p-4 sm:p-6">

      <div class="border-b pb-3 mb-4">
        <h2 class="text-base text-black sm:text-lg font-bold text-center mb-2">
          SUPPORTING DOCUMENTS
        </h2>

        <div class="text-xs sm:text-sm text-black space-y-1">
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

      <div v-for="(attachment, index) in leave.attachments" :key="attachment.attachment_id || attachment.id || index"
        class="mb-6 last:mb-0">

        <div
          class="flex flex-wrap justify-between items-center gap-2 border rounded-t-lg px-3 py-2 bg-gray-100 font-bold text-xs sm:text-sm">
          <span>Attachment {{ Number(index) + 1 }}</span>
          <span class="font-normal break-all">
            {{ attachment.file_name || attachment.name || attachment.original_name || "Supporting Document" }}
          </span>
        </div>

        <!-- PDF -->
        <div v-if="isPdf(attachment)">
          <div v-if="attachmentLoading[attachment.attachment_id]"
            class="border border-t-0 rounded-b-lg p-6 text-center">
            Loading supporting document...
          </div>

          <div v-else-if="attachmentErrors[attachment.attachment_id]"
            class="border border-t-0 rounded-b-lg p-6 text-center text-red-600">
            {{ attachmentErrors[attachment.attachment_id] }}
          </div>

          <iframe v-else-if="attachmentUrls[attachment.attachment_id]" :src="attachmentUrls[attachment.attachment_id]"
            class="attachment-frame" title="Supporting document"></iframe>
        </div>

        <!-- DOCX -->
        <div v-else-if="isDocx(attachment)" class="border border-t-0 rounded-b-lg bg-white">
          <div v-if="attachmentLoading[attachment.attachment_id]" class="p-6 text-center text-gray-600">
            Loading Word document...
          </div>

          <div v-else-if="attachmentErrors[attachment.attachment_id]" class="p-6 text-center text-red-600">
            {{ attachmentErrors[attachment.attachment_id] }}
          </div>

          <div v-else-if="attachmentDocxHtml[attachment.attachment_id]" class="docx-preview"
            v-html="attachmentDocxHtml[attachment.attachment_id]"></div>
        </div>

        <!-- IMAGE -->
        <img v-else-if="
          isImage(attachment) &&
          attachmentUrls[attachment.attachment_id]
        " :src="attachmentUrls[attachment.attachment_id]"
          class="w-full h-auto max-h-[70vh] object-contain border border-t-0 rounded-b-lg" alt="Supporting document" />

        <!-- OTHER -->
        <div v-else
          class="flex flex-col sm:flex-row items-center gap-3 border border-t-0 rounded-b-lg p-6 text-center sm:text-left">
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

  <div v-else-if="loading" class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
    <div class="text-gray-700 text-base sm:text-lg text-center">
      Loading leave application...
    </div>
  </div>

  <!-- ========================================= -->
  <!-- ERROR -->
  <!-- ========================================= -->

  <div v-else class="min-h-screen bg-gray-100 flex items-center justify-center px-4">
    <div class="bg-white p-6 sm:p-8 rounded-lg shadow text-center w-full max-w-sm">
      <h2 class="text-lg sm:text-xl font-bold text-red-600 mb-2">
        Unable to load leave application
      </h2>
      <p class="text-gray-600 mb-4 text-sm sm:text-base">
        The leave application could not be found.
      </p>
      <button @click="goBack" type="button" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
        Back
      </button>

    </div>

  </div>

</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, onBeforeUnmount } from "vue";
import { useRoute, useRouter } from "vue-router";
import { getLeave, getLeaveTypes, downloadLeavePdf, downloadLeaveAttachment } from "../services/leave";
import mammoth from "mammoth";

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
const previewRetryAttempt = ref(0);
const pdfBlob = ref<Blob | null>(null);
const pdfUrl = ref<string | null>(null);

const attachmentUrls = ref<Record<number, string>>({});
const attachmentLoading = ref<Record<number, boolean>>({});
const attachmentErrors = ref<Record<number, string>>({});
const attachmentDocxHtml = ref<Record<number, string>>({});


/*
 * Render's free tier spins down the backend after inactivity, and the
 * first request after that can take 50+ seconds to wake it up. A single
 * quick retry isn't enough for that, so this retries with increasing
 * delays (3s, 8s, 15s) — about 26s of backoff across 4 total attempts,
 * covering most cold starts without making the user wait forever on a
 * genuinely broken request.
 */
const MAX_RETRIES = 3;
const RETRY_DELAYS_MS = [3000, 8000, 15000];

const wait = (ms: number) => new Promise((resolve) => setTimeout(resolve, ms));

const fetchPdfBlobWithRetry = async (
  onRetry?: (attempt: number) => void
) => {
  let lastError: unknown;

  for (let attempt = 0; attempt <= MAX_RETRIES; attempt++) {
    try {
      const blob = await downloadLeavePdf(
        leave.value!.leave_id
      );

      if (!blob || blob.size === 0) {
        throw new Error("Empty PDF response.");
      }

      return new File(
        [blob],
        buildFileName(),
        { type: "application/pdf" }
      );

    } catch (error) {
      lastError = error;

      if (attempt < MAX_RETRIES) {
        const delay = RETRY_DELAYS_MS[attempt];

        console.warn(
          `PDF fetch attempt ${attempt + 1} failed, retrying in ${delay}ms...`,
          error
        );

        onRetry?.(attempt + 2);

        await wait(delay ?? 0);
      }
    }
  }

  throw lastError;
};

/* Shared filename used everywhere a PDF is saved (preview, toolbar, custom buttons) */
const buildFileName = () => {
  const employeeName = [
    leave.value?.employee?.last_name,
    leave.value?.employee?.first_name,
  ].filter(Boolean).join("_") || "leave_application";

  return `Leave_Application_${employeeName}_${formatDateForFilename(leave.value?.date_filed)}.pdf`;
};

const goBack = () => {

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

  router.push("/my-applications");

};

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

const getAttachmentName = (attachment: any) => {
  return (attachment.file_name || attachment.name || attachment.original_name || "").toLowerCase();
};

const isPdf = (attachment: any) => {
  const name = getAttachmentName(attachment);
  return attachment.file_type === "application/pdf" || name.endsWith(".pdf");
};

const isDocx = (attachment: any) => {
  const name = getAttachmentName(attachment);

  return (
    attachment.file_type ===
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document" ||
    name.endsWith(".docx")
  );
};

const isImage = (attachment: any) => {
  const name = getAttachmentName(attachment);
  return attachment.file_type?.startsWith("image/") || /\.(jpg|jpeg|png|gif|webp)$/i.test(name);
};

const loadAttachment = async (attachment: any) => {
  const attachmentId =
    attachment.attachment_id ?? attachment.id;

  const leaveId = leave.value?.leave_id;

  if (!attachmentId || !leaveId) {
    console.error(
      "Missing leave ID or attachment ID:",
      attachment
    );
    return;
  }

  attachmentLoading.value[attachmentId] = true;
  delete attachmentErrors.value[attachmentId];

  try {
    const blob = await downloadLeaveAttachment(
      Number(leaveId),
      Number(attachmentId)
    );

    // DOCX → HTML
    if (isDocx(attachment)) {
      const arrayBuffer = await blob.arrayBuffer();

      const result = await mammoth.convertToHtml({
        arrayBuffer,
      });

      attachmentDocxHtml.value[attachmentId] =
        result.value;

      // Mammoth may report non-fatal conversion warnings.
      if (result.messages?.length) {
        console.warn(
          `DOCX conversion messages for attachment ${attachmentId}:`,
          result.messages
        );
      }

      return;
    }

    // PDF / image → Blob URL
    if (attachmentUrls.value[attachmentId]) {
      URL.revokeObjectURL(
        attachmentUrls.value[attachmentId]
      );
    }

    attachmentUrls.value[attachmentId] =
      URL.createObjectURL(blob);

  } catch (error) {

    attachmentErrors.value[attachmentId] =
      error instanceof Error
        ? error.message
        : "Failed to load supporting document.";
  } finally {
    attachmentLoading.value[attachmentId] = false;
  }
};

/* PDF PREVIEW - fetch once (with retries), reuse for download + print */
const loadPdfPreview = async () => {
  if (!leave.value?.leave_id) return;

  loadingPreview.value = true;
  previewError.value = false;
  previewRetryAttempt.value = 0;

  try {
    const namedFile = await fetchPdfBlobWithRetry((attemptNumber) => {
      previewRetryAttempt.value = attemptNumber;
    });
    pdfBlob.value = namedFile;

    if (pdfUrl.value) window.URL.revokeObjectURL(pdfUrl.value);
    pdfUrl.value = window.URL.createObjectURL(namedFile);
  } catch (error) {
    console.error("Failed to load PDF preview after retries:", error);
    previewError.value = true;
  } finally {
    loadingPreview.value = false;
    previewRetryAttempt.value = 0;
  }
};

const ensurePdfBlob = async (): Promise<Blob> => {
  if (pdfBlob.value) return pdfBlob.value;
  const namedFile = await fetchPdfBlobWithRetry();
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

    // Load supporting documents after the leave application is loaded
    if (leave.value?.attachments?.length) {
      await Promise.all(
        leave.value.attachments.map((attachment: any) =>
          loadAttachment(attachment)
        )
      );
    }

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

onBeforeUnmount(() => {
  Object.values(attachmentUrls.value).forEach((url) => {
    URL.revokeObjectURL(url);
  });
});
</script>

<style scoped>
.pdf-frame {
  width: 100%;
  height: 75vh;
  min-height: 400px;
  border: none;
  display: block;
  background: #525659;
  /* matches typical PDF viewer chrome */
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

.docx-preview {
  background: #ffffff;
  padding: 40px;
  min-height: 500px;
  overflow-x: auto;
  color: #111827;
  font-family: Arial, Helvetica, sans-serif;
  line-height: 1.6;
}

.docx-preview p {
  margin: 0 0 12px;
}

.docx-preview h1,
.docx-preview h2,
.docx-preview h3,
.docx-preview h4 {
  margin-top: 20px;
  margin-bottom: 12px;
  font-weight: 700;
}

.docx-preview ul,
.docx-preview ol {
  margin: 12px 0;
  padding-left: 32px;
}

.docx-preview table {
  width: 100%;
  border-collapse: collapse;
  margin: 16px 0;
}

.docx-preview td,
.docx-preview th {
  border: 1px solid #d1d5db;
  padding: 8px;
  vertical-align: top;
}

.docx-preview img {
  max-width: 100%;
  height: auto;
}

@media (max-width: 640px) {
  .docx-preview {
    padding: 20px;
  }
}
</style>