<script setup>
import Navbar from '../components/layout/Navbar.vue'
import Footer from '../components/layout/Footer.vue'
import { 
  ShieldCheck, Zap, Globe, Heart, Briefcase, Users, Award, Clock,
  Server, Code, Terminal, Cpu, Database, Layout, Sparkles, Mail,
  Github, Linkedin, Send, CheckCircle2, MessageSquare, ArrowRight,
  Search, FileText, Bell, Building2
} from 'lucide-vue-next'

// ─── Team Members (1st Backend, 2nd Backend, 3rd Frontend) ─────────────────────
const team = [
  {
    name: 'Lin Dalan',
    role: 'Lead Backend Engineer',
    role_type: 'backend',
    avatar: '/Dalan.jpg',
    bio: 'Responsible for core backend architecture, Laravel RESTful API design, database migrations, and authentication security.',
    tech_stack: ['Laravel 11', 'PHP 8.3', 'MySQL', 'JWT Auth', 'REST API'],
    email: 'lindalan495@gmail.com',
    github: 'https://github.com/LinDalan-5706',
    linkedin: 'https://linkedin.com'
  },
  {
    name: 'Mao Seikmeng',
    role: 'Backend & Systems Engineer',
    role_type: 'backend',
    avatar: '/Seikmeng.jpg',
    bio: 'Focuses on database optimization, Redis queue workers, notification delivery pipelines, and server-side performance.',
    tech_stack: ['Laravel', 'PostgreSQL', 'Redis', 'WebSockets', 'Docker'],
    email: 'mengsiek8@gmail.com',
    github: 'https://github.com',
    linkedin: 'https://linkedin.com'
  },
  {
    name: 'Yon Mouy',
    role: 'Lead Frontend Engineer & UI/UX Specialist',
    role_type: 'frontend',
    avatar: '/Mouy.jpg',
    bio: 'Crafts the user interface and user experience, building reactive Vue 3 components, Tailwind dark mode system, and responsive pages.',
    tech_stack: ['Vue 3', 'Composition API', 'Tailwind CSS', 'Vite', 'JavaScript'],
    email: 'yonmouy1@gmail.com',
    github: 'https://github.com/yonmouy1-create',
    linkedin: 'https://linkedin.com'
  }
]

// ─── Platform Website Functions ───────────────────────────────────────────────
const platformFunctions = [
  {
    title: 'Smart Job Search & Matching',
    desc: 'Advanced multi-criteria filtering by salary ranges, work mode (onsite/remote/hybrid), category, and city location.',
    icon: Search,
    color: 'text-blue-600 bg-blue-50 dark:bg-blue-950/60 dark:text-blue-400'
  },
  {
    title: 'Verified Employer Portal',
    desc: 'Transparent company profiles, employee reviews, verified business badges, and direct recruitment channels.',
    icon: Building2,
    color: 'text-emerald-600 bg-emerald-50 dark:bg-emerald-950/60 dark:text-emerald-400'
  },
  {
    title: 'Real-Time Notification System',
    desc: 'Instant updates on job applications, recruiter views, and interview invites delivered via in-app alerts, email, and SMS.',
    icon: Bell,
    color: 'text-purple-600 bg-purple-50 dark:bg-purple-950/60 dark:text-purple-400'
  },
  {
    title: 'Candidate CV & Resume Builder',
    desc: 'One-click application submission with instant PDF resume uploads, expected salary specs, and availability indicators.',
    icon: FileText,
    color: 'text-amber-600 bg-amber-50 dark:bg-amber-950/60 dark:text-amber-400'
  }
]

// ─── Platform Stats ───────────────────────────────────────────────────────────
const stats = [
  { label: 'Active Job Posts', value: '12,400+', icon: Briefcase },
  { label: 'Registered Companies', value: '3,200+', icon: Users },
  { label: 'Monthly Visitors', value: '890k+', icon: Globe },
  { label: 'Successful Placements', value: '145,000+', icon: Award }
]
</script>

