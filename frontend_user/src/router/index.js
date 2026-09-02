import { createRouter, createWebHistory } from "vue-router";

const routes = [
  {
    path: "/",
    name: "Home",
    component: () => import("../views/HomeView.vue"),
  },
  {
    path: "/jobs",
    name: "Jobs",
    component: () => import("../views/JobsView.vue"),
  },
  {
    path: "/jobs/:id",
    name: "JobDetail",
    component: () => import("../views/JobDetailView.vue"),
  },
  {
    path: "/companies",
    name: "Companies",
    component: () => import("../views/CompaniesView.vue"),
  },
  {
    path: "/companies/:id",
    name: "CompanyDetail",
    component: () => import("../views/CompanyDetailView.vue"),
  },
  {
    path: "/about",
    name: "About",
    component: () => import("../views/AboutView.vue"),
  },
  {
    path: "/contact",
    name: "Contact",
    component: () => import("../views/ContactView.vue"),
  },
  {
    path: "/profile",
    name: "Profile",
    component: () => import("../views/ProfileView.vue"),
  },
  {
    path: "/login",
    name: "Login",
    component: () => import("../views/LoginView.vue"),
  },
  {
    path: "/register",
    name: "Register",
    component: () => import("../views/RegisterView.vue"),
  },
  {
    path: "/notifications",
    name: "Notifications",
    component: () => import("../views/NotificationView.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

export default router;
