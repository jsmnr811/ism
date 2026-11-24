@php
  $recordings = $meetings->filter(fn ($meeting) => filled($meeting->recording_link));
@endphp

@if($recordings->isNotEmpty())
<!-- ================= MEETING RECORDINGS ================= -->
<section id="recordings-section" class="py-16 bg-slate-50 border-b border-slate-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
      <div>
        <span class="text-xs font-bold text-red-800 uppercase tracking-wider bg-red-50 border border-red-200 px-3 py-1 rounded-full">
          Mission Recordings
        </span>
        <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Meeting Recordings ({{ $recordings->count() }})</h2>
        <p class="text-slate-600 text-sm mt-1">
          Watch recorded sessions from this mission.
        </p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
      @foreach($recordings as $meeting)
        <article class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm hover:shadow-md transition flex flex-col justify-between gap-4">
          <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-2">
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                <i class="fa-solid fa-calendar w-3 h-3"></i>
                {{ $meeting->date?->format('M j, Y') }}
              </span>
              <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                {{ $meeting->types()[$meeting->type] ?? $meeting->type }}
              </span>
            </div>

            <h3 class="text-sm font-bold text-slate-900 line-clamp-2">{{ $meeting->title }}</h3>

            @if($meeting->description)
              <p class="text-xs text-slate-600 line-clamp-2">{{ $meeting->description }}</p>
            @endif
          </div>

          <div class="flex flex-wrap items-center gap-2">
            <a href="{{ $meeting->recording_link }}" target="_blank" rel="noopener"
              class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs font-bold hover:bg-red-100 hover:border-red-300 hover:text-red-800 transition">
              <i class="fa-solid fa-circle-play w-3.5 h-3.5"></i>
              Watch Recording
            </a>

            @if($meeting->transcription_link)
              <a href="{{ $meeting->transcription_link }}" target="_blank" rel="noopener"
                class="inline-flex items-center justify-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50 hover:border-slate-300 hover:text-slate-800 transition">
                <i class="fa-solid fa-file-lines w-3.5 h-3.5"></i>
                Transcript
              </a>
            @endif
          </div>
        </article>
      @endforeach
    </div>

  </div>
</section>
@endif
