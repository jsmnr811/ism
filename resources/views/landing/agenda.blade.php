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
                    <i :class="`fa-solid fa-${item.type === 'meeting' ? 'video' : 'map-pin'} w-4 h-4`"></i>
                    <span class="text-[8px] font-bold uppercase mt-0.5" x-text="item.type === 'meeting' ? 'Meet' : 'Visit'"></span>
                  </div>
                  <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                      <span :class="`px-2 py-0.5 rounded text-[10px] font-bold ${item.type === 'meeting' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800'}`" x-text="item.categoryLabel"></span>
                      <template x-if="item.time">
                        <span class="text-xs text-slate-500 font-medium">
                          <i class="fa-solid fa-clock w-3 h-3 inline mr-1 text-slate-400"></i>
                          <span x-text="item.time"></span>
                        </span>
                      </template>
                      <template x-if="item.time">
                        <span class="text-xs text-slate-400">•</span>
                      </template>
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
                      <template x-if="item.isToday">
                        <a :href="item.link || '#'" :target="item.link ? '_blank' : null"
                          :class="item.link ? 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm' : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                          class="px-3.5 py-1.5 rounded-lg text-xs font-semibold inline-flex items-center gap-1.5 transition">
                          <i class="fa-solid fa-video w-3.5 h-3.5"></i> Join Session
                        </a>
                      </template>
                      <template x-if="!item.isToday">
                        <span class="px-3.5 py-1.5 rounded-lg bg-slate-100 text-slate-500 text-xs font-semibold inline-flex items-center gap-1.5">
                          <i class="fa-regular fa-clock w-3.5 h-3.5"></i>
                          <span x-text="item.isPast ? 'Session concluded' : 'Opens on the session date'"></span>
                        </span>
                      </template>
                      <span class="text-[10px] text-slate-400 font-mono" x-show="item.meeting_id" x-text="`ID: ${item.meeting_id}`"></span>
                    </div>
                  </template>
                  <template x-if="item.type === 'site_visit'">
                    <div class="flex flex-col md:items-end gap-2">
                      <template x-if="item.hasPhotos">
                        <button @click="openGeotagModal(item.sp_id)" class="px-3.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-sm">
                          <i class="fa-solid fa-camera w-3.5 h-3.5"></i> Inspect Photos
                        </button>
                      </template>
                      <template x-if="item.hasPhotos">
                        <span class="text-[10px] text-slate-400">AGT Station Dossier</span>
                      </template>
                    </div>
                  </template>
                </div>
              </div>
            </div>
          </template>

          <div x-show="!showAllAgenda && filteredAllActivities.length > 5" x-cloak class="pt-2 text-center">
            <button type="button" @click="showAllAgenda = true"
              class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">
              <i class="fa-solid fa-chevron-down w-3.5 h-3.5"></i>
              <span>View more</span>
            </button>
          </div>
        </div>

      </div>
    </section>

