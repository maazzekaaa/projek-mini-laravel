<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskHistory;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Tampilkan Halaman Utama / Workspace
     */
    public function index()
    {
        $allTasks = Task::orderBy('week', 'asc')
            ->orderBy('deadline', 'asc')
            ->get();

        $tasksByWeek = $allTasks->groupBy('week');

        // Ambil riwayat dari TaskHistory (Aman dari error jika tabel belum ada)
        try {
            $histories = TaskHistory::latest()->take(20)->get();
        } catch (\Exception $e) {
            $histories = collect([]);
        }

        return view('welcome', compact('tasksByWeek', 'histories'));
    }

    /**
     * Simpan Tugas Baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'week'        => 'required|integer|min:1',
            'matkul'      => 'required|string|max:255',
            'tugas'       => 'required|string|max:255',
            'deadline'    => 'nullable|date',
            'tipe'        => 'required|in:Individu,Kelompok',
            'status'      => 'required|in:Not started,In progress,Done',
            'files_media' => 'nullable|url|max:500',
            'catatan'     => 'nullable|string',
        ]);

        $task = Task::create($validated);

        try {
            TaskHistory::create([
                'action'      => 'created',
                'description' => "Menambahkan tugas baru: \"{$task->tugas}\" ({$task->matkul}) pada Minggu {$task->week}.",
            ]);
        } catch (\Exception $e) {
            // Abaikan jika tabel history belum terbuat
        }

        return redirect()->back()->with('success', 'Tugas berhasil ditambahkan!');
    }

    /**
     * Update Data / Progress Tugas
     */
    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);

        $validated = $request->validate([
            'week'        => 'required|integer|min:1',
            'matkul'      => 'required|string|max:255',
            'tugas'       => 'required|string|max:255',
            'deadline'    => 'nullable|date',
            'tipe'        => 'required|in:Individu,Kelompok',
            'status'      => 'required|in:Not started,In progress,Done',
            'files_media' => 'nullable|url|max:500',
            'catatan'     => 'nullable|string',
        ]);

        $oldStatus = $task->status;
        $task->update($validated);

        $statusInfo = ($oldStatus !== $task->status) 
            ? " (Status berubah dari '{$oldStatus}' menjadi '{$task->status}')" 
            : "";

        try {
            TaskHistory::create([
                'action'      => 'updated',
                'description' => "Memperbarui tugas \"{$task->tugas}\" ({$task->matkul}){$statusInfo}.",
            ]);
        } catch (\Exception $e) {
            // Abaikan jika tabel history belum terbuat
        }

        return redirect()->back()->with('success', 'Tugas berhasil diperbarui!');
    }

    /**
     * Hapus Tugas
     */
    public function destroy($id)
    {
        $task = Task::findOrFail($id);
        $taskTitle = $task->tugas;
        $matkulTitle = $task->matkul;

        $task->delete();

        try {
            TaskHistory::create([
                'action'      => 'deleted',
                'description' => "Menghapus tugas \"{$taskTitle}\" ({$matkulTitle}).",
            ]);
        } catch (\Exception $e) {
            // Abaikan error log history jika tabel belum siap
        }

        return redirect()->back()->with('success', 'Tugas berhasil dihapus!');
    }
}