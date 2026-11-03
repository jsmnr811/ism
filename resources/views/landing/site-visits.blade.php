    <!-- ================= VIRTUAL SITE VISITS ================= -->
    <section id="site-visits-section" class="py-16 bg-white border-b border-slate-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
          <div>
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
              {{ isset($siteVisitsMission) && $siteVisitsMission ? $siteVisitsMission->title : ($activeMission ? $activeMission->title : 'Previous Mission') }}
            </span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Geotagged Site Visits ({{ $siteVisits->count() }})</h2>
            <p class="text-slate-600 text-sm mt-1">
              Missions' Site Visits across Luzon, Visayas, and Mindanao Clusters.
            </p>
          </div>

        </div>

        <!-- Group By Toggle -->
        <div class="mb-8">
          <div class="inline-flex items-center gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200 self-start" id="siteVisitsGroupToggle">
            <button type="button" data-group-by="component"
              class="px-4 py-2 rounded-lg text-xs font-bold transition">
              <i class="fa-solid fa-layer-group w-3.5 h-3.5"></i> Group by Component
            </button>
            <button type="button" data-group-by="cluster"
              class="px-4 py-2 rounded-lg text-xs font-bold transition">
              <i class="fa-solid fa-map-location-dot w-3.5 h-3.5"></i> Group by Cluster
            </button>
          </div>
        </div>

        <div id="siteVisitsGroupsContainer" class="space-y-10">
          <!-- Site visit groups rendered by JavaScript -->
        </div>

      </div>
    </section>

