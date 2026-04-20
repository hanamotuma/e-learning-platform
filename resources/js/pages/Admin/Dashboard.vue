<!-- resources/js/Pages/Admin/Dashboard.vue -->
<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

type StatCard = {
  label: string
  value: string
  growth: number
  isPositive: boolean
  accent: string
}

const props = defineProps<{
  stats: {
    total_sales: number
    sales_increase: number
    active_students: number
    student_increase: number
    total_courses: number
    course_increase: number
    total_revenue?: number
  }
  users: {
    data: Array<{
      id: number
      full_name?: string
      username?: string
      email?: string
      status?: string
      created_at_human?: string
      last_login?: string
      course_enrollment?: string
      avatar?: string
    }>
    current_page?: number
    last_page?: number
  }
  revenue_chart: Record<string, number>
  platform_overview?: {
    thoughts?: number
    students?: number
    total_enrollment?: number
    new_enrollments?: number
    total_courses?: number
    completion_rate?: number
    engagement_rate?: number
    average_grade?: number
  }
  course_performance?: {
    completion?: number
    engagement?: number
    average_grade?: number
  }
}>()

const page = usePage()
const authUser = computed(() => page.props.auth?.user as any)

const logout = () => {
  if (confirm('Are you sure you want to logout?')) {
    router.post(route('logout'))
  }
}

const formatCurrency = (value: number) =>
  new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    maximumFractionDigits: 0,
  }).format(value ?? 0)

const mainStats = computed<StatCard[]>(() => [
  {
    label: 'Total Sales',
    value: formatCurrency(props.stats?.total_sales ?? 0),
    growth: props.stats?.sales_increase ?? 0,
    isPositive: (props.stats?.sales_increase ?? 0) >= 0,
    accent: 'from-cyan-400 to-blue-500',
  },
  {
    label: 'Active Students',
    value: (props.stats?.active_students ?? 0).toLocaleString(),
    growth: props.stats?.student_increase ?? 0,
    isPositive: (props.stats?.student_increase ?? 0) >= 0,
    accent: 'from-purple-400 to-fuchsia-500',
  },
  {
    label: 'Total Courses',
    value: String(props.stats?.total_courses ?? 0),
    growth: props.stats?.course_increase ?? 0,
    isPositive: (props.stats?.course_increase ?? 0) >= 0,
    accent: 'from-amber-400 to-orange-500',
  },
])

const userName = computed(() => {
  const name = authUser.value?.full_name || authUser.value?.name || 'Admin'
  return String(name).split(' ')[0]
})

const statusClass = (status?: string) => {
  const s = String(status || 'offline').toLowerCase()
  if (s === 'online') return 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30'
  if (s === 'active') return 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30'
  if (s === 'pending') return 'bg-amber-500/15 text-amber-300 border-amber-500/30'
  return 'bg-slate-500/15 text-slate-300 border-slate-500/30'
}

const progressWidth = (value?: number) => `${Math.min(Math.max(Number(value ?? 0), 0), 100)}%`
</script>

