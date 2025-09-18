<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pakistan AQI Dashboard</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-b from-white via-slate-50 to-indigo-50 text-slate-800 antialiased">
  <header class="sticky top-0 z-30 border-b border-slate-200/60 bg-white/80 backdrop-blur supports-[backdrop-filter]:bg-white/60">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <div class="h-9 w-9 rounded-lg bg-gradient-to-br from-indigo-600 to-sky-500 shadow ring-1 ring-indigo-400/40"></div>
        <h1 class="text-xl md:text-2xl font-bold tracking-tight">Pakistan AQI Dashboard</h1>
      </div>
      <div class="flex items-center gap-2">
        <a href="{{ route('download') }}" class="hidden sm:inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-white shadow hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4"/></svg>
          Download CSV
        </a>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto px-6 py-10">
    @if (session('success'))
      <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        {{ session('success') }}
      </div>
    @endif
    @if (session('error'))
      <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <div class="font-semibold">{{ session('error') }}</div>
        @if (session('error_details'))
          <div class="mt-1 text-red-700/90">{{ session('error_details') }}</div>
        @endif
      </div>
    @endif
    @if ($errors->any())
      <div class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
        <div class="font-semibold">There were some problems with your upload:</div>
        <ul class="mt-2 list-disc pl-5">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif
    <div class="mb-6">
      <div class="inline-flex rounded-xl bg-slate-100 p-1 shadow-sm">
        <button data-tab="upload" class="tab-btn rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:text-indigo-700 data-[active=true]:bg-white data-[active=true]:text-indigo-700 data-[active=true]:shadow">
          Upload
        </button>
        <button data-tab="messages" class="tab-btn rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:text-indigo-700">
          Messages
        </button>
        <button data-tab="analytics" class="tab-btn rounded-lg px-4 py-2 text-sm font-medium text-slate-600 transition hover:text-indigo-700">
          Analytics
        </button>
      </div>
    </div>

    <section id="upload" class="tab-content">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
          <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Upload CSV & Check AQI</h2>
            <p class="mt-1 text-sm text-slate-500">Upload contacts with columns: Name, City, Phone</p>

            <form action="{{ route('upload') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4" id="upload-form">
              @csrf
              <div id="dropzone" class="flex items-center justify-center gap-3 rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-slate-500 transition hover:border-indigo-300 hover:bg-indigo-50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M12 12V4m0 0L8 8m4-4l4 4"/></svg>
                <span class="hidden sm:inline">Drag & drop CSV here or</span>
                <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white shadow hover:bg-indigo-700">
                  <input type="file" name="csv" accept=".csv" class="hidden" id="file-input" required>
                  Browse
                </label>
              </div>
              <div id="file-error" class="mt-2 text-sm text-red-600"></div>
              <div class="flex items-center justify-between">
                <div id="file-name" class="text-sm text-slate-500"></div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-white shadow hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                  Process
                </button>
              </div>
            </form>

            @php $results = $results ?? session('aqi_results', []); @endphp
            @if(!empty($results))
              <div class="mt-8 overflow-hidden rounded-xl border border-slate-200">
                <table class="min-w-full divide-y divide-slate-200">
                  <thead class="bg-slate-50">
                    <tr>
                      <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Name</th>
                      <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">City</th>
                      <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Phone</th>
                      <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">AQI</th>
                      <th class="px-4 py-2 text-left text-xs font-semibold tracking-wide text-slate-600">Message</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100 bg-white">
                    @foreach($results as $row)
                      <tr class="hover:bg-indigo-50/40">
                        <td class="px-4 py-2 text-sm">{{ $row['name'] }}</td>
                        <td class="px-4 py-2 text-sm">{{ $row['city'] }}</td>
                        <td class="px-4 py-2 text-sm">{{ $row['phone'] }}</td>
                        <td class="px-4 py-2 text-sm font-semibold">
                          <span class="rounded-full px-2 py-1 text-xs
                            {{ ($row['aqi'] ?? 0) <= 50 ? 'bg-green-100 text-green-700' :
                               (($row['aqi'] ?? 0) <= 100 ? 'bg-yellow-100 text-yellow-700' :
                               'bg-red-100 text-red-700') }}">
                            {{ $row['aqi'] ?? 'N/A' }}
                          </span>
                        </td>
                        <td class="px-4 py-2 text-sm">{{ $row['message'] }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>
        <div>
          <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-indigo-600 to-sky-500 p-6 text-white shadow-sm">
            <h3 class="text-lg font-semibold">Tips</h3>
            <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-indigo-50/90">
              <li>Ensure CSV headers are exactly: Name, City, Phone.</li>
              <li>Only Pakistan cities are supported.</li>
              <li>You can customize messages in the Messages tab.</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section id="messages" class="tab-content hidden">
      <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Custom AQI Messages</h2>
        <p class="mt-1 text-sm text-slate-500">Override default texts per AQI range</p>
        <form method="POST" action="{{ route('save_messages') }}" class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
          @csrf
          <input type="text" name="good" placeholder="Good (0-50)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <input type="text" name="moderate" placeholder="Moderate (51-100)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <input type="text" name="unhealthy_sensitive" placeholder="Unhealthy Sensitive (101-150)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <input type="text" name="unhealthy" placeholder="Unhealthy (151-200)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <input type="text" name="very_unhealthy" placeholder="Very Unhealthy (201-300)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <input type="text" name="hazardous" placeholder="Hazardous (301+)" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-200">
          <div class="md:col-span-2 flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-sky-600 px-4 py-2 text-white shadow hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
              Save Messages
            </button>
          </div>
        </form>
      </div>
    </section>

    <section id="analytics" class="tab-content hidden">
      <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="font-semibold text-slate-900">AQI Overview</h3>
          <p class="mt-1 text-sm text-slate-500">Coming soon...</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="font-semibold text-slate-900">City Comparisons</h3>
          <p class="mt-1 text-sm text-slate-500">Coming soon...</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="font-semibold text-slate-900">Trends</h3>
          <p class="mt-1 text-sm text-slate-500">Coming soon...</p>
        </div>
      </div>
    </section>
  </main>

  <footer class="border-t border-slate-200/60 bg-white">
    <div class="max-w-7xl mx-auto px-6 py-6 text-center text-sm text-slate-500">
      Built with ❤️ for clean air awareness
    </div>
  </footer>
</body>
</html>
