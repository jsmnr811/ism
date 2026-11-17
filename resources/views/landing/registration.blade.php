@if($registrationMission)
<!-- ================= DELEGATE ACCREDITATION ================= -->
<section id="registration-section" class="py-14 bg-slate-900/5 text-slate-900 border-b border-slate-200" x-data="delegateRegistration()">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header -->
    <div class="max-w-4xl mx-auto text-center mb-10">
      <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
        Mission Registration
      </h2>
    </div>

    <!-- Expanded Form Container (max-w-6xl) -->
    <div class="max-w-6xl mx-auto bg-white rounded-3xl border border-slate-200/80 shadow-2xl shadow-slate-900/5 overflow-hidden">

      <!-- Banner Header -->
      <div class="bg-slate-950 p-6 sm:px-10 text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-800">
        <div class="flex items-center space-x-3.5">
          <div>
            <h3 class="text-sm font-semibold tracking-wide text-white uppercase">
              {{ $registrationMission?->title ?? 'Official Accreditation Portal' }}
            </h3>
            <p class="text-xs font-mono text-slate-400 mt-0.5 flex items-center gap-2">
              <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
              @if($registrationMission && $registrationMission->start_date && $registrationMission->end_date)
              {{ $registrationMission->start_date->format('M j, Y') }} – {{ $registrationMission->end_date->format('M j, Y') }}
              @elseif($registrationMission && $registrationMission->start_date)
              {{ $registrationMission->start_date->format('M j, Y') }}
              @else
              DA - PRDP System
              @endif
            </p>
          </div>
        </div>

        <span class="inline-flex items-center gap-1.5 text-xs font-mono font-semibold px-3.5 py-1 rounded-full border {{ $registrationMission ? 'text-emerald-400 bg-emerald-950/60 border-emerald-500/30' : 'text-slate-400 bg-slate-900 border-slate-800' }}">
          <span class="w-1.5 h-1.5 rounded-full {{ $registrationMission ? 'bg-emerald-400' : 'bg-slate-500' }}"></span>
          {{ $registrationMission ? 'ACTIVE' : 'OFFLINE' }}
        </span>
      </div>

      <!-- Form Content -->
      <form id="missionRegForm" class="p-6 sm:p-10 space-y-8" novalidate @submit.prevent="submitForm()">
        <input type="hidden" name="mission_id" value="{{ $registrationMission->id ?? '' }}" />

        <!-- SECTION 01: IDENTITY -->
        <div class="space-y-4">
          <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <span class="text-[11px] font-mono uppercase tracking-widest text-emerald-600 font-bold">Identity Profile</span>
            <span class="text-[10px] font-mono text-slate-400 uppercase">Required Parameters</span>
          </div>

          <!-- First Name & Last Name (Row 1) -->
          <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
            <div class="sm:col-span-6">
              <label for="reg_first_name" class="block text-xs font-semibold text-slate-700 mb-1.5">First Name <span class="text-emerald-600">*</span></label>
              <input type="text" id="reg_first_name" x-model="firstName" @input="clearFieldError('first_name')" required placeholder="Maria" :class="errors.first_name ? '!border-red-400 ring-1 ring-red-300' : ''" class="w-full h-[42px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-900 placeholder:text-slate-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 outline-none transition" />
              <p x-show="errors.first_name" x-text="errors.first_name" x-cloak class="mt-1 text-xs font-medium text-red-600"></p>
            </div>
            <div class="sm:col-span-6">
              <label for="reg_last_name" class="block text-xs font-semibold text-slate-700 mb-1.5">Last Name <span class="text-emerald-600">*</span></label>
              <input type="text" id="reg_last_name" x-model="lastName" @input="clearFieldError('last_name')" required placeholder="Santos" :class="errors.last_name ? '!border-red-400 ring-1 ring-red-300' : ''" class="w-full h-[42px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-900 placeholder:text-slate-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 outline-none transition" />
              <p x-show="errors.last_name" x-text="errors.last_name" x-cloak class="mt-1 text-xs font-medium text-red-600"></p>
            </div>
          </div>

          <!-- M.I., Suffix & Sex Fields (Row 2) -->
          <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
            <div class="sm:col-span-3">
              <label for="reg_middle_initial" class="block text-xs font-semibold text-slate-700 mb-1.5">M.I.</label>
              <input type="text" id="reg_middle_initial" x-model="middleInitial" @input="clearFieldError('middle_initial')" maxlength="1" placeholder="S" :class="errors.middle_initial ? '!border-red-400 ring-1 ring-red-300' : ''" class="w-full h-[42px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm uppercase text-slate-900 placeholder:text-slate-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 outline-none transition" />
              <p x-show="errors.middle_initial" x-text="errors.middle_initial" x-cloak class="mt-1 text-xs font-medium text-red-600"></p>
            </div>

            <div class="sm:col-span-3">
              <label for="reg_suffix" class="block text-xs font-semibold text-slate-700 mb-1.5">Suffix</label>
              <select id="reg_suffix" x-model="suffix" @change="clearFieldError('suffix')" :class="errors.suffix ? '!border-red-400 ring-1 ring-red-300' : ''" class="w-full h-[42px] px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 outline-none transition">
                <option value="">None</option>
                <template x-for="[value, label] in Object.entries(suffixLabels)" :key="value">
                  <option :value="value" x-text="label"></option>
                </template>
              </select>
              <p x-show="errors.suffix" x-text="errors.suffix" x-cloak class="mt-1 text-xs font-medium text-red-600"></p>
            </div>

            <div class="sm:col-span-6">
              <label class="block text-xs font-semibold text-slate-700 mb-1.5">Sex <span class="text-emerald-600">*</span></label>
              <div class="grid grid-cols-2 gap-3 h-[42px]" x-show="sexOptions.length" :class="errors.sex ? 'rounded-xl ring-1 ring-red-300' : ''">
                <template x-for="[value, label] in sexOptions" :key="value">
                  <label :class="sex === value ? 'border-emerald-600 bg-emerald-50/60 text-emerald-950 ring-2 ring-emerald-500/20' : 'border-slate-200 bg-white hover:border-slate-300 text-slate-700'" class="flex items-center justify-center h-full px-4 rounded-xl border cursor-pointer transition text-xs font-semibold tracking-wide">
                    <input type="radio" name="sex" :value="value" x-model="sex" @change="clearFieldError('sex')" class="sr-only" />
                    <span x-text="label"></span>
                  </label>
                </template>
              </div>
              <p x-show="errors.sex" x-text="errors.sex" x-cloak class="mt-1 text-xs font-medium text-red-600"></p>
            </div>
          </div>

        </div>

        <!-- SECTION 02: AFFILIATION -->
        <div class="space-y-4 pt-2">
          <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <span class="text-[11px] font-mono uppercase tracking-widest text-emerald-600 font-bold">Office & Organizational Assignment</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
            <div class="sm:col-span-6">
              <label for="reg_office" class="block text-xs font-semibold text-slate-700 mb-1.5">Office / Agency <span class="text-emerald-600">*</span></label>
              <select id="reg_office" x-model="office" @change="updateOffice(office); clearFieldError('office')" required :class="errors.office ? '!border-red-400 ring-1 ring-red-300' : ''" class="w-full h-[42px] px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 outline-none transition">
                <option value="">Select Office / Agency</option>
                <template x-for="[value, label] in Object.entries(officeLabels)" :key="value">
                  <option :value="value" x-text="label"></option>
                </template>
              </select>
              <p x-show="errors.office" x-text="errors.office" x-cloak class="mt-1 text-xs font-medium text-red-600"></p>
            </div>

            <div class="sm:col-span-6">
              <label for="reg_position" class="block text-xs font-semibold text-slate-700 mb-1.5">Position / Designation <span class="text-emerald-600">*</span></label>
              <input type="text" id="reg_position" x-model="position" @input="clearFieldError('position')" required placeholder="Senior Infrastructure Engineer" :class="errors.position ? '!border-red-400 ring-1 ring-red-300' : ''" class="w-full h-[42px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-900 placeholder:text-slate-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 outline-none transition" />
              <p x-show="errors.position" x-text="errors.position" x-cloak class="mt-1 text-xs font-medium text-red-600"></p>
            </div>
          </div>

          <!-- PRDP Hierarchy Conditional Section -->
          <div x-show="showManagementTier" x-cloak x-transition.opacity class="p-4 sm:p-6 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
              <div class="sm:col-span-6">
                <label for="reg_tier" class="block text-xs font-semibold text-slate-700 mb-1.5">Management Tier</label>
                <select id="reg_tier" x-model="managementTier" @change="updateManagementTier(managementTier); clearFieldError('management_tier')" :class="errors.management_tier ? '!border-red-400 ring-1 ring-red-300' : ''" class="w-full h-[42px] px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 outline-none transition">
                  <option value="">Select Management Tier</option>
                  <template x-for="[value, label] in Object.entries(managementTierLabels)" :key="value">
                    <option :value="value" x-text="label"></option>
                  </template>
                </select>
                <p x-show="errors.management_tier" x-text="errors.management_tier" x-cloak class="mt-1 text-xs font-medium text-red-600"></p>
              </div>
              <div class="sm:col-span-6">
                <label for="reg_component" class="block text-xs font-semibold text-slate-700 mb-1.5">Component / Unit</label>
                <select id="reg_component" x-model="componentUnit" @change="clearFieldError('component_unit')" :class="errors.component_unit ? '!border-red-400 ring-1 ring-red-300' : ''" class="w-full h-[42px] px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 outline-none transition">
                  <option value="">Select Component / Unit</option>
                  <template x-for="[value, label] in Object.entries(componentUnitLabels)" :key="value">
                    <option :value="value" x-text="label"></option>
                  </template>
                </select>
                <p x-show="errors.component_unit" x-text="errors.component_unit" x-cloak class="mt-1 text-xs font-medium text-red-600"></p>
              </div>
            </div>

            <div x-show="showPsoCluster" x-cloak>
              <label for="reg_cluster" class="block text-xs font-semibold text-slate-700 mb-1.5">PSO Cluster</label>
              <select id="reg_cluster" x-model="psoCluster" @change="clearFieldError('pso_cluster')" :class="errors.pso_cluster ? '!border-red-400 ring-1 ring-red-300' : ''" class="w-full h-[42px] px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 outline-none transition">
                <option value="">Select PSO Cluster</option>
                <template x-for="[value, label] in Object.entries(psoClusterLabels)" :key="value">
                  <option :value="value" x-text="label"></option>
                </template>
              </select>
              <p x-show="errors.pso_cluster" x-text="errors.pso_cluster" x-cloak class="mt-1 text-xs font-medium text-red-600"></p>
            </div>

            <div x-show="showRpcoOffice" x-cloak>
              <label for="reg_rpco" class="block text-xs font-semibold text-slate-700 mb-1.5">RPCO Office</label>
              <select id="reg_rpco" x-model="rpcoOffice" @change="clearFieldError('rpco_office')" :class="errors.rpco_office ? '!border-red-400 ring-1 ring-red-300' : ''" class="w-full h-[42px] px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-sm text-slate-900 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 outline-none transition">
                <option value="">Select RPCO Office</option>
                <template x-for="[value, label] in Object.entries(rpcoOfficeLabels)" :key="value">
                  <option :value="value" x-text="label"></option>
                </template>
              </select>
              <p x-show="errors.rpco_office" x-text="errors.rpco_office" x-cloak class="mt-1 text-xs font-medium text-red-600"></p>
            </div>
          </div>
        </div>

        <!-- SECTION 03: CONTACT & SCHEDULE -->
        <div class="space-y-4 pt-2">
          <div class="flex items-center justify-between pb-2 border-b border-slate-100">
            <span class="text-[11px] font-mono uppercase tracking-widest text-emerald-600 font-bold">Communication & Attendance Schedule</span>
          </div>

          <!-- Contact Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
            <div class="sm:col-span-6">
              <label for="reg_email" class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address <span class="text-emerald-600">*</span></label>
              <input type="email" id="reg_email" x-model="email" @input="clearFieldError('email')" required placeholder="maria.santos@da.gov.ph" :class="errors.email ? '!border-red-400 ring-1 ring-red-300' : ''" class="w-full h-[42px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-900 placeholder:text-slate-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 outline-none transition" />
              <p x-show="errors.email" x-text="errors.email" x-cloak class="mt-1 text-xs font-medium text-red-600"></p>
            </div>
            <div class="sm:col-span-6">
              <label for="reg_contact_number" class="block text-xs font-semibold text-slate-700 mb-1.5">Contact Number <span class="text-emerald-600">*</span></label>
              <input type="tel" id="reg_contact_number" x-model="contactNumber" @input="clearFieldError('contact_number')" required placeholder="+63 917 123 4567" :class="errors.contact_number ? '!border-red-400 ring-1 ring-red-300' : ''" class="w-full h-[42px] px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-900 placeholder:text-slate-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 outline-none transition" />
              <p x-show="errors.contact_number" x-text="errors.contact_number" x-cloak class="mt-1 text-xs font-medium text-red-600"></p>
            </div>
          </div>

          <!-- Attendance Grid with Select All Toggle -->
          @php
              $meetingGroups = $meetings->filter(fn($m) => $m->date)->groupBy(fn($m) => $m->date->toDateString());
              $allDateKeys = $meetingGroups->keys()->values();
          @endphp

          <div class="pt-2" x-data="{
            allDates: {{ json_encode($allDateKeys) }},
            get isAllSelected() {
              return this.allDates.length > 0 && this.allDates.every(d => attendanceDates.includes(d));
            },
            toggleSelectAll() {
              if (this.isAllSelected) {
                attendanceDates = [];
              } else {
                attendanceDates = [...this.allDates];
                this.allDates.forEach(d => {
                  if (!attendanceModes[d]) attendanceModes[d] = 'onsite';
                });
              }
            }
          }">
            
            <!-- Attendance Header with Select All Action -->
            <div class="flex items-center justify-between mb-2.5">
              <label class="block text-xs font-semibold text-slate-700">Select Target Attendance Dates & Mode</label>
              
              @if($meetingGroups->isNotEmpty())
              <button type="button" @click="toggleSelectAll()" 
                      class="inline-flex items-center gap-1.5 text-xs font-mono font-medium text-emerald-700 hover:text-emerald-800 transition">
                <input type="checkbox" :checked="isAllSelected" class="w-3.5 h-3.5 text-emerald-600 focus:ring-emerald-500/20 border-slate-300 rounded pointer-events-none" />
                <span x-text="isAllSelected ? 'Deselect All' : 'Select All Dates'"></span>
              </button>
              @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
              @forelse($meetingGroups as $dateVal => $dateMeetings)
                @php
                    $typeLabels = $dateMeetings->map(function($m) {
                        return $m->type === 'other' && $m->other_type 
                            ? $m->other_type 
                            : ($m->types()[$m->type] ?? $m->type);
                    })->unique()->implode(', ');
                @endphp

                <div :class="attendanceModeMissing('{{ $dateVal }}') ? 'border-red-400 ring-1 ring-red-300 bg-red-50/30' : (attendanceDates.includes('{{ $dateVal }}') ? 'border-emerald-500 bg-emerald-50/40 shadow-sm ring-1 ring-emerald-500/30' : 'border-slate-200 bg-white hover:border-slate-300')"
                     class="p-3.5 rounded-2xl border transition flex items-center justify-between gap-3">

                  <!-- Date Toggle Label -->
                  <label class="flex items-center gap-2.5 cursor-pointer flex-1 min-w-0">
                    <input type="checkbox" value="{{ $dateVal }}" x-model="attendanceDates"
                           @change="clearFieldError('attendance_dates'); clearFieldError('attendance_mode')"
                           class="w-4 h-4 text-emerald-600 focus:ring-emerald-500/20 border-slate-300 rounded transition" />
                    <span class="text-xs font-mono font-medium text-slate-800 truncate">
                      {{ \Carbon\Carbon::parse($dateVal)->format('D, M j, Y') }}
                      <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 block w-full pl-1 truncate">{{ $typeLabels }}</span>
                    </span>
                  </label>

                  <!-- Mode Selector -->
                  <select x-model="attendanceModes['{{ $dateVal }}']"
                          @change="clearFieldError('attendance_mode'); clearFieldError('attendance_dates')"
                          :disabled="!attendanceDates.includes('{{ $dateVal }}')"
                          :class="attendanceModeMissing('{{ $dateVal }}') ? 'bg-white border-red-400 text-slate-800 ring-1 ring-red-300' : (attendanceDates.includes('{{ $dateVal }}') ? 'bg-white border-emerald-300 text-slate-800' : 'bg-slate-100/70 border-slate-200 text-slate-400 cursor-not-allowed')"
                          class="px-2.5 py-1.5 rounded-xl border text-xs font-mono outline-none transition focus:ring-2 focus:ring-emerald-500/20">
                    <option value="">Mode</option>
                    <option value="onsite">Onsite</option>
                    <option value="online">Online</option>
                  </select>

                </div>
              @empty
              <div class="col-span-full p-4 rounded-xl bg-slate-50 border border-slate-200 text-center">
                <p class="text-xs text-slate-400 font-mono">No meeting dates available.</p>
              </div>
              @endforelse
            </div>
            <p x-show="errors.attendance_dates" x-text="errors.attendance_dates" x-cloak class="mt-2 text-xs font-medium text-red-600"></p>
            <p x-show="errors.attendance_mode" x-text="errors.attendance_mode" x-cloak class="mt-1 text-xs font-medium text-red-600"></p>
          </div>
        </div>

        <!-- SECTION 04: PRIVACY & SUBMIT -->
        <div class="pt-6 border-t border-slate-200 space-y-5">

          <!-- R.A. 10173 Compliance Box -->
          <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 border border-slate-200/70 text-xs text-slate-600 space-y-2">
            <div class="flex items-center gap-2 font-mono font-bold text-slate-800 uppercase tracking-wide">
              <i class="fa-solid fa-shield-halved text-emerald-600"></i>
              <span>Data Privacy Notice (Republic Act No. 10173)</span>
            </div>
            <p class="leading-relaxed text-slate-500">
              In compliance with the <strong>Data Privacy Act (DPA) of 2012</strong>, the Department of Agriculture - Philippine Rural Development Project (DA-PRDP) is committed to protecting your personal information. By submitting this form, you grant explicit consent to collect, process, and store your data exclusively for event accreditation, operational coordination, session management, and material distribution.
            </p>
            <label class="flex items-center gap-2 pt-1 cursor-pointer">
              <input type="checkbox" id="reg_agreed" x-model="agreed" @change="clearFieldError('agreed')" :class="errors.agreed ? '!border-red-400 ring-1 ring-red-300' : 'border-slate-300'" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500" />
              <span class="text-slate-800 font-medium">I have read, understood, and agree to the Data Privacy Act of 2012 terms. <span class="text-emerald-600">*</span></span>
            </label>
            <p x-show="errors.agreed" x-text="errors.agreed" x-cloak class="text-xs font-medium text-red-600"></p>
          </div>

          <!-- Submit Button -->
          <div class="flex items-center justify-end">
            @if($registrationMission)
            <button
              type="submit"
              :disabled="submitting"
              :class="submitting ? 'opacity-70 cursor-wait' : ''"
              class="w-full sm:w-auto px-10 py-3.5 rounded-xl bg-slate-950 hover:bg-emerald-700 text-white text-xs font-mono font-bold tracking-wider uppercase shadow-xl transition-all flex items-center justify-center gap-2 border border-slate-800 hover:border-emerald-600">
              <template x-if="!submitting">
                <span class="flex items-center gap-2">
                  <span>Submit Registration</span>
                  <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </span>
              </template>
              <template x-if="submitting">
                <span class="flex items-center gap-2">
                  <svg class="animate-spin size-3.5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                  </svg>
                  <span>Submitting...</span>
                </span>
              </template>
            </button>
            @else
            <button type="button" disabled class="w-full sm:w-auto px-10 py-3.5 rounded-xl bg-slate-200 text-slate-400 text-xs font-mono font-bold tracking-wider uppercase cursor-not-allowed flex items-center justify-center gap-2">
              <i class="fa-solid fa-lock text-[10px]"></i>
              <span>Registration Offline</span>
            </button>
            @endif
          </div>

        </div>
      </form>

      <!-- Success State -->
      <div id="regSuccessBox" class="hidden p-10 bg-emerald-50/50 border-t border-emerald-200 text-center space-y-3">
        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center mx-auto border border-emerald-200 font-mono font-bold text-lg">
          ✓
        </div>
        <h4 class="text-lg font-bold text-slate-900 font-mono tracking-tight" id="regAttendeeName">Registration Verified</h4>
        <p class="text-sm text-slate-600 max-w-md mx-auto" id="regAttendeeDetails"></p>
      </div>

    </div>

  </div>
</section>
@endif