<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DA-PRDP • ISM Portal</title>
  <meta name="description" content="Official DA-PRDP Implementation Support Mission (ISM) Portal for agendas, meetings, site visits, reports information of the Philippine Rural Development Project.">
  <meta name="keywords" content="DA-PRDP, PRDP, PRDP ISM, ISM Portal, Implementation Support Mission, Philippine Rural Development Project, Department of Agriculture, DA PRDP, WB ISM">
  <meta name="author" content="Department of Agriculture - Philippine Rural Development Project">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="{{ config('app.url') }}">

  <!-- Fonts: Inter & Roboto Mono for AGT engineering telemetry -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Roboto+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite(['resources/css/landing.css', 'resources/js/app.js'])

  <!-- Alpine.js -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.10/dist/cdn.min.js" defer></script>

</head>

<body class="bg-slate-50 text-slate-900 antialiased selection:bg-emerald-100 selection:text-emerald-900 flex flex-col min-h-screen">

  <!-- ================= REPUBLIC OFFICIAL TOP BAR ================= -->
  <aside aria-label="Official Republic Banner" class="bg-govnavy-950 text-slate-300 text-xs py-2.5 px-4 border-b border-slate-800">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">

      <!-- Agency Information -->
      <div class="flex items-center gap-2 min-w-0">
        <span class="text-slate-400 hidden sm:inline whitespace-nowrap">
          Republic of the Philippines
        </span>

        <span class="text-slate-600 hidden sm:inline">•</span>

        <span class="font-medium text-white truncate">
          Department of Agriculture • Philippine Rural Development Project
        </span>
      </div>

      <!-- Philippine Standard Time (PST) indicator -->
      <div class="flex items-center text-xs"
        x-data="{ time: '--:--:-- PST' }"
        x-init="time = new Date().toLocaleTimeString('en-US', { timeZone: 'Asia/Manila', hour12: true }); setInterval(() => { time = new Date().toLocaleTimeString('en-US', { timeZone: 'Asia/Manila', hour12: true }) }, 1000)">
        <div class="flex items-center gap-1.5 text-slate-300 font-mono text-[11px]">
          <i class="fa-solid fa-clock w-3.5 h-3.5 text-phgold-400 shrink-0"></i>
          <span class="whitespace-nowrap">
            PST: <strong x-text="time" class="text-white"></strong>
          </span>
        </div>
      </div>

    </div>
  </aside>

  <!-- ================= MAIN NAVIGATION ================= -->
  <header x-data="{ mobileOpen: false }" class="sticky top-0 z-50 bg-white/95 backdrop-blur-xl border-b border-slate-200/80 shadow-[0_2px_12px_rgba(15,23,42,0.06)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-[60px] sm:h-[76px]">

        <!-- Agency Logo & Title -->
        <a href="#banner-section" class="flex items-center gap-2 sm:gap-3.5 group min-w-0">
          <div class="relative w-10 h-10 sm:w-12 sm:h-12 shrink-0 rounded-xl bg-gradient-to-br from-emerald-700 via-emerald-800 to-slate-950 flex items-center justify-center shadow-lg shadow-emerald-900/15 ring-1 ring-emerald-700/20 transition-all duration-300 group-hover:shadow-emerald-900/25 group-hover:scale-[1.03]">
            <i class="fa-solid fa-shield-alt w-5 h-5 sm:w-6 sm:h-6 text-emerald-200"></i>
            <span class="absolute -right-0.5 -bottom-0.5 w-2.5 h-2.5 rounded-full bg-phgold-500 ring-2 ring-white"></span>
          </div>

          <div class="min-w-0">
            <div class="flex items-center gap-1">
              <span class="text-[17px] sm:text-[19px] font-black tracking-tight text-slate-900">
                ISM <span class="text-phgold-600">PORTAL</span>
              </span>
            </div>

            <div class="hidden sm:flex items-center gap-1.5 mt-0.5">
              <span class="text-[9px] sm:text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400">
                Philippine Rural Development Project
              </span>
            </div>
          </div>
        </a>

        <!-- Desktop Navigation -->
        <nav class="hidden xl:flex items-center gap-0.5 ml-auto mr-5">

          <a href="#banner-section" class="group inline-flex items-center gap-2 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 transition-all duration-200">
            <span>Overview</span>
          </a>

          <!-- Agenda -->
          <a href="#agenda-section" class="group inline-flex items-center gap-2 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-700 hover:text-emerald-700 hover:bg-emerald-50 transition-all duration-200">
            <span>Agenda</span>
          </a>

          <a href="#meetings-section" class="group inline-flex items-center gap-2 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 transition-all duration-200">
            <span>Meetings</span>
            <span class="text-[10px] text-slate-400">{{ $meetings->count() }}</span>
          </a>

          <a href="#site-visits-section" class="group inline-flex items-center gap-2 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 transition-all duration-200">
            <span>Virtual Site Visits</span>
            <span class="text-[10px] text-slate-400">{{ $siteVisits->count() }}</span>
          </a>

          <a href="#gallery" class="group inline-flex items-center gap-2 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 transition-all duration-200">
            <span>Gallery</span>
          </a>

          <a href="#reports-section" class="group inline-flex items-center gap-2 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 transition-all duration-200">
            <span>Reports</span>
          </a>

        </nav>

        <!-- Right Actions -->
        <div class="flex items-center gap-1.5 sm:gap-2.5">
          @if($activeMission && $activeMission->enable_registration)
          <a href="#registration-section" class="hidden sm:inline-flex items-center gap-1 px-3 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-lg shadow-md shadow-emerald-600/15 hover:shadow-lg hover:shadow-emerald-600/20 transition-all duration-200">
            <i class="fa-solid fa-user-plus w-3 h-3"></i>
            Register
          </a>
          @endif

          <!-- Mobile Menu Toggle -->
          <button type="button" class="xl:hidden inline-flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 hover:text-emerald-700 hover:border-emerald-200 transition-all duration-200" aria-label="Toggle Navigation" @click="mobileOpen = !mobileOpen">
            <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 sm:w-5 sm:h-5">
              <line x1="3" y1="12" x2="21" y2="12"></line>
              <line x1="2" y1="6" x2="22" y2="6"></line>
              <line x1="7" y1="18" x2="17" y2="18"></line>
            </svg>
            <svg x-show="mobileOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 sm:w-5 sm:h-5">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>

        </div>
      </div>
    </div>

    <!-- Mobile Drawer -->
    <div x-show="mobileOpen" x-cloak class="xl:hidden border-t border-slate-100 bg-white shadow-xl shadow-slate-900/5">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">
        <div class="space-y-1">
          <a href="#banner-section" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-800 hover:bg-emerald-50 hover:text-emerald-700 transition">
            <i class="fa-solid fa-gauge-high w-4 h-4"></i>
            <span>Mission Overview</span>
          </a>

          <!-- Dynamic Agenda -->
          <a href="#agenda-section" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition">
            <span class="flex items-center gap-3">
              <i class="fa-solid fa-calendar-days w-4 h-4 text-emerald-600"></i>
              <span>Agenda</span>
            </span>

            <span class="inline-flex items-center justify-center min-w-[24px] h-5 px-1.5 rounded-full bg-emerald-100 text-[10px] font-bold text-emerald-700">
              {{ $meetings->count() + $siteVisits->count() }}
            </span>
          </a>

          <a href="#meetings-section" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-emerald-700 transition">
            <span class="flex items-center gap-3">
              <i class="fa-solid fa-display w-4 h-4"></i>
              <span>Meetings</span>
            </span>
            <span class="text-[10px] font-bold text-slate-400">{{ $meetings->count() }}</span>
          </a>

          <a href="#site-visits-section" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-emerald-700 transition">
            <span class="flex items-center gap-3">
              <i class="fa-solid fa-map-pin w-4 h-4"></i>
              <span>Virtual Site Visits</span>
            </span>
            <span class="text-[10px] font-bold text-slate-400">{{ $siteVisits->count() }}</span>
          </a>

          @if($activeMission && $activeMission->enable_registration)
          <a href="#registration-section" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-emerald-700 transition">
            <i class="fa-solid fa-user-plus w-4 h-4"></i>
            <span>Registration</span>
          </a>
          @endif

          <a href="#gallery" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-emerald-700 hover:bg-emerald-50 transition">
            <i class="fa-solid fa-images w-4 h-4"></i>
            <span>Gallery</span>
          </a>

          <a href="#reports-section" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-emerald-700 transition">
            <i class="fa-solid fa-files w-4 h-4"></i>
            <span>Reports and Presentations</span>
          </a>

        </div>

        @if($activeMission && $activeMission->enable_registration)
        <div class="mt-4 pt-4 border-t border-slate-100">
          <a href="#registration-section" class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md shadow-emerald-600/15 transition">
            <i class="fa-solid fa-user-plus w-4 h-4"></i>
            Register
          </a>
        </div>
        @endif

      </div>
    </div>
  </header>

  <main class="flex-grow">
    <!-- ================= ACTIVE MISSION HERO BANNER ================= -->
    <section id="banner-section" class="relative pt-6 pb-12 border-b border-slate-200 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative rounded-3xl overflow-hidden border border-slate-800 shadow-xl bg-govnavy-950 min-h-[420px] flex items-end">
          <img src="https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=1800&q=80"
            alt="Mission Banner"
            class="absolute inset-0 w-full h-full object-cover" />

          <div class="relative z-10 p-6 sm:p-10 lg:p-12 w-full text-white space-y-4">
            <div class="flex flex-wrap items-center gap-3">
              <a href="#agenda-section" class="px-5 py-2.5 rounded-xl bg-slate-700/90 hover:bg-slate-600 text-white text-xs sm:text-sm font-semibold shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-calendar-days w-4 h-4 text-slate-300"></i>
                <span>Agenda</span>
              </a>

              <a href="#site-visits-section" class="px-5 py-2.5 rounded-xl bg-slate-700/90 hover:bg-slate-600 text-white text-xs sm:text-sm font-semibold shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-map-pin w-4 h-4 text-slate-300"></i>
                <span>Virtual Site Visits</span>
              </a>

              <a href="#meetings-section" class="px-5 py-2.5 rounded-xl bg-slate-700/90 hover:bg-slate-600 text-white text-xs sm:text-sm font-semibold shadow-lg transition flex items-center gap-2">
                <i class="fa-solid fa-video w-4 h-4 text-slate-300"></i>
                <span>Meeting Links</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ================= DYNAMIC AGENDA SECTION ================= -->
    <!-- ================= MISSION AGENDA ================= -->
    <section id="agenda-section" class="py-16 bg-white border-b border-slate-200"
      x-data="agendaTimeline()"
      @fullscreenchange.window="isFullscreen = !!document.fullscreenElement">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 pb-4 border-b border-slate-200 gap-4">
          <div>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Mission Agenda</h2>
            <p class="text-slate-600 text-sm mt-1 max-w-2xl">
              Cross-synchronized with mission database records.
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <button @click="sheetEmbedOpen = !sheetEmbedOpen"
                  :class="sheetEmbedOpen ? 'bg-slate-200 text-slate-700' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                  class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 transition">
              <i class="fa-solid fa-layer-group w-3.5 h-3.5"></i>
              <span x-text="sheetEmbedOpen ? 'Hide Agenda' : 'Toggle Agenda'"></span>
            </button>
          </div>
        </div>

        <!-- Mission Agenda Embed Container -->
        <div x-show="sheetEmbedOpen" x-cloak
             class="mb-8 rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-inner"
             x-ref="sheetEmbedContainer">
          <div class="bg-slate-100 px-4 py-3 border-b border-slate-200 flex items-center justify-between text-xs text-slate-600">
          <div class="flex items-center gap-2 font-semibold">
            <i class="fa-solid fa-file-excel w-4 h-4 text-emerald-600"></i>
             <span>Mission Agenda: <code>{{ $bannerMission?->agenda_link ?? 'N/A' }}</code>
          </div>
          <div class="flex items-center gap-1">
             <a href="{{ $bannerMission?->agenda_link ?? '#' }}" target="_blank" class="text-slate-400 hover:text-emerald-700 p-1 rounded-md" title="Open in new tab">
              <i class="fa-solid fa-up-right-from-square w-4 h-4"></i>
            </a>
            <button @click="toggleAgendaFullscreen()" class="text-slate-400 hover:text-emerald-700 p-1 rounded-md" title="Toggle fullscreen">
              <i x-show="!isFullscreen" class="fa-solid fa-expand w-4 h-4"></i>
              <i x-show="isFullscreen" class="fa-solid fa-compress w-4 h-4" x-cloak></i>
            </button>
            <button @click="sheetEmbedOpen = false" class="text-slate-400 hover:text-slate-700 p-1 rounded-md">
              <i class="fa-solid fa-x w-4 h-4"></i>
            </button>
          </div>
          </div>
          <div class="p-0">
            <div class="w-full overflow-hidden">
              <iframe
                id="agenda-iframe"
                 src="{{ $bannerMission ? $bannerMission->agenda_link . '?widget=true&headers=false' : '#' }}"
                class="w-full h-[600px] border-0"
                title="Mission Agenda"
                scrolling="yes"
                loading="lazy">
              </iframe>
            </div>
          </div>
        </div>

        <!-- Date & Type Filters -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
          <div class="flex items-center gap-1.5 overflow-x-auto pb-2 custom-scrollbar" id="agendaDateBar">
            <button @click="currentDate = 'all'"
                    :class="currentDate === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                    class="agenda-d-btn px-3 py-1.5 rounded-xl text-xs font-semibold shadow-sm transition">
              All Dates (<span x-text="allActivities.length"></span> Activities)
            </button>
            <template x-for="date in uniqueDates" :key="date">
              <button @click="currentDate = date"
                      :class="currentDate === date ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                      class="agenda-d-btn px-3 py-1.5 rounded-xl text-xs font-semibold shadow-sm transition"
                      x-text="formatDate(date)"></button>
            </template>
          </div>

          <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-medium self-start lg:self-auto border border-slate-200 shrink-0">
            <button @click="currentType = 'all'"
                    :class="currentType === 'all' ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-slate-600 hover:text-slate-900'"
                    class="agenda-t-btn px-3 py-1 rounded-lg transition">
              All Items
            </button>
            <button @click="currentType = 'meeting'"
                    :class="currentType === 'meeting' ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-slate-600 hover:text-slate-900'"
                    class="agenda-t-btn px-3 py-1 rounded-lg transition">
              Meetings Only (<span x-text="meetings.length"></span>)
            </button>
            <button @click="currentType = 'site_visit'"
                    :class="currentType === 'site_visit' ? 'bg-white text-slate-900 shadow-sm font-semibold' : 'text-slate-600 hover:text-slate-900'"
                    class="agenda-t-btn px-3 py-1 rounded-lg transition">
              Site Visits Only (<span x-text="siteVisits.length"></span>)
            </button>
          </div>
        </div>

        <!-- Rendered Combined Timeline Cards -->
        <div id="dynamicAgendaContainer" class="space-y-3.5">
          <template x-if="filteredActivities.length === 0" x-cloak>
            <div class="p-8 text-center text-xs text-slate-400 bg-slate-50 rounded-2xl border border-slate-200">No activities scheduled for this date and filter.</div>
          </template>

          <template x-for="item in filteredActivities" :key="item.id">
            <div :class="`bg-white border rounded-2xl p-4 sm:p-5 shadow-sm transition-all hover:border-emerald-300 ${item.type === 'meeting' ? 'border-slate-200' : 'border-emerald-200/90'}`">
              <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div class="flex items-start gap-3.5">
                  <div :class="`w-10 h-10 rounded-xl flex flex-col items-center justify-center shrink-0 border ${item.type === 'meeting' ? 'bg-blue-50 text-blue-700 border-blue-100' : 'bg-emerald-50 text-emerald-700 border-emerald-200'}`">
                    <i :class="`fa-solid fa-${item.type === 'meeting' ? 'video' : 'camera'} w-4 h-4`"></i>
                    <span class="text-[8px] font-bold uppercase mt-0.5" x-text="item.type === 'meeting' ? 'Meet' : 'Geotag'"></span>
                  </div>
                  <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                      <span :class="`px-2 py-0.5 rounded text-[10px] font-bold ${item.type === 'meeting' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800'}`" x-text="item.categoryLabel"></span>
                      <span class="text-xs text-slate-500 font-medium">
                        <i class="fa-solid fa-clock w-3 h-3 inline mr-1 text-slate-400"></i>
                        <span x-text="item.time"></span>
                      </span>
                      <span class="text-xs text-slate-400">•</span>
                      <span class="text-xs text-slate-500 font-medium" x-text="formatDate(item.date)"></span>
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-slate-900" x-text="item.title"></h3>
                    <p class="text-xs text-slate-600 mt-1 max-w-3xl leading-relaxed" x-text="item.description"></p>
                    <div class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
                      <i class="fa-solid fa-map-pin w-3.5 h-3.5 text-slate-400"></i>
                      <span x-text="item.venue"></span>
                    </div>
                  </div>
                </div>
                <div class="flex items-center md:flex-col md:items-end gap-2 shrink-0">
                  <template x-if="item.type === 'meeting'">
                    <div class="flex flex-col md:items-end gap-2">
                      <a :href="item.link" target="_blank" class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-video w-3.5 h-3.5"></i> Join Session
                      </a>
                      <span class="text-[10px] text-slate-400 font-mono" x-text="`ID: ${item.meeting_id}`"></span>
                    </div>
                  </template>
                  <template x-if="item.type === 'site_visit'">
                    <div class="flex flex-col md:items-end gap-2">
                      <button @click="openGeotagModal(item.sp_id)" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-camera w-3.5 h-3.5"></i> Inspect Photos
                      </button>
                      <span class="text-[10px] text-slate-400">AGT Station Dossier</span>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </template>
        </div>

      </div>
    </section>

    <!-- ================= MEETINGS DIRECTORY ================= -->
    <section id="meetings-section" class="py-8 bg-slate-50 border-b border-slate-200"
      x-data="meetingsDirectory()"
      @keydown.escape.window="descModalOpen = false">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
          <div>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Meetings Directory</h2>
            <p class="text-slate-600 text-sm mt-1"> Virtual breakout links, meeting IDs, passcodes, and attached reports mapped to <code>meetings</code>. </p>
          </div>
          <div class="w-full md:w-72 relative">
            <i class="fa-solid fa-search w-4 h-4 absolute left-3 top-3 text-slate-400"></i>
            <input type="text" x-model="searchQuery" placeholder="Search {{ $meetings->count() }} meetings..." class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-300 text-sm bg-white focus:border-emerald-600 outline-none" />
          </div>
        </div>
        <div class="flex items-center gap-2 mb-4" id="meetingsCarouselWrapper">
          <button x-show="isCarousel" @click="scrollPrev()" class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-white text-slate-700 hover:bg-slate-100 border border-slate-200 shadow">
            <i class="fa-solid fa-chevron-left text-sm"></i>
          </button>
          <div :class="isCarousel ? 'flex flex-1 overflow-x-auto scroll-px-4 gap-4 pb-4 custom-scrollbar scroll-smooth' : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5'"
               id="meetingsGridContainer" x-ref="gridContainer">
            <template x-for="m in filteredMeetings" :key="m.id">
              <div :class="isCarousel ? 'bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition shrink-0 w-full sm:w-[calc(50%-1rem)] lg:w-[calc(100%/3-1rem)] scroll-snap-align-start flex flex-col min-h-[280px]' : 'bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition h-full flex flex-col'">
                <span :class="`w-fit inline-flex items-center px-2.5 py-1 rounded-full text-[9px] font-bold border ${getTypeBadgeClass(m.type)}`" x-text="m.type"></span>
                <h3 class="text-sm font-bold text-slate-900 line-clamp-2 mt-1" x-text="m.title"></h3>
                <div class="flex items-end gap-1 mt-1">
                  <p class="text-xs text-slate-600 line-clamp-1 flex-1 overflow-hidden" x-text="(m.description || '—')"></p>
                  <button x-show="(m.description || '').length > 50"
                          @click="openModal(m)"
                          class="shrink-0 w-6 h-6 flex items-center justify-center text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg">
                    <i class="fa-solid fa-chevron-down"></i>
                  </button>
                </div>
                <div class="p-2.5 bg-slate-50 rounded-xl space-y-1 font-mono text-[11px] border border-slate-100 mt-3 min-h-[120px]">
                  <div class="flex justify-between">
                    <span class="text-slate-400">Date:</span>
                    <span class="font-bold text-slate-800" x-text="formatDate(m.date) || '—'"></span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-400">Duration:</span>
                    <span class="font-bold text-slate-800" x-text="(formatTime(m.time_from) || '—') + ' - ' + (formatTime(m.time_to) || '—')"></span>
                  </div>
                  <div class="flex justify-between font-sans text-slate-600">
                    <span class="text-slate-400">Venue:</span>
                    <span class="truncate max-w-[160px]" x-text="m.venue || '—'"></span>
                  </div>
                  <template x-if="m.meeting_id || m.passcode">
                    <div class="mt-1 space-y-1">
                      <template x-if="m.meeting_id">
                        <div class="flex justify-between items-center">
                          <span class="text-slate-400">Meeting ID:</span>
                          <span class="font-bold text-slate-800 flex items-center gap-1">
                            <span x-text="m.meeting_id"></span>
                            <button @click="copyToClipboard(m.meeting_id, 'Copied Meeting ID!')" class="text-slate-400 hover:text-emerald-700">
                              <i class="fa-solid fa-copy w-3 h-3"></i>
                            </button>
                          </span>
                        </div>
                      </template>
                      <template x-if="m.passcode">
                        <div class="flex justify-between items-center">
                          <span class="text-slate-400">Passcode:</span>
                          <span class="font-bold text-slate-800 flex items-center gap-1">
                            <span x-text="m.passcode"></span>
                            <button @click="copyToClipboard(m.passcode, 'Copied Passcode!')" class="text-slate-400 hover:text-emerald-700">
                              <i class="fa-solid fa-copy w-3 h-3"></i>
                            </button>
                          </span>
                        </div>
                      </template>
                    </div>
                  </template>
                </div>
                <a :href="m.link || ''" target="_blank"
                   :class="isCarousel ? 'mt-3' : 'mt-auto'"
                   class="w-full py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center space-x-1.5 transition">
                  <i class="fa-solid fa-video w-3.5 h-3.5"></i>
                  <span>Join Room</span>
                </a>
              </div>
            </template>
            <div x-show="filteredMeetings.length === 0" class="col-span-full p-8 text-center text-xs text-slate-400 bg-white rounded-2xl border border-slate-200" x-cloak>
              No meetings match your query.
            </div>
          </div>
          <button x-show="isCarousel" @click="scrollNext()" class="flex-shrink-0 w-10 h-10 flex items-center justify-center rounded-xl bg-white text-slate-700 hover:bg-slate-100 border border-slate-200 shadow">
            <i class="fa-solid fa-chevron-right text-sm"></i>
          </button>
        </div>
      </div>

      <!-- Meetings Description Modal -->
      <div x-show="descModalOpen" x-cloak
           class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto"
           @click.self="descModalOpen = false">
        <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-slate-200 my-8 p-6" @click.stop>
          <template x-if="selectedMeeting">
            <div>
              <div class="flex items-center justify-between mb-4">
                <span :class="`px-2 py-1 rounded-full text-[9px] font-bold border inline-block ${getTypeBadgeClass(selectedMeeting.type)}`" x-text="selectedMeeting.type"></span>
                <button @click="descModalOpen = false" class="text-slate-400 hover:text-slate-700">
                  <i class="fa-solid fa-x w-5 h-5"></i>
                </button>
              </div>
              <h3 class="text-lg font-bold text-slate-900 mb-3" x-text="selectedMeeting.title"></h3>
              <p class="text-sm text-slate-700 leading-relaxed mb-4" x-text="(selectedMeeting.description || 'No description available.')"></p>
              <div class="p-3 bg-slate-50 rounded-xl space-y-2 font-mono text-xs border border-slate-100">
                <div class="flex justify-between">
                  <span class="text-slate-400">Date:</span>
                  <span class="font-bold text-slate-800" x-text="formatDate(selectedMeeting.date) || '—'"></span>
                </div>
                <div class="flex justify-between">
                  <span class="text-slate-400">Duration:</span>
                  <span class="font-bold text-slate-800" x-text="(formatTime(selectedMeeting.time_from) || '—') + ' - ' + (formatTime(selectedMeeting.time_to) || '—')"></span>
                </div>
                <div class="flex justify-between font-sans text-slate-600">
                  <span class="text-slate-400">Venue:</span>
                  <span class="truncate max-w-[200px]" x-text="selectedMeeting.venue || '—'"></span>
                </div>
                <template x-if="selectedMeeting.meeting_id || selectedMeeting.passcode">
                  <div class="mt-2 space-y-1">
                    <template x-if="selectedMeeting.meeting_id">
                      <div class="flex justify-between items-center">
                        <span class="text-slate-400">Meeting ID:</span>
                        <span class="font-bold text-slate-800 flex items-center gap-1">
                          <span x-text="selectedMeeting.meeting_id"></span>
                          <button @click="copyToClipboard(selectedMeeting.meeting_id, 'Copied Meeting ID!')" class="text-slate-400 hover:text-emerald-700">
                            <i class="fa-solid fa-copy w-3 h-3"></i>
                          </button>
                        </span>
                      </div>
                    </template>
                    <template x-if="selectedMeeting.passcode">
                      <div class="flex justify-between items-center">
                        <span class="text-slate-400">Passcode:</span>
                        <span class="font-bold text-slate-800 flex items-center gap-1">
                          <span x-text="selectedMeeting.passcode"></span>
                          <button @click="copyToClipboard(selectedMeeting.passcode, 'Copied Passcode!')" class="text-slate-400 hover:text-emerald-700">
                            <i class="fa-solid fa-copy w-3 h-3"></i>
                          </button>
                        </span>
                      </div>
                    </template>
                  </div>
                </template>
              </div>
              <a :href="selectedMeeting.link || '#'" target="_blank"
                 class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm flex items-center justify-center gap-2 transition mt-4">
                <i class="fa-solid fa-video"></i>
                <span>Join Room</span>
              </a>
            </div>
          </template>
        </div>
      </div>
    </section>

    <!-- ================= VIRTUAL SITE VISITS ================= -->
    <section id="site-visits-section" class="py-16 bg-white border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
          <div>
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
              Subproject Telemetry
            </span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Geotagged Site Visits ({{ $siteVisits->count() }})</h2>
            <p class="text-slate-600 text-sm mt-1">
              Field telemetry, station chainages, and Applied Geotagging Tool (AGT) photos across Luzon, Visayas, and Mindanao.
            </p>
          </div>
          <div class="text-xs font-semibold text-slate-500 flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            AGT Coordinates Certified by Regional Engineering Units
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="siteVisitsGridContainer">
          <!-- 10 Site Visit Cards rendered by JavaScript -->
        </div>

      </div>
    </section>

    @if($activeMission && $activeMission->enable_registration)
    <!-- ================= DELEGATE REGISTRATION ================= -->
    <section id="registration-section" class="py-16 bg-slate-50 border-b border-slate-200"
      x-data="delegateRegistration()">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="max-w-3xl mx-auto text-center mb-10">
          <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Registration</h2>
          <p class="text-slate-600 text-sm mt-1">
            Compliant with RA 10173 (Data Privacy Act of 2012) and mapped strictly to <code>mission_registrations</code>.
          </p>
        </div>

        <div class="max-w-4xl mx-auto bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
          <div class="bg-govnavy-950 p-6 text-white flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 rounded-xl bg-emerald-500/20 border border-emerald-400/30 flex items-center justify-center text-emerald-300">
                <i class="fa-solid fa-shield-alt w-5 h-5"></i>
              </div>
              <div>
                <h3 class="text-sm font-bold text-white">Official Accreditation Form</h3>
                <p class="text-xs text-slate-300">{{ $activeMission ? 'Active Mission #' . $activeMission->id : 'No Active Mission' }} • Department of Agriculture</p>
              </div>
            </div>
            <span class="text-xs font-semibold {{ $activeMission && $activeMission->enable_registration ? 'text-emerald-300 bg-emerald-950/60 px-3 py-1 rounded-lg border border-emerald-700/50' : 'text-slate-400 bg-slate-900/60 px-3 py-1 rounded-lg border border-slate-700/50' }}">
              {{ $activeMission && $activeMission->enable_registration ? 'Registration Open' : 'Registration Closed' }}
            </span>
          </div>

