@if($registrationMission)
    <!-- ================= DELEGATE REGISTRATION ================= -->
    <section id="registration-section" class="py-10 bg-slate-50 border-b border-slate-200"
      x-data="delegateRegistration()">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="max-w-3xl mx-auto text-center mb-10">
          <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Registration</h2>
        </div>

        <div class="max-w-4xl mx-auto bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden">
          <div class="bg-govnavy-950 p-6 text-white flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-3">
              <div>
                <h3 class="text-sm font-bold text-white">{{ $registrationMission?->title ?? 'Official Accreditation Form' }}</h3>
                <p class="text-xs text-slate-300">
                  @if($registrationMission && $registrationMission->start_date && $registrationMission->end_date)
                    {{ $registrationMission->start_date->format('M j, Y') }} – {{ $registrationMission->end_date->format('M j, Y') }}
                  @elseif($registrationMission && $registrationMission->start_date)
                    {{ $registrationMission->start_date->format('M j, Y') }}
                  @else
                    Department of Agriculture
                  @endif
                </p>
              </div>
            </div>
            <span class="text-xs font-semibold {{ $registrationMission ? 'text-emerald-300 bg-emerald-950/60 px-3 py-1 rounded-lg border border-emerald-700/50' : 'text-slate-400 bg-slate-900/60 px-3 py-1 rounded-lg border border-slate-700/50' }}">
              {{ $registrationMission ? 'Registration Open' : 'Registration Closed' }}
            </span>
          </div>

          <form id="missionRegForm" class="p-6 sm:p-10 space-y-6" @submit.prevent="submitForm()">
            <input type="hidden" name="mission_id" value="{{ $registrationMission->id ?? '' }}" />

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
              @if($registrationMission)
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

