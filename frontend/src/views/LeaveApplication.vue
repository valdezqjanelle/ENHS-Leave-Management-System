<template>
  <div
    class="max-w-4xl xs:max-w-5xl sm:max-w-6xl md:max-w-7xl lg:max-w-6xl xl:max-w-7xl 2xl:max-w-8xl 3xl:max-w-9xl 4xl:max-w-10xl mx-auto px-3 xs:px-4 sm:px-6 md:px-8 lg:px-8 xl:px-12 2xl:px-16 3xl:px-20 4xl:px-24"
  >
    <div class="bg-white rounded-lg shadow">
      <div
        class="px-3 xs:px-4 sm:px-6 md:px-8 lg:px-8 xl:px-12 2xl:px-16 py-3 xs:py-4 sm:py-6 md:py-8 lg:py-8 xl:py-10 2xl:py-12 border-b border-gray-200"
      >
        <h2
          class="text-base xs:text-lg sm:text-xl md:text-2xl lg:text-2xl xl:text-3xl 2xl:text-4xl font-semibold text-gray-800"
        >
          Leave Application Form
        </h2>
        <p
          class="text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl text-gray-600 mt-1"
        >
          Fill out the form below to apply for leave
        </p>
      </div>

      <form
        @submit.prevent="submitApplication"
        class="p-3 xs:p-4 sm:p-6 md:p-8 lg:p-8 xl:p-10 2xl:p-12 space-y-3 xs:space-y-4 sm:space-y-6 md:space-y-8 lg:space-y-8 xl:space-y-10 2xl:space-y-12"
      >
        <!-- Personal Information -->
        <div
          class="grid grid-cols-1 xs:grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-4 gap-3 xs:gap-4 sm:gap-6 md:gap-8 lg:gap-8 xl:gap-10 2xl:gap-12"
        >
          <div>
            <label
              class="block text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl font-medium text-gray-700 mb-1 xs:mb-1 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
            >
              Full Name <span class="text-red-500">*</span>
            </label>
            <input
              :value="fullName"
              type="text"
              readonly
              class="w-full px-2 xs:px-2 sm:px-3 md:px-4 lg:px-4 xl:px-5 2xl:px-6 py-1.5 xs:py-2 sm:py-2.5 md:py-3 lg:py-3 xl:py-4 2xl:py-5 text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter your full name"
            />
          </div>

          <div>
            <label
              class="block text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl font-medium text-gray-700 mb-1 xs:mb-1 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
            >
              Department <span class="text-red-500">*</span>
            </label>
            <input
              :value="employee.department"
              type="text"
              readonly
              class="w-full px-2 xs:px-2 sm:px-3 md:px-4 lg:px-4 xl:px-5 2xl:px-6 py-1.5 xs:py-2 sm:py-2.5 md:py-3 lg:py-3 xl:py-4 2xl:py-5 text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label
              class="block text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl font-medium text-gray-700 mb-1 xs:mb-1 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
            >
              Position <span class="text-red-500">*</span>
            </label>
            <input
              :value="employee.position"
              type="text"
              readonly
              class="w-full px-2 xs:px-2 sm:px-3 md:px-4 lg:px-4 xl:px-5 2xl:px-6 py-1.5 xs:py-2 sm:py-2.5 md:py-3 lg:py-3 xl:py-4 2xl:py-5 text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="e.g., Professor, Assistant Professor"
            />
          </div>

          <div>
            <label
              class="block text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl font-medium text-gray-700 mb-1 xs:mb-1 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
            >
              Employee ID <span class="text-red-500">*</span>
            </label>
            <input
              :value="employee.employee_code"
              type="text"
              readonly
              class="w-full px-2 xs:px-2 sm:px-3 md:px-4 lg:px-4 xl:px-5 2xl:px-6 py-1.5 xs:py-2 sm:py-2.5 md:py-3 lg:py-3 xl:py-4 2xl:py-5 text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Enter your employee ID"
            />
          </div>
        </div>

        <!-- Leave Details -->
        <div
          class="grid grid-cols-1 xs:grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-4 gap-3 xs:gap-4 sm:gap-6 md:gap-8 lg:gap-8 xl:gap-10 2xl:gap-12"
        >
          <div>
            <label
              class="block text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl font-medium text-gray-700 mb-1 xs:mb-1 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
            >
              Leave Type <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.leave_type_id"
              required
              class="w-full px-2 xs:px-2 sm:px-3 md:px-4 lg:px-4 xl:px-5 2xl:px-6 py-1.5 xs:py-2 sm:py-2.5 md:py-3 lg:py-3 xl:py-4 2xl:py-5 text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
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

          <div
            v-if="selectedLeaveName?.includes('Vacation')"
            class="grid grid-cols-2 gap-4 text-black"
          >
            <div>
              <label>Vacation Location Type</label>

              <select
                v-model="form.vacation_location_type"
                class="w-full border rounded-lg p-2 text-black"
              >
                <option value="">Select</option>
                <option value="within_philippines">Within Philippines</option>
                <option value="abroad">Abroad</option>
              </select>
            </div>

            <div>
              <label>Specify Location</label>

              <input
                v-model="form.vacation_location"
                type="text"
                class="w-full border rounded-lg p-2"
              />
            </div>
          </div>

          <div
            v-if="selectedLeaveName?.includes('Sick')"
            class="grid grid-cols-2 gap-4 text-black"
          >
            <div>
              <label>Treatment</label>

              <select
                v-model="form.sick_type"
                class="w-full border rounded-lg p-2"
              >
                <option value="">Select</option>
                <option value="in_hospital">In Hospital</option>
                <option value="out_patient">Out Patient</option>
              </select>
            </div>

            <div>
              <label>Illness</label>

              <input
                v-model="form.illness"
                type="text"
                class="w-full border rounded-lg p-2"
              />
            </div>
          </div>

          <div v-if="selectedLeaveName?.includes('Study')">
            <label class="block">
              <input type="checkbox" v-model="form.masters_degree" />

              Masters Degree
            </label>

            <label class="block mt-2">
              <input type="checkbox" v-model="form.board_exam_review" />

              Board Examination Review
            </label>
          </div>

          <div
            v-if="
              selectedLeaveName?.includes('Special') ||
              selectedLeaveName?.includes('Mandatory')
            "
          >
            <label>
              <input type="checkbox" v-model="form.monetization" />

              Monetization
            </label>

            <label>
              <input type="checkbox" v-model="form.terminal_leave" />

              Terminal Leave
            </label>

            <input
              v-model="form.other_purpose"
              placeholder="Other purpose"
              class="w-full border rounded-lg p-2 mt-2"
            />
          </div>

          <div>
            <label
              class="block text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl font-medium text-gray-700 mb-1 xs:mb-1 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
            >
              Commutation <span class="text-red-500">*</span>
            </label>

            <select
              v-model="form.commutation"
              class="w-full border rounded-lg p-2"
            >
              <option value="">Select</option>
              <option value="requested">Requested</option>
              <option value="not requested">Not Requested</option>
            </select>
          </div>

          <div>
            <label
              class="block text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl font-medium text-gray-700 mb-1 xs:mb-1 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
            >
              Contact Number <span class="text-red-500">*</span>
            </label>
            <input
              :value="employee.contact_number"
              type="text"
              readonly
              class="w-full px-2 xs:px-2 sm:px-3 md:px-4 lg:px-4 xl:px-5 2xl:px-6 py-1.5 xs:py-2 sm:py-2.5 md:py-3 lg:py-3 xl:py-4 2xl:py-5 text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl border border-gray-300 rounded-lg"
            />
          </div>
        </div>

        <!-- Leave Dates -->
        <div
          class="grid grid-cols-1 xs:grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 2xl:grid-cols-5 gap-3 xs:gap-4 sm:gap-6 md:gap-8 lg:gap-8 xl:gap-10 2xl:gap-12"
        >
          <div>
            <label
              class="block text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl font-medium text-gray-700 mb-1 xs:mb-1 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
            >
              Start Date <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.startDate"
              type="date"
              required
              :min="minDate"
              class="w-full px-2 xs:px-2 sm:px-3 md:px-4 lg:px-4 xl:px-5 2xl:px-6 py-1.5 xs:py-2 sm:py-2.5 md:py-3 lg:py-3 xl:py-4 2xl:py-5 text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label
              class="block text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl font-medium text-gray-700 mb-1 xs:mb-1 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
            >
              End Date <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.endDate"
              type="date"
              required
              :min="form.startDate || minDate"
              class="w-full px-2 xs:px-2 sm:px-3 md:px-4 lg:px-4 xl:px-5 2xl:px-6 py-1.5 xs:py-2 sm:py-2.5 md:py-3 lg:py-3 xl:py-4 2xl:py-5 text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>

          <div>
            <label
              class="block text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl font-medium text-gray-700 mb-1 xs:mb-1 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
            >
              Total Days
            </label>
            <div
              class="px-2 xs:px-2 sm:px-3 md:px-4 lg:px-4 xl:px-5 2xl:px-6 py-1.5 xs:py-2 sm:py-2.5 md:py-3 lg:py-3 xl:py-4 2xl:py-5 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 font-medium text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl"
            >
              {{ calculateDays() }} days
            </div>
          </div>
        </div>

        <!-- Leave Credits Information -->
        <div
          class="bg-blue-50 border border-blue-200 rounded-lg p-2 xs:p-3 sm:p-4 md:p-6 lg:p-6 xl:p-8 2xl:p-10"
        >
          <h3
            class="text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl font-semibold text-blue-800 mb-1 xs:mb-2 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
          >
            Available Leave Credits
          </h3>
          <div
            class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-4 gap-1 xs:gap-2 sm:gap-4 md:gap-6 lg:gap-6 xl:gap-8 2xl:gap-10 text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl"
          >
            <div>
              <span class="text-gray-600">Sick:</span>
              <span class="font-medium text-blue-800 ml-1">5 days</span>
            </div>
            <div>
              <span class="text-gray-600">Vacation:</span>
              <span class="font-medium text-blue-800 ml-1">10 days</span>
            </div>
            <div>
              <span class="text-gray-600">Personal:</span>
              <span class="font-medium text-blue-800 ml-1">3 days</span>
            </div>
            <div>
              <span class="text-gray-600">Emergency:</span>
              <span class="font-medium text-blue-800 ml-1">2 days</span>
            </div>
          </div>
        </div>

        <!-- Reason for Leave -->
        <div>
          <label
            class="block text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl font-medium text-gray-700 mb-1 xs:mb-1 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
          >
            Reason for Leave <span class="text-red-500">*</span>
          </label>
          <textarea
            v-model="form.reason"
            required
            rows="2 xs:2 sm:3 md:4 lg:5 xl:6 2xl:8"
            class="w-full px-2 xs:px-2 sm:px-3 md:px-4 lg:px-4 xl:px-5 2xl:px-6 py-1.5 xs:py-2 sm:py-2.5 md:py-3 lg:py-3 xl:py-4 2xl:py-5 text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Please provide a detailed reason for your leave application..."
          ></textarea>
        </div>

        <!-- Supporting Documents -->
        <div>
          <label
            class="block text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl font-medium text-gray-700 mb-1 xs:mb-1 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
          >
            Supporting Documents
          </label>
          <div
            class="border-2 border-dashed border-gray-300 rounded-lg p-3 xs:p-4 sm:p-6 md:p-8 lg:p-8 xl:p-10 2xl:p-12 text-center hover:border-gray-400 transition-colors"
          >
            <Upload
              class="mx-auto h-6 xs:h-8 sm:h-12 md:h-16 lg:h-16 xl:h-20 2xl:h-24 w-6 xs:w-8 sm:w-12 md:w-16 lg:w-16 xl:w-20 2xl:w-24 text-gray-400 mb-1 xs:mb-2 sm:mb-3 md:mb-4 lg:mb-4 xl:mb-6 2xl:mb-8"
            />
            <p
              class="text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl text-gray-600 mb-1 xs:mb-2 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
            >
              Click to upload or drag and drop
            </p>
            <p
              class="text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl text-gray-500"
            >
              PDF, DOC, DOCX files up to 10MB
            </p>
            <input
              type="file"
              multiple
              accept=".pdf,.doc,.docx"
              @change="handleFileUpload"
              class="hidden"
              ref="fileInput"
            />
            <button
              type="button"
              @click="fileInput?.click()"
              class="mt-1 xs:mt-2 sm:mt-3 md:mt-4 lg:mt-4 xl:mt-6 2xl:mt-8 px-2 xs:px-3 sm:px-4 md:px-6 lg:px-6 xl:px-8 2xl:px-10 py-1 xs:py-2 sm:py-2.5 md:py-3 lg:py-3 xl:py-4 2xl:py-5 bg-blue-600 text-white text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl rounded-lg hover:bg-blue-700"
            >
              Select Files
            </button>
          </div>

          <!-- Uploaded Files -->
          <div
            v-if="form.attachments.length > 0"
            class="mt-2 xs:mt-3 sm:mt-4 md:mt-6 lg:mt-6 xl:mt-8 2xl:mt-10 space-y-1 xs:space-y-2 sm:space-y-3 md:space-y-4 lg:space-y-4 xl:space-y-6 2xl:space-y-8"
          >
            <div
              v-for="(file, index) in form.attachments"
              :key="index"
              class="flex flex-col xs:flex-col sm:flex-row md:flex-row lg:flex-row xl:flex-row 2xl:flex-row items-start xs:items-start sm:items-center md:items-center lg:items-center xl:items-center 2xl:items-center justify-between p-2 xs:p-2 sm:p-3 md:p-4 lg:p-4 xl:p-6 2xl:p-8 bg-gray-50 rounded-lg"
            >
              <div
                class="flex items-center mb-1 xs:mb-1 sm:mb-2 md:mb-0 lg:mb-0 xl:mb-0 2xl:mb-0"
              >
                <FileText
                  class="w-3 xs:w-3 sm:w-4 md:w-5 lg:w-5 xl:w-6 2xl:w-8 h-3 xs:h-3 sm:h-4 md:h-5 lg:h-5 xl:h-6 2xl:h-8 mr-1 xs:mr-1 sm:mr-2 md:mr-3 lg:mr-3 xl:mr-4 2xl:mr-6 text-gray-400"
                />
                <span
                  class="text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl text-gray-700 truncate max-w-[150px] xs:max-w-[200px] sm:max-w-none md:max-w-md lg:max-w-md xl:max-w-lg 2xl:max-w-xl"
                  >{{ file.name }}</span
                >
                <span
                  class="text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl text-gray-500 ml-1 xs:ml-1 sm:ml-2 md:ml-2 lg:ml-2 xl:ml-3 2xl:ml-4"
                  >({{ formatFileSize(file.size) }})</span
                >
              </div>
              <button
                type="button"
                @click="removeFile(index)"
                class="text-red-500 hover:text-red-700 self-start sm:self-auto"
              >
                <X class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Declaration -->
        <div class="border-t pt-6">
          <div class="flex items-start">
            <input
              v-model="form.declaration"
              type="checkbox"
              required
              class="mt-1 mr-3"
            />
            <label
              class="text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl text-gray-700"
            >
              I hereby certify that the information provided in this leave
              application is true and correct. I understand that any false
              statement may result in disciplinary action. I agree to comply
              with all company policies and procedures regarding leave
              applications.
            </label>
          </div>
        </div>

        <!-- Submit Buttons -->
        <div
          class="flex flex-col xs:flex-col sm:flex-row md:flex-row lg:flex-row xl:flex-row 2xl:flex-row justify-end space-y-1 xs:space-y-2 sm:space-y-2 md:space-y-0 lg:space-y-0 xl:space-y-0 2xl:space-y-0 space-x-0 xs:space-x-0 sm:space-x-4 md:space-x-6 lg:space-x-6 xl:space-x-8 2xl:space-x-10 pt-3 xs:pt-4 sm:pt-6 md:pt-8 lg:pt-8 xl:pt-10 2xl:pt-12 border-t"
        >
          <button
            type="button"
            @click="resetForm"
            class="w-full xs:w-full sm:w-auto md:w-auto lg:w-auto xl:w-auto 2xl:w-auto px-3 xs:px-4 sm:px-6 md:px-8 lg:px-8 xl:px-10 2xl:px-12 py-1 xs:py-2 sm:py-2.5 md:py-3 lg:py-3 xl:py-4 2xl:py-5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl"
          >
            Reset Form
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="w-full xs:w-full sm:w-auto md:w-auto lg:w-auto xl:w-auto 2xl:w-auto px-3 xs:px-4 sm:px-6 md:px-8 lg:px-8 xl:px-10 2xl:px-12 py-1 xs:py-2 sm:py-2.5 md:py-3 lg:py-3 xl:py-4 2xl:py-5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl"
          >
            {{ isSubmitting ? "Submitting..." : "Submit Application" }}
          </button>
        </div>
      </form>
    </div>

    <!-- Success Modal -->
    <div
      v-if="showSuccessModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    >
      <div
        class="relative top-20 mx-auto p-3 xs:p-4 sm:p-5 md:p-6 lg:p-6 xl:p-8 2xl:p-10 border w-full xs:w-full sm:w-11/12 md:w-10/12 lg:w-3/4 xl:w-2/3 2xl:w-3/5 shadow-lg rounded-lg bg-white"
      >
        <div class="text-center">
          <div
            class="mx-auto flex items-center justify-center h-8 xs:h-10 sm:h-12 md:h-14 lg:h-14 xl:h-16 2xl:h-20 w-8 xs:w-10 sm:w-12 md:w-14 lg:w-14 xl:w-16 2xl:w-20 rounded-full bg-green-100 mb-2 xs:mb-3 sm:mb-4 md:mb-6 lg:mb-6 xl:mb-8 2xl:mb-10"
          >
            <CheckCircle
              class="h-4 xs:h-5 sm:h-6 md:h-7 lg:h-7 xl:h-8 2xl:h-10 w-4 xs:w-5 sm:w-6 md:w-7 lg:w-7 xl:w-8 2xl:w-10 text-green-600"
            />
          </div>
          <h3
            class="text-sm xs:text-base sm:text-lg md:text-xl lg:text-xl xl:text-2xl 2xl:text-3xl font-medium text-gray-900 mb-1 xs:mb-2 sm:mb-2 md:mb-3 lg:mb-3 xl:mb-4 2xl:mb-6"
          >
            Application Submitted Successfully!
          </h3>
          <p
            class="text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl text-gray-500 mb-2 xs:mb-3 sm:mb-4 md:mb-6 lg:mb-6 xl:mb-8 2xl:mb-10"
          >
            Your leave application has been submitted to ADAS for review. You
            will receive a notification once there's an update.
          </p>
          <div
            class="flex flex-col xs:flex-col sm:flex-row md:flex-row lg:flex-row xl:flex-row 2xl:flex-row justify-center space-y-1 xs:space-y-2 sm:space-y-2 md:space-y-0 lg:space-y-0 xl:space-y-0 2xl:space-y-0 space-x-0 xs:space-x-0 sm:space-x-3 md:space-x-4 lg:space-x-4 xl:space-x-6 2xl:space-x-8"
          >
            <button
              @click="showSuccessModal = false"
              class="px-2 xs:px-3 sm:px-4 md:px-6 lg:px-6 xl:px-8 2xl:px-10 py-1 xs:py-2 sm:py-2.5 md:py-3 lg:py-3 xl:py-4 2xl:py-5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 text-xs xs:text-xs sm:text-sm md:text-base lg:text-base xl:text-lg 2xl:text-xl"
            >
              Close
            </button>
            <button
