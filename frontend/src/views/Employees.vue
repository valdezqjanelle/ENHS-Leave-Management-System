<template>
  <div class="employee-management-page w-full min-h-screen">
    <div class="dashboard-shell w-full max-w-none mx-auto space-y-6 px-4 sm:px-6 lg:px-8 py-6">
      <div class="neo-card w-full p-6">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4">
          <div class="min-w-0">
            <h2 class="text-2xl font-bold text-[var(--text)]">
              Employee Management
            </h2>

            <p class="text-[var(--text-muted)] mt-1">
              Create and manage employee accounts.
            </p>
          </div>

          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <button @click="openDeletedEmployees"
              class="employee-btn employee-btn-secondary whitespace-nowrap">
              Deleted Employees
            </button>

            <button @click="openCreateModal"
              class="employee-btn employee-btn-primary whitespace-nowrap">
              + Create Employee
            </button>
          </div>
        </div>
      </div>

      <div class="neo-card w-full p-6">
        <div class="space-y-3">
          <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <input v-model="searchInput" type="text" placeholder="Search employee..."
              class="flex-1 min-w-0 field-input rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />

            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:flex-shrink-0">
              <label for="sortBy" class="text-sm font-medium text-[var(--text-muted)] whitespace-nowrap">Sort by</label>
              <select id="sortBy" v-model="sortOption" class="w-full sm:w-60 field-input rounded-lg px-4 py-2">
                <option v-for="option in sortOptions" :key="option.value" :value="option.value">{{ option.label }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <select v-model="statusFilter" aria-label="Filter by employment status"
              class="w-full min-w-0 field-input rounded-lg px-4 py-2">
              <option value="all">All Statuses</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>

            <select v-model="personnelTypeFilter" aria-label="Filter by personnel type"
              class="w-full min-w-0 field-input rounded-lg px-4 py-2">
              <option value="all">All Personnel Types</option>
              <option v-for="value in personnelTypeOptions" :key="value" :value="value">{{ value }}</option>
            </select>

            <select v-model="positionFilter" aria-label="Filter by position"
              class="w-full min-w-0 field-input rounded-lg px-4 py-2">
              <option value="all">All Positions</option>
              <option v-for="value in positionOptions" :key="value" :value="value">{{ value }}</option>
            </select>

            <button type="button" @click="clearFilters"
              class="employee-btn employee-btn-secondary whitespace-nowrap">
              Clear filters
            </button>
          </div>
        </div>
      </div>

      <div class="neo-card w-full p-6">
        <div class="mb-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
          <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-[var(--text-muted)]" aria-live="polite">
            <strong class="text-[var(--text)]">Total: {{ employees.length }}</strong>
            <span>Active: {{ activeEmployeeCount }}</span>
            <span>Inactive: {{ inactiveEmployeeCount }}</span>
            <span>Showing: {{ filteredEmployees.length }}</span>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <span v-if="selectedEmployeeIds.length" class="text-sm text-[var(--text-muted)]">
              {{ selectedEmployeeIds.length }} selected
            </span>
            <button type="button" :disabled="!selectedEmployeeIds.length" @click="confirmBulkAction('activate')"
              class="employee-btn employee-btn-status">
              Activate
            </button>
            <button type="button" :disabled="!selectedEmployeeIds.length" @click="confirmBulkAction('deactivate')"
              class="employee-btn employee-btn-secondary">
              Deactivate
            </button>
            <button type="button" :disabled="!selectedEmployeeIds.length" @click="confirmBulkAction('delete')"
              class="employee-btn employee-btn-danger">
              Delete selected
            </button>
            <button type="button" @click="exportFilteredEmployees"
              class="employee-btn employee-btn-secondary">
              Export CSV / Excel
            </button>
          </div>
        </div>
        <div class="table-wrapper employee-list-table-wrapper">
          <table class="employee-table">
            <thead class="table-head">
              <tr class="text-left text-[var(--text)] font-semibold">
                <th class="px-2 sm:px-3 py-3 text-center">
                  <input type="checkbox" :checked="allPageEmployeesSelected" :indeterminate="somePageEmployeesSelected"
                    aria-label="Select employees on this page" @change="togglePageSelection" class="employee-checkbox" />
                </th>
                <th class="px-2 sm:px-3 py-3 font-bold text-center">#</th>
                <th v-for="column in sortableColumns" :key="column.key" class="px-2 sm:px-3 py-3 font-bold"
                  :aria-sort="ariaSort(column.key)">
                  <button type="button" @click="toggleColumnSort(column.key)"
                    class="employee-sort-button inline-flex items-center gap-1 font-bold text-left hover:text-blue-600 transition-colors"
                    :title="`Sort by ${column.label}`">
                    {{ column.label }}
                    <span class="employee-sort-indicator text-xs" :class="sortKey === column.key ? 'text-blue-600' : 'text-slate-400'">
                      {{ sortIndicator(column.key) }}
                    </span>
                  </button>
                </th>
                <th class="px-2 sm:px-3 py-3 font-bold text-center">
                  Action
                </th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="(employee, rowIndex) in paginatedEmployees" :key="employee.employee_id"
                class="border-t border-[#cbd8e8] hover:bg-[#eef4fb] transition-colors duration-200">
                <td data-label="Select" class="px-2 sm:px-3 py-4 text-center">
                  <input v-model="selectedEmployeeIds" type="checkbox" :value="employee.employee_id"
                    :aria-label="`Select ${employee.first_name} ${employee.last_name}`" class="employee-checkbox" />
                </td>
                <td data-label="#" class="employee-number-cell px-2 sm:px-3 py-4 text-[var(--text-muted)]">
                  {{ (currentPage - 1) * pageSize + rowIndex + 1 }}
                </td>
                <td data-label="Employee Code" class="employee-code-cell px-2 sm:px-3 py-4 text-[var(--text)] font-semibold">
                  {{ employee.employee_code }}
                </td>

                <td data-label="Employee" class="employee-name-cell px-2 sm:px-3 py-4 text-[var(--text)] font-medium">
                  {{ employee.last_name }},
                  {{ employee.first_name }}
                  {{ employee.middle_name || "" }}
                  {{ employee.extension_name || "" }}
                </td>

                <td data-label="Email" class="employee-email-cell px-2 sm:px-3 py-4 text-[var(--text)]">
                  <span class="employee-email-value" :title="employee.user?.email || '-'">{{ employee.user?.email || "-" }}</span>
                </td>

                <td data-label="Position" class="employee-position-cell px-2 sm:px-3 py-4 text-[var(--text)]">
                  <span class="employee-position-value" :title="employee.position?.name || '-'">
                    {{ employee.position?.name || "-" }}
                  </span>
                </td>

                <td data-label="Status" class="employee-status-cell px-2 sm:px-3 py-4 text-[var(--text)]">
                  <span :class="normalizeEmploymentStatus(employee.employment_status) ===
                    'active'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-slate-100 text-slate-700'
                    " class="inline-block px-2 py-1 rounded-full text-xs whitespace-nowrap">
                    {{ employee.employment_status || "Inactive" }}
                  </span>
                </td>

                <td data-label="Actions" class="employee-actions-cell px-2 sm:px-3 py-4">
                  <div class="employee-row-actions">
                    <button @click="viewEmployee(employee)"
                      class="employee-btn employee-btn-secondary employee-view-button">
                      View
                    </button>

                    <details class="employee-actions-menu" @keydown.esc="closeEmployeeActionMenu">
                      <summary class="employee-actions-trigger" aria-label="More employee actions" title="More actions">
                        <svg aria-hidden="true" viewBox="0 0 20 20" class="h-5 w-5">
                          <circle cx="4" cy="10" r="1.5" fill="currentColor" />
                          <circle cx="10" cy="10" r="1.5" fill="currentColor" />
                          <circle cx="16" cy="10" r="1.5" fill="currentColor" />
                        </svg>
                      </summary>
                      <div class="employee-actions-popover">
                        <button type="button" class="employee-actions-item"
                          :disabled="resettingEmployeeId === employee.employee_id"
                          @click="closeEmployeeActionMenu($event); resetEmployeePassword(employee)">
                          {{ resettingEmployeeId === employee.employee_id ? "Resetting..." : "Reset Password" }}
                        </button>
                        <button type="button" class="employee-actions-item employee-actions-item-danger"
                          @click="closeEmployeeActionMenu($event); deleteEmployee(employee)">
                          Delete
                        </button>
                      </div>
                    </details>
                  </div>
                </td>
              </tr>

              <tr v-if="filteredEmployees.length === 0">
                <td colspan="8" class="empty-row text-center py-10 text-[var(--text-muted)]">
                  No employees found.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination controls -->
        <div v-if="filteredEmployees.length > 0"
          class="mt-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 text-sm text-[var(--text-muted)]">
          <div class="flex items-center gap-3 flex-wrap">
            <span>
              Showing {{ showingFrom }}–{{ showingTo }} of
              {{ filteredEmployees.length }}
            </span>

            <label class="flex items-center gap-2">
              Rows per page
              <select v-model.number="pageSize" class="field-input rounded-lg px-2 py-1">
                <option v-for="size in pageSizeOptions" :key="size" :value="size">
                  {{ size }}
                </option>
              </select>
            </label>
          </div>

          <div class="flex items-center gap-1 flex-wrap">
            <button @click="goToPage(1)" :disabled="currentPage === 1"
              class="px-3 py-1 rounded-lg border border-[#cbd8e8] bg-[var(--surface)] disabled:opacity-40 hover:bg-[#eef4fb]">
              «
            </button>

            <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
              class="px-3 py-1 rounded-lg border border-[#cbd8e8] bg-[var(--surface)] disabled:opacity-40 hover:bg-[#eef4fb]">
              Prev
            </button>

            <button v-for="(page, i) in visiblePages" :key="i" @click="goToPage(page)" :disabled="page === '...'"
              :class="page === currentPage
                ? 'bg-blue-600 text-white border-blue-600'
                : 'bg-[var(--surface)] border-[#cbd8e8] hover:bg-[#eef4fb]'
                " class="min-w-[2rem] px-2 py-1 rounded-lg border">
              {{ page }}
            </button>

            <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
              class="px-3 py-1 rounded-lg border border-[#cbd8e8] bg-[var(--surface)] disabled:opacity-40 hover:bg-[#eef4fb]">
              Next
            </button>

            <button @click="goToPage(totalPages)" :disabled="currentPage === totalPages"
              class="px-3 py-1 rounded-lg border border-[#cbd8e8] bg-[var(--surface)] disabled:opacity-40 hover:bg-[#eef4fb]">
              »
            </button>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showCreateModal"
      class="fixed inset-0 bg-[rgba(23,32,51,0.55)] flex items-center justify-center z-50 p-4">
      <div class="bg-[var(--surface)] rounded-xl shadow-xl w-full max-w-4xl mx-auto overflow-hidden">
        <div class="bg-blue-600 text-white px-6 py-4">
          <h3 class="text-xl font-semibold">Create Employee</h3>

          <p class="text-blue-100 text-sm">
            Fill in the employee's official information.
          </p>
        </div>

        <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
          <div>
            <h4 class="font-semibold text-[var(--text)] mb-4">
              Account Information
            </h4>

            <div class="grid grid-cols-1 gap-4">
              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Email
                </label>

                <input v-model="form.email" type="email" :aria-invalid="Boolean(createFormErrors.email)"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2"
                  :class="{ 'border-red-500': createFormErrors.email }" placeholder="employee@email.com" />
                <p v-if="createFormErrors.email" class="mt-1 text-sm text-red-600">{{ createFormErrors.email }}</p>
              </div>
            </div>
          </div>

          <div>
            <h4 class="font-semibold text-[var(--text)] mb-1">
              Personal Information
            </h4>

            <p class="text-sm text-[var(--text-muted)] mb-4">
              Official basic information should be entered by the Admin.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  First Name
                </label>

                <input v-model="form.first_name" type="text" :aria-invalid="Boolean(createFormErrors.first_name)"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2"
                  :class="{ 'border-red-500': createFormErrors.first_name }" />
                <p v-if="createFormErrors.first_name" class="mt-1 text-sm text-red-600">{{ createFormErrors.first_name }}</p>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Middle Name
                </label>

                <input v-model="form.middle_name" type="text" class="w-full min-w-0 field-input rounded-lg px-3 py-2" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Last Name
                </label>

                <input v-model="form.last_name" type="text" :aria-invalid="Boolean(createFormErrors.last_name)"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2"
                  :class="{ 'border-red-500': createFormErrors.last_name }" />
                <p v-if="createFormErrors.last_name" class="mt-1 text-sm text-red-600">{{ createFormErrors.last_name }}</p>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Extension Name
                </label>

                <select v-model="form.extension_name" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option value="">None</option>
                  <option value="Jr.">Jr.</option>
                  <option value="Sr.">Sr.</option>
                  <option value="II">II</option>
                  <option value="III">III</option>
                  <option value="IV">IV</option>
                  <option value="V">V</option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Date of Birth
                </label>

                <input v-model="form.date_of_birth" type="date"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Sex
                </label>

                <select v-model="form.sex" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option value="">Select</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Civil Status
                  <span class="text-slate-400 font-normal">(Optional)</span>
                </label>

                <select v-model="form.civil_status" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option value="">Select</option>
                  <option value="Single">Single</option>
                  <option value="Married">Married</option>
                  <option value="Widowed">Widowed</option>
                  <option value="Separated">Separated</option>
                  <option value="Divorced">Divorced</option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Nationality
                  <span class="text-slate-400 font-normal">(Optional)</span>
                </label>

                <input v-model="form.nationality" type="text" class="w-full min-w-0 field-input rounded-lg px-3 py-2"
                  placeholder="e.g. Filipino" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Contact Number
                  <span class="text-slate-400 font-normal">(Optional)</span>
                </label>

                <input v-model="form.contact_number" type="tel" inputmode="numeric" maxlength="11" @input="
                  form.contact_number = form.contact_number
                    .replace(/\D/g, '')
                    .slice(0, 11)
                  " class="w-full min-w-0 field-input rounded-lg px-3 py-2" placeholder="09XXXXXXXXX" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Personal Email
                  <span class="text-slate-400 font-normal">(Optional)</span>
                </label>

                <input v-model="form.personal_email" type="email"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2" placeholder="personal@email.com" />
              </div>

              <div class="md:col-span-2">
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Address
                  <span class="text-slate-400 font-normal">(Optional)</span>
                </label>

                <textarea v-model="form.address" rows="3"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2 resize-none"
                  placeholder="Complete residential address"></textarea>
              </div>
            </div>
          </div>

          <div>
            <h4 class="font-semibold text-[var(--text)] mb-1">
              Emergency Contact
            </h4>

            <p class="text-sm text-[var(--text-muted)] mb-4">
              These details may be completed later by the employee.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Emergency Contact Name
                  <span class="text-slate-400 font-normal">(Optional)</span>
                </label>

                <input v-model="form.emergency_contact_name" type="text"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2" placeholder="Full name" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Emergency Contact Number
                  <span class="text-slate-400 font-normal">(Optional)</span>
                </label>

                <input v-model="form.emergency_contact_number" type="tel" inputmode="numeric" maxlength="11" @input="
                  form.emergency_contact_number =
                  form.emergency_contact_number
                    .replace(/\D/g, '')
                    .slice(0, 11)
                  " class="w-full min-w-0 field-input rounded-lg px-3 py-2" placeholder="09XXXXXXXXX" />
              </div>
              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Relationship
                  <span class="text-slate-400 font-normal">(Optional)</span>
                </label>

                <input v-model="form.emergency_contact_relationship" type="text"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2" placeholder="e.g. Spouse, Parent, Sibling" />
              </div>
            </div>
          </div>

          <div>
            <h4 class="font-semibold text-[var(--text)] mb-4">
              Employment Information
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Personnel Type
                </label>

                <select v-model="form.personnel_type" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option value="">Select</option>
                  <option value="Teaching">Teaching</option>
                  <option value="Non-Teaching">Non-Teaching</option>
                  <option value="School Head">School Head</option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Employment Category
                </label>

                <select v-model="form.employment_category" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option value="">Select</option>
                  <option value="Permanent">Permanent</option>
                  <option value="Probationary">Probationary</option>
                  <option value="Contractual">Contractual</option>
                  <option value="Casual">Casual</option>
                  <option value="Temporary">Temporary</option>
                  <option value="Contract of Service">
                    Contract of Service
                  </option>
                  <option value="Job Order">Job Order</option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Date Hired
                </label>

                <input v-model="form.date_hired" type="date" class="w-full min-w-0 field-input rounded-lg px-3 py-2" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Employment Status
                </label>

                <select v-model="form.employment_status" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Level
                </label>

                <select v-model="form.level" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option value="">Select</option>
                  <option value="JHS">JHS</option>
                  <option value="SHS">SHS</option>
                  <option value="Non-Teaching">Non-Teaching</option>
                </select>
              </div>

              <div v-if="form.level">
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  {{ assignmentAreaLabel(form.level) }}
                </label>

                <select v-model="form.department_id" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option :value="null">
                    {{ assignmentAreaPlaceholder(form.level) }}
                  </option>

                  <option v-for="dept in filteredDepartmentsForCreate" :key="dept.department_id"
                    :value="dept.department_id">
                    {{ dept.department_name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Position
                </label>

                <select v-model="form.position_id" :aria-invalid="Boolean(createFormErrors.position_id)"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2"
                  :class="{ 'border-red-500': createFormErrors.position_id }">
                  <option :value="null">Select</option>

                  <option v-for="pos in positions" :key="pos.id" :value="pos.id">
                    {{ pos.name }}
                  </option>
                </select>
                <p v-if="createFormErrors.position_id" class="mt-1 text-sm text-red-600">{{ createFormErrors.position_id }}</p>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Supervisor
                </label>

                <select v-model="form.supervisor_id" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option :value="null">None</option>

                  <option v-for="employee in employees" :key="employee.employee_id" :value="employee.employee_id">
                    {{ employee.first_name }}
                    {{ employee.middle_name ? employee.middle_name + " " : "" }}
                    {{ employee.last_name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Salary Grade
                </label>

                <input :value="selectedCreateSalaryGrade || '-'" type="text" readonly
                  class="w-full min-w-0 field-input field-readonly rounded-lg px-3 py-2" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Salary Step
                </label>

                <select v-model="form.salary_step" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option :value="null">Select Step</option>

                  <option v-for="step in 8" :key="step" :value="step">
                    Step {{ step }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Current Salary
                </label>

                <input :value="formattedCreateSalary" type="text" readonly
                  class="w-full min-w-0 field-input field-readonly rounded-lg px-3 py-2 font-semibold" />
              </div>
            </div>
          </div>
        </div>

        <div class="bg-[#f3f7fc] border-t border-[#cbd8e8] px-6 py-4 flex flex-col sm:flex-row justify-end gap-3">
          <button @click="showCreateModal = false"
            class="employee-btn employee-btn-secondary w-full sm:w-auto">
            Cancel
          </button>

          <button @click="saveEmployee"
            class="employee-btn employee-btn-primary w-full sm:w-auto">
            Create Employee
          </button>
        </div>
      </div>
    </div>

    <div v-if="showCredentialsModal"
      class="fixed inset-0 bg-[rgba(23,32,51,0.55)] flex items-center justify-center z-50 p-4">
      <div class="bg-[var(--surface)] rounded-xl shadow-xl w-full max-w-md mx-auto overflow-hidden">
        <div class="bg-green-600 text-white p-5">
          <h3 class="text-xl font-bold">{{ generatedCredentials.title }}</h3>
        </div>

        <div class="p-6 space-y-4">
          <p class="text-[var(--text)] text-sm">
            {{ generatedCredentials.message }}
          </p>

          <div>
            <label class="text-sm text-[var(--text)] font-semibold">
              Email
            </label>

            <div
              class="bg-[#f3f7fc] border border-[#cbd8e8] rounded-lg px-3 py-2 text-[var(--text)] font-bold mt-1 break-all">
              {{ generatedCredentials.email }}
            </div>
          </div>

          <div>
            <label class="text-sm text-[var(--text)] font-semibold">
              Temporary Password
            </label>

            <div
              class="bg-[#f3f7fc] border border-[#cbd8e8] rounded-lg px-3 py-2 text-[var(--text)] font-bold mt-1 font-mono break-all">
              {{ generatedCredentials.password }}
            </div>
          </div>

          <div class="bg-[#fffbeb] border border-[#f1dfad] rounded-lg p-3">
            <p class="text-sm text-[#a16207]">
              Keep these credentials safe. The employee will use them to log in.
            </p>
          </div>
        </div>

        <div class="bg-[#f3f7fc] border-t border-[#cbd8e8] p-4 flex justify-end">
          <button @click="showCredentialsModal = false"
            class="employee-btn employee-btn-primary">
            Close
          </button>
        </div>
      </div>
    </div>

    <div v-if="showViewModal && selectedEmployee"
      class="fixed inset-0 bg-[rgba(23,32,51,0.55)] flex items-center justify-center z-50 p-4">
      <div class="bg-[var(--surface)] rounded-xl shadow-2xl w-full max-w-5xl mx-auto overflow-hidden">
        <div class="bg-blue-600 text-white px-6 py-5 flex justify-between items-center">
          <div class="min-w-0">
            <h2 class="text-2xl font-bold">Employee Profile</h2>

            <p class="text-blue-100 text-sm">
              Employee information and employment details
            </p>
          </div>

          <button @click="showViewModal = false" class="text-white text-3xl ml-4 flex-shrink-0">
            &times;
          </button>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8 max-h-[75vh] overflow-y-auto">
          <div class="space-y-6 min-w-0">
            <div>
              <h3 class="font-semibold text-blue-600 border-b border-[#cbd8e8] pb-2">
                Account Information
              </h3>

              <div class="mt-4 space-y-3 text-[var(--text)]">
                <div>
                  <span class="font-medium"> Employee Code: </span>
                  <br />
                  {{ selectedEmployee.employee_code }}
                </div>

                <div class="break-all">
                  <span class="font-medium"> Email: </span>
                  <br />
                  {{ selectedEmployee.user?.email || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Role: </span>
                  <br />
                  Employee
                </div>

                <div>
                  <span class="font-medium"> Employment Status: </span>
                  <br />

                  <span :class="normalizeEmploymentStatus(
                    selectedEmployee.employment_status,
                  ) === 'active'
                    ? 'bg-green-100 text-green-700'
                    : 'bg-slate-100 text-slate-700'
                    " class="px-3 py-1 rounded-full text-sm">
                    {{ selectedEmployee.employment_status || "-" }}
                  </span>
                </div>

                <div>
                  <span class="font-medium"> Date Hired: </span>
                  <br />
                  {{ formatDate(selectedEmployee.date_hired) }}
                </div>

                <div>
                  <span class="font-medium"> Created By: </span>
                  <br />
                  {{ getCreatorName(selectedEmployee) || "-" }}
                </div>
              </div>
            </div>
          </div>

          <div class="space-y-6 min-w-0">
            <div>
              <h3 class="font-semibold text-blue-600 border-b border-[#cbd8e8] pb-2">
                Personal Information
              </h3>

              <div class="mt-4 space-y-3 text-[var(--text)]">
                <div>
                  <span class="font-medium"> First Name: </span>
                  <br />
                  {{ selectedEmployee.first_name || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Middle Name: </span>
                  <br />
                  {{ selectedEmployee.middle_name || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Last Name: </span>
                  <br />
                  {{ selectedEmployee.last_name || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Extension Name: </span>
                  <br />
                  {{ selectedEmployee.extension_name || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Date of Birth: </span>
                  <br />
                  {{ formatDate(selectedEmployee.date_of_birth) }}
                </div>

                <div>
                  <span class="font-medium"> Sex: </span>
                  <br />
                  {{ selectedEmployee.sex || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Civil Status: </span>
                  <br />
                  {{ selectedEmployee.civil_status || "-" }}
                </div>

                <div>
                  <span class="font-medium"> Nationality: </span>
                  <br />
                  {{ selectedEmployee.nationality || "-" }}
                </div>
              </div>
            </div>
          </div>

          <div class="md:col-span-2">
            <h3 class="font-semibold text-blue-600 border-b border-[#cbd8e8] pb-2">
              Contact Information
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mt-5 text-[var(--text)]">
              <div class="min-w-0">
                <span class="font-medium"> Contact Number </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.contact_number || "-" }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Personal Email </span>

                <p class="mt-1 break-all">
                  {{ selectedEmployee.personal_email || "-" }}
                </p>
              </div>

              <div class="min-w-0 lg:col-span-1">
                <span class="font-medium"> Address </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.address || "-" }}
                </p>
              </div>
            </div>
          </div>

          <div class="md:col-span-2">
            <h3 class="font-semibold text-blue-600 border-b border-[#cbd8e8] pb-2">
              Emergency Contact
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-5 text-[var(--text)]">
              <div>
                <span class="font-medium"> Emergency Contact Name </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.emergency_contact_name || "-" }}
                </p>
              </div>

              <div>
                <span class="font-medium"> Emergency Contact Number </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.emergency_contact_number || "-" }}
                </p>
              </div>

              <div>
                <span class="font-medium"> Relationship </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.emergency_contact_relationship || "-" }}
                </p>
              </div>
            </div>
          </div>

          <div class="md:col-span-2">
            <h3 class="font-semibold text-blue-600 border-b border-[#cbd8e8] pb-2">
              Employment Information
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mt-5 text-[var(--text)]">
              <div class="min-w-0">
                <span class="font-medium"> Personnel Type </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.personnel_type || "-" }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Employment Category </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.employment_category || "-" }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium">
                  {{ assignmentAreaLabel(selectedEmployee.level) }}
                </span>

                <p class="mt-1 break-words">
                  {{
                    selectedEmployee.department?.department_name ||
                    selectedEmployee.department_name ||
                    "-"
                  }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Position </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.position?.name || "-" }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Level </span>

                <p class="mt-1 break-words">
                  {{ selectedEmployee.level || "-" }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Supervisor </span>

                <p class="mt-1 break-words">
                  {{
                    selectedEmployee.supervisor
                      ? `${selectedEmployee.supervisor.first_name} ${selectedEmployee.supervisor.middle_name
                        ? selectedEmployee.supervisor.middle_name + " "
                        : ""
                      }${selectedEmployee.supervisor.last_name}`
                      : "-"
                  }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Salary Grade </span>

                <p class="mt-1">
                  {{ selectedEmployee.position?.salary_grade || "-" }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Salary Step </span>

                <p class="mt-1">
                  {{
                    selectedEmployee.salary_step
                      ? `Step ${selectedEmployee.salary_step}`
                      : "-"
                  }}
                </p>
              </div>

              <div class="min-w-0">
                <span class="font-medium"> Current Salary </span>

                <p class="mt-1">
                  ₱{{
                    Number(selectedEmployee.salary || 0).toLocaleString(
                      "en-PH",
                      {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2,
                      },
                    )
                  }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-[#f3f7fc] border-t border-[#cbd8e8] px-6 py-4 flex flex-col-reverse sm:flex-row justify-end gap-3">
          <button @click="showViewModal = false"
            class="employee-btn employee-btn-secondary w-full sm:w-auto">
            Close
          </button>
          <button @click="editEmployee(selectedEmployee); showViewModal = false"
            class="employee-btn employee-btn-secondary w-full sm:w-auto">
            Edit Employee
          </button>
        </div>
      </div>
    </div>

    <div v-if="showEditModal" class="fixed inset-0 bg-[rgba(23,32,51,0.55)] flex items-center justify-center z-50 p-4">
      <div class="bg-[var(--surface)] rounded-xl shadow-xl w-full max-w-4xl mx-auto overflow-hidden">
        <div class="bg-amber-500 text-white px-6 py-4">
          <h3 class="text-xl font-semibold">Edit Employee</h3>

          <p class="text-amber-100 text-sm">Update employee information.</p>
        </div>

        <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
          <div>
            <h4 class="font-semibold text-[var(--text)] mb-4">
              Account Information
            </h4>

            <div class="grid grid-cols-1 gap-4">
              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Email
                </label>

                <input v-model="editForm.email" type="email" :aria-invalid="Boolean(editFormErrors.email)"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500"
                  :class="{ 'border-red-500': editFormErrors.email }" />
                <p v-if="editFormErrors.email" class="mt-1 text-sm text-red-600">{{ editFormErrors.email }}</p>
              </div>
            </div>
          </div>

          <div>
            <h4 class="font-semibold text-[var(--text)] mb-4">
              Personal Information
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  First Name
                </label>

                <input v-model="editForm.first_name" :aria-invalid="Boolean(editFormErrors.first_name)"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2"
                  :class="{ 'border-red-500': editFormErrors.first_name }" />
                <p v-if="editFormErrors.first_name" class="mt-1 text-sm text-red-600">{{ editFormErrors.first_name }}</p>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Middle Name
                </label>

                <input v-model="editForm.middle_name" class="w-full min-w-0 field-input rounded-lg px-3 py-2" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Last Name
                </label>

                <input v-model="editForm.last_name" :aria-invalid="Boolean(editFormErrors.last_name)"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2"
                  :class="{ 'border-red-500': editFormErrors.last_name }" />
                <p v-if="editFormErrors.last_name" class="mt-1 text-sm text-red-600">{{ editFormErrors.last_name }}</p>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Extension Name
                </label>

                <select v-model="editForm.extension_name" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option value="">None</option>
                  <option value="Jr.">Jr.</option>
                  <option value="Sr.">Sr.</option>
                  <option value="II">II</option>
                  <option value="III">III</option>
                  <option value="IV">IV</option>
                  <option value="V">V</option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Date of Birth
                </label>

                <input v-model="editForm.date_of_birth" type="date"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Sex
                </label>

                <select v-model="editForm.sex" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option value="">Select</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Civil Status
                </label>

                <select v-model="editForm.civil_status" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option value="">Select</option>
                  <option value="Single">Single</option>
                  <option value="Married">Married</option>
                  <option value="Widowed">Widowed</option>
                  <option value="Separated">Separated</option>
                  <option value="Divorced">Divorced</option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Nationality
                </label>

                <input v-model="editForm.nationality" type="text"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Contact Number
                </label>

                <input v-model="editForm.contact_number" type="tel" inputmode="numeric" maxlength="11" @input="
                  editForm.contact_number = editForm.contact_number
                    .replace(/\D/g, '')
                    .slice(0, 11)
                  " class="w-full min-w-0 field-input rounded-lg px-3 py-2" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Personal Email
                </label>

                <input v-model="editForm.personal_email" type="email"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2" />
              </div>

              <div class="md:col-span-2">
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Address
                </label>

                <textarea v-model="editForm.address" rows="3"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2 resize-none"></textarea>
              </div>
            </div>
          </div>

          <div>
            <h4 class="font-semibold text-[var(--text)] mb-4">
              Emergency Contact
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Emergency Contact Name
                </label>

                <input v-model="editForm.emergency_contact_name" type="text"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Emergency Contact Number
                </label>

                <input v-model="editForm.emergency_contact_number" type="tel" inputmode="numeric" maxlength="11" @input="
                  editForm.emergency_contact_number =
                  editForm.emergency_contact_number
                    .replace(/\D/g, '')
                    .slice(0, 11)
                  " class="w-full min-w-0 field-input rounded-lg px-3 py-2" />
              </div>
              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Relationship
                </label>

                <input v-model="editForm.emergency_contact_relationship" type="text"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2" />
              </div>
            </div>
          </div>

          <div>
            <h4 class="font-semibold text-[var(--text)] mb-4">
              Employment Information
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Personnel Type
                </label>

                <select v-model="editForm.personnel_type" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option value="">Select</option>
                  <option value="Teaching">Teaching</option>
                  <option value="Non-Teaching">Non-Teaching</option>
                  <option value="School Head">School Head</option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Employment Category
                </label>

                <select v-model="editForm.employment_category" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option value="">Select</option>
                  <option value="Permanent">Permanent</option>
                  <option value="Probationary">Probationary</option>
                  <option value="Contractual">Contractual</option>
                  <option value="Casual">Casual</option>
                  <option value="Temporary">Temporary</option>
                  <option value="Contract of Service">
                    Contract of Service
                  </option>
                  <option value="Job Order">Job Order</option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Date Hired
                </label>

                <input v-model="editForm.date_hired" type="date"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Level
                </label>

                <select v-model="editForm.level" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option value="">Select</option>
                  <option value="JHS">JHS</option>
                  <option value="SHS">SHS</option>
                  <option value="Non-Teaching">Non-Teaching</option>
                </select>
              </div>

              <div v-if="editForm.level">
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  {{ assignmentAreaLabel(editForm.level) }}
                </label>

                <select v-model="editForm.department_id" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option :value="null">
                    {{ assignmentAreaPlaceholder(editForm.level) }}
                  </option>

                  <option v-for="dept in filteredDepartmentsForEdit" :key="dept.department_id"
                    :value="dept.department_id">
                    {{ dept.department_name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Position
                </label>

                <select v-model="editForm.position_id" :aria-invalid="Boolean(editFormErrors.position_id)"
                  class="w-full min-w-0 field-input rounded-lg px-3 py-2"
                  :class="{ 'border-red-500': editFormErrors.position_id }">
                  <option :value="null">Select</option>

                  <option v-for="pos in positions" :key="pos.id" :value="pos.id">
                    {{ pos.name }}
                  </option>
                </select>
                <p v-if="editFormErrors.position_id" class="mt-1 text-sm text-red-600">{{ editFormErrors.position_id }}</p>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Supervisor
                </label>

                <select v-model="editForm.supervisor_id" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option :value="null">None</option>

                  <option v-for="employee in employees" :key="employee.employee_id" :value="employee.employee_id">
                    {{ employee.first_name }}
                    {{ employee.middle_name ? employee.middle_name + " " : "" }}
                    {{ employee.last_name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Salary Grade
                </label>

                <input :value="selectedSalaryGrade || '-'" type="text" readonly
                  class="w-full min-w-0 field-input field-readonly rounded-lg px-3 py-2" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Salary Step
                </label>

                <select v-model="editForm.salary_step" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option :value="null">Select Step</option>

                  <option v-for="step in 8" :key="step" :value="step">
                    Step {{ step }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Current Salary
                </label>

                <input :value="formattedSalary" type="text" readonly
                  class="w-full min-w-0 field-input field-readonly rounded-lg px-3 py-2 font-semibold" />
              </div>

              <div>
                <label class="block mb-2 text-sm text-[var(--text)] font-medium">
                  Employment Status
                </label>

                <select v-model="editForm.employment_status" class="w-full min-w-0 field-input rounded-lg px-3 py-2">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-[#f3f7fc] border-t border-[#cbd8e8] px-6 py-4 flex flex-col sm:flex-row justify-end gap-3">
          <button @click="showEditModal = false"
            class="employee-btn employee-btn-secondary w-full sm:w-auto">
            Cancel
          </button>

          <button @click="updateEmployee"
            class="employee-btn employee-btn-primary w-full sm:w-auto">
            Save Changes
          </button>
        </div>
      </div>
    </div>

    <div v-if="showDeletedModal"
      class="fixed inset-0 bg-[rgba(23,32,51,0.55)] flex items-center justify-center z-50 p-4">
      <div class="bg-[var(--surface)] rounded-xl shadow-2xl w-full max-w-6xl mx-auto overflow-hidden">
        <div class="bg-slate-600 text-white px-6 py-5 flex justify-between items-center">
          <div class="min-w-0">
            <h2 class="text-2xl font-bold">Deleted Employees</h2>

            <p class="text-slate-200 text-sm mt-1">
              View and restore previously deleted employee records.
            </p>
          </div>

          <button @click="showDeletedModal = false" class="text-white text-3xl hover:text-slate-200 ml-4 flex-shrink-0">
            &times;
          </button>
        </div>

        <div class="p-6 max-h-[70vh] overflow-y-auto">
          <div v-if="deletedEmployees.length === 0" class="text-center py-12 text-[var(--text-muted)]">
            <p class="text-lg font-semibold">No deleted employees found.</p>

            <p class="text-sm mt-1">
              Deleted employee records will appear here.
            </p>
          </div>

          <div v-else class="table-wrapper border border-[#cbd8e8] rounded-lg">
            <table class="deleted-table">
              <thead class="table-head">
                <tr class="text-left text-[var(--text)] font-semibold">
                  <th class="px-2 sm:px-4 py-3">Employee Code</th>
                  <th class="px-2 sm:px-4 py-3">Employee</th>
                  <th class="px-2 sm:px-4 py-3">Email</th>
                  <th class="px-2 sm:px-4 py-3">Position</th>
                  <th class="px-2 sm:px-4 py-3">Deleted At</th>
                  <th class="px-2 sm:px-4 py-3 text-center">Action</th>
                </tr>
              </thead>

              <tbody>
                <tr v-for="employee in deletedEmployees" :key="employee.employee_id"
                  class="border-t border-[#cbd8e8] hover:bg-[#eef4fb]">
                  <td class="px-2 sm:px-4 py-4 text-[var(--text)] font-semibold break-words">
                    {{ employee.employee_code }}
                  </td>

                  <td class="px-2 sm:px-4 py-4 text-[var(--text)] break-words">
                    {{ employee.last_name }},
                    {{ employee.first_name }}
                    {{ employee.middle_name || "" }}
                    {{ employee.extension_name || "" }}
                  </td>

                  <td class="px-2 sm:px-4 py-4 text-[var(--text)] break-all">
                    {{ employee.user?.email || "-" }}
                  </td>

                  <td class="px-2 sm:px-4 py-4 text-[var(--text)] break-words">
                    {{ employee.position?.name || "-" }}
                  </td>

                  <td class="px-2 sm:px-4 py-4 text-[var(--text)] break-words">
                    {{ formatDate(employee.deleted_at) }}
                  </td>

                  <td class="px-2 sm:px-4 py-4 text-center">
                    <div class="flex items-center justify-center gap-1 flex-nowrap">
                      <button @click="restoreEmployeeRecord(employee)"
                        class="employee-btn employee-btn-status whitespace-nowrap">
                        Restore
                      </button>

                      <button @click="permanentlyDeleteEmployeeRecord(employee)"
                        class="employee-btn employee-btn-danger whitespace-nowrap">
                        Delete Permanently
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="bg-[#f3f7fc] border-t border-[#cbd8e8] px-6 py-4 flex justify-end">
          <button @click="showDeletedModal = false"
            class="employee-btn employee-btn-secondary">
            Close
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from "vue";
import axios from "axios";
import { confirmAction, notify } from "@/composables/useNotifications";

import {
  getEmployees,
  createEmployee as createEmployeeAPI,
  updateEmployee as updateEmployeeAPI,
  deleteEmployee as deleteEmployeeAPI,
  getDeletedEmployees,
  restoreEmployee,
  permanentlyDeleteEmployee,
  getPositions,
  resetEmployeePassword as resetEmployeePasswordAPI,
  applyBulkEmployeeAction,
} from "../services/employee";

interface Employee {
  employee_id: number;
  employee_code: string;
  deleted_at?: string;

  first_name: string;
  middle_name: string;
  last_name: string;
  extension_name?: string | null;
  date_of_birth?: string | null;
  sex: string;
  civil_status?: string | null;
  nationality?: string | null;
  address?: string | null;
  contact_number?: string | null;
  personal_email?: string | null;

  emergency_contact_name?: string | null;
  emergency_contact_number?: string | null;
  emergency_contact_relationship?: string | null;

  personnel_type: string;
  employment_status: string;
  employment_category?: string | null;
  date_hired?: string | null;
  years_of_service?: number | null;

  department_id: number | null;

  department_name?: string;

  department?: {
    department_id: number;
    department_name: string;
    level: string;
  } | null;

  position_id: number | null;

  position?: {
    id: number;
    code: string;
    name: string;
    type: string;
    salary_grade: string | null;
  } | null;

  supervisor_id?: number | null;

  supervisor?: {
    employee_id: number;
    first_name: string;
    middle_name?: string | null;
    last_name: string;
  } | null;

  level: string;
  salary_step: number | null;
  salary: number;

  user?: {
    email: string;
  } | null;

  created_by?: {
    user_id: number;
    email: string;
    admin_profile: {
      first_name: string;
      middle_name?: string | null;
      last_name: string;
    };
  } | null;
}

const listStateStorageKey = "employee-management-list-state";
const savedListState = (() => {
  try {
    const value: unknown = JSON.parse(localStorage.getItem(listStateStorageKey) || "{}");
    return value && typeof value === "object"
      ? (value as Record<string, unknown>)
      : {};
  } catch (error) {
    console.warn("Unable to restore employee list state:", error);
    return {};
  }
})();
const search = ref(typeof savedListState.search === "string" ? savedListState.search : "");
const searchInput = ref(search.value);
const statusFilter = ref(
  savedListState.statusFilter === "active" || savedListState.statusFilter === "inactive"
    ? savedListState.statusFilter
    : "all",
);
const personnelTypeFilter = ref(typeof savedListState.personnelTypeFilter === "string" ? savedListState.personnelTypeFilter : "all");
const positionFilter = ref(typeof savedListState.positionFilter === "string" ? savedListState.positionFilter : "all");

// ---------- Sorting ----------
type SortKey =
  | "employee_code"
  | "name"
  | "email"
  | "department"
  | "position"
  | "status"
  | "date_hired";

type SortDir = "asc" | "desc";

// null = default order (as loaded from the server)
const allowedSortKeys: SortKey[] = [
  "employee_code", "name", "email", "department", "position", "status", "date_hired",
];
const sortKey = ref<SortKey | null>(
  typeof savedListState.sortKey === "string" && allowedSortKeys.includes(savedListState.sortKey as SortKey)
    ? savedListState.sortKey as SortKey
    : null,
);
const sortDir = ref<SortDir>(savedListState.sortDir === "desc" ? "desc" : "asc");

// Columns that can be sorted by clicking their header
const sortableColumns: { key: SortKey; label: string }[] = [
  { key: "employee_code", label: "Employee Code" },
  { key: "name", label: "Employee" },
  { key: "email", label: "Email" },
  { key: "position", label: "Position" },
  { key: "status", label: "Status" },
];

// Choices shown in the "Sort by" dropdown
const sortOptions = [
  { value: "default", label: "Default order" },
  { value: "name:asc", label: "Name (A–Z)" },
  { value: "name:desc", label: "Name (Z–A)" },
  { value: "email:asc", label: "Email (A–Z)" },
  { value: "email:desc", label: "Email (Z–A)" },
  { value: "department:asc", label: "Assignment Area (A–Z)" },
  { value: "department:desc", label: "Assignment Area (Z–A)" },
  { value: "position:asc", label: "Position (A–Z)" },
  { value: "position:desc", label: "Position (Z–A)" },
  { value: "date_hired:desc", label: "Date Hired (Newest first)" },
  { value: "date_hired:asc", label: "Date Hired (Oldest first)" },
];

// The dropdown and the column headers share the same sortKey/sortDir,
// so changing one automatically updates the other.
const sortOption = computed({
  get: () => (sortKey.value ? `${sortKey.value}:${sortDir.value}` : "default"),
  set: (value: string) => {
    if (value === "default") {
      sortKey.value = null;
      sortDir.value = "asc";
      return;
    }

    const [key, dir] = value.split(":");
    sortKey.value = key as SortKey;
    sortDir.value = dir as SortDir;
  },
});

const toggleColumnSort = (key: SortKey) => {
  if (sortKey.value !== key) {
    sortKey.value = key;
    sortDir.value = "asc";
    return;
  }

  if (sortDir.value === "asc") {
    sortDir.value = "desc";
    return;
  }

  sortKey.value = null;
  sortDir.value = "asc";
};

const sortIndicator = (key: SortKey) => {
  if (sortKey.value !== key) return "↕";
  return sortDir.value === "asc" ? "▲" : "▼";
};

const ariaSort = (key: SortKey) => {
  if (sortKey.value !== key) return "none";
  return sortDir.value === "asc" ? "ascending" : "descending";
};

// The value each sort key compares. null = empty (always listed last).
const getSortValue = (
  employee: Employee,
  key: SortKey,
): string | number | null => {
  switch (key) {
    case "employee_code":
      return employee.employee_code || null;

    case "name": {
      const fullName = [
        employee.last_name,
        employee.first_name,
        employee.middle_name,
        employee.extension_name,
      ]
        .filter(Boolean)
        .join(" ")
        .trim();

      return fullName || null;
    }

    case "email":
      return employee.user?.email || null;

    case "department":
      return (
        employee.department?.department_name || employee.department_name || null
      );

    case "position":
      return employee.position?.name || null;

    case "status":
      return employee.employment_status || null;

    case "date_hired": {
      if (!employee.date_hired) return null;

      const time = new Date(employee.date_hired).getTime();

      return Number.isNaN(time) ? null : time;
    }

    default:
      return null;
  }
};

const employees = ref<Employee[]>([]);
const selectedEmployeeIds = ref<number[]>([]);
const activeEmployeeCount = computed(() =>
  employees.value.filter((employee) => normalizeEmploymentStatus(employee.employment_status) === "active").length,
);
const inactiveEmployeeCount = computed(() =>
  employees.value.filter((employee) => normalizeEmploymentStatus(employee.employment_status) === "inactive").length,
);
const personnelTypeOptions = computed(() =>
  [...new Set(employees.value.map((employee) => employee.personnel_type).filter(Boolean))].sort(),
);
const positionOptions = computed(() =>
  [...new Set(employees.value.map((employee) => employee.position?.name).filter((value): value is string => Boolean(value)))].sort(),
);
const createFormErrors = ref<Record<string, string>>({});
const editFormErrors = ref<Record<string, string>>({});
let searchTimer: ReturnType<typeof setTimeout> | undefined;
const resettingEmployeeId = ref<number | null>(null);

watch(searchInput, (value) => {
  if (searchTimer) clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    search.value = value;
  }, 300);
});

onBeforeUnmount(() => {
  if (searchTimer) clearTimeout(searchTimer);
});

const deletedEmployees = ref<Employee[]>([]);

const showDeletedModal = ref(false);

const showCreateModal = ref(false);

const showViewModal = ref(false);

const showEditModal = ref(false);

const showCredentialsModal = ref(false);

const selectedEmployee = ref<Employee | null>(null);

const getCreatorName = (employee: Employee) => {
  if (!employee.created_by) return "-";

  const { first_name, middle_name, last_name } =
    employee.created_by.admin_profile;

  return `${first_name} ${middle_name ? middle_name + " " : ""}${last_name}`;
};

const positions = ref<
  {
    id: number;
    name: string;
    type: string;
    salary_grade: string | null;
  }[]
>([]);

const departments = ref<
  {
    department_id: number;
    department_name: string;
    level: string;
  }[]
>([]);

const assignmentAreaLabel = (level?: string) => {
  if (level === "JHS") return "Subject Area / Specialization";
  if (level === "SHS") return "Track / Strand";
  return "Office / Department";
};

const assignmentAreaPlaceholder = (level?: string) => {
  if (level === "JHS") return "Select Subject Area / Specialization";
  if (level === "SHS") return "Select Track / Strand";
  return "Select Office / Department";
};

const filteredDepartmentsForCreate = computed(() => {
  return (departments.value ?? []).filter(
    (department) => department.level === form.value.level,
  );
});

const filteredDepartmentsForEdit = computed(() => {
  return (departments.value ?? []).filter(
    (department) => department.level === editForm.value.level,
  );
});

const loadPositions = async () => {
  try {
    positions.value = await getPositions();
  } catch (error) {
    console.error("Failed to load positions:", error);
  }
};

const loadDepartments = async () => {
  try {
    const response = await axios.get(
      "https://enhs-leave-management-system.onrender.com/api/departments",
      {
        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      },
    );

    departments.value = Array.isArray(response.data)
      ? response.data
      : (response.data?.data ?? []);

    console.log("Departments:", departments.value);
  } catch (error) {
    console.error("Failed to load departments:", error);
  }
};

const form = ref({
  email: "",

  first_name: "",
  middle_name: "",
  last_name: "",
  extension_name: "",
  date_of_birth: "",
  sex: "",
  civil_status: "",
  nationality: "",
  address: "",
  contact_number: "",
  personal_email: "",

  emergency_contact_name: "",
  emergency_contact_number: "",
  emergency_contact_relationship: "",

  personnel_type: "",
  employment_status: "active",
  employment_category: "",
  date_hired: "",

  department_id: null as number | null,
  level: "",
  position_id: null as number | null,
  supervisor_id: null as number | null,

  salary_step: null as number | null,
  salary: "",
});

const watchPositionForLevel = (
  newId: number | null,
  formRef: { value: { level: string } },
) => {
  const selected = positions.value.find((position) => position.id === newId);

  if (selected?.type === "Non-Teaching") {
    formRef.value.level = "Non-Teaching";
  }
};

const selectedCreateSalaryGrade = computed(() => {
  const selected = positions.value.find(
    (position) => position.id === form.value.position_id,
  );

  return selected?.salary_grade || null;
});

const calculateSalary = async () => {
  if (!form.value.position_id || !form.value.salary_step) {
    form.value.salary = "";
    return;
  }

  try {
    const response = await axios.get(
      "https://enhs-leave-management-system.onrender.com/api/salary-info",
      {
        params: {
          position_id: form.value.position_id,
          salary_step: form.value.salary_step,
        },

        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      },
    );

    form.value.salary = response.data.salary ?? "";
  } catch (error) {
    console.error("Failed to calculate salary:", error);

    form.value.salary = "";
  }
};

watch([() => form.value.position_id, () => form.value.salary_step], () => {
  calculateSalary();
});

const formattedCreateSalary = computed(() => {
  if (
    form.value.salary === "" ||
    form.value.salary === null ||
    form.value.salary === undefined
  ) {
    return "-";
  }

  return `₱${Number(form.value.salary).toLocaleString("en-PH", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
});

watch(
  () => form.value.position_id,
  (newId) => {
    watchPositionForLevel(newId, form);
  },
);

watch(
  () => form.value.level,
  (level) => {
    const selectedDepartment = departments.value.find(
      (department) => department.department_id === form.value.department_id,
    );

    if (!selectedDepartment || selectedDepartment.level !== level) {
      form.value.department_id = null;
    }
  },
);

const generatedCredentials = ref({
  title: "Employee Created Successfully",
  message: "Give these login credentials to the employee.",
  email: "",
  password: "",
});

const editForm = ref({
  employee_id: 0,

  email: "",

  first_name: "",
  middle_name: "",
  last_name: "",
  extension_name: "",
  date_of_birth: "",
  sex: "",
  civil_status: "",
  nationality: "",
  address: "",
  contact_number: "",
  personal_email: "",

  emergency_contact_name: "",
  emergency_contact_number: "",
  emergency_contact_relationship: "",

  personnel_type: "",
  employment_status: "active",
  employment_category: "",
  date_hired: "",

  department_id: null as number | null,
  level: "",
  position_id: null as number | null,
  supervisor_id: null as number | null,

  salary_step: null as number | null,
  salary: "",
});

const selectedSalaryGrade = computed(() => {
  const selected = positions.value.find(
    (position) => position.id === editForm.value.position_id,
  );

  return selected?.salary_grade || null;
});

const formattedSalary = computed(() => {
  if (
    editForm.value.salary === "" ||
    editForm.value.salary === null ||
    editForm.value.salary === undefined
  ) {
    return "-";
  }

  return `₱${Number(editForm.value.salary).toLocaleString("en-PH", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
});

const calculateEditSalary = async () => {
  if (!editForm.value.position_id || !editForm.value.salary_step) {
    editForm.value.salary = "";
    return;
  }

  try {
    const response = await axios.get(
      "https://enhs-leave-management-system.onrender.com/api/salary-info",
      {
        params: {
          position_id: editForm.value.position_id,
          salary_step: editForm.value.salary_step,
        },

        headers: {
          Authorization: `Bearer ${localStorage.getItem("token")}`,
        },
      },
    );

    editForm.value.salary = response.data.salary ?? "";
  } catch (error) {
    console.error("Failed to calculate edit salary:", error);

    editForm.value.salary = "";
  }
};

watch(
  [() => editForm.value.position_id, () => editForm.value.salary_step],
  () => {
    calculateEditSalary();
  },
);

watch(
  () => editForm.value.position_id,
  (newId) => {
    watchPositionForLevel(newId, editForm);
  },
);

watch(
  () => editForm.value.level,
  (level) => {
    const selectedDepartment = departments.value.find(
      (department) => department.department_id === editForm.value.department_id,
    );

    if (!selectedDepartment || selectedDepartment.level !== level) {
      editForm.value.department_id = null;
    }
  },
);

const formatDate = (date?: string | null) => {
  if (!date) {
    return "-";
  }

  const parsedDate = new Date(date);

  if (Number.isNaN(parsedDate.getTime())) {
    return "-";
  }

  return parsedDate.toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

const normalizeEmploymentStatus = (status: string | undefined) => {
  return String(status || "")
    .trim()
    .toLowerCase();
};

const viewEmployee = (employee: Employee) => {
  selectedEmployee.value = employee;

  showViewModal.value = true;
};

const closeEmployeeActionMenu = (event: Event) => {
  const currentTarget = event.currentTarget;
  const details = currentTarget instanceof HTMLDetailsElement
    ? currentTarget
    : (currentTarget as HTMLElement | null)?.closest("details");
  details?.removeAttribute("open");
};

const filteredEmployees = computed(() => {
  const keyword = search.value.toLowerCase().trim();

  const result = employees.value.filter((employee) => {
    const firstName = employee.first_name?.toLowerCase() || "";
    const lastName = employee.last_name?.toLowerCase() || "";
    const middleName = employee.middle_name?.toLowerCase() || "";
    const extensionName = employee.extension_name?.toLowerCase() || "";
    const employeeCode = employee.employee_code?.toLowerCase() || "";
    const email = employee.user?.email?.toLowerCase() || "";

    const department = (
      employee.department?.department_name ||
      employee.department_name ||
      ""
    ).toLowerCase();

    const position = employee.position?.name?.toLowerCase() || "";
    const level = employee.level?.toLowerCase() || "";
    const personnelType = employee.personnel_type?.toLowerCase() || "";
    const employmentStatus = employee.employment_status?.toLowerCase() || "";
    const employmentCategory = employee.employment_category?.toLowerCase() || "";

    const matchesSearch =
      !keyword ||
      firstName.includes(keyword) ||
      middleName.includes(keyword) ||
      lastName.includes(keyword) ||
      extensionName.includes(keyword) ||
      employeeCode.includes(keyword) ||
      email.includes(keyword) ||
      department.includes(keyword) ||
      position.includes(keyword) ||
      level.includes(keyword) ||
      personnelType.includes(keyword) ||
      employmentCategory.includes(keyword);

    const matchesStatus =
      statusFilter.value === "all" || employmentStatus === statusFilter.value;
    const matchesPersonnelType =
      personnelTypeFilter.value === "all" || personnelType === personnelTypeFilter.value.toLowerCase();
    const matchesPosition =
      positionFilter.value === "all" || position === positionFilter.value.toLowerCase();

    return matchesSearch && matchesStatus && matchesPersonnelType && matchesPosition;
  });

  // No sort chosen: keep the original (load/creation) order.
  const key = sortKey.value;

  if (!key) {
    return result;
  }

  const direction = sortDir.value === "asc" ? 1 : -1;

  return [...result].sort((a, b) => {
    const aValue = getSortValue(a, key);
    const bValue = getSortValue(b, key);

    // Empty values always go to the bottom, whichever direction is used
    if (aValue === null && bValue === null) return 0;
    if (aValue === null) return 1;
    if (bValue === null) return -1;

    if (typeof aValue === "number" && typeof bValue === "number") {
      return (aValue - bValue) * direction;
    }

    // Case-insensitive, and numbers sort naturally (EMP-2 before EMP-10)
    return (
      String(aValue).localeCompare(String(bValue), undefined, {
        sensitivity: "base",
        numeric: true,
      }) * direction
    );
  });
});

// ---------- Pagination ----------
const pageSizeOptions = [10, 25, 50, 100];
const savedPageSize = Number(savedListState.pageSize);
const pageSize = ref(pageSizeOptions.includes(savedPageSize) ? savedPageSize : 10);
const savedPage = Number(savedListState.currentPage);
const currentPage = ref(Number.isInteger(savedPage) && savedPage > 0 ? savedPage : 1);
const pageEmployees = computed(() => paginatedEmployees.value);
const allPageEmployeesSelected = computed(() =>
  pageEmployees.value.length > 0 &&
  pageEmployees.value.every((employee) => selectedEmployeeIds.value.includes(employee.employee_id)),
);
const somePageEmployeesSelected = computed(() =>
  pageEmployees.value.some((employee) => selectedEmployeeIds.value.includes(employee.employee_id)) &&
  !allPageEmployeesSelected.value,
);

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredEmployees.value.length / pageSize.value)),
);

// Only the rows for the current page (what the table actually loops over)
const paginatedEmployees = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  return filteredEmployees.value.slice(start, start + pageSize.value);
});

const showingFrom = computed(() =>
  filteredEmployees.value.length === 0
    ? 0
    : (currentPage.value - 1) * pageSize.value + 1,
);

const showingTo = computed(() =>
  Math.min(currentPage.value * pageSize.value, filteredEmployees.value.length),
);

const visiblePages = computed<(number | string)[]>(() => {
  const total = totalPages.value;
  const current = currentPage.value;

  if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
  if (current <= 4) return [1, 2, 3, 4, 5, "...", total];
  if (current >= total - 3)
    return [1, "...", total - 4, total - 3, total - 2, total - 1, total];
  return [1, "...", current - 1, current, current + 1, "...", total];
});

const goToPage = (page: number | string) => {
  if (typeof page !== "number") return;
  currentPage.value = Math.min(Math.max(1, page), totalPages.value);
};

const filterSources = [
  search,
  statusFilter,
  personnelTypeFilter,
  positionFilter,
  sortKey,
  sortDir,
  pageSize,
];

// Go back to page 1 and clear selection whenever the visible list changes.
watch(filterSources, () => {
  currentPage.value = 1;
  selectedEmployeeIds.value = [];
});

// If the last row on the last page is removed, don't stay on an empty page
watch(totalPages, (total) => {
  if (currentPage.value > total) currentPage.value = total;
});

watch(
  [...filterSources, currentPage],
  () => {
    try {
      localStorage.setItem(listStateStorageKey, JSON.stringify({
        search: search.value,
        statusFilter: statusFilter.value,
        personnelTypeFilter: personnelTypeFilter.value,
        positionFilter: positionFilter.value,
        sortKey: sortKey.value,
        sortDir: sortDir.value,
        pageSize: pageSize.value,
        currentPage: currentPage.value,
      }));
    } catch (error) {
      console.warn("Unable to save employee list state:", error);
    }
  },
);

const togglePageSelection = (event: Event) => {
  const checked = (event.target as HTMLInputElement).checked;
  const pageIds = pageEmployees.value.map((employee) => employee.employee_id);
  if (checked) {
    selectedEmployeeIds.value = [...new Set([...selectedEmployeeIds.value, ...pageIds])];
  } else {
    selectedEmployeeIds.value = selectedEmployeeIds.value.filter((id) => !pageIds.includes(id));
  }
};

const clearFilters = () => {
  searchInput.value = "";
  search.value = "";
  statusFilter.value = "all";
  personnelTypeFilter.value = "all";
  positionFilter.value = "all";
};

const csvCell = (value: unknown) => {
  let text = String(value ?? "");
  if (/^\s*[=+\-@]/.test(text)) text = `'${text}`;
  return `"${text.replace(/"/g, '""')}"`;
};

const exportFilteredEmployees = () => {
  const headings = [
    "#", "Employee Code", "Employee Name", "Email", "Level", "Personnel Type",
    "Employment Category", "Assignment Area", "Position", "Status", "Date Hired",
  ];
  const rows = filteredEmployees.value.map((employee, index) => [
    index + 1,
    employee.employee_code,
    [employee.last_name, employee.first_name, employee.middle_name, employee.extension_name].filter(Boolean).join(", "),
    employee.user?.email,
    employee.level,
    employee.personnel_type,
    employee.employment_category,
    employee.department?.department_name || employee.department_name,
    employee.position?.name,
    employee.employment_status,
    employee.date_hired,
  ]);
  const csv = "\uFEFF" + [headings, ...rows].map((row) => row.map(csvCell).join(",")).join("\r\n");
  const url = URL.createObjectURL(new Blob([csv], { type: "text/csv;charset=utf-8;" }));
  const link = document.createElement("a");
  link.href = url;
  link.download = "employees-filtered.csv";
  document.body.appendChild(link);
  link.click();
  link.remove();
  setTimeout(() => URL.revokeObjectURL(url), 1000);
  notify(`Exported ${rows.length} employee(s) to CSV.`);
};

const validateEmployeeForm = (
  data: { email: string; first_name: string; last_name: string; position_id: number | null },
  errors: Record<string, string>,
  excludingEmployeeId?: number,
) => {
  const nextErrors: Record<string, string> = {};
  const email = data.email.trim();
  if (!email) {
    nextErrors.email = "Email is required.";
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    nextErrors.email = "Enter a valid email address.";
  } else if (employees.value.some((employee) =>
    employee.employee_id !== excludingEmployeeId &&
    employee.user?.email?.trim().toLowerCase() === email.toLowerCase()
  )) {
    nextErrors.email = "This email is already assigned to another employee.";
  }
  if (!data.first_name.trim()) nextErrors.first_name = "First name is required.";
  if (!data.last_name.trim()) nextErrors.last_name = "Last name is required.";
  if (!data.position_id) nextErrors.position_id = "Position is required.";
  Object.assign(errors, nextErrors);
  for (const field of ["email", "first_name", "last_name", "position_id"]) {
    if (!(field in nextErrors)) delete errors[field];
  }
  return Object.keys(nextErrors).length === 0;
};

const setServerFieldErrors = (error: unknown, target: Record<string, string>) => {
  if (!axios.isAxiosError(error)) return false;
  const responseData = error.response?.data as {
    errors?: Record<string, unknown>;
  } | undefined;
  const serverErrors = responseData?.errors;
  if (!serverErrors) return false;
  for (const field of ["email", "first_name", "last_name", "position_id"]) {
    const messages = serverErrors[field];
    if (Array.isArray(messages) && typeof messages[0] === "string") {
      target[field] = messages[0];
    }
  }
  return Object.keys(target).length > 0;
};

const getErrorMessage = (error: unknown, fallback: string) => {
  if (!axios.isAxiosError(error)) return fallback;
  const responseData = error.response?.data as { message?: unknown } | undefined;
  return typeof responseData?.message === "string" ? responseData.message : fallback;
};

const editEmployee = (employee: Employee) => {
  editFormErrors.value = {};
  editForm.value = {
    employee_id: Number(employee.employee_id),

    email: employee.user?.email || "",

    first_name: employee.first_name || "",

    middle_name: employee.middle_name || "",

    last_name: employee.last_name || "",

    extension_name: employee.extension_name || "",

    date_of_birth: employee.date_of_birth
      ? employee.date_of_birth.substring(0, 10)
      : "",

    sex: employee.sex || "",

    civil_status: employee.civil_status || "",

    nationality: employee.nationality || "",

    address: employee.address || "",

    contact_number: employee.contact_number || "",

    personal_email: employee.personal_email || "",

    emergency_contact_name: employee.emergency_contact_name || "",

    emergency_contact_number: employee.emergency_contact_number || "",

    emergency_contact_relationship: employee.emergency_contact_relationship || "",

    personnel_type: employee.personnel_type || "",

    employment_status: employee.employment_status || "active",

    employment_category: employee.employment_category || "",

    date_hired: employee.date_hired ? employee.date_hired.substring(0, 10) : "",

    department_id: employee.department_id || null,

    level: employee.level || "",

    position_id: employee.position_id || null,

    supervisor_id: employee.supervisor_id || null,

    salary_step: employee.salary_step || null,

    salary: employee.salary != null ? String(employee.salary) : "",
  };

  showEditModal.value = true;
};

const updateEmployee = async () => {
  if (!editForm.value.employee_id) {
    notify("Invalid employee.", "error");
    return;
  }
  if (!validateEmployeeForm(editForm.value, editFormErrors.value, editForm.value.employee_id)) {
    return;
  }

  try {
    await updateEmployeeAPI(editForm.value.employee_id, editForm.value);

    notify("Employee updated successfully.");

    showEditModal.value = false;

    await loadEmployees();
  } catch (error: unknown) {
    console.error("Failed to update employee:", error);
    if (!setServerFieldErrors(error, editFormErrors.value)) {
      notify(getErrorMessage(error, "Unable to update employee."), "error");
    }
  }
};

const deleteEmployee = async (employee: Employee) => {
  const employeeName = `${employee.first_name} ${employee.last_name}`.trim();
  await confirmAction({
    title: "Delete employee?",
    message: `${employeeName} will be moved to Deleted Employees and can be restored later.`,
    confirmLabel: "Delete employee",
    busyLabel: "Deleting...",
    onConfirm: async () => {
      try {
        await deleteEmployeeAPI(employee.employee_id);
        selectedEmployeeIds.value = [];
        notify(`${employeeName} was moved to Deleted Employees.`);
        await loadEmployees();
      } catch (error: unknown) {
        console.error("Failed to delete employee:", error);
        notify(getErrorMessage(error, "Unable to delete employee."), "error");
      }
    },
  });
};

const confirmBulkAction = async (action: "activate" | "deactivate" | "delete") => {
  const ids = [...selectedEmployeeIds.value];
  if (!ids.length) return;
  const actionLabel = action === "delete" ? "Delete" : action === "activate" ? "Activate" : "Deactivate";
  const busyLabel =
    action === "delete"
      ? "Deleting..."
      : action === "activate"
        ? "Activating..."
        : "Deactivating...";
  const message = action === "delete"
    ? `${ids.length} selected employee(s) will be moved to Deleted Employees and can be restored later.`
    : `${ids.length} selected employee(s) will be ${action === "activate" ? "marked active" : "marked inactive"}.`;
  await confirmAction({
    title: `${actionLabel} selected employees?`,
    message,
    confirmLabel: actionLabel,
    busyLabel,
    variant: action === "delete" ? "danger" : "primary",
    onConfirm: async () => {
      try {
        const result = await applyBulkEmployeeAction(ids, action);
        selectedEmployeeIds.value = [];
        notify(result?.message || `${actionLabel} action completed.`);
        await loadEmployees();
      } catch (error: unknown) {
        console.error("Failed to apply bulk employee action:", error);
        notify(getErrorMessage(error, "Unable to apply bulk action."), "error");
      }
    },
  });
};

const resetEmployeePassword = async (employee: Employee) => {
  if (resettingEmployeeId.value !== null) return;
  resettingEmployeeId.value = employee.employee_id;
  try {
    const result = await resetEmployeePasswordAPI(employee.employee_id);
    generatedCredentials.value = {
      title: "Password Reset Successfully",
      message: "Give the new temporary password to the employee. Their existing sessions have been signed out.",
      email: result.email,
      password: result.password,
    };
    showCredentialsModal.value = true;
  } catch (error: unknown) {
    console.error("Failed to reset employee password:", error);
    notify(getErrorMessage(error, "Unable to reset employee password."), "error");
  } finally {
    resettingEmployeeId.value = null;
  }
};

const loadEmployees = async () => {
  try {
    const result = await getEmployees();

    employees.value = Array.isArray(result) ? result : [];
    const existingIds = new Set(employees.value.map((employee) => employee.employee_id));
    selectedEmployeeIds.value = selectedEmployeeIds.value.filter((id) => existingIds.has(id));
  } catch (error) {
    console.error("Failed to load employees:", error);
    notify(getErrorMessage(error, "Unable to load employees."), "error");
  }
};

const loadDeletedEmployees = async () => {
  try {
    const result = await getDeletedEmployees();

    deletedEmployees.value = Array.isArray(result) ? result : [];

    console.log("Deleted Employees:", deletedEmployees.value);
  } catch (error) {
    console.error("Failed to load deleted employees:", error);
    notify(getErrorMessage(error, "Unable to load deleted employees."), "error");
  }
};

const openDeletedEmployees = async () => {
  showDeletedModal.value = true;

  await loadDeletedEmployees();
};

const restoreEmployeeRecord = async (employee: Employee) => {
  const employeeName = `${employee.first_name} ${employee.last_name}`.trim();
  await confirmAction({
    title: "Restore employee?",
    message: `Restore ${employeeName} to the active employee list?`,
    confirmLabel: "Restore employee",
    variant: "primary",
    busyLabel: "Restoring...",
    onConfirm: async () => {
      try {
        await restoreEmployee(employee.employee_id);
        notify(`${employeeName} was restored.`);
        await Promise.all([loadEmployees(), loadDeletedEmployees()]);
      } catch (error: unknown) {
        console.error("Failed to restore employee:", error);
        notify(getErrorMessage(error, "Unable to restore employee."), "error");
      }
    },
  });
};

const permanentlyDeleteEmployeeRecord = async (employee: Employee) => {
  await confirmAction({
    title: "Permanently delete employee?",
    message: `Permanently delete ${employee.first_name} ${employee.last_name}? This cannot be undone. Their leave applications, credits, balances, attendance, and personnel records will be removed, and login access will be revoked. Historical audit and school-year records remain.`,
    confirmLabel: "Permanently delete",
    busyLabel: "Deleting permanently...",
    onConfirm: async () => {
      try {
        const result = await permanentlyDeleteEmployee(employee.employee_id);
        notify(result?.message || "Employee permanently deleted.");
        await Promise.all([loadEmployees(), loadDeletedEmployees()]);
      } catch (error: unknown) {
        console.error("Failed to permanently delete employee:", error);
        notify(getErrorMessage(error, "Unable to permanently delete employee. Please try again."), "error");
      }
    },
  });
};

const resetCreateForm = () => {
  form.value = {
    email: "",

    first_name: "",
    middle_name: "",
    last_name: "",
    extension_name: "",
    date_of_birth: "",
    sex: "",
    civil_status: "",
    nationality: "",
    address: "",
    contact_number: "",
    personal_email: "",

    emergency_contact_name: "",
    emergency_contact_number: "",
    emergency_contact_relationship: "",

    personnel_type: "",
    employment_status: "active",
    employment_category: "",
    date_hired: "",

    department_id: null,
    level: "",
    position_id: null,
    supervisor_id: null,

    salary_step: null,
    salary: "",
  };
};

const openCreateModal = () => {
  createFormErrors.value = {};
  showCreateModal.value = true;
};

const saveEmployee = async () => {
  if (!validateEmployeeForm(form.value, createFormErrors.value)) return;
  try {
    const response = await createEmployeeAPI(form.value);

    generatedCredentials.value = {
      title: "Employee Created Successfully",
      message: "Give these login credentials to the employee.",
      email: response?.email || form.value.email,
      password: response?.password || "",
    };

    showCreateModal.value = false;

    showCredentialsModal.value = true;

    resetCreateForm();
    createFormErrors.value = {};

    await loadEmployees();
  } catch (error: unknown) {
    console.error("Failed to create employee:", error);
    if (!setServerFieldErrors(error, createFormErrors.value)) {
      notify(getErrorMessage(error, "Unable to create employee."), "error");
    }
  }
};

onMounted(async () => {
  await Promise.all([loadEmployees(), loadPositions(), loadDepartments()]);
});
</script>

<style scoped>
.dashboard-shell {
  background: var(--app-bg);
  min-height: 100vh;
  width: 100%;
  max-width: none;
  box-sizing: border-box;
}

.neo-card {
  background: var(--surface);
  border: 1px solid #cbd8e8;
  border-radius: 1rem;
  box-shadow: 0 4px 14px rgba(23, 32, 51, 0.045);

  width: 100%;
  max-width: none;
  min-width: 0;
  box-sizing: border-box;
}

.stats-card {
  border-left: 4px solid currentColor;
  padding: 1.35rem;
}

.stats-card .p-3 {
  border-radius: 0.9rem;
}

.neo-card h3,
.neo-card p,
.neo-card span,
.neo-card button {
  letter-spacing: -0.01em;
}

.field-input {
  background: var(--surface-muted);
  color: var(--text);
  border: 1px solid #c8d8eb;
}

.field-input::placeholder {
  color: #94a3b8;
}

.field-input:focus {
  outline: none;
  border-color: #7aa7e8;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.field-readonly {
  background: #f3f7fc;
  color: var(--text-muted);
}

.table-head {
  background: var(--surface-muted);
}

.employee-management-page {
  --employee-control-height: 2.5rem;
  --employee-control-radius: 0.5rem;
}

.employee-management-page .employee-btn {
  display: inline-flex;
  min-height: var(--employee-control-height);
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
  border: 1px solid transparent;
  border-radius: var(--employee-control-radius);
  padding: 0.5rem 0.8rem;
  font-size: 0.875rem;
  font-weight: 600;
  line-height: 1.25rem;
  transition: background-color 0.15s ease, border-color 0.15s ease, color 0.15s ease;
}

.employee-management-page .neo-card {
  padding: 1.25rem;
}

.employee-management-page .employee-btn:focus-visible,
.employee-management-page .employee-actions-trigger:focus-visible,
.employee-management-page .employee-sort-button:focus-visible {
  outline: 3px solid rgba(37, 99, 235, 0.35);
  outline-offset: 2px;
}

.employee-management-page .employee-btn-primary {
  color: #fff;
  background: #2563eb;
}

.employee-management-page .employee-btn-primary:hover {
  background: #1d4ed8;
}

.employee-management-page .employee-btn-secondary {
  color: var(--text);
  border-color: #cbd8e8;
  background: var(--surface);
}

.employee-management-page .employee-btn-secondary:hover {
  background: #eef4fb;
}

.employee-management-page .employee-btn-status {
  color: #166534;
  border-color: #bbf7d0;
  background: #f0fdf4;
}

.employee-management-page .employee-btn-status:hover:not(:disabled) {
  background: #dcfce7;
}

.employee-management-page .employee-btn-danger {
  color: #b91c1c;
  border-color: #fecaca;
  background: #fff;
}

.employee-management-page .employee-btn-danger:hover:not(:disabled) {
  color: #fff;
  background: #b91c1c;
}

.employee-management-page .employee-btn:disabled {
  cursor: not-allowed;
  opacity: 0.48;
  filter: grayscale(0.35);
}

.table-wrapper {
  display: block;
  width: 100%;
  max-width: 100%;
  overflow-x: auto;
  overflow-y: hidden;
  -webkit-overflow-scrolling: touch;
  overscroll-behavior-x: contain;
}

.dashboard-shell .employee-table {
  width: 100%;
  min-width: 1040px;
  max-width: none;
  table-layout: fixed;
  border-collapse: collapse;
}

.employee-table th {
  height: 3.25rem;
  border-bottom: 1px solid #cbd8e8;
  color: var(--text-muted);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.035em;
  text-align: left;
  text-transform: uppercase;
  vertical-align: middle;
}

.employee-table th:first-child,
.employee-table td:first-child,
.employee-table th:nth-child(2),
.employee-table td:nth-child(2),
.employee-table th:nth-child(7),
.employee-table td:nth-child(7),
.employee-table th:last-child {
  text-align: center;
}

.employee-table th,
.employee-table td {
  white-space: normal;
  word-break: break-word;
  overflow-wrap: anywhere;
  vertical-align: middle;
}

.employee-management-page .employee-sort-button {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0;
  color: inherit;
  font-size: inherit;
  font-weight: inherit;
  letter-spacing: inherit;
  text-align: inherit;
  text-transform: inherit;
  background: transparent;
  cursor: pointer;
}

.employee-sort-indicator {
  display: inline-block;
  min-width: 0.75rem;
  flex: 0 0 auto;
  text-align: center;
}

.employee-checkbox {
  width: 1rem;
  height: 1rem;
  margin: 0;
  accent-color: #2563eb;
  vertical-align: middle;
  cursor: pointer;
}

.employee-table th:nth-child(1),
.employee-table td:nth-child(1) {
  width: 4.5%;
}

.employee-table th:nth-child(2),
.employee-table td:nth-child(2) {
  width: 4.5%;
}

.employee-table th:nth-child(3),
.employee-table td:nth-child(3) {
  width: 11%;
}

.employee-table th:nth-child(4),
.employee-table td:nth-child(4) {
  width: 19%;
}

.employee-table th:nth-child(5),
.employee-table td:nth-child(5) {
  width: 23%;
}

.employee-table th:nth-child(6),
.employee-table td:nth-child(6) {
  width: 15%;
}

.employee-table th:nth-child(7),
.employee-table td:nth-child(7) {
  width: 9%;
}

.employee-table th:nth-child(8),
.employee-table td:nth-child(8) {
  width: 14%;
}

.employee-number-cell {
  color: var(--text-muted);
  font-variant-numeric: tabular-nums;
  text-align: center;
}

.employee-code-cell {
  white-space: nowrap !important;
  font-variant-numeric: tabular-nums;
}

.employee-name-cell {
  font-weight: 600;
}

.employee-email-cell {
  overflow: hidden;
  white-space: nowrap !important;
}

.employee-email-value,
.employee-position-value {
  display: block;
  overflow: hidden;
  max-width: 100%;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.employee-status-cell {
  white-space: nowrap !important;
}

.employee-row-actions {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
}

.employee-management-page .employee-view-button {
  min-width: 4rem;
}

.employee-actions-menu {
  display: grid;
  justify-items: center;
}

.employee-actions-trigger {
  display: flex;
  width: 2.25rem;
  height: 2.25rem;
  align-items: center;
  justify-content: center;
  border: 1px solid #cbd8e8;
  border-radius: var(--employee-control-radius);
  color: var(--text);
  background: var(--surface);
  list-style: none;
  cursor: pointer;
}

.employee-actions-trigger::-webkit-details-marker {
  display: none;
}

.employee-actions-trigger:hover {
  background: #eef4fb;
}

.employee-actions-popover {
  display: grid;
  width: 100%;
  max-width: 11rem;
  margin-top: 0.35rem;
  padding: 0.3rem;
  border: 1px solid #cbd8e8;
  border-radius: 0.65rem;
  background: var(--surface);
  box-shadow: 0 4px 12px rgba(23, 32, 51, 0.08);
}

.employee-actions-item {
  width: 100%;
  min-height: 2.25rem;
  padding: 0.5rem 0.55rem;
  border: 0;
  border-radius: 0.4rem;
  color: var(--text);
  font-size: 0.875rem;
  font-weight: 500;
  text-align: left;
  background: transparent;
  cursor: pointer;
}

.employee-actions-item:hover:not(:disabled) {
  background: #eef4fb;
}

.employee-actions-item:disabled {
  color: var(--text-muted);
  cursor: not-allowed;
  opacity: 0.6;
}

.employee-actions-item-danger {
  color: #b91c1c;
}

.employee-actions-item-danger:hover {
  background: #fef2f2;
}

.deleted-table {
  width: 100%;
  max-width: 100%;
  table-layout: auto;
  border-collapse: collapse;
}

.deleted-table th,
.deleted-table td {
  white-space: normal;
  word-break: normal;
  overflow-wrap: break-word;
}

.deleted-table th:nth-child(1),
.deleted-table td:nth-child(1) {
  width: 14%;
}

.deleted-table th:nth-child(2),
.deleted-table td:nth-child(2) {
  width: 20%;
}

.deleted-table th:nth-child(3),
.deleted-table td:nth-child(3) {
  width: 23%;
}

.deleted-table th:nth-child(4),
.deleted-table td:nth-child(4) {
  width: 15%;
}

.deleted-table th:nth-child(5),
.deleted-table td:nth-child(5) {
  width: 14%;
}

.deleted-table th:nth-child(6),
.deleted-table td:nth-child(6) {
  width: 14%;
}

.dashboard-shell *,
.neo-card * {
  min-width: 0;
}

@media (max-width: 1024px) {

  .employee-table th,
  .employee-table td {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
  }

  .dashboard-shell .employee-table {
    font-size: 0.875rem;
  }
}

@media (max-width: 768px) {
  .dashboard-shell {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }

  .neo-card {
    border-radius: 1rem;
    padding: 1rem;
  }

  .employee-list-table-wrapper {
    overflow: visible;
  }

  .dashboard-shell .employee-table {
    display: block;
    width: 100%;
    min-width: 0;
    font-size: 0.875rem;
  }

  .employee-table thead {
    display: none;
  }

  .employee-table tbody {
    display: grid;
    gap: 0.75rem;
  }

  .employee-table tbody tr {
    position: relative;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    border: 1px solid #cbd8e8;
    border-radius: 0.75rem;
    background: var(--surface);
    overflow: hidden;
  }

  .employee-table tbody tr:hover {
    background: var(--surface);
  }

  .employee-table tbody td {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    width: auto !important;
    min-width: 0;
    padding: 0.65rem 0.75rem;
    border-top: 1px solid #e2e8f0;
    text-align: right;
    overflow-wrap: anywhere;
  }

  .employee-table tbody td::before {
    content: attr(data-label);
    flex: 0 0 auto;
    color: var(--text-muted);
    font-size: 0.75rem;
    font-weight: 600;
    text-align: left;
  }

  .employee-table tbody td:nth-child(1),
  .employee-table tbody td:nth-child(2) {
    align-items: center;
    border-top: 0;
  }

  .employee-table tbody td:nth-child(1) {
    justify-content: flex-start;
  }

  .employee-table tbody td:nth-child(2) {
    justify-content: flex-end;
  }

  .employee-table tbody td:nth-child(n + 3) {
    grid-column: 1 / -1;
  }

  .employee-table tbody td.employee-actions-cell {
    display: block;
    text-align: left;
  }

  .employee-table tbody td.employee-actions-cell::before {
    display: block;
    margin-bottom: 0.5rem;
  }

  .employee-row-actions {
    justify-content: flex-start;
    gap: 0.4rem;
  }

  .employee-row-actions > .employee-btn {
    padding: 0.4rem 0.6rem;
    font-size: 0.75rem;
    white-space: nowrap;
  }

  .employee-actions-menu {
    justify-items: start;
  }

  .employee-actions-popover {
    width: min(12rem, calc(100vw - 4rem));
  }

  .employee-email-value,
  .employee-position-value {
    flex: 1 1 auto;
    min-width: 0;
    text-align: right;
  }

  .employee-table tbody tr.empty-row {
    display: block;
  }

  .employee-table tbody tr.empty-row td {
    justify-content: center;
    text-align: center;
  }

  .employee-table tbody tr.empty-row td::before {
    content: none;
  }

  .deleted-table {
    width: 980px;
    min-width: 980px;
    max-width: none;
    table-layout: auto;
    font-size: 0.875rem;
  }

  .deleted-table th,
  .deleted-table td {
    white-space: nowrap;
    word-break: normal;
    overflow-wrap: normal;
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }

  .deleted-table td:nth-child(2) {
    white-space: normal;
    min-width: 170px;
  }

  .deleted-table td:nth-child(3) {
    white-space: normal;
    overflow-wrap: anywhere;
    min-width: 210px;
  }

  .deleted-table button {
    font-size: 0.75rem;
    padding: 0.4rem 0.65rem;
    white-space: nowrap;
  }
}

@media (max-width: 480px) {
  .dashboard-shell {
    padding-left: 0.5rem;
    padding-right: 0.5rem;
  }

  .neo-card {
    border-radius: 0.9rem;
    padding: 0.85rem;
  }

  .deleted-table {
    width: 950px;
    min-width: 950px;
  }
}
</style>