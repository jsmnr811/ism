    <!-- ================= OFFICIAL REPORTS, PRESENTATIONS & DELIVERABLES ================= -->
    <section id="reports-section" class="py-10 bg-slate-50 border-b border-slate-200" x-data="reportsList()">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
          <div>
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider bg-emerald-100/70 border border-emerald-200 px-3 py-1 rounded-full">
              Mission Documentation
            </span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2"
              x-text="`Reports &amp; Presentations (${filteredReports.length})`"></h2>
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

          <template x-if="focusedMeeting">
            <div class="ml-auto flex items-center gap-2 shrink-0">
              <span class="hidden sm:inline text-[11px] text-slate-400">Showing reports for</span>
              <button @click="clearMeetingFocus()"
                class="inline-flex items-center gap-2 pl-2 pr-2.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-600 text-white shadow-sm hover:bg-emerald-700 transition">
                <span class="w-5 h-5 rounded-md bg-white/20 flex items-center justify-center">
                  <i class="fa-solid fa-filter w-2.5 h-2.5"></i>
                </span>
                <span class="max-w-[180px] truncate" x-text="focusedMeeting.title"></span>
                <span class="px-1.5 py-0.5 rounded-md bg-white/20 text-[10px] font-bold" x-text="`${focusedMeeting.reportCount} report${focusedMeeting.reportCount > 1 ? 's' : ''}`"></span>
                <i class="fa-solid fa-xmark w-3 h-3 opacity-80 hover:opacity-100"></i>
              </button>
            </div>
          </template>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100 shadow-sm overflow-hidden text-xs" id="reportsListContainer">
          <template x-if="filteredReports.length === 0" x-cloak>
            <div class="p-8 text-center text-xs text-slate-400 bg-white" x-text="focusedMeeting ? 'No reports available for this meeting.' : 'No reports match the selected filter or search term.'"></div>
          </template>

          <template x-for="group in reportGroups" :key="group.date">
            <div class="mb-4 last:mb-0">
              <div class="bg-slate-100 px-4 py-2 border-b border-slate-200 flex items-center gap-2 flex-wrap">
                <span class="text-xs font-bold text-slate-700" x-text="group.label"></span>
                <span class="text-xs text-slate-500" x-text="`(${group.reports.length} document(s))`"></span>
                <template x-if="focusedMeeting">
                  <span class="inline-flex items-center gap-1.5 ml-auto px-2 py-0.5 rounded-lg bg-white border border-emerald-200 text-emerald-700 text-[10px] font-bold">
                    <i class="fa-solid fa-video w-3 h-3"></i>
                    <span class="truncate max-w-[200px]" x-text="group.reports[0]?.meetingTitle"></span>
                  </span>
                </template>
              </div>
              <template x-for="rep in group.reports" :key="rep.id">
                <div class="p-4 sm:p-5 flex items-center justify-between hover:bg-slate-50 transition border-b border-slate-100 last:border-0"
                  :id="`report-${rep.id}`"
                  :class="(focusedReportId === rep.id || focusedMeetingId === rep.meetingId) ? 'bg-emerald-50 ring-2 ring-inset ring-emerald-200' : ''">
                  <div class="flex items-center space-x-3.5 pr-4 min-w-0">
                    <div :class="`p-2.5 rounded-xl border shrink-0 ${rep.format === 'PDF' ? 'bg-red-50 text-red-600 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'}`">
                      <i :class="`w-5 h-5 fa-solid ${rep.format === 'XLSX' ? 'fa-file-excel' : 'fa-file-text'}`"></i>
                    </div>
                    <div class="min-w-0">
                      <h4 class="font-bold text-slate-900 text-xs sm:text-sm truncate" x-text="rep.title"></h4>
                      <p x-show="rep.description" class="text-slate-600 text-[11px] mt-0.5 line-clamp-2" x-text="rep.description"></p>
                      <p class="text-slate-400 text-[11px] mt-0.5 truncate">
                        <span x-text="rep.format"></span>
                        <span x-show="rep.fileName" x-text="` • ${rep.fileName}`"></span>
                        <span x-show="rep.meetingTitle" x-text="` • ${rep.meetingTitle}`"></span>
                      </p>
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

