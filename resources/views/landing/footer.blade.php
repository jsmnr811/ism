  <!-- ================= FOOTER ================= -->
  <footer class="bg-white border-t border-slate-200 text-slate-600 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <img src="{{ asset('assets/scale-up-letterhead.png') }}" alt="Department of Agriculture — Philippine Rural Development Project" class="h-12 sm:h-14 w-auto object-contain object-left" />

        <nav class="flex flex-wrap items-center justify-center gap-x-4 gap-y-2 text-xs">
          <a href="#banner-section" class="hover:underline">Banner</a>
          <a href="#agenda-section" class="hover:underline">Agenda</a>
          <a href="#meetings-section" class="hover:underline">Meetings ({{ $meetingsCount }})</a>
          <a href="#site-visits-section" class="hover:underline">Site Visits ({{ $siteVisitsCount }})</a>
          <a href="#gallery" class="hover:underline font-bold text-emerald-700">All Photos</a>
          <a href="#reports-section" class="hover:underline">Reports ({{ $reportsCount }})</a>
        </nav>
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
