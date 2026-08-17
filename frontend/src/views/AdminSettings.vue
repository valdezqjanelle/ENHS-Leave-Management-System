<template>
  <div class="p-6 space-y-6">
    <!-- Header -->

    <div class="bg-white rounded-xl shadow p-6">
      <h1 class="text-2xl font-bold text-gray-800">Admin Settings</h1>

      <p class="text-gray-500 mt-2">
        Manage system configuration and administration settings.
      </p>
    </div>

    <!-- Tabs -->

    <div class="bg-white rounded-xl shadow">
      <div class="border-b px-6">
        <nav class="flex flex-wrap gap-6">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-4 text-sm font-medium transition',
              activeTab === tab.id
                ? 'text-blue-600 border-b-2 border-blue-600'
                : 'text-gray-500 hover:text-gray-700',
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Account Settings -->

    <!-- Account Settings -->

    <div v-if="activeTab === 'account'" class="bg-white rounded-xl shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-2">Account Settings</h2>

      <p class="text-gray-500 mb-6">
        Manage administrator account information and security.
      </p>
<!-- Admin Profile -->
<div class="mb-8">
  <div class="flex items-center justify-between mb-4">
    <div>
      <h3 class="text-lg font-semibold text-gray-800">
        Administrator Profile
      </h3>
      <p class="text-sm text-gray-500">
        Update your administrator information.
      </p>
    </div>

    <!-- Edit button -->
    <button
      v-if="!isEditingProfile"
      @click="startEditProfile"
      type="button"
      class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg"
    >
      Edit Profile
    </button>

    <!-- Save / Cancel buttons -->
    <div v-else class="flex gap-2">
      <button
        @click="cancelEditProfile"
        type="button"
        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100"
      >
        Cancel
      </button>

      <button
        @click="saveAdminProfile"
        type="button"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
      >
        Save Changes
      </button>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

    <!-- First Name -->
    <div>
      <label class="block text-sm font-medium text-gray-700">
        First Name
      </label>

      <input
        v-model="adminProfile.first_name"
        type="text"
        :readonly="!isEditingProfile"
        :class="[
          'mt-1 w-full border rounded-lg px-3 py-2',
          isEditingProfile
            ? 'text-black bg-white'
            : 'text-gray-600 bg-gray-100'
        ]"
      />
    </div>

    <!-- Middle Name -->
    <div>
      <label class="block text-sm font-medium text-gray-700">
        Middle Name
      </label>

      <input
        v-model="adminProfile.middle_name"
        type="text"
        :readonly="!isEditingProfile"
        :class="[
          'mt-1 w-full border rounded-lg px-3 py-2',
          isEditingProfile
            ? 'text-black bg-white'
            : 'text-gray-600 bg-gray-100'
        ]"
      />
    </div>

    <!-- Last Name -->
    <div>
      <label class="block text-sm font-medium text-gray-700">
        Last Name
      </label>

      <input
        v-model="adminProfile.last_name"
        type="text"
        :readonly="!isEditingProfile"
        :class="[
          'mt-1 w-full border rounded-lg px-3 py-2',
          isEditingProfile
            ? 'text-black bg-white'
            : 'text-gray-600 bg-gray-100'
        ]"
      />
    </div>

    <!-- Sex -->
    <div>
      <label class="block text-sm font-medium text-gray-700">
        Sex
      </label>

      <select
        v-model="adminProfile.sex"
        :disabled="!isEditingProfile"
        :class="[
          'mt-1 w-full border rounded-lg px-3 py-2',
          isEditingProfile
            ? 'text-black bg-white'
            : 'text-gray-600 bg-gray-100'
        ]"
      >
        <option value="">Select Sex</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
      </select>
    </div>

    <!-- Position -->
    <div>
      <label class="block text-sm font-medium text-gray-700">
        Position
      </label>

      <input
        v-model="adminProfile.position"
        type="text"
        :readonly="!isEditingProfile"
        :class="[
          'mt-1 w-full border rounded-lg px-3 py-2',
          isEditingProfile
            ? 'text-black bg-white'
            : 'text-gray-600 bg-gray-100'
        ]"
      />
    </div>

    <!-- Department -->
    <div>
      <label class="block text-sm font-medium text-gray-700">
        Department
      </label>

      <input
        v-model="adminProfile.department"
        type="text"
        :readonly="!isEditingProfile"
        :class="[
          'mt-1 w-full border rounded-lg px-3 py-2',
          isEditingProfile
            ? 'text-black bg-white'
            : 'text-gray-600 bg-gray-100'
        ]"
      />
    </div>

    <!-- Contact Number -->
    <div>
      <label class="block text-sm font-medium text-gray-700">
        Contact Number
      </label>

      <input
        v-model="adminProfile.contact_number"
        type="text"
        :readonly="!isEditingProfile"
        :class="[
          'mt-1 w-full border rounded-lg px-3 py-2',
          isEditingProfile
            ? 'text-black bg-white'
            : 'text-gray-600 bg-gray-100'
        ]"
      />
    </div>

  </div>
