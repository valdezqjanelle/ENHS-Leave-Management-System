import { createRouter, createWebHistory } from "vue-router";
import LeaveApplicationPrint from "../views/LeaveApplicationPrint.vue";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),

  routes: [

    // Default route
    {
      path: "/",
      redirect: "/login",
    },


    // Standalone print route (no dashboard layout wrapper)
    {
      path: "/leave-print/:id",
      name: "LeaveApplicationPrint",
      component: LeaveApplicationPrint,
      meta: {
        requiresAuth: true,
      },
    },


    // Main Layout
    {
      path: "/",
      component: () => import("../components/Layout.vue"),

      children: [

        {
          path: "/dashboard",
          name: "dashboard",
          component: () => import("../views/Dashboard.vue"),
          meta: {
            requiresAuth: true,
          },
        },


        {
          path: "/leave-application",
          name: "leave-application",
          component: () => import("../views/LeaveApplication.vue"),
          meta: {
            requiresAuth: true,
            role: "employee",
          },
        },


        {
          path: "/simple-leave",
          name: "simple-leave",
          component: () => import("../views/LeaveApplication.vue"),
        },


        {
          path: "/admin-applications",
          name: "admin-applications",
          component: () => import("../views/AdminApplications.vue"),
          meta: {
            requiresAuth: true,
            role: "admin",
          },
        },


        {
          path: "/employees",
          name: "employees",
          component: () => import("../views/Employees.vue"),
          meta: {
            requiresAuth: true,
            role: "admin",
          },
        },



        {
          path: "/my-applications",
          name: "my-applications",
          component: () => import("../views/MyApplications.vue"),
          meta: {
            requiresAuth: true,
            role: "employee",
          },
        },


        {
          path: "/attendance",
          name: "attendance",
          component: () => import("../views/Attendance.vue"),
          meta: {
            requiresAuth: true,
          },
        },


        {
          path: "/leave-credits",
          name: "leave-credits",
          component: () => import("../views/LeaveCredits.vue"),
          meta: {
            requiresAuth: true,
            role: "admin",
          },
        },


        {
          path: "/leave-balances",
          name: "leave-balances",
          component: () => import("../views/LeaveBalances.vue"),
          meta: {
            requiresAuth: true,
            role: "admin",
          },
        },


        {
          path: "/records",
          name: "records",
          component: () => import("../views/RecordsEmployee.vue"),
          meta: {
            requiresAuth: true,
          },
        },


        {
          path: "/reports",
          name: "reports",
          component: () => import("../views/Reports.vue"),
          meta: {
            requiresAuth: true,
          },
        },


        {
          path: "/settings",
          name: "settings",
          component: () => import("../views/Settings.vue"),
          meta: {
            requiresAuth: true,
            role: "employee",
          },
        },

        {
          path: "/admin-settings",
          name: "admin-settings",
          component: () => import("../views/AdminSettings.vue"),
        }


      ],
    },


    // Login
    {
      path: "/login",
      name: "login",
      component: () => import("../views/Login.vue"),
    },


  ],
});



// Authentication Guard

router.beforeEach((to, from, next) => {

  const token = localStorage.getItem("token");

  const user = JSON.parse(
    localStorage.getItem("user") || "{}"
  );


  // Need login
  if (to.meta.requiresAuth && !token) {
    return next("/login");
  }


  // Already logged in cannot go back to login
  if (to.path === "/login" && token) {
    return next("/dashboard");
  }


  // Role checking
  if (to.meta.role && user.role !== to.meta.role) {

    return next("/dashboard");

  }


  next();

});


export default router;