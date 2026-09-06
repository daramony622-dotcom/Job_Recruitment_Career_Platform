<script setup>
import { Send } from "lucide-vue-next";
import { Facebook, Instagram, Twitter, Linkedin } from "lucide-vue-next";

const currentYear = new Date().getFullYear();

const socialLinks = [
  { name: "Facebook", icon: Facebook, href: "#" },
  { name: "Instagram", icon: Instagram, href: "#" },
  { name: "Twitter", icon: Twitter, href: "#" },
  { name: "LinkedIn", icon: Linkedin, href: "#" },
];
</script>

<template>
  <footer class="bg-slate-800 backdrop-blur-md text-slate-700 py-10 border-t border-slate-200/80 shadow-lg font-sans">
    <div class="max-w-7xl mx-auto px-6 lg:px-8 space-y-8">
      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start pb-8 border-b border-slate-200/80">
        
        <!-- Brand & Social Buttons (Takes 4 cols) -->
        <div class="space-y-4 md:col-span-4">
          <router-link to="/" class="inline-block">
            <img
              src="/logo.png"
              alt="Job Search Logo"
              class="h-14 md:h-16 w-auto object-contain transition-transform hover:scale-105"
            />
          </router-link>

          <p class="text-xs text-white leading-relaxed max-w-sm">
            Connecting professionals with top employers globally through our intelligent career platform.
          </p>

          <div class="flex items-center gap-2 pt-1">
            <a
              v-for="social in socialLinks"
              :key="social.name"
              :href="social.href"
              :aria-label="social.name"
              class="w-8 h-8 flex items-center justify-center rounded-xl bg-white border border-slate-200/80 text-slate-500 hover:text-white hover:bg-blue-600 hover:border-blue-600 transition duration-200 shadow-sm"
            >
              <component :is="social.icon" class="w-4 h-4" />
            </a>
          </div>
        </div>

        <!-- Quick Links Grid Container (Takes 8 cols) -->
        <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-6">
          <!-- For Job Seekers -->
          <div>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">
              For Job Seekers
            </h4>
            <ul class="space-y-2 text-xs text-blue-500">
              <li><router-link to="/jobs" class="hover:text-blue-600 transition">Browse Jobs</router-link></li>
              <li><router-link to="/companies" class="hover:text-blue-600 transition">Top Companies</router-link></li>
              <li><router-link to="/profile" class="hover:text-blue-600 transition">My Profile</router-link></li>
            </ul>
          </div>

          <!-- Company -->
          <div>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">
              Company
            </h4>
            <ul class="space-y-2 text-xs text-blue-500">
              <li><router-link to="/about" class="hover:text-blue-600 transition">About Us</router-link></li>
              <li><router-link to="/contact" class="hover:text-blue-600 transition">Contact Us</router-link></li>
              <li><router-link to="/jobs" class="hover:text-blue-600 transition">Career Opportunities</router-link></li>
            </ul>
          </div>

          <!-- Newsletter -->
          <div>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">
              Newsletter
            </h4>
            <p class="text-xs text-blue-500 mb-2">Get weekly job alerts in your inbox.</p>
            <form @submit.prevent class="flex flex-col sm:flex-row gap-2">
              <input
                type="email"
                placeholder="Your email address"
                class="w-full bg-white/90 border border-slate-200/80 rounded-xl px-3 py-2 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-600 transition shadow-sm"
                required
              />
              <button
                type="submit"
                class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold transition flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20"
              >
                <Send class="w-3.5 h-3.5" />
              </button>
            </form>
          </div>
        </div>

      </div>

      <!-- Bottom Bar -->
      <div class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-3">
        <p>© {{ currentYear }} Job Search. All rights reserved.</p>
        <div class="flex gap-4">
          <router-link to="/about" class="hover:text-blue-600 transition">Privacy Policy</router-link>
          <router-link to="/about" class="hover:text-blue-600 transition">Terms of Service</router-link>
          <router-link to="/contact" class="hover:text-blue-600 transition">Contact</router-link>
        </div>
      </div>
    </div>
  </footer>
</template>