</div>

      <hr class="my-8" />

      <!-- Email -->

      <!-- Email -->

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">
            Admin Email
          </label>

          <input
            v-model="adminEmail"
            type="email"
            class="mt-1 w-full border rounded-lg px-3 py-2 text-black"
          />
        </div>

        <button
          @click="updateEmail"
          class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700"
        >
          Update Email
        </button>
      </div>

      <hr class="my-8" />

      <!-- Password -->

      <h3 class="text-lg font-semibold text-gray-800 mb-4">Change Password</h3>

      <div class="space-y-4">
        <input
          v-model="password.current_password"
          type="password"
          placeholder="Current Password"
          class="w-full border rounded-lg px-3 py-2 text-black"
        />

        <input
          v-model="password.new_password"
          type="password"
          placeholder="New Password"
          class="w-full border rounded-lg px-3 py-2 text-black"
        />

        <input
          v-model="password.new_password_confirmation"
          type="password"
          placeholder="Confirm New Password"
          class="w-full border rounded-lg px-3 py-2 text-black"
        />

        <button
          @click="updatePassword"
          class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700"
        >
          Update Password
        </button>
      </div>
    </div>

    <!-- Leave Settings -->

    <!-- Leave Settings -->

    <div v-if="activeTab === 'leave'" class="bg-white rounded-xl shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800 mb-2">Leave Settings</h2>

      <p class="text-gray-500 mb-6">
        Manage available leave types and requirements.
      </p>

      <button
        @click="openAddLeaveModal"
        class="bg-blue-600 text-white px-4 py-2 rounded-lg"
      >
        + Add Leave Type
      </button>

      <div class="space-y-4">
        <div
          v-for="leave in leaveTypes"
          :key="leave.id"
          class="flex justify-between items-center border rounded-xl p-4"
        >
          <div>
            <h3 class="font-semibold text-gray-800">
              {{ leave.leave_type_name }}
            </h3>

            <p class="text-sm text-gray-500">
              {{ leave.description }}
            </p>
          </div>

          <div class="flex gap-2">
            <button
              @click="openEditLeaveModal(leave)"
              class="px-4 py-2 bg-yellow-500 text-white rounded-lg"
            >
              Edit
            </button>

            <button
              @click="removeLeaveType(leave.leave_type_id)"
              class="px-4 py-2 bg-red-600 text-white rounded-lg"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Leave Credit Settings -->

    <div v-if="activeTab === 'credits'" class="bg-white rounded-xl shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800">Leave Credit Settings</h2>

      <p class="text-gray-500 mt-2">
        Configure leave allocation and credit computation.
      </p>
    </div>

    <!-- Approval Settings -->

    <div v-if="activeTab === 'approval'" class="bg-white rounded-xl shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800">Approval Settings</h2>

      <p class="text-gray-500 mt-2">Configure leave approval workflow.</p>
    </div>

    <!-- System Settings -->

    <div v-if="activeTab === 'system'" class="bg-white rounded-xl shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800">System Settings</h2>

      <p class="text-gray-500 mt-2">
        Manage system information and configuration.
      </p>
    </div>

    <!-- Backup -->

    <div v-if="activeTab === 'backup'" class="bg-white rounded-xl shadow p-6">
      <h2 class="text-xl font-semibold text-gray-800">Backup & Maintenance</h2>

      <p class="text-gray-500 mt-2">
        Audit trail of admin and login activity.
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
        <div class="bg-gray-50 rounded-lg p-4">
          <p class="text-sm text-gray-500">Logged events</p>
          <p class="text-2xl font-semibold text-gray-800">{{ auditTotal }}</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
          <p class="text-sm text-gray-500">Showing</p>
          <p class="text-2xl font-semibold text-gray-800">{{ auditLogs.length }}</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4">
          <p class="text-sm text-gray-500">Last event</p>
          <p class="text-2xl font-semibold text-gray-800">{{ lastEventComputed }}</p>
        </div>
      </div>

      <div class="flex flex-wrap gap-3 mt-6">
        <select
          v-model="auditFilters.action"
          @change="loadAuditLogs(1)"
          class="border rounded-lg px-3 py-2 text-sm text-black"
        >
          <option value="">All actions</option>
          <option v-for="a in auditActions" :key="a" :value="a">{{ a }}</option>
        </select>

        <input
          type="date"
          v-model="auditFilters.date_from"
          @change="loadAuditLogs(1)"
          class="border rounded-lg px-3 py-2 text-sm text-black"
        />

        <input
          type="date"
          v-model="auditFilters.date_to"
          @change="loadAuditLogs(1)"
          class="border rounded-lg px-3 py-2 text-sm text-black"
        />

        <button
          v-if="auditFilters.action || auditFilters.date_from || auditFilters.date_to"
          @click="clearAuditFilters"
          class="text-sm text-blue-600 hover:underline"
        >
          Clear filters
        </button>
      </div>

      <div class="mt-4 overflow-x-auto border rounded-lg">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50">
            <tr>
              <th class="text-left px-4 py-3 font-medium text-gray-500">User</th>
              <th class="text-left px-4 py-3 font-medium text-gray-500">Action</th>
              <th class="text-left px-4 py-3 font-medium text-gray-500">Description</th>
              <th class="text-left px-4 py-3 font-medium text-gray-500">Date</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="auditLoading">
              <td colspan="4" class="px-4 py-6 text-center text-gray-400">Loading...</td>
            </tr>

            <tr v-else-if="auditLogs.length === 0">
              <td colspan="4" class="px-4 py-6 text-center text-gray-400">No audit log entries yet.</td>
            </tr>

            <tr v-for="log in auditLogs" :key="log.log_id" class="border-t">
              <td class="px-4 py-3 text-gray-800">{{ log.user?.email ?? "Unknown user" }}</td>

              <td class="px-4 py-3">
                <span
                  class="px-2 py-1 rounded-md text-xs font-medium"
                  :class="actionBadgeClass(log.action)"
                >
                  {{ log.action }}
                </span>
              </td>

              <td class="px-4 py-3 text-gray-600">{{ log.description }}</td>

              <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ formatAuditDate(log.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between mt-4">
        <p class="text-sm text-gray-400">
          Showing {{ auditFrom }}-{{ auditTo }} of {{ auditTotal }}
        </p>

        <div class="flex gap-2">
          <button
            :disabled="auditPage <= 1"
            @click="loadAuditLogs(auditPage - 1)"
            class="px-3 py-1.5 border rounded-lg text-sm disabled:opacity-40"
          >
            Prev
          </button>

          <button
            :disabled="auditPage >= auditLastPage"
            @click="loadAuditLogs(auditPage + 1)"
            class="px-3 py-1.5 border rounded-lg text-sm disabled:opacity-40"
          >
            Next
          </button>
        </div>
      </div>
    </div>
    <!-- Leave Type Modal -->

    <div
      v-if="isLeaveModalOpen"
      class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-5">
          {{ isEditMode ? "Edit Leave Type" : "Add Leave Type" }}
        </h2>

        <div class="space-y-4">
          <div>
            <label class="text-sm text-gray-700"> Leave Type Name </label>

            <input
              v-model="leaveForm.leave_type_name"
              class="w-full border rounded-lg px-3 py-2 text-black"
            />
          </div>

          <div>
            <label class="text-sm text-gray-700"> Description </label>

            <textarea
              v-model="leaveForm.description"
              rows="3"
              class="w-full border rounded-lg px-3 py-2 text-black"
            ></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <button
            @click="isLeaveModalOpen = false"
            class="px-4 py-2 text-black border border-gray-600 rounded-lg"
          >
            Cancel
          </button>

          <button
            @click="saveLeaveType"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg"
          >
            Save
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from "vue";

import {
  getAdminProfile,
  updateAdminEmail,
  updateAdminPassword,
  updateAdminProfile,
} from "@/services/admin";
import {
  getLeaveTypes,
  createLeaveType,
  updateLeaveType,
  deleteLeaveType,
} from "@/services/leaveType";
import { getAuditLogs, getAuditLogActions } from "@/services/auditLog";

const activeTab = ref("account");

// AUDIT LOGS
const auditLogs = ref<any[]>([]);
const auditActions = ref<string[]>([]);
const auditFilters = ref({ action: "", date_from: "", date_to: "" });
const auditPage = ref(1);
const auditLastPage = ref(1);
const auditTotal = ref(0);
const auditFrom = ref(0);
const auditTo = ref(0);
const auditLoading = ref(false);

// const lastEventLabel = () => {};
const lastEventComputed = ref("—");

const loadAuditLogs = async (page = 1) => {
  auditLoading.value = true;
  try {
    const { data } = await getAuditLogs({
      page,
      action: auditFilters.value.action || undefined,
      date_from: auditFilters.value.date_from || undefined,
      date_to: auditFilters.value.date_to || undefined,
    });

    auditLogs.value = data.data;
    auditPage.value = data.current_page;
    auditLastPage.value = data.last_page;
    auditTotal.value = data.total;
    auditFrom.value = data.from ?? 0;
    auditTo.value = data.to ?? 0;

    lastEventComputed.value = auditLogs.value.length
      ? formatAuditDate(auditLogs.value[0].created_at)
      : "—";
  } catch (error) {
    console.log(error);
  } finally {
    auditLoading.value = false;
  }
};

const loadAuditActions = async () => {
  try {
    const { data } = await getAuditLogActions();
    auditActions.value = data;
  } catch (error) {
    console.log(error);
  }
};

const clearAuditFilters = () => {
  auditFilters.value = { action: "", date_from: "", date_to: "" };
  loadAuditLogs(1);
};

const actionBadgeClass = (action: string) => {
  const a = action.toLowerCase();
  if (a.includes("login")) return "bg-green-100 text-green-700";
  if (a.includes("approved")) return "bg-blue-100 text-blue-700";
  if (a.includes("rejected") || a.includes("deleted")) return "bg-red-100 text-red-700";
  if (a.includes("updated") || a.includes("created")) return "bg-amber-100 text-amber-700";
  return "bg-gray-100 text-gray-700";
};

const formatAuditDate = (dateStr: string) => {
  const d = new Date(dateStr);
  return d.toLocaleString("en-US", {
    month: "short",
    day: "numeric",
    hour: "numeric",
    minute: "2-digit",
  });
};

const tabs = [
  {
    id: "account",
    name: "Account Settings",
  },

  {
    id: "leave",
    name: "Leave Settings",
  },

  {
    id: "credits",
    name: "Leave Credits",
  },

  {
    id: "approval",
    name: "Approval Settings",
  },

  {
    id: "system",
    name: "System Settings",
  },

  {
    id: "backup",
    name: "Backup",
  },
];

const adminEmail = ref("");
const adminProfile = ref({
  first_name: "",
  middle_name: "",
  last_name: "",
  sex: "",
  position: "",
  department: "",
  contact_number: "",
});

const isEditingProfile = ref(false);

const originalAdminProfile = ref({
  first_name: "",
  middle_name: "",
  last_name: "",
  sex: "",
  position: "",
  department: "",
  contact_number: "",
});

const startEditProfile = () => {
  originalAdminProfile.value = {
    ...adminProfile.value,
  };

  isEditingProfile.value = true;
};

const cancelEditProfile = () => {
  adminProfile.value = {
    ...originalAdminProfile.value,
  };

  isEditingProfile.value = false;
};

const saveAdminProfile = async () => {
  try {
    await updateAdminProfile({
      first_name: adminProfile.value.first_name,
      middle_name: adminProfile.value.middle_name,
      last_name: adminProfile.value.last_name,
      sex: adminProfile.value.sex,
      position: adminProfile.value.position,
      department: adminProfile.value.department,
      contact_number: adminProfile.value.contact_number,
    });

    // Update original copy after successful save
    originalAdminProfile.value = {
      ...adminProfile.value,
    };

    isEditingProfile.value = false;

    alert("Profile updated successfully.");
  } catch (error: any) {
    console.error("Profile update error:", error);

    if (error.response?.data?.message) {
      alert(error.response.data.message);
    } else {
      alert("Failed to update profile.");
    }
  }
};

const password = ref({
  current_password: "",
  new_password: "",
  new_password_confirmation: "",
});
const loadAdmin = async () => {
  try {
    const data = await getAdminProfile();

    adminEmail.value = data.email || "";

    if (data.profile) {
      adminProfile.value = {
        first_name: data.profile.first_name || "",
        middle_name: data.profile.middle_name || "",
        last_name: data.profile.last_name || "",
        sex: data.profile.sex || "",
        position: data.profile.position || "",
        department: data.profile.department || "",
        contact_number: data.profile.contact_number || "",
      };

      originalAdminProfile.value = {
        ...adminProfile.value,
      };
    }
  } catch (error: any) {
    console.error(
      "Failed to load admin profile:",
      error.response?.data || error
    );
  }
};

const updateEmail = async () => {
  try {
    await updateAdminEmail(adminEmail.value);

    alert("Email updated successfully");
  } catch (error) {
    console.log(error);

    alert("Email update failed");
  }
};

const updatePassword = async () => {
  try {
    await updateAdminPassword(password.value);

    alert("Password updated successfully");
  } catch (error) {
    console.log(error);

    alert("Password update failed");
  }
};

const leaveTypes = ref<any[]>([]);

const loadLeaveTypes = async () => {
  try {
    const data = await getLeaveTypes();

    leaveTypes.value = data;
  } catch (error) {
    console.log(error);
  }
};

const isLeaveModalOpen = ref(false);
const isEditMode = ref(false);
const leaveForm = ref({
  leave_type_id: null,
  leave_type_name: "",
  description: "",
});
const openAddLeaveModal = () => {
  isEditMode.value = false;

  leaveForm.value = {
    leave_type_id: null,
    leave_type_name: "",
    description: "",
  };

  isLeaveModalOpen.value = true;
};

const openEditLeaveModal = (leave: any) => {
  isEditMode.value = true;

  leaveForm.value = {
    leave_type_id: leave.leave_type_id,
    leave_type_name: leave.leave_type_name,
    description: leave.description,
  };

  isLeaveModalOpen.value = true;
};

const removeLeaveType = async (id: number) => {
  try {
    await deleteLeaveType(id);

    alert("Leave type deleted successfully");

    loadLeaveTypes();
  } catch (error) {
    console.log(error);

    alert("Delete failed");
  }
};

const saveLeaveType = async () => {
  try {
    if (isEditMode.value) {
      await updateLeaveType(leaveForm.value.leave_type_id!, {
        leave_type_name: leaveForm.value.leave_type_name,
        description: leaveForm.value.description,
      });

      alert("Leave type updated successfully");
    } else {
      await createLeaveType({
        leave_type_name: leaveForm.value.leave_type_name,
        description: leaveForm.value.description,
      });

      alert("Leave type created successfully");
    }

    isLeaveModalOpen.value = false;

    await loadLeaveTypes();
  } catch (error) {
    console.log(error);

    alert("Failed saving leave type");
  }
};

onMounted(() => {
  loadAdmin();

  loadLeaveTypes();

  loadAuditLogs();
  loadAuditActions();
});
</script>