<template>
  <div class="min-h-screen bg-[#09091a] text-white">
    <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(168,85,247,0.22),_transparent_25%),radial-gradient(circle_at_top_right,_rgba(59,130,246,0.18),_transparent_24%),radial-gradient(circle_at_bottom_right,_rgba(16,185,129,0.12),_transparent_22%)]">
      <div class="min-h-screen bg-black/20 backdrop-blur-[2px]">
        <div class="mx-auto max-w-[1500px] p-4">
          <div class="rounded-[28px] border border-white/10 bg-[#0f1230]/80 shadow-[0_20px_80px_rgba(0,0,0,0.5)] overflow-hidden">
            <div class="grid grid-cols-[240px_1fr]">
              <aside class="bg-[#11153a]/95 border-r border-white/10 min-h-[calc(100vh-2rem)] flex flex-col">
                <div class="p-5 border-b border-white/10 flex items-center gap-3">
                  <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-cyan-400 to-purple-500 flex items-center justify-center font-black text-[#081126]">
                    e
                  </div>
                  <div>
                    <div class="text-lg font-bold leading-tight">E-Learning</div>
                    <div class="text-sm text-slate-300">Admin</div>
                  </div>
                </div>

                <nav class="p-4 space-y-2">
                  <Link href="#" class="flex items-center gap-3 rounded-2xl bg-violet-600/25 border border-violet-500/30 px-4 py-3 text-sm font-medium text-white">
                    <span class="w-8 h-8 rounded-xl bg-violet-500/30 flex items-center justify-center">⌂</span>
                    Dashboard
                  </Link>
                  <Link href="#" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <span class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center">◌</span>
                    Users
                  </Link>
                  <Link href="#" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <span class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center">▣</span>
                    Courses
                  </Link>
                  <Link href="#" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <span class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center">◔</span>
                    Analytics
                  </Link>
                  <Link href="#" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <span class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center">⚙</span>
                    Settings
                  </Link>
                </nav>

                <div class="mt-auto p-4 border-t border-white/10 space-y-2">
                  <Link :href="route('profile.edit')" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <span class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center">◫</span>
                    Profile
                  </Link>
                  <Link href="#" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-300 hover:bg-white/5">
                    <span class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center">◉</span>
                    Notifications
                  </Link>

                  <div class="flex items-center justify-between rounded-2xl bg-white/5 px-4 py-3 mt-4">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-gradient-to-br from-fuchsia-500 to-cyan-500"></div>
                      <div class="leading-tight">
                        <div class="text-sm font-semibold">{{ authUser?.full_name || authUser?.name || 'Admin' }}</div>
                        <div class="text-xs text-slate-400">Administrator</div>
                      </div>
                    </div>
                    <button @click="logout" class="text-slate-300 hover:text-white text-sm">⇢</button>
                  </div>
                </div>
              </aside>

              <main class="p-5">
                <header class="flex items-center justify-between gap-4 mb-5">
                  <div class="flex items-center gap-4 flex-1">
                    <div class="text-3xl font-semibold">Welcome back, Admin</div>
                    <div class="hidden md:flex items-center gap-2 flex-1 max-w-[360px] ml-4">
                      <div class="relative w-full">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">⌕</span>
                        <input
                          type="text"
                          placeholder="Search..."
                          class="w-full rounded-2xl border border-white/10 bg-white/5 pl-11 pr-4 py-3 text-sm outline-none focus:ring-2 focus:ring-violet-500/40"
                        />
                      </div>
                    </div>
                  </div>

                  <div class="flex items-center gap-3">
                    <button class="w-10 h-10 rounded-full bg-white/5 border border-white/10">◈</button>
                    <button class="w-10 h-10 rounded-full bg-white/5 border border-white/10">🔔</button>
                    <button class="w-10 h-10 rounded-full bg-white/5 border border-white/10">⚙</button>
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-pink-500 to-purple-500 overflow-hidden"></div>
                  </div>
                </header>

                <section class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                  <div
                    v-for="stat in mainStats"
                    :key="stat.label"
                    class="relative overflow-hidden rounded-2xl border border-white/10 bg-[#171b44]/80 p-5 shadow-[0_10px_30px_rgba(0,0,0,0.25)]"
                  >
                    <div :class="`absolute inset-x-0 bottom-0 h-16 bg-gradient-to-r ${stat.accent} opacity-25 blur-2xl`"></div>
                    <div class="relative flex items-start justify-between gap-3">
                      <div>
                        <div class="text-sm text-slate-400 mb-2">{{ stat.label }}</div>
                        <div class="text-3xl font-bold tracking-tight">{{ stat.value }}</div>
                      </div>
                      <div class="rounded-xl px-3 py-1 text-xs font-semibold border" :class="stat.isPositive ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30' : 'bg-rose-500/15 text-rose-300 border-rose-500/30'">
                        {{ stat.isPositive ? '+' : '-' }}{{ Math.abs(stat.growth) }}%
                      </div>
                    </div>
                  </div>
                </section>

                <section class="grid grid-cols-12 gap-4">
                  <div class="col-span-12 xl:col-span-8 rounded-2xl border border-white/10 bg-[#15193f]/80 p-4">
                    <div class="flex items-center justify-between mb-3">
                      <h2 class="text-lg font-semibold">User Management</h2>
                      <div class="flex items-center gap-2 text-xs text-slate-400">
                        <span class="rounded-lg bg-white/5 px-3 py-1">All Status</span>
                        <span class="rounded-lg bg-white/5 px-3 py-1">Export</span>
                      </div>
                    </div>

                    <div class="overflow-x-auto rounded-xl border border-white/10">
                      <table class="w-full text-left text-sm">
                        <thead class="bg-white/5 text-slate-400">
                          <tr>
                            <th class="px-4 py-3 font-medium">User</th>
                            <th class="px-4 py-3 font-medium">Email</th>
                            <th class="px-4 py-3 font-medium">Status</th>
                            <th class="px-4 py-3 font-medium">Last Login</th>
                            <th class="px-4 py-3 font-medium">Course Enrollment</th>
                            <th class="px-4 py-3 font-medium text-right">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr
                            v-for="row in (users?.data ?? [])"
                            :key="row.id"
                            class="border-t border-white/5 hover:bg-white/5"
                          >
                            <td class="px-4 py-3">
                              <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-cyan-400 to-purple-500 flex items-center justify-center font-bold text-xs">
                                  {{ (row.full_name || row.username || 'U').charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                  <div class="font-medium">{{ row.full_name || row.username || 'Unnamed' }}</div>
                                  <div class="text-xs text-slate-400">ID {{ row.id }}</div>
                                </div>
                              </div>
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ row.email || '—' }}</td>
                            <td class="px-4 py-3">
                              <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs" :class="statusClass(row.status)">
                                {{ row.status || 'Offline' }}
                              </span>
                            </td>
                            <td class="px-4 py-3 text-slate-300">{{ row.last_login || 'Today' }}</td>
                            <td class="px-4 py-3">
                              <span class="inline-flex rounded-full bg-violet-500/15 border border-violet-500/25 px-3 py-1 text-xs text-violet-300">
                                {{ row.course_enrollment || 'No enrollment' }}
                              </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                              <button class="text-slate-400 hover:text-white">⋯</button>
                            </td>
                          </tr>
                          <tr v-if="!(users?.data?.length)">
                            <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                              No users found.
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <div class="mt-3 flex items-center justify-between text-xs text-slate-400">
                      <div>Page {{ users?.current_page ?? 1 }} of {{ users?.last_page ?? 1 }}</div>
                      <div class="flex items-center gap-2">
                        <button class="rounded-lg bg-white/5 px-3 py-1">‹</button>
                        <button class="rounded-lg bg-white/5 px-3 py-1">1</button>
                        <button class="rounded-lg bg-white/5 px-3 py-1">›</button>
                      </div>
                    </div>
                  </div>

                  <div class="col-span-12 xl:col-span-4 space-y-4">
                    <div class="rounded-2xl border border-white/10 bg-[#15193f]/80 p-4">
                      <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold">Sales & Enrollment</h3>
                        <span class="text-xs text-slate-400">30d</span>
                      </div>
                      <div class="h-44 rounded-xl bg-gradient-to-b from-violet-500/20 to-transparent border border-white/10 flex items-end p-3">
                        <div class="w-full h-full rounded-lg bg-[linear-gradient(180deg,rgba(139,92,246,0.2),rgba(59,130,246,0.1))] flex items-end p-2">
                          <div class="w-full h-[72%] rounded-xl bg-[linear-gradient(180deg,#a855f7,#3b82f6)] opacity-90"></div>
                        </div>
                      </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#15193f]/80 p-4">
                      <h3 class="font-semibold mb-3">Online Status</h3>
                      <div class="flex items-center justify-between">
                        <div>
                          <div class="text-3xl font-bold">635</div>
                          <div class="text-xs text-slate-400">Online now</div>
                        </div>
                        <div>
                          <div class="text-3xl font-bold text-right">635</div>
                          <div class="text-xs text-slate-400 text-right">Online now</div>
                        </div>
                      </div>
                      <div class="mt-4 grid grid-cols-3 gap-2 text-xs">
                        <button class="rounded-lg bg-white/5 py-2">Mail</button>
                        <button class="rounded-lg bg-white/5 py-2">More</button>
                        <button class="rounded-lg bg-white/5 py-2">Share</button>
                      </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#15193f]/80 p-4">
                      <div class="flex items-center justify-between mb-2">
                        <h3 class="font-semibold">Sales & Enrollment</h3>
                        <span class="text-xs text-slate-400">Last 30 days</span>
                      </div>
                      <div class="h-32 rounded-xl bg-white/5 border border-white/10 p-3 flex items-end gap-2">
                        <div v-for="n in 12" :key="n" class="flex-1 rounded-t-md bg-gradient-to-t from-purple-600 to-cyan-400" :style="{ height: `${20 + (n % 6) * 12}%` }"></div>
                      </div>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-[#15193f]/80 p-4">
                      <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold">Revenue & Sales</h3>
                        <span class="text-xs text-slate-400">Side view</span>
                      </div>
                      <div class="flex items-center gap-4">
                        <div class="relative h-40 w-40 rounded-full border-[14px] border-violet-500/30 border-t-cyan-400 border-r-fuchsia-500 border-b-purple-500 border-l-blue-500 flex items-center justify-center">
                          <div class="text-center">
                            <div class="text-xs text-slate-400">Sales</div>
                            <div class="font-bold text-lg">$160,271</div>
                          </div>
                        </div>
                        <div class="flex-1 space-y-3 text-sm">
                          <div class="flex justify-between"><span class="text-slate-400">Web Dev</span><span>$7.2k</span></div>
                          <div class="flex justify-between"><span class="text-slate-400">Associate</span><span>$5.4k</span></div>
                          <div class="flex justify-between"><span class="text-slate-400">Activity</span><span>$5.2k</span></div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-span-12 xl:col-span-7 rounded-2xl border border-white/10 bg-[#15193f]/80 p-4">
                    <div class="flex items-center justify-between mb-4">
                      <h3 class="font-semibold">Course Performance</h3>
                      <span class="text-xs text-slate-400">Monthly</span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                      <div class="rounded-2xl bg-white/5 border border-white/10 p-4">
                        <div class="h-32 rounded-xl bg-[linear-gradient(180deg,rgba(168,85,247,.25),rgba(59,130,246,.05))] border border-white/10"></div>
                      </div>

                      <div class="space-y-4">
                        <div>
                          <div class="flex justify-between text-xs text-slate-400 mb-1">
                            <span>Completion Rate</span>
                            <span>{{ props.course_performance?.completion ?? 90 }}%</span>
                          </div>
                          <div class="h-2 rounded-full bg-white/10 overflow-hidden">
                            <div class="h-full bg-emerald-500" :style="{ width: progressWidth(props.course_performance?.completion ?? 90) }"></div>
                          </div>
                        </div>

                        <div>
                          <div class="flex justify-between text-xs text-slate-400 mb-1">
                            <span>Engagement</span>
                            <span>{{ props.course_performance?.engagement ?? 77 }}%</span>
                          </div>
                          <div class="h-2 rounded-full bg-white/10 overflow-hidden">
                            <div class="h-full bg-cyan-500" :style="{ width: progressWidth(props.course_performance?.engagement ?? 77) }"></div>
                          </div>
                        </div>

                        <div>
                          <div class="flex justify-between text-xs text-slate-400 mb-1">
                            <span>Average Grade</span>
                            <span>{{ props.course_performance?.average_grade ?? 88 }}%</span>
                          </div>
                          <div class="h-2 rounded-full bg-white/10 overflow-hidden">
                            <div class="h-full bg-violet-500" :style="{ width: progressWidth(props.course_performance?.average_grade ?? 88) }"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="col-span-12 xl:col-span-5 rounded-2xl border border-white/10 bg-[#15193f]/80 p-4">
                    <div class="flex items-center justify-between mb-4">
                      <h3 class="font-semibold">Platform Overview</h3>
                      <span class="text-xs text-slate-400">This week</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                      <div class="rounded-2xl bg-white/5 border border-white/10 p-4">
                        <div class="text-2xl font-bold">{{ props.platform_overview?.thoughts ?? 9564 }}</div>
                        <div class="text-xs text-slate-400">Thoughts</div>
                      </div>
                      <div class="rounded-2xl bg-white/5 border border-white/10 p-4">
                        <div class="text-2xl font-bold">{{ props.platform_overview?.students ?? 853 }}</div>
                        <div class="text-xs text-slate-400">Students</div>
                      </div>
                      <div class="rounded-2xl bg-white/5 border border-white/10 p-4">
                        <div class="text-2xl font-bold">{{ props.platform_overview?.total_enrollment ?? 7685 }}</div>
                        <div class="text-xs text-slate-400">Total Enrollment</div>
                      </div>
                      <div class="rounded-2xl bg-white/5 border border-white/10 p-4">
                        <div class="text-2xl font-bold">{{ props.platform_overview?.new_enrollments ?? 197 }}</div>
                        <div class="text-xs text-slate-400">New Enrollments</div>
                      </div>
                      <div class="rounded-2xl bg-white/5 border border-white/10 p-4">
                        <div class="text-2xl font-bold">{{ props.platform_overview?.total_courses ?? 125 }}</div>
                        <div class="text-xs text-slate-400">Total Courses</div>
                      </div>
                      <div class="rounded-2xl bg-white/5 border border-white/10 p-4">
                        <div class="text-2xl font-bold">{{ formatCurrency(props.stats?.total_sales ?? 0) }}</div>
                        <div class="text-xs text-slate-400">Revenue</div>
                      </div>
                    </div>
                  </div>
                </section>
              </main>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
* {
  scrollbar-width: thin;
  scrollbar-color: rgba(139, 92, 246, 0.55) rgba(15, 18, 48, 0.8);
}
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}
::-webkit-scrollbar-track {
  background: rgba(15, 18, 48, 0.8);
}
::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, rgba(168, 85, 247, 0.85), rgba(59, 130, 246, 0.85));
  border-radius: 999px;
}
</style>