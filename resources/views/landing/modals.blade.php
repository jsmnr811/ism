  <!-- ================= SUBPROJECT GEOTAGGED PHOTO INSPECTION MODAL ================= -->
  <div id="geotagModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-4xl w-full overflow-hidden shadow-2xl border border-slate-200 max-h-[90vh] flex flex-col">
      <div class="bg-govnavy-950 text-white p-4 px-6 flex items-center justify-between shrink-0">
        <div class="flex items-center space-x-3">
          <div class="w-8 h-8 rounded-lg bg-emerald-500/20 border border-emerald-400/40 flex items-center justify-center text-emerald-300">
            <i class="fa-solid fa-map-pin w-4 h-4"></i>
          </div>
          <div>
            <h3 class="text-sm font-bold text-white" id="agtModalTitle">Subproject Geotagged Dossier</h3>
            <p class="text-xs text-slate-300 font-mono" id="agtModalSubtitle">Applied Geotagging Tool (AGT) Image Stream</p>
          </div>
        </div>
        <button onclick="closeGeotagModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
          <i class="fa-solid fa-x w-5 h-5"></i>
        </button>
      </div>

      <div class="p-6 overflow-y-auto space-y-6">
        <!-- Photo with AGT Watermark -->
        <div id="agtPhotoSection" class="relative rounded-2xl overflow-hidden bg-slate-900 border border-slate-300 shadow-inner">
          <img id="agtMainImage" src="" alt="Geotagged Photo" class="w-full h-80 sm:h-96 object-cover" />

          <div class="agt-watermark-overlay absolute inset-x-0 bottom-0 p-4 text-white">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 font-mono text-[11px] bg-black/60 backdrop-blur-md p-3 rounded-xl border border-white/20">
              <div>
                <span class="text-slate-400 block text-[9px] uppercase tracking-wider">Coordinates</span>
                <strong class="text-emerald-400" id="agtCoord">—</strong>
              </div>
              <div>
                <span class="text-slate-400 block text-[9px] uppercase tracking-wider">Station / Chainage</span>
                <strong class="text-white" id="agtStation">—</strong>
              </div>
              <div>
                <span class="text-slate-400 block text-[9px] uppercase tracking-wider">Elevation &amp; Bearing</span>
                <strong class="text-white" id="agtElev">—</strong>
              </div>
              <div>
                <span class="text-slate-400 block text-[9px] uppercase tracking-wider">Survey Timestamp</span>
                <strong class="text-phgold-400" id="agtTime">—</strong>
              </div>
            </div>
          </div>
        </div>

        <div id="agtStationsSection">
          <label class="font-bold text-xs uppercase tracking-wider text-slate-600 block mb-2">
            Sequential Geotagged Stations (<span id="agtPhotoCount">0</span> photos in this subproject)
          </label>
          <div class="grid grid-cols-3 sm:grid-cols-4 gap-3" id="agtThumbnailsGrid"></div>
        </div>

        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 text-xs space-y-2">
          <h4 class="font-bold text-slate-800 text-sm" id="agtSpName">—</h4>
          <p class="text-slate-600 leading-relaxed" id="agtSpDesc"></p>
          <div class="pt-2 border-t border-slate-200 flex flex-wrap items-center gap-4 text-slate-500">
            <span>Date: <strong class="text-slate-700" id="agtSpDate">—</strong></span>
            <span>Funding: <strong class="text-slate-700" id="agtSpFunding">—</strong></span>
            <span>Cluster: <strong class="text-slate-700" id="agtSpCluster">—</strong></span>
            <span>Component: <strong class="text-slate-700" id="agtSpComponent">—</strong></span>
            <a id="agtSpDocLink" href="#" target="_blank" class="text-emerald-700 font-bold hover:underline flex items-center gap-1">
              <i class="fa-solid fa-file-text w-3.5 h-3.5"></i> Project Document
            </a>
          </div>
        </div>
      </div>

      <div class="p-4 px-6 border-t border-slate-100 bg-slate-50 flex items-center justify-between shrink-0">
        <span class="text-xs text-slate-500">Applied Geotagging Tool (AGT) official audit format</span>
        <button onclick="closeGeotagModal()" class="px-5 py-2 rounded-xl bg-govnavy-950 hover:bg-slate-800 text-white text-xs font-semibold">
          Close Dossier
        </button>
      </div>
    </div>
  </div>

  <!-- ================= FULL PHOTO GALLERY MODAL ================= -->
  <div id="photoGalleryModal" role="dialog" aria-modal="true" aria-label="Mission photos"
    class="fixed inset-0 z-[60] hidden bg-slate-950/95 backdrop-blur-md">
    <div class="flex h-full flex-col">
      <header class="shrink-0 border-b border-white/10 bg-slate-950/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 flex items-center justify-center">
              <i class="fa-solid fa-images w-4 h-4"></i>
            </div>
            <div>
              <h3 class="text-sm font-bold text-white">Mission Photos</h3>
              <p class="text-[11px] text-slate-400" id="photoGalleryModalCount"></p>
            </div>
          </div>
          <button onclick="closePhotoGalleryModal()" aria-label="Close gallery"
            class="w-9 h-9 rounded-full bg-white/5 border border-white/10 hover:bg-white/10 text-slate-300 hover:text-white flex items-center justify-center transition">
            <i class="fa-solid fa-x w-4 h-4"></i>
          </button>
        </div>
      </header>
      <div class="flex-1 overflow-y-auto">
        <div id="photoGalleryModalGrid"
          class="max-w-7xl mx-auto px-4 sm:px-6 py-4 sm:py-5 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-2 sm:gap-3"></div>
      </div>
    </div>
  </div>

  <!-- ================= GOOGLE PHOTOS LIGHTBOX MODAL ================= -->
  <div id="gLightboxModal" class="fixed inset-0 z-50 bg-slate-950/95 backdrop-blur-md hidden flex-col">
    <div class="h-16 px-4 sm:px-6 bg-slate-900/80 border-b border-slate-800/80 flex items-center justify-between text-white shrink-0 z-20">
      <div class="flex items-center space-x-3 min-w-0">
        <button onclick="closeLightbox()" class="p-2 rounded-full hover:bg-white/10 text-slate-300 hover:text-white transition">
          <i class="fa-solid fa-arrow-left w-5 h-5"></i>
        </button>
        <div class="truncate">
          <p class="text-sm font-semibold text-white truncate" id="lbHeaderTitle"></p>
          <p class="text-xs text-slate-400 truncate" id="lbHeaderSubtitle"></p>
        </div>
      </div>

      <div class="flex items-center space-x-1 sm:space-x-2 shrink-0">
        <button onclick="toggleInfoDrawer()" class="p-2 rounded-full hover:bg-white/10 text-slate-300 hover:text-white transition" title="Details">
          <i class="fa-solid fa-circle-info w-5 h-5"></i>
        </button>
        <button onclick="downloadCurrentPhoto()" class="p-2 rounded-full hover:bg-white/10 text-slate-300 hover:text-white transition" title="Download">
          <i class="fa-solid fa-download w-5 h-5"></i>
        </button>
        <button onclick="closeLightbox()" class="p-2 rounded-full hover:bg-white/10 text-slate-300 hover:text-white transition" title="Close">
          <i class="fa-solid fa-x w-5 h-5"></i>
        </button>
      </div>
    </div>

    <div class="flex-1 relative flex overflow-hidden">
      <div class="flex-1 relative flex items-center justify-center p-4 sm:p-8 select-none">
        <button onclick="prevPhoto()" class="absolute left-4 top-1/2 -translate-y-1/2 p-3 rounded-full bg-slate-900/60 hover:bg-slate-900 text-white/80 hover:text-white shadow-xl backdrop-blur transition z-10">
          <i class="fa-solid fa-chevron-left w-6 h-6"></i>
        </button>
        <img id="lbMainImage" src="" alt="Mission Photo" class="max-w-full max-h-[82vh] object-contain rounded-lg shadow-2xl transition duration-300" />
        <button onclick="nextPhoto()" class="absolute right-4 top-1/2 -translate-y-1/2 p-3 rounded-full bg-slate-900/60 hover:bg-slate-900 text-white/80 hover:text-white shadow-xl backdrop-blur transition z-10">
          <i class="fa-solid fa-chevron-right w-6 h-6"></i>
        </button>
      </div>

      <div id="lbInfoDrawer" class="w-80 sm:w-96 bg-white text-slate-800 border-l border-slate-200 overflow-y-auto hidden flex-col z-20 shadow-2xl">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
          <h4 class="font-bold text-sm text-slate-800">Telemetry &amp; Details</h4>
          <button onclick="toggleInfoDrawer()" class="text-slate-400 hover:text-slate-700">
            <i class="fa-solid fa-x w-4 h-4"></i>
          </button>
        </div>
        <div class="p-5 space-y-5 text-xs">
          <div id="drawerCaptionBlock">
            <label class="font-bold text-slate-400 uppercase tracking-wider text-[10px]">Caption</label>
            <p class="text-sm font-semibold text-slate-900 mt-1" id="drawerCaption"></p>
          </div>
          <div class="flex items-start space-x-3">
            <i class="fa-solid fa-calendar w-4 h-4 text-slate-400 mt-0.5"></i>
            <div>
              <p class="font-bold text-slate-800" id="drawerDate"></p>
              <p class="text-slate-400" id="drawerTime"></p>
            </div>
          </div>
          <div class="flex items-start space-x-3" id="drawerAuthorBlock">
            <i class="fa-solid fa-layer-group w-4 h-4 text-slate-400 mt-0.5"></i>
            <div>
              <p class="font-bold text-slate-800" id="drawerAuthor"></p>
              <p class="text-slate-500" id="drawerOffice"></p>
            </div>
          </div>
          <div class="flex items-start space-x-3">
            <i class="fa-solid fa-map-pin w-4 h-4 text-emerald-600 mt-0.5"></i>
            <div>
              <p class="font-bold text-slate-800" id="drawerLocation"></p>
            </div>
          </div>
          <div class="pt-3 border-t border-slate-100">
            <label class="font-bold text-slate-400 uppercase tracking-wider text-[10px] block mb-2">Sensor Telemetry</label>
            <div class="space-y-2 bg-slate-50 p-3 rounded-2xl border border-slate-100">
              <div class="flex items-center space-x-2 text-slate-700">
                <i class="fa-solid fa-camera w-3.5 h-3.5 text-slate-400"></i>
                <span id="drawerDevice"></span>
              </div>
              <div class="text-[11px] text-slate-500 font-mono" id="drawerExif"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= UNAUTHENTICATED PARTICIPANT PHOTO UPLOAD MODAL ================= -->
  <div id="gUploadModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full overflow-hidden shadow-2xl border border-slate-200 my-8">
      <div class="p-5 px-6 border-b border-slate-100 flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <i class="fa-solid fa-image w-5 h-5 text-emerald-600"></i>
          <h3 class="font-bold text-base text-slate-900">Upload to Mission Photo Stream</h3>
        </div>
        <button onclick="closeUploadModal()" class="text-slate-400 hover:text-slate-700">
          <i class="fa-solid fa-x w-5 h-5"></i>
        </button>
      </div>

      <form id="publicUploadForm" class="p-6 space-y-5" onsubmit="event.preventDefault(); handleNewPhotoUpload();">
        <input type="hidden" id="photoUploadEndpoint" value="{{ route('landing.photos') }}" />
        <input type="hidden" id="photoUploadCsrf" value="{{ csrf_token() }}" />
        <div
          id="dropArea"
          onclick="document.getElementById('fileInput').click()"
          ondragover="handleUploadDragOver(event)"
          ondragleave="handleUploadDragLeave(event)"
          ondrop="handleUploadDrop(event)"
          class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-2xl p-6 text-center cursor-pointer bg-slate-50 hover:bg-emerald-50/20 transition relative">
          <input
            type="file"
            id="fileInput"
            accept="image/*"
            multiple
            class="hidden"
            onchange="handleFileInputPreview(event)" />
          <div id="dropPrompt" class="space-y-2">
            <div class="w-12 h-12 rounded-full bg-white text-emerald-600 shadow flex items-center justify-center mx-auto">
              <i class="fa-solid fa-cloud-arrow-up w-6 h-6"></i>
            </div>
            <p class="text-xs font-bold text-slate-800">Select photos from device</p>
            <p class="text-[11px] text-slate-400">JPG, PNG, WEBP • Select or drop several at once (No login required)</p>
          </div>
        </div>

        <div id="dropPreview" class="hidden">
          <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-semibold text-slate-600" id="previewCount"></span>
            <button type="button" onclick="event.stopPropagation(); clearUploadFile();" class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 hover:text-red-600 hover:bg-red-50 px-2 py-1 rounded-lg transition">
              <i class="fa-solid fa-trash w-3 h-3"></i>
              Remove all
            </button>
          </div>
          <div id="previewGrid" class="grid grid-cols-3 sm:grid-cols-4 gap-2"></div>
        </div>

        <div>
          <label for="upCaption" class="block text-xs font-semibold text-slate-700 mb-1">Caption / Field Activity <span class="font-normal text-slate-400">(optional)</span></label>
          <input type="text" id="upCaption" placeholder="Core drill test on Station 05+100" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 outline-none" />
        </div>

        <div>
          <label for="upLocation" class="block text-xs font-semibold text-slate-700 mb-1">Location Tag <span class="font-normal text-slate-400">(optional)</span></label>
          <input type="text" id="upLocation" placeholder="Balingasag, Misamis Oriental" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs focus:border-emerald-600 outline-none" />
        </div>

        <div class="pt-3 flex items-center justify-between gap-2">
          <p class="text-[11px] text-slate-400">
            <i class="fa-solid fa-circle-info w-3 h-3 mr-1"></i>Caption and location apply to all selected photos.
          </p>
          <div class="flex items-center space-x-2">
            <button type="button" onclick="closeUploadModal()" class="px-4 py-2 rounded-full text-xs font-medium text-slate-600 hover:bg-slate-100">Cancel</button>
            <button type="submit" class="px-6 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20">
              Publish to Stream
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

