    <!-- ================= ACTIVE MISSION HERO BANNER ================= -->
    <section id="banner-section" class="relative border-b border-slate-200/80 bg-white px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
      <div class="mx-auto max-w-7xl">

        {{-- Mission Banner Container --}}
        <div
          class="group relative isolate flex min-h-[340px] flex-col
                      justify-end overflow-hidden rounded-2xl
                      bg-slate-100 shadow-[0_20px_60px_-20px_rgba(15,23,42,0.22)]
                      ring-1 ring-slate-900/5
                      sm:min-h-[380px] sm:rounded-3xl
                      lg:min-h-[420px]">

          {{-- Dynamic Banner Image --}}
          <img
            src="{{ $bannerMission && $bannerMission->banner
                        ? (
                            Str::startsWith($bannerMission->banner, ['http://', 'https://'])
                                ? $bannerMission->banner
                                : asset('storage/' . ltrim($bannerMission->banner, '/'))
                        )
                        : 'https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=1800&q=85'
                    }}"
            alt="5th World Bank Implementation Support Mission to the DA-PRDP Scale-Up"
            class="absolute inset-0 -z-20 h-full w-full
                          object-cover object-center"
            fetchpriority="high" />

          {{-- Subtle Contrast Overlay --}}
          <div
            aria-hidden="true"
            class="absolute inset-0 -z-10
                          bg-gradient-to-t
                          from-slate-950/25 via-slate-900/[0.02]
                          to-transparent"></div>

        </div>
      </div>
    </section>

