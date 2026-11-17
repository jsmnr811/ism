  <!-- ================= LAZY DATASETS, SECTION LOADER & PAGE LOGIC ================= -->
  @php
  $officeLabelsJson = json_encode(\App\Enums\MissionOffice::labels());
  $managementTierLabelsJson = json_encode(\App\Enums\MissionManagementTier::labels());
  $psoClusterLabelsJson = json_encode(\App\Enums\MissionPsoCluster::labels());
  $rpcoOfficeLabelsJson = json_encode(\App\Enums\MissionRpcoOffice::labels());
  $componentUnitLabelsJson = json_encode(\App\Enums\MissionComponentUnit::labels());
  $sexLabelsJson = json_encode(\App\Enums\Sex::labels());
  $suffixLabelsJson = json_encode(\App\Enums\Suffix::labels());
  @endphp
  <script>
    // Static enum labels rendered with the page - no database round-trip required
    const officeLabels = {!! $officeLabelsJson !!};
    const managementTierLabels = {!! $managementTierLabelsJson !!};
    const psoClusterLabels = {!! $psoClusterLabelsJson !!};
    const rpcoOfficeLabels = {!! $rpcoOfficeLabelsJson !!};
    const componentUnitLabels = {!! $componentUnitLabelsJson !!};
    const sexLabels = {!! $sexLabelsJson !!};
    const suffixLabels = {!! $suffixLabelsJson !!};

    // ----------------------------------------------------
    // LAZY DATA - REQUESTED ONLY WHEN A SECTION NEEDS IT
    // ----------------------------------------------------
    let meetingsData = [];
    let siteVisitsData = [];
    let missionPhotosData = [];
    let canUploadPhotos = false;
    let photos = [];
    let landingDataPromise = null;

    function loadLandingData() {
      if (!landingDataPromise) {
        landingDataPromise = fetch(@json(route('landing.data')), {
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
          .then(response => {
            if (!response.ok) throw new Error('Unable to load landing data.');
            return response.json();
          })
          .then(data => {
            meetingsData = Array.isArray(data.meetings) ? data.meetings : [];
            siteVisitsData = Array.isArray(data.siteVisits) ? data.siteVisits : [];
            missionPhotosData = Array.isArray(data.photos) ? data.photos : [];
            photos = [...missionPhotosData];
            canUploadPhotos = !!data.canUploadPhotos;
            return data;
          })
          .catch(error => {
            console.error(error);
            return {};
          });
      }

      return landingDataPromise;
    }

    // ----------------------------------------------------
    // ALPINE LAZY SECTION - FETCHES HTML ONLY WHEN NEAR VIEWPORT
    // ----------------------------------------------------
    function lazySection(name) {
      return {
        html: '',
        loaded: false,
        loading: false,
        init() {
          if (!('IntersectionObserver' in window)) {
            this.load();
            return;
          }

          const observer = new IntersectionObserver((entries) => {
            if (entries.some(entry => entry.isIntersecting)) {
              observer.disconnect();
              this.load();
            }
          }, { rootMargin: '600px 0px' });

          observer.observe(this.$el);
        },
        async load() {
          if (this.loading || this.loaded) return;
          this.loading = true;

          try {
            const [, response] = await Promise.all([
              loadLandingData(),
              fetch(`{!! url('/landing/section') !!}/${name}`, {
                headers: {
                  'Accept': 'text/html',
                  'X-Requested-With': 'XMLHttpRequest'
                }
              })
            ]);

            if (!response.ok) throw new Error(`Unable to load ${name} section.`);

            this.html = await response.text();
            this.loaded = true;
            const notify = () => window.dispatchEvent(new CustomEvent('landing:section-loaded', {
              detail: name
            }));
            if (typeof this.$nextTick === 'function') {
              this.$nextTick(notify);
            } else {
              setTimeout(notify, 0);
            }
          } catch (error) {
            console.error(error);
          } finally {
            this.loading = false;
          }
        }
      };
    }

    window.addEventListener('landing:section-loaded', (event) => {
      if (event.detail === 'site-visits') renderSiteVisitsGrid();
      if (event.detail === 'gallery') renderGooglePhotosStream();
    });

    // Resolve in-page anchor links to their lazy section on demand
    const LAZY_SECTION_ANCHORS = {
      '#agenda-section': 'agenda',
      '#meetings-section': 'meetings',
      '#site-visits-section': 'site-visits',
      '#registration-section': 'registration',
      '#gallery': 'gallery',
      '#reports-section': 'reports',
    };

    function lazySectionComponent(name) {
      const wrapper = document.querySelector(`[data-lazy-section="${name}"]`);
      if (!wrapper || !window.Alpine) return null;

      return window.Alpine.$data(wrapper);
    }

    function loadSectionForHash() {
      const name = LAZY_SECTION_ANCHORS[window.location.hash];
      if (!name) return;

      const component = lazySectionComponent(name);
      if (component && !component.loaded) component.load();
    }

    document.addEventListener('click', (event) => {
      const link = event.target.closest('a[href^="#"]');
      if (!link) return;

      const name = LAZY_SECTION_ANCHORS[link.getAttribute('href')];
      if (!name) return;

      const component = lazySectionComponent(name);
      if (!component || component.loaded) return;

      event.preventDefault();
      component.load().then(() => {
        document.getElementById(link.getAttribute('href').slice(1))?.scrollIntoView({ behavior: 'smooth' });
      });
    });

    window.addEventListener('hashchange', loadSectionForHash);
    document.addEventListener('DOMContentLoaded', loadSectionForHash);

    let activeSearchQuery = "";
    let currentLightboxIdx = 0;
    let visiblePhotos = [];
    let pendingUploads = [];
    let activeAgtSp = null;
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

    function dayOfMonth(dateStr) {
      if (!dateStr) return '—';
      return String(parseInt(dateStr.split('-')[2]));
    }

    function monthShort(dateStr) {
      if (!dateStr) return '—';
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      return months[parseInt(dateStr.split('-')[1]) - 1];
    }

    function weekdayOf(dateStr) {
      if (!dateStr) return '';
      const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
      const [y, m, d] = dateStr.split('-').map(Number);
      return days[new Date(y, m - 1, d).getDay()];
    }

    function todayDateString() {
      const now = new Date();
      const month = String(now.getMonth() + 1).padStart(2, '0');
      const day = String(now.getDate()).padStart(2, '0');
      return `${now.getFullYear()}-${month}-${day}`;
    }

    function focusReport(reportId) {
      window.dispatchEvent(new CustomEvent('focus-report', {
        detail: reportId
      }));
    }

    function focusMeetingReports(meetingId) {
      window.dispatchEvent(new CustomEvent('focus-meeting-reports', {
        detail: meetingId
      }));
    }

    function isUpcoming(meeting) {
      if (!meeting || !meeting.date) return false;
      const endTime = meeting.time_to ? `${meeting.date} ${meeting.time_to}` : `${meeting.date} 23:59`;
      return new Date(endTime.replace(' ', 'T')) >= new Date();
    }

    function isToday(meeting) {
      return !!meeting && !!meeting.date && meeting.date === todayDateString();
    }

    function isPast(meeting) {
      if (!meeting || !meeting.date) return false;
      const endTime = meeting.time_to ? `${meeting.date} ${meeting.time_to}` : `${meeting.date} 23:59`;
      return new Date(endTime.replace(' ', 'T')) < new Date();
    }

    // ----------------------------------------------------
    // MISSION AGENDA ALPINE COMPONENT
    // ----------------------------------------------------
    function agendaTimeline() {
      return {
        currentDate: 'all',
        currentType: 'all',
        sheetEmbedOpen: false,
        isFullscreen: false,
        showAllAgenda: false,
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
              meeting_id: m.meeting_id,
              isToday: isToday(m),
              isPast: isPast(m)
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
              time: '',
              venue: s.location,
              hasPhotos: Array.isArray(s.images) && s.images.length > 0,
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
        get filteredAllActivities() {
          return this.allActivities.filter(act => {
            const matchesDate = (this.currentDate === 'all' || act.date === this.currentDate);
            const matchesType = (this.currentType === 'all' || act.type === this.currentType);
            return matchesDate && matchesType;
          });
        },
        get filteredActivities() {
          const filtered = this.filteredAllActivities;
          return this.showAllAgenda ? filtered : filtered.slice(0, 5);
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
      const TYPE_ACCENTS = {
        'Kick Off': {
          icon: 'fa-flag',
          soft: 'bg-emerald-50 text-emerald-700 border-emerald-200',
          blurb: 'Opening sessions that set the mission agenda and expectations.'
        },
        'Technical Discussions': {
          icon: 'fa-comments',
          soft: 'bg-sky-50 text-sky-700 border-sky-200',
          blurb: 'Working sessions on subproject engineering, procurement, and safeguards.'
        },
        'Wrap Up': {
          icon: 'fa-flag-checkered',
          soft: 'bg-violet-50 text-violet-700 border-violet-200',
          blurb: 'Closing sessions consolidating outputs and agreed action points.'
        },
        'Other': {
          icon: 'fa-layer-group',
          soft: 'bg-slate-100 text-slate-600 border-slate-200',
          blurb: 'Other sessions recorded for this mission.'
        }
      };

      const TYPE_ORDER = ['Ribbon Cutting', 'Kick Off', 'Technical Discussions', 'Wrap Up', 'Other'];

      return {
        searchQuery: '',
        selectedMeeting: null,
        descModalOpen: false,
        groupBy: 'date',
        showAllDateGroups: false,
        expandedTypeGroups: [],
        toggleAllDateGroups() {
          this.showAllDateGroups = true;
        },
        toggleTypeGroup(type) {
          if (!this.expandedTypeGroups.includes(type)) {
            this.expandedTypeGroups.push(type);
          }
        },
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
        get totalDateGroups() {
          const groups = new Set(this.filteredMeetings.map(m => m.date || 'Undated'));
          return groups.size;
        },
        get meetingGroups() {
          const meetings = this.filteredMeetings;

          if (this.groupBy === 'date') {
            const groups = new Map();
            meetings.forEach(m => {
              const key = m.date || 'Undated';
              if (!groups.has(key)) groups.set(key, []);
              groups.get(key).push(m);
            });

            const today = todayDateString();

            const sortedGroups = Array.from(groups.entries())
              .sort((a, b) => a[0].localeCompare(b[0]))
              .map(([date, items]) => ({
                key: `date-${date}`,
                label: date === 'Undated' ? 'Undated' : formatDate(date),
                blurb: date === 'Undated' ? 'Sessions without a scheduled date.' : `${weekdayOf(date)} • ${items.length} session${items.length > 1 ? 's' : ''} scheduled`,
                icon: 'fa-calendar-day',
                soft: 'bg-slate-100 text-slate-600 border-slate-200',
                isToday: date === today,
                meetings: items,
                chips: this.buildTypeChips(items)
              }));

            return this.showAllDateGroups ? sortedGroups : sortedGroups.slice(0, 1);
          }

          const groups = new Map();
          meetings.forEach(m => {
            const key = m.type_key || 'Other';
            if (!groups.has(key)) groups.set(key, []);
            groups.get(key).push(m);
          });

          return Array.from(groups.entries())
            .sort((a, b) => {
              const ai = TYPE_ORDER.indexOf(a[0]);
              const bi = TYPE_ORDER.indexOf(b[0]);
              if (ai === -1 && bi === -1) return a[0].localeCompare(b[0]);
              if (ai === -1) return 1;
              if (bi === -1) return -1;
              return ai - bi;
            })
            .map(([type, items]) => {
              const meta = TYPE_ACCENTS[type] || TYPE_ACCENTS['Other'];
              const expanded = this.expandedTypeGroups.includes(type);
              const visibleItems = expanded ? items : items.slice(0, 3);
              return {
                key: `type-${type}`,
                label: type,
                blurb: meta.blurb,
                icon: meta.icon,
                soft: meta.soft,
                isToday: false,
                meetings: visibleItems,
                totalMeetings: items.length,
                hasMoreMeetings: items.length > 3 && !expanded,
                chips: this.buildVenueChips(items)
              };
            });
        },
        buildTypeChips(items) {
          const counts = {};
          items.forEach(m => {
            const key = m.type_key || 'Other';
            counts[key] = (counts[key] || 0) + 1;
          });
          return Object.entries(counts)
            .sort((a, b) => b[1] - a[1])
            .map(([label, count]) => ({
              label,
              count,
              icon: 'fa-layer-group'
            }));
        },
        buildVenueChips(items) {
          const counts = {};
          items.forEach(m => {
            const venue = m.venue || 'Unspecified venue';
            counts[venue] = (counts[venue] || 0) + 1;
          });
          return Object.entries(counts)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 3)
            .map(([label, count]) => ({
              label,
              count,
              icon: 'fa-location-dot'
            }));
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
    // GEOTAGGED SITE VISITS (grouped by component or cluster)
    // ----------------------------------------------------
    let siteVisitsGroupBy = 'component';
    const expandedSiteVisitGroups = new Set();

    const SITE_VISIT_COMPONENT_META = {
      'I-BUILD': {
        icon: 'fa-road',
        accent: 'sky',
        blurb: 'Infrastructure — farm-to-market roads, bridges, and post-production facilities.'
      },
      'I-REAP': {
        icon: 'fa-seedling',
        accent: 'emerald',
        blurb: 'Enterprise — production, processing, and marketing enterprises.'
      }
    };

    const SITE_VISIT_CLUSTER_META = {
      'North Luzon': {
        accent: 'sky'
      },
      'South Luzon': {
        accent: 'emerald'
      },
      'Visayas': {
        accent: 'amber'
      },
      'Mindanao': {
        accent: 'violet'
      }
    };

    const SITE_VISIT_ACCENTS = {
      sky: {
        soft: 'bg-sky-50 text-sky-700 border-sky-200',
        text: 'text-sky-700'
      },
      emerald: {
        soft: 'bg-emerald-50 text-emerald-700 border-emerald-200',
        text: 'text-emerald-700'
      },
      amber: {
        soft: 'bg-amber-50 text-amber-700 border-amber-200',
        text: 'text-amber-700'
      },
      violet: {
        soft: 'bg-violet-50 text-violet-700 border-violet-200',
        text: 'text-violet-700'
      },
      slate: {
        soft: 'bg-slate-100 text-slate-600 border-slate-200',
        text: 'text-slate-600'
      }
    };

    function siteVisitComponentMeta(component) {
      return SITE_VISIT_COMPONENT_META[(component || '').toUpperCase()] || {
        icon: 'fa-layer-group',
        accent: 'slate',
        blurb: 'Other field components recorded for this mission.'
      };
    }

    function siteVisitClusterMeta(cluster) {
      return SITE_VISIT_CLUSTER_META[cluster] || {
        accent: 'slate'
      };
    }

    function getSiteVisitGroups() {
      const visits = Array.isArray(siteVisitsData) ? siteVisitsData : [];
      const groups = new Map();
      const byComponent = siteVisitsGroupBy === 'component';

      visits.forEach(sp => {
        const key = byComponent ? (sp.component || 'Other') : (sp.cluster || 'Unassigned');
        if (!groups.has(key)) {
          groups.set(key, []);
        }
        groups.get(key).push(sp);
      });

      if (byComponent) {
        const order = ['I-BUILD', 'I-REAP'];
        return Array.from(groups.entries()).sort((a, b) => {
          const ai = order.indexOf(a[0].toUpperCase());
          const bi = order.indexOf(b[0].toUpperCase());
          if (ai === -1 && bi === -1) return a[0].localeCompare(b[0]);
          if (ai === -1) return 1;
          if (bi === -1) return -1;
          return ai - bi;
        });
      }

      const order = ['North Luzon', 'South Luzon', 'Visayas', 'Mindanao'];
      return Array.from(groups.entries()).sort((a, b) => {
        const ai = order.indexOf(a[0]);
        const bi = order.indexOf(b[0]);
        if (ai === -1 && bi === -1) return a[0].localeCompare(b[0]);
        if (ai === -1) return 1;
        if (bi === -1) return -1;
        return ai - bi;
      });
    }

    function renderSiteVisitsGroupToggle() {
      const toggle = document.getElementById('siteVisitsGroupToggle');
      if (!toggle) return;

      toggle.querySelectorAll('button[data-group-by]').forEach(btn => {
        const isActive = btn.dataset.groupBy === siteVisitsGroupBy;
        btn.classList.toggle('bg-white', isActive);
        btn.classList.toggle('text-slate-900', isActive);
        btn.classList.toggle('shadow-sm', isActive);
        btn.classList.toggle('text-slate-500', !isActive);
        btn.classList.toggle('hover:text-slate-900', !isActive);
      });

      toggle.querySelectorAll('button[data-group-by]').forEach(btn => {
        btn.onclick = () => {
          siteVisitsGroupBy = btn.dataset.groupBy;
          expandedSiteVisitGroups.clear();
          renderSiteVisitsGrid();
        };
      });
    }

    function renderSiteVisitsGrid() {
      const container = document.getElementById('siteVisitsGroupsContainer');
      if (!container) return;

      container.innerHTML = "";
      renderSiteVisitsGroupToggle();

      const groups = getSiteVisitGroups();
      const total = groups.reduce((sum, [, items]) => sum + items.length, 0);

      if (total === 0) {
        container.innerHTML = `
          <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
            <span class="w-12 h-12 rounded-2xl bg-white border border-slate-200 text-slate-400 flex items-center justify-center mx-auto mb-3 shadow-sm">
              <i class="fa-solid fa-map-location-dot w-5 h-5"></i>
            </span>
            <p class="text-sm font-bold text-slate-700">No site visits recorded yet</p>
            <p class="text-xs text-slate-500 mt-1">Geotagged field visits for this mission will appear here once logged.</p>
          </div>`;
        return;
      }

      const byComponent = siteVisitsGroupBy === 'component';

      groups.forEach(([groupKey, items]) => {
        const meta = byComponent ?
          siteVisitComponentMeta(groupKey) :
          siteVisitClusterMeta(groupKey);
        const accents = SITE_VISIT_ACCENTS[meta.accent] || SITE_VISIT_ACCENTS.slate;

        const breakdown = {};
        items.forEach(sp => {
          const key = byComponent ? (sp.cluster || 'Unassigned') : (sp.component || 'Other');
          breakdown[key] = (breakdown[key] || 0) + 1;
        });
        const breakdownChips = Object.entries(breakdown)
          .sort((a, b) => b[1] - a[1])
          .map(([key, count]) => `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold ${accents.soft}"><i class="fa-solid ${byComponent ? 'fa-location-dot' : 'fa-layer-group'} w-3 h-3"></i>${key} <span class="opacity-60">${count}</span></span>`)
          .join('');

        const group = document.createElement('div');
        group.className = "space-y-4";
        group.innerHTML = `
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-200 pb-3">
            <div class="flex items-start gap-3">
              ${byComponent ? `
              <span class="mt-0.5 w-9 h-9 rounded-xl ${accents.soft} border flex items-center justify-center shrink-0">
                <i class="fa-solid ${meta.icon} w-4 h-4"></i>
              </span>` : ''}
              <div>
                <h3 class="text-base font-extrabold text-slate-900 leading-tight">${groupKey}</h3>
                <p class="text-xs text-slate-500 mt-0.5">${byComponent ? meta.blurb : 'Regional cluster coverage for this mission.'}</p>
              </div>
            </div>
            <div class="flex flex-wrap items-center gap-1.5">
              ${breakdownChips}
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                <i class="fa-solid fa-map-location-dot w-3 h-3"></i>${items.length} site visit${items.length > 1 ? 's' : ''}
              </span>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5"></div>
        `;

        const grid = group.querySelector('div.grid');

        const isExpanded = expandedSiteVisitGroups.has(groupKey);
        const visibleItems = isExpanded ? items : items.slice(0, 3);

        visibleItems.forEach(sp => {
          const hasPhotos = Array.isArray(sp.images) && sp.images.length > 0;
          const card = document.createElement('div');
          card.className = "bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md transition flex flex-col justify-between h-full";
          card.innerHTML = `
            ${hasPhotos ? `
            <div class="relative h-44 bg-slate-900 overflow-hidden group">
              <img src="${sp.images[0].url}" alt="${sp.sp_name}" loading="lazy" decoding="async" class="w-full h-full object-cover opacity-85 group-hover:scale-105 transition duration-500" />
              <span class="absolute top-3 left-3 bg-govnavy-950/85 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg">${sp.cluster}</span>
              ${sp.images.length > 1 ? `<span class="absolute top-3 right-3 bg-slate-900/85 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg flex items-center gap-1"><i class="fa-solid fa-images w-3 h-3"></i>${sp.images.length}</span>` : ''}
            </div>` : ''}
            <div class="p-5 space-y-2 flex-1">
              <div class="flex items-start justify-between gap-2">
                <p class="text-[11px] font-semibold ${accents.text} flex items-center gap-1.5 min-w-0">
                  <i class="fa-solid fa-location-dot w-3 h-3 shrink-0"></i><span class="truncate">${sp.location || 'Location not specified'}</span>
                </p>
                ${sp.date ? `<span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200"><i class="fa-solid fa-calendar w-3 h-3"></i>${formatDate(sp.date)}</span>` : ''}
              </div>
              <a href="${sp.document_link || '#'}" target="_blank" rel="noopener" class="block text-sm font-bold text-slate-900 line-clamp-2 hover:text-emerald-700 hover:underline">${sp.sp_name}</a>
              ${sp.description ? `<p class="text-xs text-slate-600 line-clamp-2">${sp.description}</p>` : ''}
              <div class="flex flex-wrap items-center gap-1.5 pt-1">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">${sp.component}</span>
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200"><i class="fa-solid fa-location-dot w-3 h-3"></i>${sp.cluster}</span>
                ${sp.funding_source ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200"><i class="fa-solid fa-coins w-3 h-3"></i>${sp.funding_source}</span>` : ''}
              </div>
            </div>
            <div class="p-5 pt-0 flex flex-wrap items-center justify-end gap-2">
              ${hasPhotos ? `
              <button onclick="openGeotagModal(${sp.id})" class="px-3 py-2 rounded-xl bg-emerald-600 text-white text-[11px] font-bold hover:bg-emerald-700 transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-images w-3.5 h-3.5"></i> Inspect AGT Photos
              </button>` : ''}
              <a href="${sp.virtual_site_visit_link || '#'}" target="_blank" rel="noopener" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-[11px] font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-arrow-up-right-from-square w-3.5 h-3.5"></i> Inspect Site
              </a>
            </div>
          `;
          grid.appendChild(card);
        });

        if (items.length > 3 && !isExpanded) {
          const moreBtn = document.createElement('button');
          moreBtn.type = 'button';
          moreBtn.className = 'inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-slate-200 transition';
          moreBtn.innerHTML = '<i class="fa-solid fa-chevron-down w-3.5 h-3.5"></i><span>View more</span>';
          moreBtn.onclick = () => {
            expandedSiteVisitGroups.add(groupKey);
            renderSiteVisitsGrid();
          };
          const wrap = document.createElement('div');
          wrap.className = 'pt-2 text-center';
          wrap.appendChild(moreBtn);
          group.appendChild(wrap);
        }

        container.appendChild(group);
      });
    }

    function openGeotagModal(spId) {
      activeAgtSp = spId;
      activeAgtPhotoIdx = 0;
      const sp = siteVisitsData.find(item => item.id === spId);
      if (!sp) return;

      const hasPhotos = Array.isArray(sp.images) && sp.images.length > 0;
      document.getElementById('agtPhotoSection').classList.toggle('hidden', !hasPhotos);
      document.getElementById('agtStationsSection').classList.toggle('hidden', !hasPhotos);

      document.getElementById('agtModalTitle').innerText = sp.sp_name;
      document.getElementById('agtSpName').innerText = sp.sp_name;
      document.getElementById('agtSpDesc').innerText = sp.description;
      document.getElementById('agtSpFunding').innerText = sp.funding_source;
      document.getElementById('agtSpDate').innerText = sp.date ? formatDate(sp.date) : '—';
      document.getElementById('agtSpCluster').innerText = sp.cluster;
      document.getElementById('agtSpComponent').innerText = sp.component;
      document.getElementById('agtSpDocLink').href = sp.document_link || '#';
      document.getElementById('agtPhotoCount').innerText = hasPhotos ? sp.images.length : 0;

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
          <img src="${p.url}" alt="${p.label}" loading="lazy" decoding="async" class="w-full h-14 object-cover" />
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
        focusedReportId: null,
        focusedMeetingId: null,
        init() {
          window.addEventListener('focus-report', (event) => {
            this.focusedReportId = event.detail;
            this.currentCategory = 'all';
            this.searchQuery = '';

            this.$nextTick(() => {
              const target = document.getElementById(`report-${event.detail}`);
              if (target) {
                target.scrollIntoView({
                  behavior: 'smooth',
                  block: 'center'
                });
              }
            });
          });

          window.addEventListener('focus-meeting-reports', (event) => {
            this.focusedMeetingId = event.detail;
            this.focusedReportId = null;
            this.currentCategory = 'all';
            this.searchQuery = '';

            this.$nextTick(() => {
              document.getElementById('reports-section')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
              });
            });
          });
        },
        clearMeetingFocus() {
          this.focusedMeetingId = null;
          this.focusedReportId = null;
        },
        get focusedMeeting() {
          if (!this.focusedMeetingId) return null;

          const meeting = this.allReports.find(rep => rep.meetingId === this.focusedMeetingId);
          if (!meeting) return null;

          return {
            id: this.focusedMeetingId,
            title: meeting.meetingTitle,
            reportCount: this.allReports.filter(rep => rep.meetingId === this.focusedMeetingId).length
          };
        },
        get allReports() {
          let reports = [];
          if (typeof meetingsData !== 'undefined' && Array.isArray(meetingsData)) {
            meetingsData.forEach(m => {
              if (Array.isArray(m.reports)) {
                m.reports.forEach((report, idx) => {
                  const url = typeof report === 'string' ? report : (report?.url || '');
                  if (!url) return;

                  const ext = url.split('?')[0].split('.').pop().toLowerCase();
                  const fmt = ext === 'xlsx' || ext === 'xls' ? 'XLSX' : 'PDF';
                  const fileName = decodeURIComponent(url.split('?')[0].split('/').pop() || '');

                  reports.push({
                    id: `mtg-${m.id}-${idx}`,
                    meetingId: m.id,
                    title: (typeof report === 'object' && report?.title) || m.report_name || m.title,
                    description: (typeof report === 'object' && report?.description) || '',
                    meetingTitle: m.title,
                    meetingDate: m.date || '',
                    category: 'presentation',
                    format: fmt,
                    fileName: fileName,
                    link: url
                  });
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
            const matchesMeeting = (!this.focusedMeetingId || rep.meetingId === this.focusedMeetingId);
            const matchesSearch = !q ||
              rep.title.toLowerCase().includes(q) ||
              rep.format.toLowerCase().includes(q) ||
              (rep.fileName || '').toLowerCase().includes(q) ||
              (rep.description || '').toLowerCase().includes(q);
            return matchesCategory && matchesMeeting && matchesSearch;
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
        attendanceDates: [],
        attendanceModes: {},
        sex: 'female',
        agreed: false,
        submitted: false,
        submitting: false,
        submitError: '',
        errors: {},
        fieldElementIds: {
          first_name: 'reg_first_name',
          middle_initial: 'reg_middle_initial',
          suffix: 'reg_suffix',
          last_name: 'reg_last_name',
          office: 'reg_office',
          management_tier: 'reg_tier',
          pso_cluster: 'reg_cluster',
          rpco_office: 'reg_rpco',
          position: 'reg_position',
          component_unit: 'reg_component',
          contact_number: 'reg_contact_number',
          email: 'reg_email',
          agreed: 'reg_agreed',
        },
        clearFieldError(field) {
          if (!this.errors[field] && !Object.keys(this.errors).some(key => key.startsWith(field + '.'))) {
            return;
          }

          this.errors = Object.fromEntries(
            Object.entries(this.errors).filter(([key]) => key !== field && !key.startsWith(field + '.'))
          );
        },
        scrollToFirstError() {
          const firstError = Object.keys(this.errors)[0];
          const target = firstError && this.fieldElementIds[firstError]
            ? document.getElementById(this.fieldElementIds[firstError])
            : null;

          (target || document.querySelector('#registration-section button[type="submit"]'))?.scrollIntoView({
            behavior: 'smooth',
            block: 'center',
          });

          if (target) {
            target.focus({ preventScroll: true });
          }
        },
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
        attendanceModeMissing(date) {
          return this.attendanceDates.includes(date) && !this.attendanceModes[date];
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
          this.errors = {};
          this.submitError = '';
          document.getElementById('regSuccessBox')?.classList.add('hidden');

          const clientErrors = {};

          if (!this.firstName.trim()) {
            clientErrors.first_name = 'First name is required.';
          }

          if (!this.lastName.trim()) {
            clientErrors.last_name = 'Last name is required.';
          }

          if (!this.office) {
            clientErrors.office = 'Please select your office or agency.';
          }

          if (!this.position.trim()) {
            clientErrors.position = 'Position / designation is required.';
          }

          if (!this.contactNumber.trim()) {
            clientErrors.contact_number = 'Contact number is required.';
          }

          if (!this.email.trim()) {
            clientErrors.email = 'Email address is required.';
          } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email.trim())) {
            clientErrors.email = 'Enter a valid email address.';
          }

          if (!this.agreed) {
            clientErrors.agreed = 'You must accept the Data Privacy notice to continue.';
          }

          if (this.attendanceDates.length === 0) {
            clientErrors.attendance_dates = 'Select at least one attendance date.';
          } else if (this.attendanceDates.some(date => !this.attendanceModes[date])) {
            clientErrors.attendance_mode = 'Select a mode for each selected attendance date.';
          }

          if (Object.keys(clientErrors).length > 0) {
            this.errors = clientErrors;
            this.submitError = 'Please correct the highlighted fields and try again.';
            this.$nextTick(() => this.scrollToFirstError());
            return;
          }

          this.submitting = true;

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
          this.attendanceDates.forEach(date => {
            formData.append('attendance_dates[]', date);
            formData.append('attendance_mode[]', this.attendanceModes[date] || '');
          });

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
                document.getElementById('regAttendeeDetails').innerText = 'You are successfully registered.';
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
                this.agreed = false;
                this.attendanceDates = [];
                this.attendanceModes = {};
                this.submitted = false;
                this.errors = {};
                this.submitError = '';
              } else {
                if (response.status === 422 && body.errors) {
                  this.errors = body.errors;
                  this.submitError = body.message || 'Please correct the highlighted fields and try again.';
                } else {
                  this.submitError = body.message || 'Registration failed. Please check your inputs and try again.';
                }

                showToast(this.submitError);

                this.$nextTick(() => this.scrollToFirstError());
              }
            })
            .catch(error => {
              console.error(error);
              this.submitError = 'Registration failed. Please try again.';
              showToast(this.submitError);
            })
            .finally(() => {
              this.submitting = false;
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
        if (!q) return true;
        return (
          (item.caption || '').toLowerCase().includes(q) ||
          (item.author || '').toLowerCase().includes(q) ||
          (item.office || '').toLowerCase().includes(q) ||
          (item.location || '').toLowerCase().includes(q) ||
          (item.dateDisplay || '').toLowerCase().includes(q)
        );
      });

      const streamCounterEl = document.getElementById('streamCounter');
      if (streamCounterEl) {
        streamCounterEl.innerText = q ? `Showing ${visiblePhotos.length} of ${photos.length} photos` : `All ${photos.length} photos displayed`;
      }

      container.innerHTML = "";

      if (visiblePhotos.length === 0) {
        if (photos.length === 0) {
          container.innerHTML = "";
          return;
        }

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

      // Flat gallery — no grouping, photos flow in a single responsive grid
      const gridEl = document.createElement('div');
      gridEl.className = "grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2.5 sm:gap-3";

      visiblePhotos.forEach((photo, globalIdx) => {
        const card = document.createElement('div');
        card.className = "g-photo-card relative rounded-2xl overflow-hidden bg-slate-100 aspect-square sm:aspect-[4/3] group cursor-pointer border border-slate-200/70 shadow-sm hover:shadow-md transition-all";
        card.onclick = () => openLightbox(globalIdx);

        card.innerHTML = `
          <img src="${photo.imageUrl}" alt="${photo.caption}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy" />
          <div class="g-photo-overlay absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/10 to-black/20 opacity-0 transition duration-200 pointer-events-none"></div>
          <div class="absolute bottom-2.5 left-2.5 right-2.5 text-white opacity-0 group-hover:opacity-100 transition duration-200 pointer-events-none">
            <p class="text-xs font-semibold truncate leading-snug">${photo.caption || 'Mission photo'}</p>
            ${[photo.author, photo.location].filter(Boolean).length
              ? `<p class="text-[10px] text-slate-300 truncate">${[photo.author, photo.location].filter(Boolean).join(' • ')}</p>`
              : ''}
          </div>
        `;
        gridEl.appendChild(card);
      });

      container.appendChild(gridEl);
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
      document.getElementById('lbHeaderTitle').innerText = photo.caption || 'Mission photo';
      document.getElementById('lbHeaderSubtitle').innerText = [photo.dateDisplay, photo.location].filter(Boolean).join(' • ');

      const caption = photo.caption || '';
      document.getElementById('drawerCaption').innerText = caption;
      document.getElementById('drawerCaptionBlock').classList.toggle('hidden', !caption);

      document.getElementById('drawerDate').innerText = photo.dateDisplay || '—';
      document.getElementById('drawerTime').innerText = photo.timeDisplay || '';
      document.getElementById('drawerAuthor').innerText = photo.author || '';
      document.getElementById('drawerOffice').innerText = photo.office || '';
      document.getElementById('drawerLocation').innerText = photo.location || '—';
      document.getElementById('drawerDevice').innerText = photo.device || '';
      document.getElementById('drawerExif').innerText = photo.exif || '';

      const hasAuthor = !!(photo.author || photo.office);
      document.getElementById('drawerAuthorBlock').classList.toggle('hidden', !hasAuthor);
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
      if (typeof canUploadPhotos !== 'undefined' && !canUploadPhotos) {
        alert('Photo uploads are closed. Uploads reopen when a new mission starts or within two weeks after the latest mission ends.');
        return;
      }
      document.getElementById('gUploadModal').classList.remove('hidden');
      document.getElementById('gUploadModal').classList.add('flex');
    }

    function closeUploadModal() {
      document.getElementById('gUploadModal').classList.add('hidden');
      document.getElementById('gUploadModal').classList.remove('flex');
      clearUploadFile();
    }

    function handleFileInputPreview(event) {
      addPendingFiles(event.target.files);
      event.target.value = "";
    }

    function handleUploadDragOver(event) {
      event.preventDefault();
      event.stopPropagation();
      document.getElementById('dropArea').classList.add('border-emerald-500', 'bg-emerald-50/30');
    }

    function handleUploadDragLeave(event) {
      event.preventDefault();
      event.stopPropagation();
      document.getElementById('dropArea').classList.remove('border-emerald-500', 'bg-emerald-50/30');
    }

    function handleUploadDrop(event) {
      event.preventDefault();
      event.stopPropagation();
      document.getElementById('dropArea').classList.remove('border-emerald-500', 'bg-emerald-50/30');
      addPendingFiles(event.dataTransfer.files);
    }

    function addPendingFiles(fileList) {
      const files = Array.from(fileList || []).filter(file => file.type.startsWith('image/'));

      if (files.length === 0) {
        showToast('Please select image files.');
        return;
      }

      files.forEach(file => {
        const reader = new FileReader();
        reader.onload = function(e) {
          pendingUploads.push({
            id: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
            name: file.name,
            size: file.size,
            file: file,
            dataUrl: e.target.result
          });
          renderPendingUploads();
        };
        reader.readAsDataURL(file);
      });
    }

    function removePendingUpload(id) {
      pendingUploads = pendingUploads.filter(upload => upload.id !== id);
      renderPendingUploads();
    }

    function renderPendingUploads() {
      const grid = document.getElementById('previewGrid');
      const preview = document.getElementById('dropPreview');
      const count = document.getElementById('previewCount');
      const prompt = document.getElementById('dropPrompt');

      if (!grid) return;

      if (pendingUploads.length === 0) {
        grid.innerHTML = '';
        preview.classList.add('hidden');
        prompt.classList.remove('hidden');
        return;
      }

      prompt.classList.add('hidden');
      preview.classList.remove('hidden');
      count.innerText = `${pendingUploads.length} photo${pendingUploads.length > 1 ? 's' : ''} selected`;

      grid.innerHTML = pendingUploads.map(upload => `
        <div class="relative group rounded-xl overflow-hidden border border-slate-200 bg-slate-100 aspect-square">
          <img src="${upload.dataUrl}" alt="${upload.name}" class="w-full h-full object-cover" />
          <button type="button" onclick="removePendingUpload('${upload.id}')"
            class="absolute top-1.5 right-1.5 w-6 h-6 bg-slate-900/80 text-white rounded-full hover:bg-red-600 flex items-center justify-center transition opacity-0 group-hover:opacity-100 focus:opacity-100">
            <i class="fa-solid fa-xmark w-3 h-3"></i>
          </button>
        </div>
      `).join('');
    }

    function clearUploadFile() {
      pendingUploads = [];
      document.getElementById('fileInput').value = "";
      renderPendingUploads();
    }

    function handleNewPhotoUpload() {
      if (pendingUploads.length === 0) {
        showToast("Please select at least one photo to upload.");
        return;
      }

      const caption = document.getElementById('upCaption').value.trim();
      const location = document.getElementById('upLocation').value.trim();

      const formData = new FormData();
      pendingUploads.forEach(upload => formData.append('images[]', upload.file));
      formData.append('caption', caption);
      formData.append('location', location);

      const submitButton = document.querySelector('#publicUploadForm button[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Publishing...';
      }

      fetch(document.getElementById('photoUploadEndpoint').value, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.getElementById('photoUploadCsrf').value,
            'Accept': 'application/json'
          },
          body: formData
        })
        .then(response => response.json().then(data => ({
          ok: response.ok,
          data
        })))
        .then(({
          ok,
          data
        }) => {
          if (!ok) {
            throw new Error(data.message || 'Upload failed.');
          }

          const uploaded = Array.isArray(data.photos) ? data.photos : [];
          photos.unshift(...uploaded);

          document.getElementById('publicUploadForm').reset();
          closeUploadModal();
          renderGooglePhotosStream();
          showToast(data.message || 'Photo published! Visible to all mission delegates.');
        })
        .catch(error => {
          showToast(error.message || 'Upload failed. Please try again.');
        })
        .finally(() => {
          if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Publish to Stream';
          }
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