<template>
  <div class="min-h-screen bg-slate-50/60 dark:bg-[#070c16] font-sans text-slate-900 dark:text-slate-100 antialiased transition-colors duration-300">
    <Navbar />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-14">
      
      <!-- ─── 1. High-End Hero Banner ────────────────────────────────────── -->
      <section class="relative rounded-2xl overflow-hidden bg-linear-to-br from-slate-950 via-blue-950 to-blue-800 text-white p-8 sm:p-14 shadow-xl shadow-blue-950/10">
        <div class="absolute inset-0 bg-linear-to-br from-transparent via-blue-500/15 to-white/10 pointer-events-none"></div>

        <div class="relative z-10 max-w-3xl space-y-5">
          <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs font-semibold text-blue-100">
            <Sparkles class="w-3.5 h-3.5 text-amber-300" />
            <span>About Job Search Career Platform</span>
          </div>

          <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight text-white">
            Empowering Careers, <br />
            <span class="text-blue-300">Connecting Top Talent.</span>
          </h1>

          <p class="text-sm sm:text-base text-blue-100 leading-relaxed font-medium">
            Job Search is Cambodia’s premier career recruitment ecosystem. Designed and engineered by our dedicated core development team, we connect passionate job seekers with leading employers across Southeast Asia.
          </p>

          <div class="flex flex-wrap items-center gap-3 pt-2">
            <router-link to="/jobs" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-blue-600 hover:bg-blue-50 rounded-2xl text-xs font-bold transition shadow-md active:scale-95">
              <span>Browse 12,400+ Jobs</span>
              <ArrowRight class="w-4 h-4" />
            </router-link>
            <router-link to="/contact" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-500/30 hover:bg-blue-500/40 border border-white/20 text-white rounded-2xl text-xs font-bold transition active:scale-95">
              <Mail class="w-4 h-4" />
              <span>Contact Support</span>
            </router-link>
          </div>
        </div>
      </section>

      <!-- ─── 2. Live Platform Metrics ────────────────────────────────────── -->
      <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        <div 
          v-for="st in stats" 
          :key="st.label"
          class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-xl text-center space-y-2 shadow-sm hover:shadow-md"
        >
          <div class="p-3 bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 rounded-2xl w-12 h-12 mx-auto flex items-center justify-center">
            <component :is="st.icon" class="w-6 h-6" />
          </div>
          <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white">{{ st.value }}</div>
          <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ st.label }}</div>
        </div>
      </section>

      <!-- ─── 3. Core Website Functions ──────────────────────────────────── -->
      <section class="space-y-8">
        <div class="text-center max-w-2xl mx-auto space-y-2">
          <span class="px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-xs font-bold uppercase tracking-wider">
            Platform Capability
          </span>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Core Website Functions</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">Essential features driving seamless career connections on Job Search.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div 
            v-for="fn in platformFunctions" 
            :key="fn.title"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-xl space-y-4 shadow-sm hover:shadow-md"
          >
            <div :class="fn.color" class="p-3.5 rounded-2xl w-12 h-12 flex items-center justify-center shrink-0">
              <component :is="fn.icon" class="w-6 h-6" />
            </div>
            <h3 class="font-extrabold text-slate-900 dark:text-white text-base">{{ fn.title }}</h3>
            <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">{{ fn.desc }}</p>
          </div>
        </div>
      </section>

      <!-- ─── 4. Development Team Profiles (3 People) ───────────────────── -->
      <section class="space-y-8">
        <div class="text-center max-w-2xl mx-auto space-y-2">
          <span class="px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 text-xs font-bold uppercase tracking-wider">
            Meet The Engineers
          </span>
          <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Core Engineering Team</h2>
          <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">The developers behind the architecture, databases, APIs, and frontend user interface.</p>
        </div>

        <!-- 3 Team Member Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div 
            v-for="member in team" 
            :key="member.name"
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl p-6 sm:p-8 space-y-5 shadow-sm hover:shadow-lg flex flex-col justify-between relative group"
          >

            <div class="space-y-4">
              <!-- Avatar -->
              <div class="relative w-24 h-24 mx-auto">
                <img 
                  :src="member.avatar" 
                  :alt="member.name" 
                  class="w-24 h-24 rounded-3xl object-cover border-4 border-slate-100 dark:border-slate-800 shadow-md group-hover:scale-105 transition transform duration-300"
                />
              </div>

              <!-- Name & Title -->
              <div class="text-center space-y-1">
                <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ member.name }}</h3>
                <p class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ member.role }}</p>
              </div>

              <!-- Bio -->
              <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed text-center">
                {{ member.bio }}
              </p>

              <!-- Tech Stack Badges -->
              <div class="flex flex-wrap justify-center gap-1.5 pt-2">
                <span 
                  v-for="stk in member.tech_stack" 
                  :key="stk"
                  class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[11px] font-semibold"
                >
                  {{ stk }}
                </span>
              </div>
            </div>

            <!-- Social Links / Contact -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-center gap-3">
              <a 
                :href="`mailto:${member.email}`" 
                class="p-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 text-slate-600 dark:text-slate-300 rounded-xl transition cursor-pointer" 
                title="Email Engineer"
              >
                <Mail class="w-4 h-4" />
              </a>
              <a 
                :href="member.github" 
                target="_blank" 
                class="p-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-900 hover:text-white dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl transition cursor-pointer" 
                title="GitHub Profile"
              >
                <Github class="w-4 h-4" />
              </a>
              <a 
                :href="member.linkedin" 
                target="_blank" 
                class="p-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-blue-700 hover:text-white dark:hover:bg-blue-700 text-slate-600 dark:text-slate-300 rounded-xl transition cursor-pointer" 
                title="LinkedIn Profile"
              >
                <Linkedin class="w-4 h-4" />
              </a>
            </div>

          </div>
        </div>
      </section>

      <!-- ─── 5. Contact & Support CTA Banner ────────────────────────────── -->
      <section class="bg-linear-to-r from-blue-700 to-indigo-700 rounded-2xl p-8 sm:p-12 text-white shadow-xl shadow-blue-900/10 flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="space-y-2 max-w-xl">
          <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Have Questions or Need Assistance?</h2>
          <p class="text-xs sm:text-sm text-blue-100">Our team is here to assist candidates and hiring employers 24/7.</p>
        </div>

        <router-link to="/contact" class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-blue-600 hover:bg-blue-50 rounded-2xl text-xs font-bold transition shadow-md shrink-0 active:scale-95">
          <Send class="w-4 h-4" />
          <span>Contact Our Team</span>
        </router-link>
      </section>

    </main>

    <Footer />
  </div>
</template>