<form id="missionRegForm" class="p-6 sm:p-10 space-y-6" @submit.prevent="submitForm()">
            <input type="hidden" name="mission_id" value="{{ $activeMission->id ?? '' }}" />

            <!-- First Name & Last Name -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
              <div class="sm:col-span-6">
                <label for="reg_first_name" class="block text-xs font-semibold uppercase text-slate-700 mb-1.5">First Name *</label>
                <input type="text" id="reg_first_name" x-model="firstName" required placeholder="Maria" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-emerald-600 outline-none" />
              </div>
              <div class="sm:col-span-6">
                <label for="reg_last_name" class="block text-xs font-semibold uppercase text-slate-700 mb-1.5">Last Name *</label>
                <input type="text" id="reg_last_name" x-model="lastName" required placeholder="Santos" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-emerald-600 outline-none" />
              </div>
            </div>

            <!-- Middle Initial, Suffix & Sex Row -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
              <div class="sm:col-span-3">
                <label for="reg_middle_initial" class="block text-xs font-semibold uppercase text-slate-700 mb-1.5">M.I.</label>
                <input type="text" id="reg_middle_initial" x-model="middleInitial" maxlength="1" placeholder="S" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm text-center uppercase focus:border-emerald-600 outline-none" />
              </div>
              <div class="sm:col-span-3">
                <label for="reg_suffix" class="block text-xs font-semibold uppercase text-slate-700 mb-1.5">Suffix</label>
                <select id="reg_suffix" x-model="suffix" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm text-center focus:border-emerald-600 outline-none">
                  <option value="">—</option>
                  <template x-for="[value, label] in Object.entries(suffixLabels)" :key="value">
                    <option :value="value" x-text="label"></option>
                  </template>
                </select>
              </div>
              <div class="sm:col-span-6">
                <label class="block text-xs font-semibold uppercase text-slate-700 mb-1.5">Sex *</label>
                <div class="flex items-center gap-6 h-[42px]" x-show="sexOptions.length">
                    <template x-for="[value, label] in sexOptions" :key="value">
                      <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="sex" :value="value" x-model="sex" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 border-slate-300" />
                        <span class="text-sm font-medium text-slate-700" x-text="label"></span>
                      </label>
                    </template>
                </div>
              </div>
            </div>

            <!-- Office / Agency & Position / Designation -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
              <div class="sm:col-span-6">
                <label for="reg_office" class="block text-xs font-semibold uppercase text-slate-700 mb-1.5">Office / Agency *</label>
                <select id="reg_office" x-model="office" @change="updateOffice(office)" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:border-emerald-600 outline-none">
                  <option value="">Select Office</option>
                  <template x-for="[value, label] in Object.entries(officeLabels)" :key="value">
                    <option :value="value" x-text="label"></option>
                  </template>
                </select>
              </div>

              <div class="sm:col-span-6">
                <label for="reg_position" class="block text-xs font-semibold uppercase text-slate-700 mb-1.5">Position / Designation *</label>
                <input type="text" id="reg_position" x-model="position" required placeholder="Senior Infrastructure Engineer" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-emerald-600 outline-none" />
              </div>
            </div>

            <!-- Management Tier & Component / Unit (conditional - only if Office is PRDP) -->
            <div x-show="showManagementTier" x-cloak>
              <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-6">
                  <label for="reg_tier" class="block text-xs font-semibold uppercase text-slate-700 mb-1.5">Management Tier</label>
                  <select id="reg_tier" x-model="managementTier" @change="updateManagementTier(managementTier)" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:border-emerald-600 outline-none">
                    <option value="">Select Management Tier</option>
                    <template x-for="[value, label] in Object.entries(managementTierLabels)" :key="value">
                      <option :value="value" x-text="label"></option>
                    </template>
                  </select>
                </div>
                <div class="sm:col-span-6">
                  <label for="reg_component" class="block text-xs font-semibold uppercase text-slate-700 mb-1.5">Component / Unit</label>
                  <select id="reg_component" x-model="componentUnit" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:border-emerald-600 outline-none">
                    <option value="">Select Component Unit</option>
                    <template x-for="[value, label] in Object.entries(componentUnitLabels)" :key="value">
                      <option :value="value" x-text="label"></option>
                    </template>
                  </select>
                </div>
              </div>
            </div>

            <!-- PSO Cluster (conditional - only if Management Tier is PSO) -->
            <div x-show="showPsoCluster" x-cloak>
              <label for="reg_cluster" class="block text-xs font-semibold uppercase text-slate-700 mb-1.5">PSO Cluster</label>
              <select id="reg_cluster" x-model="psoCluster" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:border-emerald-600 outline-none">
                <option value="">Select PSO Cluster</option>
                <template x-for="[value, label] in Object.entries(psoClusterLabels)" :key="value">
                  <option :value="value" x-text="label"></option>
                </template>
              </select>
            </div>

            <!-- RPCO Office (conditional - only if Management Tier is RPCO) -->
            <div x-show="showRpcoOffice" x-cloak>
              <label for="reg_rpco" class="block text-xs font-semibold uppercase text-slate-700 mb-1.5">RPCO Office</label>
              <select id="reg_rpco" x-model="rpcoOffice" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:border-emerald-600 outline-none">
                <option value="">Select RPCO Office</option>
                <template x-for="[value, label] in Object.entries(rpcoOfficeLabels)" :key="value">
                  <option :value="value" x-text="label"></option>
                </template>
              </select>
            </div>

            <!-- Contact Details: Email & Contact Number -->
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
              <div class="sm:col-span-6">
                <label for="reg_email" class="block text-xs font-semibold uppercase text-slate-700 mb-1.5">Email Address *</label>
                <input type="email" id="reg_email" x-model="email" required placeholder="maria.santos@example.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-emerald-600 outline-none" />
              </div>
              <div class="sm:col-span-6">
                <label for="reg_contact_number" class="block text-xs font-semibold uppercase text-slate-700 mb-1.5">Contact Number *</label>
                <input type="tel" id="reg_contact_number" x-model="contactNumber" required placeholder="+63 917 123 4567" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-sm focus:border-emerald-600 outline-none" />
              </div>
            </div>

            <div class="pt-4 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
              <p class="text-xs text-slate-500">
                <i class="fa-solid fa-lock w-3.5 h-3.5 inline mr-1 text-slate-400"></i>
                By filling-up this form you agree with the Data Privacy Policy of the Department of Agriculture - Philippine Rural Development Project. Both personal and non-personal information may be collected from you for using this form. Rest assured that these data shall be kept safe and secured, and will not be shared to anyone except to designated personnel who will process the needed information only for facilitating smooth participation and distribution of materials for the virtual sessions.
              </p>
              @if($activeMission && $activeMission->enable_registration)
              <button type="submit" class="w-full sm:w-auto px-8 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-md transition">
                Register
              </button>
              @else
              <button type="button" disabled class="w-full sm:w-auto px-8 py-3 rounded-xl bg-slate-400 text-white text-sm font-bold shadow-md cursor-not-allowed">
                Registration Closed
              </button>
              @endif
            </div>
          </form>

          <div id="regSuccessBox" class="hidden p-8 bg-emerald-50 border-t border-emerald-200 text-center">
            <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto mb-3">
              <i class="fa-solid fa-check-circle w-7 h-7"></i>
            </div>
            <h4 class="text-base font-bold text-slate-900" id="regAttendeeName">Registration Verified</h4>
            <p class="text-sm text-slate-600 mt-2" id="regAttendeeDetails"></p>
          </div>
        </div>

      </div>
    </section>
    @endif

    <!-- ================= GOOGLE PHOTOS-STYLE STREAM ================= -->
    <section id="gallery" class="py-16 bg-white border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- App Bar -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-4 sm:p-5 mb-8">
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">

            <div class="flex items-center space-x-3">
              <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-400 via-rose-500 to-emerald-500 p-0.5 shadow-sm">
                <div class="w-full h-full bg-white rounded-[14px] flex items-center justify-center">
                  <i class="fa-solid fa-camera w-5 h-5 text-emerald-600"></i>
                </div>
              </div>
              <div>
                <div class="flex items-center space-x-2">
                  <h2 class="text-lg font-bold text-slate-900 tracking-tight">Mission Photos Stream</h2>
                  <span class="text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 px-2 py-0.5 rounded-full font-semibold">Always Shows All</span>
                </div>
                <p class="text-xs text-slate-400">Public participant gallery • All submissions displayed in unified date timeline</p>
              </div>
            </div>

            <!-- Instant Search Bar -->
            <div class="flex-1 max-w-xl mx-auto w-full">
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                  <i class="fa-solid fa-search w-4 h-4"></i>
                </div>
                <input
                  type="text"
                  id="gSearchInput"
                  oninput="handlePhotoSearch(this.value)"
                  placeholder="Filter across all photos (activity, person, location, agency)..."
                  class="w-full pl-10 pr-10 py-2.5 bg-slate-100/90 hover:bg-slate-100 focus:bg-white rounded-full text-xs sm:text-sm text-slate-800 placeholder-slate-400 border border-transparent focus:border-emerald-500 focus:ring-4 focus:ring-emerald-50 outline-none transition shadow-inner" />
                <button
                  id="clearSearchBtn"
                  onclick="clearPhotoSearch()"
                  class="absolute inset-y-0 right-0 pr-3.5 hidden items-center text-slate-400 hover:text-slate-600">
                  <i class="fa-solid fa-x w-4 h-4"></i>
                </button>
              </div>
            </div>

            <!-- Upload Action -->
            <div class="flex items-center space-x-2.5 justify-end">
              <button
                onclick="openUploadModal()"
                class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-emerald-600/20 transition active:scale-95">
                <i class="fa-solid fa-cloud-arrow-up w-4 h-4"></i>
                <span>Upload Photo</span>
              </button>
            </div>

          </div>

          <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <div class="flex items-center space-x-2">
              <i class="fa-solid fa-layer-group w-3.5 h-3.5 text-emerald-600"></i>
              <span>Displaying <strong>all participant photos</strong> with zero hidden subsets.</span>
            </div>
            <span class="font-mono text-[11px] text-slate-400" id="streamCounter">Showing all items</span>
          </div>
        </div>

        <!-- Google Photos Stream Container -->
        <div id="googlePhotosStreamContainer" class="space-y-8">
          <!-- Dynamically injected -->
        </div>

      </div>
    </section>

    <!-- ================= OFFICIAL REPORTS, PRESENTATIONS & DELIVERABLES ================= -->
    <section id="reports-section" class="py-16 bg-slate-50 border-b border-slate-200" x-data="reportsList()">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
          <div>
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider bg-emerald-100/70 border border-emerald-200 px-3 py-1 rounded-full">
              Mission Documentation
            </span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2"
                x-text="`Reports &amp; Presentations (${allReports.length})`"></h2>
            <p class="text-slate-600 text-sm mt-1">Official deliverables, presentations, annexes, fiduciary reviews, and technical evaluation papers.</p>
          </div>

          <!-- Reports Search -->
          <div class="w-full md:w-80 relative">
            <i class="fa-solid fa-search w-4 h-4 absolute left-3 top-3 text-slate-400"></i>
            <input
              type="text"
              x-model="searchQuery"
              placeholder="Search reports by meeting title, format..."
              class="w-full pl-9 pr-3.5 py-2 rounded-xl border border-slate-300 text-sm bg-white focus:border-emerald-600 outline-none" />
          </div>
        </div>

        <!-- Category Filters -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-3 mb-6 custom-scrollbar" id="reportCategoryFilters">
          <button @click="currentCategory = 'all'"
                  :class="currentCategory === 'all' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                  class="rep-cat-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold transition">
            All Reports (<span x-text="allReports.length"></span>)
          </button>
          <button @click="currentCategory = 'presentation'"
                  :class="currentCategory === 'presentation' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                  class="rep-cat-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold transition">
            Presentations
          </button>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100 shadow-sm overflow-hidden text-xs" id="reportsListContainer">
          <template x-if="filteredReports.length === 0" x-cloak>
            <div class="p-8 text-center text-xs text-slate-400 bg-white">No reports match the selected filter or search term.</div>
          </template>

          <template x-for="group in reportGroups" :key="group.date">
            <div class="mb-4 last:mb-0">
              <div class="bg-slate-100 px-4 py-2 border-b border-slate-200">
                <span class="text-xs font-bold text-slate-700" x-text="group.label"></span>
                <span class="text-xs text-slate-500 ml-2" x-text="`(${group.reports.length} document(s))`"></span>
              </div>
              <template x-for="rep in group.reports" :key="rep.id">
                <div class="p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50 transition border-b border-slate-100 last:border-0">
                  <div class="flex items-center space-x-3.5 pr-4 min-w-0">
                    <div :class="`p-2.5 rounded-xl border shrink-0 ${rep.format === 'PDF' ? 'bg-red-50 text-red-600 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'}`">
                      <i :class="`w-5 h-5 fa-solid ${rep.format === 'XLSX' ? 'fa-file-excel' : 'fa-file-text'}`"></i>
                    </div>
                    <div class="min-w-0">
                      <h4 class="font-bold text-slate-900 text-xs sm:text-sm truncate" x-text="rep.title"></h4>
                      <p class="text-slate-400 text-[11px] mt-0.5" x-text="rep.format"></p>
                    </div>
                  </div>
                  <a :href="rep.link" target="_blank" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 rounded-xl font-bold text-slate-700 flex items-center gap-1.5 transition shrink-0">
                    <i class="fa-solid fa-download w-3.5 h-3.5"></i> Download
                  </a>
                </div>
              </template>
            </div>
          </template>
        </div>

      </div>
    </section>
  </main>

  <!-- ================= SUBPROJECT GEOTAGGED PHOTO INSPECTION MODAL ================= -->
  <div id="geotagModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-4xl w-full overflow-hidden shadow-2xl border border-slate-200 max-h-[90vh] flex flex-col">
      <div class="bg-govnavy-950 text-white p-4 px-6 flex items-center justify-between shrink-0">
        <div class="flex items-center space-x-3">
          <div class="w-8 h-8 rounded-lg bg-emerald-500/20 border border-emerald-400/40 flex items-center justify-center text-emerald-300">
            <i class="fa-solid fa-map-pin w-4 h-4"></i>
          </div>
          <div>
            <h3 class="text-sm font-bold text-white" id="agtModalTitle">Subproject Geotagged Dossier</h3>
            <p class="text-xs text-slate-300 font-mono" id="agtModalSubtitle">Applied Geotagging Tool (AGT) Image Stream</p>
          </div>
        </div>
        <button onclick="closeGeotagModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
          <i class="fa-solid fa-x w-5 h-5"></i>
        </button>
      </div>

      <div class="p-6 overflow-y-auto space-y-6">
        <!-- Photo with AGT Watermark -->
        <div class="relative rounded-2xl overflow-hidden bg-slate-900 border border-slate-300 shadow-inner">
          <img id="agtMainImage" src="" alt="Geotagged Photo" class="w-full h-80 sm:h-96 object-cover" />

          <div class="agt-watermark-overlay absolute inset-x-0 bottom-0 p-4 text-white">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 font-mono text-[11px] bg-black/60 backdrop-blur-md p-3 rounded-xl border border-white/20">
              <div>
                <span class="text-slate-400 block text-[9px] uppercase tracking-wider">Coordinates</span>
                <strong class="text-emerald-400" id="agtCoord">8.7490Â° N, 124.7744Â° E</strong>
              </div>
              <div>
                <span class="text-slate-400 block text-[9px] uppercase tracking-wider">Station / Chainage</span>
                <strong class="text-white" id="agtStation">Sta. 04+250</strong>
              </div>
              <div>
                <span class="text-slate-400 block text-[9px] uppercase tracking-wider">Elevation &amp; Bearing</span>
                <strong class="text-white" id="agtElev">142m MSL • 42Â° NE</strong>
              </div>
              <div>
                <span class="text-slate-400 block text-[9px] uppercase tracking-wider">Survey Timestamp</span>
                <strong class="text-phgold-400" id="agtTime">2026-10-15 10:24 PST</strong>
              </div>
            </div>
          </div>
        </div>

        <div>
          <label class="font-bold text-xs uppercase tracking-wider text-slate-600 block mb-2">
            Sequential Geotagged Stations (<span id="agtPhotoCount">3</span> photos in this subproject)
          </label>
          <div class="grid grid-cols-3 sm:grid-cols-4 gap-3" id="agtThumbnailsGrid"></div>
        </div>

        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 text-xs space-y-2">
          <h4 class="font-bold text-slate-800 text-sm" id="agtSpName">Subproject Full Title</h4>
          <p class="text-slate-600 leading-relaxed" id="agtSpDesc">Technical audit description</p>
          <div class="pt-2 border-t border-slate-200 flex flex-wrap items-center gap-4 text-slate-500">
            <span>Funding: <strong class="text-slate-700" id="agtSpFunding">WB Loan</strong></span>
            <span>Cluster: <strong class="text-slate-700" id="agtSpCluster">Mindanao</strong></span>
            <span>Component: <strong class="text-slate-700" id="agtSpComponent">I-BUILD</strong></span>
            <a id="agtSpDocLink" href="#" target="_blank" class="text-emerald-700 font-bold hover:underline flex items-center gap-1">
              <i class="fa-solid fa-file-text w-3.5 h-3.5"></i> Project Document
            </a>
          </div>
        </div>
      </div>

      <div class="p-4 px-6 border-t border-slate-100 bg-slate-50 flex items-center justify-between shrink-0">
        <span class="text-xs text-slate-500">Applied Geotagging Tool (AGT) official audit format</span>
        <button onclick="closeGeotagModal()" class="px-5 py-2 rounded-xl bg-govnavy-950 hover:bg-slate-800 text-white text-xs font-semibold">
          Close Dossier
        </button>
      </div>
    </div>
  </div>

  <!-- ================= GOOGLE PHOTOS LIGHTBOX MODAL ================= -->
  <div id="gLightboxModal" class="fixed inset-0 z-50 bg-slate-950/95 backdrop-blur-md hidden flex-col">
    <div class="h-16 px-4 sm:px-6 bg-slate-900/80 border-b border-slate-800/80 flex items-center justify-between text-white shrink-0 z-20">
      <div class="flex items-center space-x-3 min-w-0">
        <button onclick="closeLightbox()" class="p-2 rounded-full hover:bg-white/10 text-slate-300 hover:text-white transition">
          <i class="fa-solid fa-arrow-left w-5 h-5"></i>
        </button>
        <div class="truncate">
          <p class="text-sm font-semibold text-white truncate" id="lbHeaderTitle">Photo Title</p>
          <p class="text-xs text-slate-400 truncate" id="lbHeaderSubtitle">Date • Location</p>
        </div>
      </div>

      <div class="flex items-center space-x-1 sm:space-x-2 shrink-0">
        <button onclick="toggleInfoDrawer()" class="p-2 rounded-full hover:bg-white/10 text-slate-300 hover:text-white transition" title="Details">
          <i class="fa-solid fa-circle-info w-5 h-5"></i>
        </button>
        <button onclick="downloadCurrentPhoto()" class="p-2 rounded-full hover:bg-white/10 text-slate-300 hover:text-white transition" title="Download">
          <i class="fa-solid fa-download w-5 h-5"></i>
        </button>
        <button onclick="closeLightbox()" class="p-2 rounded-full hover:bg-white/10 text-slate-300 hover:text-white transition" title="Close">
          <i class="fa-solid fa-x w-5 h-5"></i>
        </button>
      </div>
    </div>

    <div class="flex-1 relative flex overflow-hidden">
      <div class="flex-1 relative flex items-center justify-center p-4 sm:p-8 select-none">
        <button onclick="prevPhoto()" class="absolute left-4 top-1/2 -translate-y-1/2 p-3 rounded-full bg-slate-900/60 hover:bg-slate-900 text-white/80 hover:text-white shadow-xl backdrop-blur transition z-10">
          <i class="fa-solid fa-chevron-left w-6 h-6"></i>
        </button>
        <img id="lbMainImage" src="" alt="Mission Photo" class="max-w-full max-h-[82vh] object-contain rounded-lg shadow-2xl transition duration-300" />
        <button onclick="nextPhoto()" class="absolute right-4 top-1/2 -translate-y-1/2 p-3 rounded-full bg-slate-900/60 hover:bg-slate-900 text-white/80 hover:text-white shadow-xl backdrop-blur transition z-10">
          <i class="fa-solid fa-chevron-right w-6 h-6"></i>
        </button>
      </div>

      <div id="lbInfoDrawer" class="w-80 sm:w-96 bg-white text-slate-800 border-l border-slate-200 overflow-y-auto hidden flex-col z-20 shadow-2xl">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
          <h4 class="font-bold text-sm text-slate-800">Telemetry &amp; Details</h4>
          <button onclick="toggleInfoDrawer()" class="text-slate-400 hover:text-slate-700">
            <i class="fa-solid fa-x w-4 h-4"></i>
          </button>
        </div>
        <div class="p-5 space-y-5 text-xs">
          <div>
            <label class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Caption</label>
            <p class="text-sm font-semibold text-slate-900 mt-1" id="drawerCaption">Caption</p>
          </div>
          <div class="flex items-start space-x-3">
            <i class="fa-solid fa-calendar w-4 h-4 text-slate-400 mt-0.5"></i>
            <div>
              <p class="font-bold text-slate-800" id="drawerDate">Date</p>
              <p class="text-slate-400" id="drawerTime">Time</p>
            </div>
          </div>
          <div class="flex items-start space-x-3">
            <i class="fa-solid fa-user w-4 h-4 text-slate-400 mt-0.5"></i>
            <div>
              <p class="font-bold text-slate-800" id="drawerAuthor">Contributor</p>
              <p class="text-slate-500" id="drawerOffice">Agency</p>
            </div>
          </div>
          <div class="flex items-start space-x-3">
            <i class="fa-solid fa-map-pin w-4 h-4 text-emerald-600 mt-0.5"></i>
            <div>
              <p class="font-bold text-slate-800" id="drawerLocation">Location</p>
            </div>
          </div>
          <div class="pt-3 border-t border-slate-100">
            <label class="font-bold text-slate-400 uppercase tracking-wider text-[10px] block mb-2">Sensor Telemetry</label>
            <div class="space-y-2 bg-slate-50 p-3 rounded-2xl border border-slate-100">
              <div class="flex items-center space-x-2 text-slate-700">
                <i class="fa-solid fa-camera w-3.5 h-3.5 text-slate-400"></i>
                <span id="drawerDevice">Camera Sensor</span>
              </div>
              <div class="text-[11px] text-slate-500 font-mono" id="drawerExif">Telemetry</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= UNAUTHENTICATED PARTICIPANT PHOTO UPLOAD MODAL ================= -->
  <div id="gUploadModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full overflow-hidden shadow-2xl border border-slate-200 my-8">
      <div class="p-5 px-6 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <i class="fa-solid fa-image w-5 h-5 text-emerald-600"></i>
          <h3 class="font-bold text-base text-slate-900">Upload to Mission Photo Stream</h3>
        </div>
        <button onclick="closeUploadModal()" class="text-slate-400 hover:text-slate-700">
          <i class="fa-solid fa-x w-5 h-5"></i>
        </button>
      </div>

      <form id="publicUploadForm" class="p-6 space-y-5" onsubmit="event.preventDefault(); handleNewPhotoUpload();">
        <div
          id="dropArea"
          onclick="document.getElementById('fileInput').click()"
          class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-2xl p-6 text-center cursor-pointer bg-slate-50 hover:bg-emerald-50/20 transition relative">
          <input
            type="file"
            id="fileInput"
            accept="image/*"
            class="hidden"
            required
            onchange="handleFileInputPreview(event)" />
          <div id="dropPrompt" class="space-y-2">
            <div class="w-12 h-12 rounded-full bg-white text-emerald-600 shadow flex items-center justify-center mx-auto">
              <i class="fa-solid fa-cloud-arrow-up w-6 h-6"></i>
            </div>
            <p class="text-xs font-bold text-slate-800">Select photo from device</p>
            <p class="text-[11px] text-slate-400">JPG, PNG, WEBP (No login required)</p>
          </div>
          <div id="dropPreview" class="hidden relative">
            <img id="tempImgPreview" src="" alt="Preview" class="w-full h-44 object-cover rounded-xl shadow-sm" />
            <button type="button" onclick="event.stopPropagation(); clearUploadFile();" class="absolute top-2 right-2 bg-slate-900/80 text-white p-1 rounded-full hover:bg-slate-900">
              <i class="fa-solid fa-trash w-4 h-4"></i>
            </button>
          </div>
        </div>

        <div>
          <label for="upCaption" class="block text-xs font-semibold text-slate-700 mb-1">Caption / Field Activity *</label>
          <input type="text" id="upCaption" required placeholder="Core drill test on Station 05+100" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 outline-none" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="upAuthor" class="block text-xs font-semibold text-slate-700 mb-1">Your Name *</label>
            <input type="text" id="upAuthor" required placeholder="Engr. Leo Garcia" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 outline-none" />
          </div>
          <div>
            <label for="upOffice" class="block text-xs font-semibold text-slate-700 mb-1">Office / Agency *</label>
            <input type="text" id="upOffice" required placeholder="RPCO 10 / PLGU Misamis" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 outline-none" />
          </div>
        </div>

        <div>
          <label for="upLocation" class="block text-xs font-semibold text-slate-700 mb-1">Location Tag</label>
          <input type="text" id="upLocation" placeholder="Balingasag, Misamis Oriental" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 outline-none" />
        </div>

        <div class="pt-3 flex items-center justify-end space-x-2">
          <button type="button" onclick="closeUploadModal()" class="px-4 py-2 rounded-full text-xs font-medium text-slate-600 hover:bg-slate-100">Cancel</button>
          <button type="submit" class="px-6 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20">
            Publish to Stream
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- ================= FOOTER ================= -->
  <footer class="bg-white border-t border-slate-200 text-slate-600 py-10 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
      <div class="flex items-center space-x-3">
        <div class="w-8 h-8 rounded-xl bg-govnavy-950 text-white flex items-center justify-center font-bold">
          <i class="fa-solid fa-shield-alt w-4 h-4 text-emerald-400"></i>
        </div>
        <span>Â© 2026 Department of Agriculture • Philippine Rural Development Project (PRDP Scale-Up)</span>
      </div>
      <div class="flex space-x-4">
        <a href="#banner-section" class="hover:underline">Banner</a>
        <a href="#agenda-section" class="hover:underline">Agenda</a>
        <a href="#meetings-section" class="hover:underline">Meetings ({{ $meetings->count() }})</a>
        <a href="#site-visits-section" class="hover:underline">Site Visits ({{ $siteVisits->count() }})</a>
        <a href="#gallery" class="hover:underline font-bold text-emerald-700">All Photos</a>
        <a href="#reports-section" class="hover:underline">Reports (30)</a>
      </div>
    </div>
  </footer>

   <!-- Toast Box -->
   <div id="toastBox" class="fixed bottom-5 right-5 z-50 transform translate-y-28 opacity-0 transition-all duration-300 pointer-events-none">
     <div class="bg-govnavy-950 text-white px-4 py-3 rounded-2xl shadow-xl flex items-center space-x-3 text-xs border border-slate-800">
       <i class="fa-solid fa-circle-check w-4 h-4 text-emerald-400"></i>
       <span id="toastMsg">Action completed.</span>
     </div>
   </div>

    <!-- ================= COMPLETE REALISTIC DATASETS & JAVASCRIPT ================= -->
  @php
  $meetingsJson = $meetings->map(fn ($m) => [
  'id' => $m->id,
  'title' => $m->title,
  'description' => $m->description ?? '',
  'date' => $m->date?->format('Y-m-d'),
  'time_from' => $m->time_from?->format('H:i'),
  'time_to' => $m->time_to?->format('H:i'),
  'type' => $m->type === 'other' && $m->other_type ? $m->other_type : ($m->types()[$m->type] ?? $m->type),
  'venue' => $m->venue ?? '',
  'link' => $m->link ?? '',
  'meeting_id' => $m->meeting_id ?? '',
  'passcode' => $m->passcode ?? '',
  'reports' => $m->reports ?? [],
  'report_name' => $m->report_name ?? '',
  ])->toJson();
  $siteVisitsJson = $siteVisits->map(fn ($sv) => [
  'id' => $sv->id,
  'sp_name' => $sv->sp_name,
  'cluster' => $sv->cluster?->label() ?? '',
  'component' => $sv->component?->label() ?? '',
  'location' => $sv->location ?? '',
  'funding_source' => $sv->funding_source?->label() ?? '',
  'document_link' => $sv->document_link ?? '#',
  'virtual_site_visit_link' => $sv->virtual_site_visit_link ?? '#',
  'date' => $sv->date?->format('Y-m-d') ?? '',
  'description' => '',
  'images' => $sv->images ? array_map(fn ($img) => [
  'url' => $img['url'] ?? '',
  'station' => $img['station'] ?? '',
  'coords' => $img['coords'] ?? '',
  'elev' => $img['elev'] ?? '',
  'time' => $img['time'] ?? '',
  'label' => $img['label'] ?? '',
  ], $sv->images) : [],
  ])->toJson();

  $officeLabelsJson = json_encode(\App\Enums\MissionOffice::labels());
  $managementTierLabelsJson = json_encode(\App\Enums\MissionManagementTier::labels());
  $psoClusterLabelsJson = json_encode(\App\Enums\MissionPsoCluster::labels());
  $rpcoOfficeLabelsJson = json_encode(\App\Enums\MissionRpcoOffice::labels());
  $componentUnitLabelsJson = json_encode(\App\Enums\MissionComponentUnit::labels());
   $sexLabelsJson = json_encode(\App\Enums\Sex::labels());
   $suffixLabelsJson = json_encode(\App\Enums\Suffix::labels());
  @endphp
  <script>
    // ----------------------------------------------------
    // 1. MEETINGS (from database)
    // ----------------------------------------------------
    const meetingsData = {!! $meetingsJson !!};

    // ----------------------------------------------------
    // 2. GEOTAGGED SITE VISITS (from database)
    // ----------------------------------------------------
    const siteVisitsData = {!! $siteVisitsJson !!};

    // ----------------------------------------------------
    // 3. ENUMS FOR DELEGATE REGISTRATION (from PHP enums)
    // ----------------------------------------------------
    const officeLabels = {!! $officeLabelsJson !!};
    const managementTierLabels = {!! $managementTierLabelsJson !!};
    const psoClusterLabels = {!! $psoClusterLabelsJson !!};
    const rpcoOfficeLabels = {!! $rpcoOfficeLabelsJson !!};
    const componentUnitLabels = {!! $componentUnitLabelsJson !!};
    const sexLabels = {!! $sexLabelsJson !!};
    const suffixLabels = {!! $suffixLabelsJson !!};

    // ----------------------------------------------------
    // 4. ALL MISSION PHOTOS STREAM (ALWAYS SHOWS ALL)
    // ----------------------------------------------------
    const allMissionPhotos = [{
        id: 101,
        dateGroup: "Today",
        dateDisplay: "October 16, 2026",
        timeDisplay: "02:15 PM PST",
        caption: "Field Geotagging & Drone Alignment at Balingasag Farm-to-Market Road",
        author: "Engr. Carmela De Leon",
        office: "RPCO Region 10",
        location: "Misamis Oriental, Region X",
        device: "DJI Mavic 3 Pro • 24mm f/2.8",
        exif: "1/1000s • ISO 100",
        imageUrl: "https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80"
      },
      {
        id: 102,
        dateGroup: "Today",
        dateDisplay: "October 16, 2026",
        timeDisplay: "11:30 AM PST",
        caption: "High-Level Opening Plenary with DA Secretary and World Bank TTL",
        author: "InfoACE Media Desk",
        office: "NPCO Secretariat",
        location: "Clark Marriott Ballroom",
        device: "Sony A7 IV • 35mm f/1.8",
        exif: "1/250s • ISO 800",
        imageUrl: "https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1200&q=80"
      },
      {
        id: 103,
        dateGroup: "Yesterday",
        dateDisplay: "October 15, 2026",
        timeDisplay: "03:45 PM PST",
        caption: "Solar-Powered Irrigation Ultrasonic Flow Meter Verification",
        author: "Engr. Ronald Soriano",
        office: "PSO Visayas Cluster",
        location: "Pototan, Iloilo",
        device: "iPhone 15 Pro • 24mm f/1.78",
        exif: "1/1200s • ISO 64",
        imageUrl: "https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=80"
      },
      {
        id: 104,
        dateGroup: "Yesterday",
        dateDisplay: "October 15, 2026",
        timeDisplay: "01:10 PM PST",
        caption: "Cacao Post-Harvest Fermentation Quality Assessment & Sensory Lab",
        author: "Joy Morales, M.Sc.",
        office: "I-REAP Component Lead",
        location: "Davao del Norte Processing Center",
        device: "Canon EOS R6 • 50mm",
        exif: "1/400s • ISO 400",
        imageUrl: "https://images.unsplash.com/photo-1592982537447-7440770cbfc9?auto=format&fit=crop&w=1200&q=80"
      },
      {
        id: 105,
        dateGroup: "Earlier this Mission",
        dateDisplay: "October 14, 2026",
        timeDisplay: "04:30 PM PST",
        caption: "Safeguards (SES) Consultation with Tribal Chieftains on Right-of-Way",
        author: "Atty. Pamela Rosales",
        office: "SES Unit - Luzon B",
        location: "Victoria, Oriental Mindoro",
        device: "Nikon Z6 II • 24-70mm",
        exif: "1/160s • ISO 640",
        imageUrl: "https://images.unsplash.com/photo-1541888946425-d0fbb18615f3?auto=format&fit=crop&w=1200&q=80"
      },
      {
        id: 106,
        dateGroup: "Earlier this Mission",
        dateDisplay: "October 14, 2026",
        timeDisplay: "09:45 AM PST",
        caption: "National Project Advisory Board Alignment and Budget Obligation Brief",
        author: "Planning Specialist K. Sison",
        office: "DA-PRDP NPCO",
        location: "BSWM Convention Hall",
        device: "Fujifilm X-T4 • 33mm",
        exif: "1/320s • ISO 1600",
        imageUrl: "https://images.unsplash.com/photo-1475721027785-f74eccf877e2?auto=format&fit=crop&w=1200&q=80"
      },
      {
        id: 107,
        dateGroup: "Earlier this Mission",
        dateDisplay: "October 14, 2026",
        timeDisplay: "08:15 AM PST",
        caption: "Core sampling drill calibration for pavement quality control test",
        author: "Quality Inspector V. Cruz",
        office: "PSO Mindanao QA Unit",
        location: "Bonifacio FMR, Misamis Occ.",
        device: "Samsung S24 Ultra",
        exif: "1/800s • ISO 50",
        imageUrl: "https://images.unsplash.com/photo-1584463699039-447a151b7593?auto=format&fit=crop&w=1200&q=80"
      },
      {
        id: 108,
        dateGroup: "Earlier this Mission",
        dateDisplay: "October 14, 2026",
        timeDisplay: "07:30 AM PST",
        caption: "Highland Arabica coffee nursery inspection and seedling propagation",
        author: "Agri-Specialist D. Mendoza",
        office: "RPCO CAR",
        location: "Atok, Benguet",
        device: "Sony A6700",
        exif: "1/500s • ISO 200",
        imageUrl: "https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=1200&q=80"
      }
    ];

    let photos = [...allMissionPhotos];
    let activeSearchQuery = "";
    let currentLightboxIdx = 0;
    let visiblePhotos = [];
    let tempUploadBase64 = null;
    let activeAgtSp = 1;
    let activeAgtPhotoIdx = 0;

    function formatTime(timeStr) {
      if (!timeStr) return '';
      const parts = timeStr.split(':');
      const hour = parseInt(parts[0]);
      const minute = parts[1];
      const ampm = hour >= 12 ? 'PM' : 'AM';
      const displayHour = hour % 12 || 12;
      return displayHour + ':' + minute + ' ' + ampm;
    }

    function formatDate(dateStr) {
      if (!dateStr) return '';
      const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
      const parts = dateStr.split('-');
      return months[parseInt(parts[1]) - 1] + ' ' + parseInt(parts[2]) + ', ' + parseInt(parts[0]);
    }

    // ----------------------------------------------------
    // INITIALIZATION & EVENT LOGIC
    // ----------------------------------------------------
    window.addEventListener('DOMContentLoaded', () => {
      renderSiteVisitsGrid();
      renderGooglePhotosStream();
    });

    // ----------------------------------------------------
    // MISSION AGENDA ALPINE COMPONENT
    // ----------------------------------------------------
    function agendaTimeline() {
      return {
        currentDate: 'all',
        currentType: 'all',
        sheetEmbedOpen: false,
        isFullscreen: false,
        get meetings() {
          return typeof meetingsData !== 'undefined' ? meetingsData : [];
        },
        get siteVisits() {
          return typeof siteVisitsData !== 'undefined' ? siteVisitsData : [];
        },
        get allActivities() {
          const activities = [];
          this.meetings.forEach(m => {
            activities.push({
              id: `m-${m.id}`,
              type: 'meeting',
              categoryLabel: m.type,
              title: m.title,
              description: m.description,
              date: m.date,
              time: formatTime(m.time_from) + ' - ' + formatTime(m.time_to),
              venue: m.venue,
              link: m.link,
              meeting_id: m.meeting_id
            });
          });
          this.siteVisits.forEach(s => {
            activities.push({
              id: `s-${s.id}`,
              type: 'site_visit',
              categoryLabel: `${s.component} • ${s.cluster}`,
              title: s.sp_name,
              description: s.description,
              date: s.date || '',
              time: formatTime("08:30") + ' - ' + formatTime("15:30"),
              venue: s.location,
              sp_id: s.id
            });
          });
          activities.sort((a, b) => a.date.localeCompare(b.date));
          return activities;
        },
        get uniqueDates() {
          const dates = [...new Set(this.meetings.map(m => m.date).filter(d => d))];
          dates.sort();
          return dates;
        },
        get filteredActivities() {
          return this.allActivities.filter(act => {
            const matchesDate = (this.currentDate === 'all' || act.date === this.currentDate);
            const matchesType = (this.currentType === 'all' || act.type === this.currentType);
            return matchesDate && matchesType;
          });
        },
        toggleAgendaFullscreen() {
          const container = this.$refs.sheetEmbedContainer;
          if (!container) return;
          if (document.fullscreenElement) {
            document.exitFullscreen();
          } else {
            container.requestFullscreen().catch(err => console.log(err));
          }
        }
      };
    }

    // ----------------------------------------------------
    // MEETINGS DIRECTORY ALPINE COMPONENT
    // ----------------------------------------------------
    function meetingsDirectory() {
      return {
        searchQuery: '',
        selectedMeeting: null,
        descModalOpen: false,
        getTypeBadgeClass(type) {
          const lower = typeof type === 'string' ? type.toLowerCase() : '';
          if (lower.includes('kick off') || lower.includes('wrap up')) {
            return 'bg-emerald-100 text-emerald-800 border-emerald-200';
          }
          if (lower.includes('technical')) {
            return 'bg-blue-100 text-blue-800 border-blue-200';
          }
          return 'bg-govnavy-950 text-white border-govnavy-950';
        },
        get allMeetings() {
          return typeof meetingsData !== 'undefined' ? meetingsData : [];
        },
        get filteredMeetings() {
          const q = (this.searchQuery || '').toLowerCase().trim();
          if (!q) return this.allMeetings;
          return this.allMeetings.filter(m =>
            (m.title || '').toLowerCase().includes(q) ||
            (m.type || '').toLowerCase().includes(q) ||
            (m.venue || '').toLowerCase().includes(q) ||
            (m.description || '').toLowerCase().includes(q)
          );
        },
        get isCarousel() {
          return this.filteredMeetings.length > 3;
        },
        scrollPrev() {
          const container = this.$refs.gridContainer;
          if (container) {
            container.scrollLeft -= 340;
          }
        },
        scrollNext() {
          const container = this.$refs.gridContainer;
          if (container) {
            container.scrollLeft += 340;
          }
        },
        openModal(m) {
          this.selectedMeeting = m;
          this.descModalOpen = true;
        },
        closeModal() {
          this.descModalOpen = false;
          this.selectedMeeting = null;
        }
      };
    }

    // ----------------------------------------------------
    // GEOTAGGED SITE VISITS (10)
    // ----------------------------------------------------
    function renderSiteVisitsGrid() {
      const container = document.getElementById('siteVisitsGridContainer');
      if (!container) return;

      container.innerHTML = "";

      siteVisitsData.forEach(sp => {
        const card = document.createElement('div');
        card.className = "bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between h-full";
        card.innerHTML = `
          <div>
            <div class="relative h-44 bg-slate-900 overflow-hidden group">
              <img src="${sp.images && sp.images[0] ? sp.images[0].url : 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80'}" alt="${sp.sp_name}" class="w-full h-full object-cover opacity-85 group-hover:scale-105 transition duration-500" />
              <span class="absolute top-3 left-3 bg-govnavy-950/85 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg">${sp.cluster}</span>
              <span class="absolute bottom-3 right-3 bg-emerald-950/90 text-emerald-300 text-[10px] font-mono px-2 py-0.5 rounded border border-emerald-500/40">
                <i class="fa-solid fa-camera w-3 h-3 inline mr-1"></i> ${sp.images ? sp.images.length : 0} Geotagged Photos
              </span>
            </div>
            <div class="p-5 space-y-2">
              <p class="text-[11px] font-semibold text-emerald-700">${sp.component} • ${sp.location}</p>
              <h3 class="text-sm font-bold text-slate-900 line-clamp-2">${sp.sp_name}</h3>
              <p class="text-xs text-slate-600 line-clamp-2">${sp.description}</p>
            </div>
          </div>
          <div class="p-5 pt-0 flex items-center justify-between">
            <span class="text-[11px] text-slate-400 truncate max-w-[120px]">${sp.funding_source}</span>
            <button onclick="openGeotagModal(${sp.id})" class="px-3.5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 transition flex items-center gap-1.5 shadow-sm">
              <i class="fa-solid fa-images w-3.5 h-3.5"></i> Inspect AGT Photos
            </button>
          </div>
        `;
        container.appendChild(card);
      });
    }

    function openGeotagModal(spId) {
      activeAgtSp = spId;
      activeAgtPhotoIdx = 0;
      const sp = siteVisitsData.find(item => item.id === spId);
      if (!sp) return;

      document.getElementById('agtModalTitle').innerText = sp.sp_name;
      document.getElementById('agtSpName').innerText = sp.sp_name;
      document.getElementById('agtSpDesc').innerText = sp.description;
      document.getElementById('agtSpFunding').innerText = sp.funding_source;
      document.getElementById('agtSpCluster').innerText = sp.cluster;
      document.getElementById('agtSpComponent').innerText = sp.component;
      document.getElementById('agtSpDocLink').href = sp.document_link;
      document.getElementById('agtPhotoCount').innerText = sp.images ? sp.images.length : 0;

      renderAgtPhotos();
      document.getElementById('geotagModal').classList.remove('hidden');
      document.getElementById('geotagModal').classList.add('flex');
    }

    function closeGeotagModal() {
      document.getElementById('geotagModal').classList.add('hidden');
      document.getElementById('geotagModal').classList.remove('flex');
    }

    function renderAgtPhotos() {
      const sp = siteVisitsData.find(item => item.id === activeAgtSp);
      if (!sp) return;
      const images = sp.images || [];
      if (images.length === 0) return;
      const photo = images[activeAgtPhotoIdx];

      document.getElementById('agtMainImage').src = photo.url;
      document.getElementById('agtCoord').innerText = photo.coords;
      document.getElementById('agtStation').innerText = photo.station;
      document.getElementById('agtElev').innerText = photo.elev;
      document.getElementById('agtTime').innerText = photo.time;

      const thumbsContainer = document.getElementById('agtThumbnailsGrid');
      thumbsContainer.innerHTML = '';

      (sp.images || []).forEach((p, idx) => {
        const thumb = document.createElement('div');
        const isActive = idx === activeAgtPhotoIdx;
        thumb.className = `cursor-pointer rounded-xl overflow-hidden border-2 transition ${isActive ? 'border-emerald-600 ring-2 ring-emerald-200' : 'border-slate-200 hover:border-slate-300'}`;
        thumb.onclick = () => {
          activeAgtPhotoIdx = idx;
          renderAgtPhotos();
        };
        thumb.innerHTML = `
          <img src="${p.url}" alt="${p.label}" class="w-full h-14 object-cover" />
          <div class="p-1 bg-white text-[9px] text-slate-700 font-semibold truncate text-center">${p.station}</div>
        `;
        thumbsContainer.appendChild(thumb);
      });
    }

    // ----------------------------------------------------
    // REPORTS & PRESENTATIONS & DOCUMENTATION (ALPINE COMPONENT)
    // ----------------------------------------------------
    function reportsList() {
      return {
        searchQuery: '',
        currentCategory: 'all',
        get allReports() {
          let reports = [];
          if (typeof meetingsData !== 'undefined' && Array.isArray(meetingsData)) {
            meetingsData.forEach(m => {
              if (Array.isArray(m.reports)) {
                m.reports.forEach((url, idx) => {
                  if (url) {
                    const ext = url.split('.').pop().toLowerCase();
                    const fmt = ext === 'xlsx' ? 'XLSX' : 'PDF';
                    reports.push({
                      id: `mtg-${m.id}-${idx}`,
                      title: m.report_name || m.title,
                      meetingDate: m.date || '',
                      category: 'presentation',
                      format: fmt,
                      link: url
                    });
                  }
                });
              }
            });
          }
          return reports;
        },
        get filteredReports() {
          const q = (this.searchQuery || '').toLowerCase().trim();
          return this.allReports.filter(rep => {
            const matchesCategory = (this.currentCategory === 'all' || rep.category === this.currentCategory);
            const matchesSearch = !q ||
              rep.title.toLowerCase().includes(q) ||
              rep.format.toLowerCase().includes(q);
            return matchesCategory && matchesSearch;
          });
        },
        get reportGroups() {
          const groups = {};
          this.filteredReports.forEach(rep => {
            const groupKey = rep.meetingDate || 'undated';
            if (!groups[groupKey]) {
              groups[groupKey] = [];
            }
            groups[groupKey].push(rep);
          });
          return Object.keys(groups).sort().map(dateKey => ({
            date: dateKey,
            label: dateKey ? formatDate(dateKey) : 'Undated',
            reports: groups[dateKey]
          }));
        }
      };
    }

    // ----------------------------------------------------
    // DELEGATE REGISTRATION ALPINE COMPONENT
    // ----------------------------------------------------
    function delegateRegistration() {
      return {
         firstName: '',
         middleInitial: '',
         suffix: '',
         lastName: '',
        office: '',
        managementTier: '',
        psoCluster: '',
        rpcoOffice: '',
        position: '',
        componentUnit: '',
        contactNumber: '',
        email: '',
        sex: 'female',
        submitted: false,
        get isPRDP() {
          return this.office === 'prdp';
        },
        get showManagementTier() {
          return this.isPRDP;
        },
        get showPsoCluster() {
          return this.managementTier === 'pso';
        },
        get showRpcoOffice() {
          return this.managementTier === 'rpco';
        },
        get showComponentUnit() {
          return this.isPRDP;
        },
        get officeLabel() {
          return officeLabels[this.office] || this.office;
        },
        get sexOptions() {
          return Object.entries(sexLabels).filter(([value]) => value !== 'other');
        },
        updateOffice(value) {
          this.office = value;
          this.managementTier = '';
          this.psoCluster = '';
          this.rpcoOffice = '';
          this.componentUnit = '';
        },
        updateManagementTier(value) {
          this.managementTier = value;
          this.psoCluster = '';
          this.rpcoOffice = '';
        },
         submitForm() {
           this.submitted = true;

           const formData = new FormData();
           formData.append('first_name', this.firstName);
           formData.append('middle_initial', this.middleInitial);
           formData.append('suffix', this.suffix);
           formData.append('last_name', this.lastName);
           formData.append('office', this.office);
           formData.append('management_tier', this.managementTier);
           formData.append('pso_cluster', this.psoCluster);
           formData.append('rpco_office', this.rpcoOffice);
           formData.append('position', this.position);
           formData.append('component_unit', this.componentUnit);
           formData.append('contact_number', this.contactNumber);
           formData.append('sex', this.sex);
           formData.append('email', this.email);

           const submitBtn = document.querySelector('#registration-section button[type="submit"]');
           if (submitBtn) submitBtn.disabled = true;

            fetch('{{ route('landing.register') }}', {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
              },
              body: formData,
            })
              .then(async (response) => {
                const text = await response.text();
                let body = {};
                try {
                  body = text ? JSON.parse(text) : {};
                } catch (e) {
                  console.error('Response not JSON:', response.status, text.substring(0, 200));
                }

                if (response.status >= 200 && response.status < 300) {
                  const suffixLabel = this.suffix ? (suffixLabels[this.suffix] || this.suffix) : '';
                  const name = `${this.firstName} ${this.middleInitial ? this.middleInitial.toUpperCase() + '. ' : ''}${this.lastName}${suffixLabel ? ' ' + suffixLabel : ''}`;

                  document.getElementById('regAttendeeName').innerText = `Registration Verified: ${name}`;
                  document.getElementById('regAttendeeDetails').innerText = 'Your delegate profile has been enrolled into mission_registrations.';
                  document.getElementById('regSuccessBox').classList.remove('hidden');
                  document.getElementById('regSuccessBox').scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                  });
                  showToast(`Welcome, ${this.firstName}! Registration recorded.`);

                  this.firstName = '';
                  this.middleInitial = '';
                  this.suffix = '';
                  this.lastName = '';
                  this.office = '';
                  this.managementTier = '';
                  this.psoCluster = '';
                  this.rpcoOffice = '';
                  this.componentUnit = '';
                  this.position = '';
                  this.contactNumber = '';
                  this.email = '';
                  this.sex = 'female';
                  this.submitted = false;
                } else {
                  const errorMsg = body.message || 'Registration failed. Please check your inputs and try again.';
                  showToast(errorMsg);
                }
              })
             .catch(error => {
               console.error(error);
               showToast('Registration failed. Please try again.');
             })
             .finally(() => {
               if (submitBtn) submitBtn.disabled = false;
             });
         }
      };
    }

    // ----------------------------------------------------
    // GOOGLE PHOTOS STREAM (ALWAYS SHOWS ALL)
    // ----------------------------------------------------
    function renderGooglePhotosStream() {
      const container = document.getElementById('googlePhotosStreamContainer');
      if (!container) return;

      const q = activeSearchQuery.toLowerCase().trim();
      visiblePhotos = photos.filter(item => {
        if (!q) return true; // ALWAYS SHOW ALL PHOTOS WHEN NO SEARCH QUERY
        return (
          item.caption.toLowerCase().includes(q) ||
          item.author.toLowerCase().includes(q) ||
          item.office.toLowerCase().includes(q) ||
          item.location.toLowerCase().includes(q)
        );
      });

      const topCountEl = document.getElementById('topPhotoCount');
      if (topCountEl) topCountEl.innerText = photos.length;
      const streamCounterEl = document.getElementById('streamCounter');
      if (streamCounterEl) {
        streamCounterEl.innerText = q ? `Showing ${visiblePhotos.length} of ${photos.length} photos` : `All ${photos.length} photos displayed`;
      }

      container.innerHTML = "";

      if (visiblePhotos.length === 0) {
        container.innerHTML = `
          <div class="text-center py-16 bg-slate-50 rounded-3xl border border-slate-200">
            <i class="fa-solid fa-image w-12 h-12 text-slate-300 mx-auto mb-3"></i>
            <h3 class="font-bold text-base text-slate-700">No photos match "${activeSearchQuery}"</h3>
            <p class="text-xs text-slate-400 mt-1">Clear search query to see all photos again.</p>
            <button onclick="clearPhotoSearch()" class="mt-4 px-4 py-2 bg-emerald-600 text-white text-xs font-semibold rounded-xl">
              Reset &amp; Show All Photos
            </button>
          </div>
        `;
        return;
      }

      // Group into date sections while displaying ALL photos
      const dateGroups = {};
      visiblePhotos.forEach(p => {
        if (!dateGroups[p.dateGroup]) {
          dateGroups[p.dateGroup] = [];
        }
        dateGroups[p.dateGroup].push(p);
      });

      Object.keys(dateGroups).forEach(groupName => {
        const groupPhotos = dateGroups[groupName];
        const groupWrapper = document.createElement('div');
        groupWrapper.className = "space-y-3";

        const headerEl = document.createElement('div');
        headerEl.className = "flex items-baseline space-x-2 text-slate-800 pt-2 border-b border-slate-100 pb-1.5";
        headerEl.innerHTML = `
          <h3 class="font-bold text-base text-slate-900 tracking-tight">${groupName}</h3>
          <span class="text-xs text-slate-400 font-medium">${groupPhotos[0].dateDisplay} • ${groupPhotos.length} photo${groupPhotos.length > 1 ? 's' : ''}</span>
        `;
        groupWrapper.appendChild(headerEl);

        const gridEl = document.createElement('div');
        gridEl.className = "grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-3";

        groupPhotos.forEach(photo => {
          const globalIdx = visiblePhotos.findIndex(item => item.id === photo.id);
          const card = document.createElement('div');
          card.className = "g-photo-card relative rounded-2xl overflow-hidden bg-slate-100 aspect-square sm:aspect-[4/3] group cursor-pointer border border-slate-200/70 shadow-sm hover:shadow-md transition-all";
          card.onclick = () => openLightbox(globalIdx);

          card.innerHTML = `
            <img src="${photo.imageUrl}" alt="${photo.caption}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy" />
            <div class="g-photo-overlay absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-black/20 opacity-0 transition duration-200 pointer-events-none"></div>
            <div class="absolute bottom-2.5 left-2.5 right-2.5 text-white opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none">
              <p class="text-xs font-semibold truncate leading-snug">${photo.caption}</p>
              <p class="text-[10px] text-slate-300 truncate">${photo.author} • ${photo.location}</p>
            </div>
          `;
          gridEl.appendChild(card);
        });

        groupWrapper.appendChild(gridEl);
        container.appendChild(groupWrapper);
      });
    }

    function handlePhotoSearch(val) {
      activeSearchQuery = val;
      const clearBtn = document.getElementById('clearSearchBtn');
      if (val) {
        clearBtn.classList.remove('hidden');
        clearBtn.classList.add('flex');
      } else {
        clearBtn.classList.add('hidden');
        clearBtn.classList.remove('flex');
      }
      renderGooglePhotosStream();
    }

    function clearPhotoSearch() {
      document.getElementById('gSearchInput').value = "";
      activeSearchQuery = "";
      document.getElementById('clearSearchBtn').classList.add('hidden');
      renderGooglePhotosStream();
    }

    // Lightbox Controls
    function openLightbox(index) {
      currentLightboxIdx = index;
      const photo = visiblePhotos[currentLightboxIdx];
      if (!photo) return;
      document.getElementById('gLightboxModal').classList.remove('hidden');
      document.getElementById('gLightboxModal').classList.add('flex');
      updateLightboxDisplay();
    }

    function closeLightbox() {
      document.getElementById('gLightboxModal').classList.add('hidden');
      document.getElementById('gLightboxModal').classList.remove('flex');
    }

    function updateLightboxDisplay() {
      const photo = visiblePhotos[currentLightboxIdx];
      if (!photo) return;

      document.getElementById('lbMainImage').src = photo.imageUrl;
      document.getElementById('lbHeaderTitle').innerText = photo.caption;
      document.getElementById('lbHeaderSubtitle').innerText = `${photo.dateDisplay} • ${photo.location}`;

      document.getElementById('drawerCaption').innerText = photo.caption;
      document.getElementById('drawerDate').innerText = photo.dateDisplay;
      document.getElementById('drawerTime').innerText = photo.timeDisplay;
      document.getElementById('drawerAuthor').innerText = photo.author;
      document.getElementById('drawerOffice').innerText = photo.office;
      document.getElementById('drawerLocation').innerText = photo.location;
      document.getElementById('drawerDevice').innerText = photo.device;
      document.getElementById('drawerExif').innerText = photo.exif;
    }

    function prevPhoto() {
      currentLightboxIdx = (currentLightboxIdx > 0) ? currentLightboxIdx - 1 : visiblePhotos.length - 1;
      updateLightboxDisplay();
    }

    function nextPhoto() {
      currentLightboxIdx = (currentLightboxIdx < visiblePhotos.length - 1) ? currentLightboxIdx + 1 : 0;
      updateLightboxDisplay();
    }

    function toggleInfoDrawer() {
      const drawer = document.getElementById('lbInfoDrawer');
      drawer.classList.toggle('hidden');
      drawer.classList.toggle('flex');
    }

    function downloadCurrentPhoto() {
      const photo = visiblePhotos[currentLightboxIdx];
      if (!photo) return;
      const link = document.createElement('a');
      link.href = photo.imageUrl;
      link.download = `PRDP_Mission_${photo.id}.jpg`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      showToast("Downloading photo...");
    }

    // Unauthenticated Participant Upload
    function openUploadModal() {
      document.getElementById('gUploadModal').classList.remove('hidden');
      document.getElementById('gUploadModal').classList.add('flex');
    }

    function closeUploadModal() {
      document.getElementById('gUploadModal').classList.add('hidden');
      document.getElementById('gUploadModal').classList.remove('flex');
      clearUploadFile();
    }

    function handleFileInputPreview(event) {
      const file = event.target.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = function(e) {
        tempUploadBase64 = e.target.result;
        document.getElementById('tempImgPreview').src = tempUploadBase64;
        document.getElementById('dropPrompt').classList.add('hidden');
        document.getElementById('dropPreview').classList.remove('hidden');
      };
      reader.readAsDataURL(file);
    }

    function clearUploadFile() {
      tempUploadBase64 = null;
      document.getElementById('fileInput').value = "";
      document.getElementById('dropPreview').classList.add('hidden');
      document.getElementById('dropPrompt').classList.remove('hidden');
    }

    function handleNewPhotoUpload() {
      if (!tempUploadBase64) {
        showToast("Please select a photo to upload.");
        return;
      }

      const caption = document.getElementById('upCaption').value.trim();
      const author = document.getElementById('upAuthor').value.trim();
      const office = document.getElementById('upOffice').value.trim();
      const location = document.getElementById('upLocation').value.trim() || "Mission Site";

      const now = new Date();
      const timeStr = now.toLocaleTimeString([], {
        hour: '2-digit',
        minute: '2-digit'
      }) + ' PST';

      const newPhoto = {
        id: Date.now(),
        dateGroup: "Today",
        dateDisplay: "October 16, 2026",
        timeDisplay: timeStr,
        caption: caption,
        author: author,
        office: office,
        location: location,
        device: "Participant Camera",
        exif: "f/1.8 • 1/500 • ISO 200",
        imageUrl: tempUploadBase64
      };

      photos.unshift(newPhoto);
      document.getElementById('publicUploadForm').reset();
      closeUploadModal();
      renderGooglePhotosStream();
      showToast("Photo published! Visible to all mission delegates.");
      document.getElementById('gallery').scrollIntoView({
        behavior: 'smooth'
      });
    }

     // Clipboard Helper
    function copyToClipboard(text, msg) {
      const temp = document.createElement('input');
      temp.value = text;
      document.body.appendChild(temp);
      temp.select();
      document.execCommand('copy');
      document.body.removeChild(temp);
      showToast(msg);
    }

    // Toast Box
    function showToast(msg) {
      const box = document.getElementById('toastBox');
      const text = document.getElementById('toastMsg');
      if (!box || !text) return;
      text.innerText = msg;
      box.classList.remove('translate-y-28', 'opacity-0');
      box.classList.add('translate-y-0', 'opacity-100');
      setTimeout(() => {
        box.classList.add('translate-y-28', 'opacity-0');
        box.classList.remove('translate-y-0', 'opacity-100');
      }, 3000);
    }

    // Keyboard shortcuts for Lightbox & AGT
    window.addEventListener('keydown', (e) => {
      const modal = document.getElementById('gLightboxModal');
      if (!modal.classList.contains('hidden')) {
        if (e.key === 'ArrowLeft') prevPhoto();
        if (e.key === 'ArrowRight') nextPhoto();
        if (e.key === 'Escape') closeLightbox();
      }
      const geoModal = document.getElementById('geotagModal');
      if (!geoModal.classList.contains('hidden') && e.key === 'Escape') {
        closeGeotagModal();
      }
    });
  </script>
</body>

</html>