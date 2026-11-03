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
                  <span class="text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 px-2 py-0.5 rounded-full font-semibold">{{ $photosMission ? $photosMission->title : 'No Mission' }}</span>
                </div>
                <p class="text-xs text-slate-400">
                  {{ $activeMission ? 'Photos of the active mission' : 'Photos of the latest previous mission' }} — every photo submitted to the stream in one gallery.
                </p>
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
              @if($photosUploadAllowed)
              <button
                onclick="openUploadModal()"
                class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-semibold shadow-md shadow-emerald-600/20 transition active:scale-95">
                <i class="fa-solid fa-cloud-arrow-up w-4 h-4"></i>
                <span>Upload Photo</span>
              </button>
              @endif
            </div>

          </div>

          <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
            <div class="flex items-center space-x-2">
              <i class="fa-solid fa-images w-3.5 h-3.5 text-emerald-600"></i>
              <span>Photos submitted to the stream for {{ $photosMission ? $photosMission->title : 'this mission' }}.</span>
            </div>
            <span class="font-mono text-[11px] text-slate-400" id="streamCounter">Showing all items</span>
          </div>
        </div>

        <!-- Mission Photo Gallery -->
        <div id="googlePhotosStreamContainer">
          <!-- Dynamically injected -->
        </div>

      </div>
    </section>

