    <!-- ================= MEETINGS DIRECTORY ================= -->
    <section id="meetings-section" class="py-10 bg-slate-50 border-b border-slate-200"
      x-data="meetingsDirectory()"
      @keydown.escape.window="descModalOpen = false">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
          <div>
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider bg-emerald-50 border border-emerald-200 px-3 py-1 rounded-full">
              {{ $meetingsMission ? $meetingsMission->title : 'No Mission' }}
            </span>
            <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Meetings Directory ({{ $meetings->count() }})</h2>
            <p class="text-slate-600 text-sm mt-1">Your session guide — dates, times, venues, and join details mapped to <code>meetings</code>. </p>
          </div>
          <div class="w-full md:w-80">
            <div class="relative">
              <i class="fa-solid fa-search w-4 h-4 absolute left-3 top-3 text-slate-400"></i>
              <input type="text" x-model="searchQuery" placeholder="Search title, type, venue, or description..." class="w-full pl-9 pr-9 py-2.5 rounded-xl border border-slate-300 text-sm bg-white focus:border-emerald-600 outline-none" />
              <button x-show="searchQuery" @click="searchQuery = ''" class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg">
                <i class="fa-solid fa-x w-3 h-3"></i>
              </button>
            </div>
            <p class="text-[11px] text-slate-500 mt-1.5 text-right" x-text="`Showing ${filteredMeetings.length} of ${allMeetings.length} meeting${allMeetings.length === 1 ? '' : 's'}`"></p>
          </div>
        </div>

        <!-- Group By Toggle -->
        <div class="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <div class="inline-flex items-center gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200 self-start">
            <button type="button" @click="groupBy = 'date'"
              :class="groupBy === 'date' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900'"
              class="px-4 py-2 rounded-lg text-xs font-bold transition">
              <i class="fa-solid fa-calendar-days w-3.5 h-3.5"></i> Group by Date
            </button>
            <button type="button" @click="groupBy = 'type'"
              :class="groupBy === 'type' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-900'"
              class="px-4 py-2 rounded-lg text-xs font-bold transition">
              <i class="fa-solid fa-layer-group w-3.5 h-3.5"></i> Group by Type
            </button>
          </div>

          <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            AGT Coordinates Certified by Regional Engineering Units
          </div>
        </div>

        <div id="meetingsGroupsContainer" class="space-y-10">
          <template x-for="group in meetingGroups" :key="group.key">
            <div class="space-y-4 pt-1">
              <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b"
                :class="group.isToday ? 'border-emerald-300' : 'border-slate-200'">
                <div class="flex items-start gap-3">
                  <span class="mt-0.5 w-9 h-9 rounded-xl border flex items-center justify-center shrink-0"
                    :class="group.isToday ? 'bg-emerald-600 text-white border-emerald-600' : group.soft">
                    <i class="fa-solid w-4 h-4" :class="group.icon"></i>
                  </span>
                  <div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <h3 class="text-base font-extrabold text-slate-900 leading-tight" x-text="group.label"></h3>
                      <span x-show="group.isToday"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-600 text-white">
                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        Today
                      </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="group.blurb"></p>
                  </div>
                </div>
                <div class="flex flex-wrap items-center gap-1.5">
                  <template x-for="chip in group.chips" :key="chip.label">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold" :class="group.soft">
                      <i class="fa-solid w-3 h-3" :class="chip.icon"></i>
                      <span x-text="chip.label"></span>
                      <span class="opacity-60" x-text="chip.count"></span>
                    </span>
                  </template>
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                    <i class="fa-solid fa-video w-3 h-3"></i>
                    <span x-text="`${group.meetings.length} meeting${group.meetings.length > 1 ? 's' : ''}`"></span>
                  </span>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5"
                :class="group.isToday ? 'rounded-2xl bg-emerald-50/50 ring-1 ring-emerald-200/70 p-4 -m-1' : ''">
                <template x-for="m in group.meetings" :key="m.id">
                  <article class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md hover:border-emerald-200 transition h-full flex flex-col overflow-hidden">
                    <div class="flex items-start justify-between gap-2 p-5 pb-3">
                      <span :class="`w-fit inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[9px] font-bold border ${getTypeBadgeClass(m.type)}`">
                        <i class="fa-solid fa-tag w-2.5 h-2.5"></i>
                        <span x-text="m.type"></span>
                      </span>
                      <span x-show="isUpcoming(m)"
                        class="w-fit inline-flex items-center gap-1 px-2 py-1 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Upcoming
                      </span>
                    </div>

                    <div class="px-5">
                      <h4 class="text-sm font-bold text-slate-900 leading-snug" x-text="m.title"></h4>
                      <div class="flex items-start gap-1 mt-1" x-show="m.description">
                        <p class="text-xs text-slate-600 line-clamp-2 flex-1" x-text="m.description"></p>
                        <button x-show="(m.description || '').length > 50"
                          @click="openModal(m)"
                          class="shrink-0 w-5 h-5 flex items-center justify-center text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-md"
                          title="Read full description">
                          <i class="fa-solid fa-chevron-down text-[10px]"></i>
                        </button>
                      </div>
                    </div>

                    <div class="px-5 pt-4">
                      <div class="flex items-stretch gap-3 bg-slate-50 border border-slate-100 rounded-xl p-3">
                        <div class="flex flex-col items-center justify-center shrink-0 w-12 rounded-lg bg-white border border-slate-200 py-1.5">
                          <span class="text-lg font-extrabold text-slate-900 leading-none" x-text="dayOfMonth(m.date)"></span>
                          <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 mt-0.5" x-text="monthShort(m.date)"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                          <p class="text-xs font-bold text-slate-800 leading-tight" x-text="formatDate(m.date) || 'Date to be confirmed'"></p>
                          <p class="text-[11px] text-slate-400 mt-0.5" x-show="weekdayOf(m.date)" x-text="weekdayOf(m.date)"></p>
                          <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                            <i class="fa-solid fa-clock w-3 h-3 text-slate-400"></i>
                            <span x-text="(formatTime(m.time_from) || '—') + ' – ' + (formatTime(m.time_to) || '—')"></span>
                          </p>
                          <p class="text-[11px] text-slate-600 mt-1 flex items-start gap-1 leading-snug">
                            <i class="fa-solid fa-location-dot w-3 h-3 text-slate-400 mt-0.5"></i>
                            <span class="break-words" x-text="m.venue || 'Venue to be announced'"></span>
                          </p>
                        </div>
                      </div>
                    </div>

                    <div class="px-5 pt-3 flex-1">
                      <template x-if="m.meeting_id">
                        <div class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100">
                          <span class="text-[11px] text-slate-400 flex items-center gap-1.5"><i class="fa-solid fa-id-card w-3.5 h-3.5"></i>Meeting ID</span>
                          <span class="font-mono text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <span x-text="m.meeting_id"></span>
                            <button @click="copyToClipboard(m.meeting_id, 'Copied Meeting ID!')" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 rounded-md" title="Copy meeting ID">
                              <i class="fa-solid fa-copy w-3 h-3"></i>
                            </button>
                          </span>
                        </div>
                      </template>
                      <template x-if="m.passcode">
                        <div class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100">
                          <span class="text-[11px] text-slate-400 flex items-center gap-1.5"><i class="fa-solid fa-lock w-3.5 h-3.5"></i>Passcode</span>
                          <span class="font-mono text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <span x-text="m.passcode"></span>
                            <button @click="copyToClipboard(m.passcode, 'Copied Passcode!')" class="w-6 h-6 flex items-center justify-center text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 rounded-md" title="Copy passcode">
                              <i class="fa-solid fa-copy w-3 h-3"></i>
                            </button>
                          </span>
                        </div>
                      </template>
                      <template x-if="m.reports_count > 0">
                        <button type="button"
                          @click="focusMeetingReports(m.id)"
                          class="group w-full flex items-center gap-3 px-3 py-2.5 rounded-xl bg-white border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/60 hover:shadow-sm transition-all">
                          <span class="w-8 h-8 shrink-0 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center transition group-hover:bg-emerald-600 group-hover:text-white group-hover:border-emerald-600">
                            <i class="fa-solid fa-file-lines w-3.5 h-3.5"></i>
                          </span>
                          <span class="min-w-0 flex-1 text-left">
                            <span class="block text-[11px] font-bold text-slate-800 leading-tight group-hover:text-emerald-900"
                              x-text="`${m.reports_count} report${m.reports_count > 1 ? 's' : ''} available`"></span>
                            <span class="block text-[10px] text-slate-500 leading-tight mt-0.5 group-hover:text-emerald-700">View in Reports &amp; Presentations</span>
                          </span>
                          <i class="fa-solid fa-chevron-right w-3 h-3 text-slate-300 shrink-0 transition-all group-hover:text-emerald-600 group-hover:translate-x-0.5"></i>
                        </button>
                      </template>
                    </div>

                    <div class="p-5 pt-4">
                      <template x-if="isToday(m)">
                        <a :href="m.link || '#'"
                          :class="m.link ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm hover:shadow-md' : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                          :target="m.link ? '_blank' : null"
                          class="w-full py-2.5 rounded-xl font-bold text-xs flex items-center justify-center space-x-1.5 transition">
                          <i class="fa-solid fa-video w-3.5 h-3.5"></i>
                          <span x-text="m.link ? 'Join Room' : 'Link not available'"></span>
                        </a>
                      </template>
                      <template x-if="!isToday(m)">
                        <div class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-500 text-xs font-bold flex items-center justify-center gap-1.5">
                          <i class="fa-regular fa-clock w-3.5 h-3.5"></i>
                          <span x-text="isPast(m) ? 'Session concluded' : 'Opens on the session date'"></span>
                        </div>
                      </template>
                    </div>
                  </article>
                </template>
              </div>
              <div x-show="group.hasMoreMeetings" x-cloak class="pt-2 text-center">
                <button type="button" @click="toggleTypeGroup(group.label)"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">
                  <i class="fa-solid fa-chevron-down w-3.5 h-3.5"></i>
                  <span>View more</span>
                </button>
              </div>
            </div>
          </template>

          <div x-show="groupBy === 'date' && !showAllDateGroups && totalDateGroups > 1" x-cloak class="pt-2 text-center">
            <button type="button" @click="toggleAllDateGroups()"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition">
              <i class="fa-solid fa-chevron-down w-3.5 h-3.5"></i>
              <span>View more dates</span>
            </button>
          </div>

          <div x-show="meetingGroups.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center">
            <span class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center mx-auto mb-3 shadow-sm">
              <i class="fa-solid fa-video w-5 h-5"></i>
            </span>
            <p class="text-sm font-bold text-slate-700" x-text="searchQuery ? 'No meetings match your search' : 'No meetings recorded yet'"></p>
            <p class="text-xs text-slate-500 mt-1" x-text="searchQuery ? 'Try a different title, type, or venue.' : 'Session schedules for this mission will appear here once logged.'"></p>
          </div>
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
                <div class="flex justify-between gap-4">
                  <span class="text-slate-400 shrink-0">Date:</span>
                  <span class="font-bold text-slate-800 text-right" x-text="formatDate(selectedMeeting.date) || '—'"></span>
                </div>
                <div class="flex justify-between gap-4">
                  <span class="text-slate-400 shrink-0">Duration:</span>
                  <span class="font-bold text-slate-800 text-right" x-text="(formatTime(selectedMeeting.time_from) || '—') + ' - ' + (formatTime(selectedMeeting.time_to) || '—')"></span>
                </div>
                <div class="flex justify-between gap-4 font-sans text-slate-600">
                  <span class="text-slate-400 shrink-0">Venue:</span>
                  <span class="font-semibold text-slate-700 text-right break-words" x-text="selectedMeeting.venue || 'To be announced'"></span>
                </div>
                <template x-if="selectedMeeting.meeting_id || selectedMeeting.passcode">
                  <div class="mt-2 space-y-1">
                    <template x-if="selectedMeeting.meeting_id">
                      <div class="flex justify-between items-center gap-4">
                        <span class="text-slate-400 shrink-0">Meeting ID:</span>
                        <span class="font-bold text-slate-800 flex items-center gap-1">
                          <span x-text="selectedMeeting.meeting_id"></span>
                          <button @click="copyToClipboard(selectedMeeting.meeting_id, 'Copied Meeting ID!')" class="text-slate-400 hover:text-emerald-700">
                            <i class="fa-solid fa-copy w-3 h-3"></i>
                          </button>
                        </span>
                      </div>
                    </template>
                    <template x-if="selectedMeeting.passcode">
                      <div class="flex justify-between items-center gap-4">
                        <span class="text-slate-400 shrink-0">Passcode:</span>
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
                <template x-if="selectedMeeting.reports_count > 0">
                  <button type="button"
                    @click="descModalOpen = false; focusMeetingReports(selectedMeeting.id)"
                    class="group mt-4 w-full flex items-center gap-3 px-4 py-3 rounded-xl bg-white border border-slate-200 hover:border-emerald-400 hover:bg-emerald-50/60 hover:shadow-sm transition-all">
                    <span class="w-9 h-9 shrink-0 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center transition group-hover:bg-emerald-600 group-hover:text-white group-hover:border-emerald-600">
                      <i class="fa-solid fa-file-lines w-4 h-4"></i>
                    </span>
                    <span class="min-w-0 flex-1 text-left">
                      <span class="block text-xs font-bold text-slate-800 leading-tight group-hover:text-emerald-900"
                        x-text="`${selectedMeeting.reports_count} report${selectedMeeting.reports_count > 1 ? 's' : ''} available`"></span>
                      <span class="block text-[11px] text-slate-500 leading-tight mt-0.5 group-hover:text-emerald-700">View in Reports &amp; Presentations</span>
                    </span>
                    <i class="fa-solid fa-chevron-right w-3.5 h-3.5 text-slate-300 shrink-0 transition-all group-hover:text-emerald-600 group-hover:translate-x-0.5"></i>
                  </button>
                </template>
              </div>
              <template x-if="isToday(selectedMeeting)">
                <a :href="selectedMeeting.link || '#'"
                  :class="selectedMeeting.link ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-slate-100 text-slate-400 cursor-not-allowed'"
                  :target="selectedMeeting.link ? '_blank' : null"
                  class="w-full py-2.5 rounded-xl font-bold text-sm flex items-center justify-center gap-2 transition mt-4">
                  <i class="fa-solid fa-video"></i>
                  <span x-text="selectedMeeting.link ? 'Join Room' : 'Link not available'"></span>
                </a>
              </template>
              <template x-if="!isToday(selectedMeeting)">
                <div class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-500 font-bold text-sm flex items-center justify-center gap-2 mt-4">
                  <i class="fa-regular fa-clock"></i>
                  <span x-text="isPast(selectedMeeting) ? 'Session concluded' : 'Opens on the session date'"></span>
                </div>
              </template>
            </div>
          </template>
        </div>
      </div>
    </section>