@click="viewApplicationStatus"
class="px-2 xs:px-3 sm:px-4 md:px-6 lg:px-6 xl:px-8 2xl:px-10 py-1 xs:py-2 sm:py-2.5 md:py-3 bg-blue-600 text-white rounded-lg"
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
import { ref, computed, onMounted } from "vue";
import {
  getLeaveTypes,
  submitLeave,
  getMyLeaves
} from "../services/leave";
import { useRouter } from "vue-router";
import { Upload, FileText, X, CheckCircle } from "lucide-vue-next";
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

const fullName = computed(() => {
  return [
    employee.value.first_name,
    employee.value.middle_name,
    employee.value.last_name,
  ]
    .filter(Boolean)
    .join(" ");
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

const isSubmitting = ref(false);
const leaveTypes = ref<any[]>([]);
onMounted(async () => {
  try {
    leaveTypes.value = await getLeaveTypes();

    employee.value = await getMyProfile();
  } catch (error) {
    console.error(error);
  }
});
const showSuccessModal = ref(false);
const submittedLeaveId = ref<number | null>(null);
const fileInput = ref<HTMLInputElement | null>(null);

const minDate = computed(() => {
  const today = new Date();
  return today.toISOString().split("T")[0];
});

const calculateDays = () => {
  if (!form.value.startDate || !form.value.endDate) return 0;

  const start = new Date(form.value.startDate);
  const end = new Date(form.value.endDate);
  const diffTime = Math.abs(end.getTime() - start.getTime());
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

  return diffDays;
};

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const files = target.files;

  if (files) {
    for (let i = 0; i < files.length; i++) {
      const file = files[i];
      if (file && file.size <= 10 * 1024 * 1024) {
        // 10MB limit
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
  if (bytes === 0) return "0 Bytes";
  const k = 1024;
  const sizes = ["Bytes", "KB", "MB", "GB"];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
};

const submitApplication = async () => {
  isSubmitting.value = true;

  try {
    const data = new FormData();

    data.append("leave_type_id", String(form.value.leave_type_id));
    data.append("date_filed", new Date().toISOString().split("T")[0]);
    data.append("start_date", form.value.startDate);
    data.append("end_date", form.value.endDate);
    data.append("number_of_days", String(calculateDays()));
    data.append("reason", form.value.reason);

    // Additional Leave Details
    data.append("vacation_location_type", form.value.vacation_location_type);

    data.append("vacation_location", form.value.vacation_location);

    data.append("sick_type", form.value.sick_type);

    data.append("illness", form.value.illness);

    data.append("masters_degree", form.value.masters_degree ? "1" : "0");

    data.append("board_exam_review", form.value.board_exam_review ? "1" : "0");

    data.append("monetization", form.value.monetization ? "1" : "0");

    data.append("terminal_leave", form.value.terminal_leave ? "1" : "0");

    data.append("other_purpose", form.value.other_purpose);

    data.append("commutation", form.value.commutation);

    // Attachments
    form.value.attachments.forEach((file) => {
      data.append("attachments[]", file.file);
    });

    // Submit to API
const response = await submitLeave(data);

console.log("SUBMIT RESPONSE:", response);


// get the newly created leave application
const applications = await getMyLeaves();

const latestLeave = applications[0];


submittedLeaveId.value = latestLeave.leave_id;


console.log(
  "SUBMITTED LEAVE ID:",
  submittedLeaveId.value
);


showSuccessModal.value = true;


resetForm();
  } catch (error) {
    console.error(error);
  } finally {
    isSubmitting.value = false;
  }
};

const viewApplicationStatus = () => {

  if (!submittedLeaveId.value) {
    console.error("No leave ID available");
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
  const leave = leaveTypes.value.find(
    (l) => l.leave_type_id == form.value.leave_type_id,
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
};

</script>
<style scoped>
.input-field {
  @apply w-full border border-gray-300 rounded-lg px-4 py-3 bg-white text-gray-900;
}

.input-field:focus {
  @apply outline-none ring-2 ring-blue-500;
}

.input-field::placeholder {
  @apply text-gray-400;
}

.input-field:read-only {
  @apply text-gray-900 bg-gray-100;
}

input,
select,
textarea {
  color: #111827 !important;
}

input:read-only {
  color: #111827 !important;
  background: #f3f4f6;
}

select option {
  color: #111827;
}
</style>
