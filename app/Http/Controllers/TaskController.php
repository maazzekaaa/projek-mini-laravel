<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskController extends Controller
{

    public function index()
    {

        $tasks = session('tasks', [
            [
                'id'          => 1,
                'week'        => 1,
                'matkul'      => '',
                'tugas'       => '',
                'deadline'    => '',
                'tipe'        => '',
                'status'      => '',
                'files_media' => null,
                'catatan'     => '',
            ]
        ]);

        $sortedTasks = collect($tasks)->sortBy([
            ['week', 'asc'],
            ['deadline', 'asc'],
        ]);

        $tasksByWeek = $sortedTasks->groupBy('week');

        $histories = session('histories', []);

        return view('welcome', compact('tasksByWeek', 'histories'));
    }

  
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

        $tasks = session('tasks', []);
        
        $validated['id'] = count($tasks) > 0 ? max(array_column($tasks, 'id')) + 1 : 1;

        $tasks[] = $validated;
        session(['tasks' => $tasks]);

        $this->addHistory('created', "Menambahkan tugas baru: \"{$validated['tugas']}\" ({$validated['matkul']}) pada Minggu {$validated['week']}.");

        return redirect()->back()->with('success', 'Tugas berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
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

        $tasks = session('tasks', []);

        foreach ($tasks as $key => $task) {
            if ($task['id'] == $id) {
                $oldStatus = $task['status'];
                $validated['id'] = (int) $id;
                $tasks[$key] = $validated;

                $statusInfo = ($oldStatus !== $validated['status']) 
                    ? " (Status berubah dari '{$oldStatus}' menjadi '{$validated['status']}')" 
                    : "";

                $this->addHistory('updated', "Memperbarui tugas \"{$validated['tugas']}\" ({$validated['matkul']}){$statusInfo}.");
                break;
            }
        }

        session(['tasks' => $tasks]);

        return redirect()->back()->with('success', 'Tugas berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $tasks = session('tasks', []);

        foreach ($tasks as $key => $task) {
            if ($task['id'] == $id) {
                $taskTitle = $task['tugas'];
                $matkulTitle = $task['matkul'];

                unset($tasks[$key]);
                $this->addHistory('deleted', "Menghapus tugas \"{$taskTitle}\" ({$matkulTitle}).");
                break;
            }
        }

        session(['tasks' => array_values($tasks)]);

        return redirect()->back()->with('success', 'Tugas berhasil dihapus!');
    }

    private function addHistory($action, $description)
    {
        $histories = session('histories', []);
        array_unshift($histories, [
            'action'      => $action,
            'description' => $description,
            'time'        => now()->diffForHumans(),
        ]);

        session(['histories' => array_slice($histories, 0, 20)]);
    }
}