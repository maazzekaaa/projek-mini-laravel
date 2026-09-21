<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Inter & Lucide Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        notionBg: '#121212',
                        notionCard: '#1e1e24',
                        notionHover: '#2a2a32',
                        notionBorder: '#2d2d38'
                    }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar {
            display: none;
        }
        * {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="bg-notionBg text-gray-200 min-h-screen font-sans antialiased pb-20 selection:bg-indigo-500 selection:text-white overflow-x-hidden">

    <!-- Header Section (Tanpa Logo) -->
    <header class="border-b border-notionBorder bg-notionCard/50 backdrop-blur-md sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <div>
                    <!-- 2. GANTI NAMA WEB UTAMA DI SINI -->
                    <h1 class="text-xl font-bold text-white tracking-tight">Pencatat Tugas Kuliah</h1>
                    <p class="text-xs text-gray-400">Kelola deadline & tugas perkuliahan secara terorganisir</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <!-- Tombol History -->
                <button onclick="document.getElementById('modalHistory').classList.remove('hidden')" 
                        class="bg-notionCard hover:bg-notionHover text-gray-300 text-sm font-medium px-4 py-2.5 rounded-xl border border-notionBorder flex items-center gap-2 transition duration-200">
                    <i data-lucide="history" class="w-4 h-4 text-amber-400"></i>
                    <span>Riwayat Aktivitas</span>
                </button>

                <!-- Tombol Tambah Tugas -->
                <button onclick="document.getElementById('modalAdd').classList.remove('hidden')" 
                        class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20 flex items-center gap-2 transition duration-200 active:scale-95">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Tambah Tugas</span>
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 mt-8">

        <!-- Notification Alert -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-950/60 border border-emerald-500/40 text-emerald-300 text-sm rounded-xl flex items-center justify-between shadow-lg backdrop-blur-sm">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-400"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif

        @php
            $allTasks = $tasksByWeek->flatten();
            $totalTasks = $allTasks->count();
            $inProgress = $allTasks->where('status', 'In progress')->count();
            $completed = $allTasks->where('status', 'Done')->count();

            // Logika Reminder Deadline (Ambil semua yang status != Done dan deadline <= 3 hari / terlewat)
            $urgentTasks = $allTasks->filter(function($t) {
                if ($t->status === 'Done' || empty($t->deadline)) return false;
                
                $today = \Carbon\Carbon::today();
                $deadline = \Carbon\Carbon::parse($t->deadline)->startOfDay();
                $daysLeft = (int) $today->diffInDays($deadline, false);
                
                return $daysLeft <= 3;
            })->sortBy(function($t) {
                return \Carbon\Carbon::parse($t->deadline)->timestamp;
            });
        @endphp

        <!-- Banner Reminder Deadline (Bisa Memuat Banyak Deadline) -->
        @if($urgentTasks->count() > 0)
            <div class="mb-8 p-4 bg-rose-950/40 border border-rose-500/40 rounded-2xl backdrop-blur-sm shadow-lg">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-rose-500/20">
                    <div class="flex items-center gap-2 text-rose-300 font-bold">
                        <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400 animate-pulse"></i>
                        <span class="tracking-wide uppercase text-sm">NDANGAN DIGARAP TUGAS E!!!</span>
                    </div>
                    <span class="text-xs bg-rose-900/80 text-rose-200 border border-rose-700/60 font-bold px-2.5 py-1 rounded-full shadow-sm">
                        {{ $urgentTasks->count() }} Tugas Mendesak
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-80 overflow-y-auto pr-1">
                    @foreach($urgentTasks as $uTask)
                        @php
                            $today = \Carbon\Carbon::today();
                            $deadline = \Carbon\Carbon::parse($uTask->deadline)->startOfDay();
                            $daysLeft = (int) $today->diffInDays($deadline, false);
                        @endphp
                        <div class="bg-notionCard/90 border border-rose-800/50 p-3 rounded-xl flex justify-between items-center text-xs hover:border-rose-500/50 transition">
                            <div class="min-w-0 pr-2">
                                <p class="font-bold text-white text-sm truncate" title="{{ $uTask->tugas }}">{{ $uTask->tugas }}</p>
                                <p class="text-gray-400 mt-0.5 truncate">{{ $uTask->matkul }} • {{ $deadline->format('d M Y') }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-md font-bold shrink-0 shadow-sm text-center
                                {{ $daysLeft < 0 ? 'bg-red-900/80 text-red-200 border border-red-700' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40' }}">
                                @if($daysLeft < 0)
                                    Terlewat {{ abs($daysLeft) }} hr
                                @elseif($daysLeft === 0)
                                    Hari ini!
                                @else
                                    H-{{ $daysLeft }}
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Quick Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-notionCard/70 border border-notionBorder p-4 rounded-xl flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Total Tugas</p>
                    <p class="text-2xl font-bold text-white mt-1">{{ $totalTasks }}</p>
                </div>
                <div class="p-3 bg-blue-500/10 text-blue-400 rounded-lg"><i data-lucide="layers" class="w-5 h-5"></i></div>
            </div>
            <div class="bg-notionCard/70 border border-notionBorder p-4 rounded-xl flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Sedang Dikerjakan</p>
                    <p class="text-2xl font-bold text-amber-400 mt-1">{{ $inProgress }}</p>
                </div>
                <div class="p-3 bg-amber-500/10 text-amber-400 rounded-lg"><i data-lucide="clock" class="w-5 h-5"></i></div>
            </div>
            <div class="bg-notionCard/70 border border-notionBorder p-4 rounded-xl flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Selesai</p>
                    <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $completed }}</p>
                </div>
                <div class="p-3 bg-emerald-500/10 text-emerald-400 rounded-lg"><i data-lucide="check-circle" class="w-5 h-5"></i></div>
            </div>
        </div>

        <!-- Task List per Week -->
        @forelse($tasksByWeek as $week => $tasks)
            <div class="mb-8 bg-notionCard/40 border border-notionBorder rounded-2xl p-5 backdrop-blur-sm shadow-sm">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-notionBorder/60">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 bg-indigo-500 rounded-full"></span>
                        <h2 class="text-lg font-bold text-white">Minggu {{ $week }}</h2>
                        <span class="text-xs bg-notionBorder text-gray-400 font-medium px-2.5 py-0.5 rounded-full">
                            {{ count($tasks) }} tugas
                        </span>
                    </div>
                </div>

                <div class="rounded-xl border border-notionBorder/80 bg-notionCard">
                    <table class="w-full text-left text-sm border-collapse table-auto">
                        <thead class="bg-notionBorder/30 text-gray-400 text-xs uppercase tracking-wider border-b border-notionBorder">
                            <tr>
                                <th class="py-3.5 px-3 font-semibold">Mata Kuliah</th>
                                <th class="py-3.5 px-3 font-semibold">Judul Tugas</th>
                                <th class="py-3.5 px-3 font-semibold">Deadline</th>
                                <th class="py-3.5 px-3 font-semibold">Tipe</th>
                                <th class="py-3.5 px-3 font-semibold">Status</th>
                                <th class="py-3.5 px-3 font-semibold">Media & File</th>
                                <th class="py-3.5 px-3 font-semibold">Catatan</th>
                                <th class="py-3.5 px-3 font-semibold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-notionBorder/60 text-gray-300">
                            @foreach($tasks as $task)
                                @php
                                    $today = \Carbon\Carbon::today();
                                    $deadline = $task->deadline ? \Carbon\Carbon::parse($task->deadline)->startOfDay() : null;
                                    $daysLeft = ($deadline && $task->status !== 'Done') ? (int) $today->diffInDays($deadline, false) : null;
                                @endphp
                                <tr class="hover:bg-notionHover/60 transition duration-150">
                                    <td class="py-3.5 px-3 font-medium">
                                        <span class="bg-indigo-950/80 text-indigo-200 border border-indigo-700/60 px-2.5 py-1.5 rounded-lg text-xs font-bold shadow-sm inline-block">
                                            {{ $task->matkul }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 font-bold text-white text-sm">{{ $task->tugas }}</td>
                                    <td class="py-3.5 px-3 text-gray-400 whitespace-nowrap text-xs">
                                        @if($deadline)
                                            <div class="flex items-center gap-1.5">
                                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-gray-500"></i>
                                                <span>{{ $deadline->format('d M Y') }}</span>
                                                @if($daysLeft !== null && $daysLeft <= 3)
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase
                                                        {{ $daysLeft < 0 ? 'bg-red-950 text-red-400 border border-red-800' : 'bg-rose-950 text-rose-400 border border-rose-800' }}">
                                                        {{ $daysLeft < 0 ? 'Terlewat' : ($daysLeft === 0 ? 'Hari ini' : "H-{$daysLeft}") }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-600">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold inline-flex items-center gap-1
                                            {{ $task->tipe === 'Individu' ? 'bg-orange-950/60 text-orange-400 border border-orange-800/50' : 'bg-purple-950/60 text-purple-400 border border-purple-800/50' }}">
                                            <i data-lucide="{{ $task->tipe === 'Individu' ? 'user' : 'users' }}" class="w-3 h-3"></i>
                                            {{ $task->tipe }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold inline-flex items-center gap-1.5
                                            @if($task->status === 'Done') bg-emerald-950/60 text-emerald-400 border border-emerald-800/50
                                            @elseif($task->status === 'In progress') bg-amber-950/60 text-amber-400 border border-amber-800/50
                                            @else bg-rose-950/60 text-rose-400 border border-rose-800/50 @endif">
                                            <span class="w-1.5 h-1.5 rounded-full 
                                                @if($task->status === 'Done') bg-emerald-400
                                                @elseif($task->status === 'In progress') bg-amber-400
                                                @else bg-rose-400 @endif"></span>
                                            {{ $task->status }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-3 max-w-[120px] truncate">
                                        @if($task->files_media)
                                            <a href="{{ $task->files_media }}" target="_blank" class="inline-flex items-center gap-1 text-indigo-400 hover:text-indigo-300 hover:underline text-xs">
                                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                                <span class="truncate">{{ $task->files_media }}</span>
                                            </a>
                                        @else
                                            <span class="text-gray-600 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-3 text-gray-400 max-w-[120px] truncate text-xs">{{ $task->catatan ?? '-' }}</td>
                                    <td class="py-3.5 px-3 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Tombol Edit -->
                                            <button type="button"
                                                    data-task='@json($task)'
                                                    onclick="openEditModal(this)" 
                                                    class="p-1.5 text-gray-400 hover:text-amber-400 hover:bg-amber-400/10 rounded-lg transition" title="Edit Tugas">
                                                <i data-lucide="pencil" class="w-4 h-4"></i>
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus tugas ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-400 hover:bg-rose-400/10 rounded-lg transition" title="Hapus Tugas">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="text-center py-20 bg-notionCard/40 border border-notionBorder rounded-2xl">
                <div class="inline-flex p-4 bg-notionBorder/50 rounded-full text-gray-400 mb-3">
                    <i data-lucide="folder-open" class="w-8 h-8"></i>
                </div>
                <h3 class="text-lg font-bold text-white">Belum Ada Tugas</h3>
                <p class="text-sm text-gray-400 mt-1">Klik tombol "+ Tambah Tugas" di atas untuk menambahkan tugas baru.</p>
            </div>
        @endforelse

    </main>

    <!-- Modal Form Tambah Tugas -->
    <div id="modalAdd" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50 transition-opacity">
        <div class="bg-notionCard border border-notionBorder rounded-2xl p-6 w-full max-w-lg shadow-2xl">
            <div class="flex justify-between items-center mb-5 pb-3 border-b border-notionBorder">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-5 h-5 text-indigo-400"></i>
                    Tambah Tugas Baru
                </h3>
                <button onclick="document.getElementById('modalAdd').classList.add('hidden')" class="text-gray-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="{{ route('tasks.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Minggu Ke-</label>
                        <input type="number" name="week" min="1" required placeholder="1"
                               class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white placeholder-gray-600 outline-none transition">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Mata Kuliah</label>
                        <input type="text" name="matkul" required placeholder="Pemrograman Web"
                               class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white placeholder-gray-600 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Judul Tugas</label>
                    <input type="text" name="tugas" required placeholder="Membuat Landing Page"
                           class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white placeholder-gray-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Deadline Tanggal</label>
                    <input type="date" name="deadline"
                           class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white outline-none transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Tipe</label>
                        <select name="tipe" class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white outline-none transition">
                            <option value="Individu">Individu</option>
                            <option value="Kelompok">Kelompok</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Status</label>
                        <select name="status" class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white outline-none transition">
                            <option value="Not started">Not started</option>
                            <option value="In progress">In progress</option>
                            <option value="Done">Done</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Link File / Media (Opsional)</label>
                    <input type="url" name="files_media" placeholder="https://drive.google.com/..."
                           class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white placeholder-gray-600 outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Catatan (Opsional)</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan..."
                              class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white placeholder-gray-600 outline-none transition"></textarea>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-3 border-t border-notionBorder">
                    <button type="button" onclick="document.getElementById('modalAdd').classList.add('hidden')" 
                            class="px-4 py-2.5 text-sm text-gray-400 hover:text-white font-medium transition">
                        Batal
                    </button>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-600/20 transition">
                        Simpan Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Form Edit Tugas -->
    <div id="modalEdit" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50 transition-opacity">
        <div class="bg-notionCard border border-notionBorder rounded-2xl p-6 w-full max-w-lg shadow-2xl">
            <div class="flex justify-between items-center mb-5 pb-3 border-b border-notionBorder">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i data-lucide="pencil" class="w-5 h-5 text-amber-400"></i>
                    Edit Progress & Tugas
                </h3>
                <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-gray-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEdit" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Minggu Ke-</label>
                        <input type="number" id="edit_week" name="week" min="1" required
                               class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white outline-none transition">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Mata Kuliah</label>
                        <input type="text" id="edit_matkul" name="matkul" required
                               class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Judul Tugas</label>
                    <input type="text" id="edit_tugas" name="tugas" required
                           class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Deadline Tanggal</label>
                    <input type="date" id="edit_deadline" name="deadline"
                           class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white outline-none transition">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 mb-1">Tipe</label>
                        <select id="edit_tipe" name="tipe" class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white outline-none transition">
                            <option value="Individu">Individu</option>
                            <option value="Kelompok">Kelompok</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-amber-400 mb-1">Status / Progress</label>
                        <select id="edit_status" name="status" class="w-full bg-notionBg border border-amber-500/50 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 p-2.5 rounded-xl text-sm text-white outline-none transition">
                            <option value="Not started">Not started</option>
                            <option value="In progress">In progress</option>
                            <option value="Done">Done</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Link File / Media (Opsional)</label>
                    <input type="url" id="edit_files_media" name="files_media"
                           class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 mb-1">Catatan (Opsional)</label>
                    <textarea id="edit_catatan" name="catatan" rows="2"
                              class="w-full bg-notionBg border border-notionBorder focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 p-2.5 rounded-xl text-sm text-white outline-none transition"></textarea>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-3 border-t border-notionBorder">
                    <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" 
                            class="px-4 py-2.5 text-sm text-gray-400 hover:text-white font-medium transition">
                        Batal
                    </button>
                    <button type="submit" class="bg-amber-600 hover:bg-amber-500 text-white text-sm font-medium px-5 py-2.5 rounded-xl shadow-lg shadow-amber-600/20 transition">
                        Update Tugas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Riwayat Aktivitas (History) -->
    <div id="modalHistory" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden flex items-center justify-center p-4 z-50 transition-opacity">
        <div class="bg-notionCard border border-notionBorder rounded-2xl p-6 w-full max-w-xl shadow-2xl">
            <div class="flex justify-between items-center mb-5 pb-3 border-b border-notionBorder">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <i data-lucide="history" class="w-5 h-5 text-amber-400"></i>
                    Riwayat Aktivitas Tugas
                </h3>
                <button onclick="document.getElementById('modalHistory').classList.add('hidden')" class="text-gray-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="max-h-96 overflow-y-auto space-y-3 pr-2">
                @forelse($histories ?? [] as $history)
                    <div class="p-3 bg-notionBg/60 border border-notionBorder rounded-xl flex items-start gap-3">
                        <div class="p-2 rounded-lg mt-0.5 shrink-0
                            @if($history->action === 'created') bg-emerald-500/10 text-emerald-400
                            @elseif($history->action === 'updated') bg-amber-500/10 text-amber-400
                            @else bg-rose-500/10 text-rose-400 @endif">
                            <i data-lucide="@if($history->action === 'created') plus-circle @elseif($history->action === 'updated') refresh-cw @else trash-2 @endif" class="w-4 h-4"></i>
                        </div>
                        <div class="flex-1 text-xs">
                            <p class="text-gray-200 font-medium leading-relaxed">{{ $history->description }}</p>
                            <p class="text-gray-500 text-[10px] mt-1">{{ $history->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-sm text-gray-500 py-6">Belum ada riwayat aktivitas.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Script Lucide Icons & Helper JS -->
    <script>
        lucide.createIcons();

        function openEditModal(btn) {
            let task = JSON.parse(btn.getAttribute('data-task'));

            document.getElementById('formEdit').action = '/tasks/' + task.id;
            document.getElementById('edit_week').value = task.week;
            document.getElementById('edit_matkul').value = task.matkul;
            document.getElementById('edit_tugas').value = task.tugas;
            document.getElementById('edit_tipe').value = task.tipe;
            document.getElementById('edit_status').value = task.status;
            document.getElementById('edit_files_media').value = task.files_media ?? '';
            document.getElementById('edit_catatan').value = task.catatan ?? '';

            if (task.deadline) {
                let dateStr = task.deadline.split('T')[0];
                document.getElementById('edit_deadline').value = dateStr;
            } else {
                document.getElementById('edit_deadline').value = '';
            }

            document.getElementById('modalEdit').classList.remove('hidden');
        }
    </script>
</body>
</html>