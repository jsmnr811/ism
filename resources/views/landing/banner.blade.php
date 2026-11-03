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

          {{-- Mission Navigation --}}
          <div class="relative z-10 w-full p-5 sm:p-8 lg:p-10">
            <nav
              aria-label="Mission navigation"
              class="flex flex-wrap items-center gap-2 sm:gap-3">
              {{-- Agenda --}}
              <a
                href="#agenda-section"
                class="group inline-flex min-h-10 items-center gap-2
                                rounded-lg border border-white/15
                                bg-[#142B4D]/95 px-3.5 py-2
                                text-sm font-semibold text-white shadow-sm
                                backdrop-blur-md
                                transition duration-200 ease-out
                                hover:-translate-y-0.5 hover:bg-[#1D3A63]
                                hover:border-white/30 hover:shadow-md
                                focus-visible:outline-none focus-visible:ring-2
                                focus-visible:ring-white focus-visible:ring-offset-2
                                focus-visible:ring-offset-[#142B4D]">
                <i
                  class="fa-solid fa-calendar-days text-white/80"
                  aria-hidden="true"></i>
                <span>Agenda</span>
                <i
                  class="fa-solid fa-arrow-right ml-1 text-[10px] text-white/50
                                    transition-transform group-hover:translate-x-0.5
                                    group-hover:text-white"
                  aria-hidden="true"></i>
              </a>

              {{-- Virtual Site Visits --}}
              <a
                href="#site-visits-section"
                class="group inline-flex min-h-10 items-center gap-2
                                rounded-lg border border-white/15
                                bg-[#142B4D]/95 px-3.5 py-2
                                text-sm font-semibold text-white shadow-sm
                                backdrop-blur-md
                                transition duration-200 ease-out
                                hover:-translate-y-0.5 hover:bg-[#1D3A63]
                                hover:border-white/30 hover:shadow-md
                                focus-visible:outline-none focus-visible:ring-2
                                focus-visible:ring-white focus-visible:ring-offset-2
                                focus-visible:ring-offset-[#142B4D]">
                <i
                  class="fa-solid fa-map-pin text-white/80"
                  aria-hidden="true"></i>
                <span>Virtual Site Visits</span>
                <i
                  class="fa-solid fa-arrow-right ml-1 text-[10px] text-white/50
                                    transition-transform group-hover:translate-x-0.5
                                    group-hover:text-white"
                  aria-hidden="true"></i>
              </a>

              {{-- Meeting Links --}}
              <a
                href="#meetings-section"
                class="group inline-flex min-h-10 items-center gap-2
                                rounded-lg border border-white/15
                                bg-[#142B4D]/95 px-3.5 py-2
                                text-sm font-semibold text-white shadow-sm
                                backdrop-blur-md
                                transition duration-200 ease-out
                                hover:-translate-y-0.5 hover:bg-[#1D3A63]
                                hover:border-white/30 hover:shadow-md
                                focus-visible:outline-none focus-visible:ring-2
                                focus-visible:ring-white focus-visible:ring-offset-2
                                focus-visible:ring-offset-[#142B4D]">
                <i
                  class="fa-solid fa-video text-white/80"
                  aria-hidden="true"></i>
                <span>Meeting Links</span>
                <i
                  class="fa-solid fa-arrow-right ml-1 text-[10px] text-white/50
                                    transition-transform group-hover:translate-x-0.5
                                    group-hover:text-white"
                  aria-hidden="true"></i>
              </a>
            </nav>
          </div>
        </div>
      </div>
    </section>

