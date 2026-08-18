<template>
  <div class="dashboard-shell p-8 min-h-screen space-y-8">
    <!-- Header -->

    <div class="neo-card p-6">
      <h1 class="text-2xl font-bold text-gray-100">Admin Settings</h1>

      <p class="text-gray-400 mt-2">
        Manage system configuration and administration settings.
      </p>
    </div>

    <!-- Tabs -->

    <div class="neo-card">
      <div class="border-b border-gray-700 px-6">
        <nav class="flex flex-wrap gap-6">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-4 text-sm font-medium transition',
              activeTab === tab.id
                ? 'text-blue-400 border-b-2 border-blue-500'
                : 'text-gray-500 hover:text-gray-300',
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Account Settings -->

    <div v-if="activeTab === 'account'" class="neo-card p-6">
      <h2 class="text-xl font-semibold text-gray-100 mb-2">Account Settings</h2>

      <p class="text-gray-400 mb-6">
        Manage administrator account information and security.
      </p>

      <!-- Email -->

      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-400">
            Admin Email
          </label>

          <input
            v-model="adminEmail"
            type="email"
            class="mt-1 w-full bg-white/5 border border-gray-700 rounded-lg px-3 py-2 text-gray-100"
          />
        </div>

        <button
          @click="updateEmail"
          class="bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700"
        >
          Update Email
        </button>
      </div>

      <hr class="my-8 border-gray-700" />

      <!-- Password -->

      <h3 class="text-lg font-semibold text-gray-100 mb-4">Change Password</h3>

      <div class="space-y-4">
        <input
          v-model="password.current_password"
          type="password"
          placeholder="Current Password"
          class="w-full bg-white/5 border border-gray-700 rounded-lg px-3 py-2 text-gray-100 placeholder-gray-500"
        />

        <input
          v-model="password.new_password"
          type="password"
          placeholder="New Password"
          class="w-full bg-white/5 border border-gray-700 rounded-lg px-3 py-2 text-gray-100 placeholder-gray-500"
        />

        <input
          v-model="password.new_password_confirmation"
          type="password"
          placeholder="Confirm New Password"
          class="w-full bg-white/5 border border-gray-700 rounded-lg px-3 py-2 text-gray-100 placeholder-gray-500"
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

    <div v-if="activeTab === 'leave'" class="neo-card p-6 space-y-6">
      <div>
        <h2 class="text-xl font-semibold text-gray-100 mb-2">Leave Settings</h2>

        <p class="text-gray-400 mb-6">
          Manage available leave types and requirements.
        </p>

        <button
          @click="openAddLeaveModal"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700"
        >
          + Add Leave Type
        </button>
      </div>

      <div class="space-y-4">
        <div
          v-for="leave in leaveTypes"
          :key="leave.id"
          class="flex justify-between items-center border border-gray-700 rounded-xl p-4"
        >
          <div>
            <h3 class="font-semibold text-gray-100">
              {{ leave.leave_type_name }}
            </h3>

            <p class="text-sm text-gray-400">
              {{ leave.description }}
            </p>
          </div>

          <div class="flex gap-2">
            <button
              @click="openEditLeaveModal(leave)"
              class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600"
            >
              Edit
            </button>

            <button
              @click="removeLeaveType(leave.leave_type_id)"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Leave Credit Settings -->

    <div v-if="activeTab === 'credits'" class="neo-card p-6">
      <h2 class="text-xl font-semibold text-gray-100">Leave Credit Settings</h2>

      <p class="text-gray-400 mt-2">
        Configure leave allocation and credit computation.
      </p>
    </div>

    <!-- Approval Settings -->

    <div v-if="activeTab === 'approval'" class="neo-card p-6">
      <h2 class="text-xl font-semibold text-gray-100">Approval Settings</h2>

      <p class="text-gray-400 mt-2">Configure leave approval workflow.</p>
    </div>

    <!-- System Settings -->

    <div v-if="activeTab === 'system'" class="neo-card p-6">
      <h2 class="text-xl font-semibold text-gray-100">System Settings</h2>

      <p class="text-gray-400 mt-2">
        Manage system information and configuration.
      </p>
    </div>

    <!-- Backup -->

    <div v-if="activeTab === 'backup'" class="neo-card p-6">
      <h2 class="text-xl font-semibold text-gray-100">Backup & Maintenance</h2>

      <p class="text-gray-400 mt-2">Manage database backup and system logs.</p>
    </div>

    <!-- Leave Type Modal -->

    <div
      v-if="isLeaveModalOpen"
      class="fixed inset-0 bg-black/60 flex items-center justify-center z-50"
    >
      <div class="neo-card w-full max-w-md p-6">
        <h2 class="text-xl font-semibold text-gray-100 mb-5">
          {{ isEditMode ? "Edit Leave Type" : "Add Leave Type" }}
        </h2>

        <div class="space-y-4">
          <div>
            <label class="text-sm text-gray-400"> Leave Type Name </label>

            <input
              v-model="leaveForm.leave_type_name"
              class="w-full bg-white/5 border border-gray-700 rounded-lg px-3 py-2 text-gray-100"
            />
          </div>

          <div>
            <label class="text-sm text-gray-400"> Description </label>

            <textarea
              v-model="leaveForm.description"
              rows="3"
              class="w-full bg-white/5 border border-gray-700 rounded-lg px-3 py-2 text-gray-100"
            ></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <button
            @click="isLeaveModalOpen = false"
            class="px-4 py-2 text-gray-300 border border-gray-600 rounded-lg hover:bg-white/5"
          >
            Cancel
          </button>

          <button
            @click="saveLeaveType"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
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
} from "@/services/admin";
import {
  getLeaveTypes,
  createLeaveType,
  updateLeaveType,
  deleteLeaveType,
} from "@/services/leaveType";

const activeTab = ref("account");

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

const password = ref({
  current_password: "",
  new_password: "",
  new_password_confirmation: "",
});
const loadAdmin = async () => {
  const data = await getAdminProfile();

  adminEmail.value = data.email;
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
});
</script>

<style scoped>
.dashboard-shell {
  background: #080D14;
}

.neo-card {
  background: #111D2E;
  border: 1px solid #1E293B;
  border-radius: 1.4rem;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04);
  transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.neo-card:hover {
  box-shadow: 0 14px 26px rgba(15, 23, 42, 0.06);
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
</style>