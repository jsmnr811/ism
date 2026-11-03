  <!-- ================= MAIN NAVIGATION ================= -->
  <header x-data="{ mobileOpen: false }" class="sticky top-0 z-50 bg-white/95 backdrop-blur-xl border-b border-slate-200/80 shadow-[0_2px_12px_rgba(15,23,42,0.06)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-[60px] sm:h-[76px]">

        <!-- Agency Logo & Title -->
        <a href="#banner-section" class="flex items-center group min-w-0">
          <img src="{{ asset('assets/scale-up-letterhead.png') }}" alt="Department of Agriculture — Philippine Rural Development Project" class="h-10 sm:h-12 w-auto object-contain object-left transition-transform duration-300 group-hover:scale-[1.02]" />
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

          @if($meetings->contains(fn($m) => !empty($m->report_documents)))
          <a href="#reports-section" class="group inline-flex items-center gap-2 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:text-emerald-700 hover:bg-emerald-50 transition-all duration-200">
            <span>Reports</span>
          </a>
          @endif

        </nav>

        <!-- Right Actions -->
        <div class="flex items-center gap-1.5 sm:gap-2.5">
          @if($registrationMission)
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

          @if($registrationMission)
          <a href="#registration-section" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-emerald-700 transition">
            <i class="fa-solid fa-user-plus w-4 h-4"></i>
            <span>Registration</span>
          </a>
          @endif

          <a href="#gallery" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-emerald-700 hover:bg-emerald-50 transition">
            <i class="fa-solid fa-images w-4 h-4"></i>
            <span>Gallery</span>
          </a>

          @if($meetings->contains(fn($m) => !empty($m->report_documents)))
          <a href="#reports-section" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-emerald-700 transition">
            <i class="fa-solid fa-files w-4 h-4"></i>
            <span>Reports and Presentations</span>
          </a>
          @endif

        </div>

        @if($registrationMission)
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
