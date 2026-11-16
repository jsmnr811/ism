<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DA-PRDP • ISM Portal</title>
  <meta name="description" content="Official DA-PRDP Implementation Support Mission (ISM) Portal for agendas, meetings, site visits, reports information of the Philippine Rural Development Project.">
  <meta name="keywords" content="DA-PRDP, PRDP, PRDP ISM, ISM Portal, Implementation Support Mission, Philippine Rural Development Project, Department of Agriculture, DA PRDP, WB ISM">
  <meta name="author" content="Department of Agriculture - Philippine Rural Development Project">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="{{ url('/') }}">

  @include('partials.favicon')

  <!-- Fonts: Inter & Roboto Mono for AGT engineering telemetry -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Roboto+Mono:wght@400;500;600;700&display=swap" rel="stylesheet" />

  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite(['resources/css/landing.css', 'resources/js/app.js'])

  <!-- Alpine.js -->
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.10/dist/cdn.min.js" defer></script>

</head>

<body class="bg-slate-50 text-slate-900 antialiased selection:bg-emerald-100 selection:text-emerald-900 flex flex-col min-h-screen">


  @include('landing.top-bar')
  @include('landing.nav')

  <main class="flex-grow">
    @include('landing.banner')

    <div x-data="lazySection('agenda')" data-lazy-section="agenda">
      <template x-if="!loaded">
        <div class="flex items-center justify-center py-24 text-slate-300">
          <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
        </div>
      </template>
      <div x-html="html"></div>
    </div>

    <div x-data="lazySection('meetings')" data-lazy-section="meetings">
      <template x-if="!loaded">
        <div class="flex items-center justify-center py-24 text-slate-300">
          <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
        </div>
      </template>
      <div x-html="html"></div>
    </div>

    <div x-data="lazySection('site-visits')" data-lazy-section="site-visits">
      <template x-if="!loaded">
        <div class="flex items-center justify-center py-24 text-slate-300">
          <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
        </div>
      </template>
      <div x-html="html"></div>
    </div>

    <div x-data="lazySection('registration')" data-lazy-section="registration">
      <template x-if="!loaded">
        <div class="flex items-center justify-center py-24 text-slate-300">
          <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
        </div>
      </template>
      <div x-html="html"></div>
    </div>

    <div x-data="lazySection('gallery')" data-lazy-section="gallery">
      <template x-if="!loaded">
        <div class="flex items-center justify-center py-24 text-slate-300">
          <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
        </div>
      </template>
      <div x-html="html"></div>
    </div>

    <div x-data="lazySection('reports')" data-lazy-section="reports">
      <template x-if="!loaded">
        <div class="flex items-center justify-center py-24 text-slate-300">
          <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
        </div>
      </template>
      <div x-html="html"></div>
    </div>
  </main>

  @include('landing.footer')
  @include('landing.modals')
  @include('landing.scripts')

</body>

</html>
