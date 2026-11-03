  <!-- ================= REPUBLIC OFFICIAL TOP BAR ================= -->
  <aside aria-label="Official Republic Banner" class="bg-govnavy-950 text-slate-300 text-xs py-2.5 px-4 border-b border-slate-800">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3">

      <!-- Agency Information -->
      <div class="flex items-center gap-2 min-w-0">
        <span class="text-slate-400 hidden sm:inline whitespace-nowrap">
          Republic of the Philippines
        </span>

        <span class="text-slate-600 hidden sm:inline">•</span>

        <span class="font-medium text-white truncate">
          Department of Agriculture • Philippine Rural Development Project
        </span>
      </div>

      <!-- Philippine Standard Time (PST) indicator -->
      <div class="flex items-center text-xs"
        x-data="{ time: '--:--:-- PST' }"
        x-init="time = new Date().toLocaleTimeString('en-US', { timeZone: 'Asia/Manila', hour12: true }); setInterval(() => { time = new Date().toLocaleTimeString('en-US', { timeZone: 'Asia/Manila', hour12: true }) }, 1000)">
        <div class="flex items-center gap-1.5 text-slate-300 font-mono text-[11px]">
          <i class="fa-solid fa-clock w-3.5 h-3.5 text-phgold-400 shrink-0"></i>
          <span class="whitespace-nowrap">
            PST: <strong x-text="time" class="text-white"></strong>
          </span>
        </div>
      </div>

    </div>
  </aside>

