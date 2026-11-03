<?php

namespace App\Http\Controllers\Dormitory;

use App\Http\Controllers\Controller;
use App\Models\Dormitory;
use App\Models\DormitoryBed;
use App\Models\House;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DormitoryController extends Controller
{
    public function index()
    {
        $houses = House::with(['dormitories.beds.student'])->orderBy('name')->get();

        return view('dormitory.setup', [
            'houses' => $houses,
        ]);
    }

    public function storeHouse(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
        ]);

        if (House::where('name', trim($request->name))->exists()) {
            return back()->with('message_error', 'This house already exists.');
        }

        House::create([
            'name' => trim($request->name),
            'description' => trim($request->description ?? '') ?: null,
            'status' => trim($request->status),
            'created_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'House created successfully.');
    }

    public function showHouse($id)
    {
        return response()->json(House::findOrFail($id));
    }

    public function updateHouse(Request $request)
    {
        $request->validate([
            'house_id' => 'required|exists:houses,id',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'status' => 'required|in:Active,Inactive',
        ]);

        $house = House::findOrFail($request->house_id);

        if (House::where('name', trim($request->name))->where('id', '!=', $house->id)->exists()) {
            return back()->with('message_error', 'This house already exists.');
        }

        $house->update([
            'name' => trim($request->name),
            'description' => trim($request->description ?? '') ?: null,
            'status' => trim($request->status),
            'updated_by' => Auth::id(),
        ]);

        return back()->with('message_success', 'House updated successfully.');
    }

    public function storeDormitory(Request $request)
    {
        $request->validate([
            'house_id' => 'required|exists:houses,id',
            'name' => 'required|string|max:100',
            'bed_count' => 'required|integer|min:1|max:100',
            'status' => 'required|in:Active,Inactive',
        ]);

        $houseId = (int) $request->house_id;
        $name = trim($request->name);

        if (Dormitory::where('house_id', $houseId)->where('name', $name)->exists()) {
            return back()->with('message_error', 'This dormitory already exists in the selected house.');
        }

        DB::transaction(function () use ($request, $houseId, $name) {
            $dormitory = Dormitory::create([
                'house_id' => $houseId,
                'name' => $name,
                'bed_count' => (int) $request->bed_count,
                'status' => trim($request->status),
                'created_by' => Auth::id(),
            ]);

            $this->syncDormitoryBeds($dormitory, (int) $request->bed_count);
        });

        return back()->with('message_success', 'Dormitory created with beds successfully.');
    }

    public function showDormitory($id)
    {
        return response()->json(Dormitory::with('house')->findOrFail($id));
    }

    public function updateDormitory(Request $request)
    {
        $request->validate([
            'dormitory_id' => 'required|exists:dormitories,id',
            'house_id' => 'required|exists:houses,id',
            'name' => 'required|string|max:100',
            'bed_count' => 'required|integer|min:1|max:100',
            'status' => 'required|in:Active,Inactive',
        ]);

        $dormitory = Dormitory::with('beds')->findOrFail($request->dormitory_id);
        $houseId = (int) $request->house_id;
        $name = trim($request->name);
        $bedCount = (int) $request->bed_count;

        if (Dormitory::where('house_id', $houseId)->where('name', $name)->where('id', '!=', $dormitory->id)->exists()) {
            return back()->with('message_error', 'This dormitory already exists in the selected house.');
        }

        if ($bedCount < $dormitory->beds->whereNotNull('student_id')->count()) {
            return back()->with('message_error', 'Bed count cannot be less than the number of occupied beds.');
        }

        DB::transaction(function () use ($request, $dormitory, $houseId, $name, $bedCount) {
            $dormitory->update([
                'house_id' => $houseId,
                'name' => $name,
                'bed_count' => $bedCount,
                'status' => trim($request->status),
                'updated_by' => Auth::id(),
            ]);

            $this->syncDormitoryBeds($dormitory->fresh(['beds']), $bedCount);
        });

        return back()->with('message_success', 'Dormitory updated successfully.');
    }

    public function dormitoriesByHouse($houseId)
    {
        $dormitories = Dormitory::where('house_id', $houseId)
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'bed_count']);

        return response()->json($dormitories);
    }

    public function availableBeds($dormitoryId, Request $request)
    {
        $studentId = $request->query('student_id');

        $beds = DormitoryBed::where('dormitory_id', $dormitoryId)
            ->where(function ($query) use ($studentId) {
                $query->whereNull('student_id');

                if ($studentId) {
                    $query->orWhere('student_id', $studentId);
                }
            })
            ->orderBy('id')
            ->get(['id', 'bed_label', 'student_id'])
            ->sortBy(function ($bed) {
                if (preg_match('/\d+/', $bed->bed_label, $matches)) {
                    return (int) $matches[0];
                }

                return PHP_INT_MAX;
            })
            ->values();

        return response()->json($beds);
    }

    public function getStudentAssignment($id)
    {
        $student = Student::with(['house', 'dormitory', 'bed'])->findOrFail($id);

        return response()->json([
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'house_id' => $student->house_id,
            'dormitory_id' => $student->dormitory_id,
            'bed_id' => $student->bed_id,
            'house_name' => $student->house?->name,
            'dormitory_name' => $student->dormitory?->name,
            'bed_label' => $student->bed?->bed_label,
        ]);
    }

    public function assignStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'house_id' => 'required|exists:houses,id',
            'dormitory_id' => 'required|exists:dormitories,id',
            'bed_id' => 'required|exists:dormitory_beds,id',
        ]);

        $student = Student::findOrFail($request->student_id);
        $bed = DormitoryBed::with('dormitory')->findOrFail($request->bed_id);

        if ((int) $bed->dormitory->house_id !== (int) $request->house_id) {
            return back()->with('message_error', 'Selected dormitory does not belong to the chosen house.');
        }

        if ((int) $bed->dormitory_id !== (int) $request->dormitory_id) {
            return back()->with('message_error', 'Selected bed does not belong to the chosen dormitory.');
        }

        if ($bed->student_id && (int) $bed->student_id !== (int) $student->id) {
            return back()->with('message_error', 'This bed is already occupied.');
        }

        DB::transaction(function () use ($student, $bed) {
            DormitoryBed::where('student_id', $student->id)
                ->where('id', '!=', $bed->id)
                ->update(['student_id' => null]);

            $bed->student_id = $student->id;
            $bed->save();

            $student->house_id = $bed->dormitory->house_id;
            $student->dormitory_id = $bed->dormitory_id;
            $student->bed_id = $bed->id;
            $student->updated_by = Auth::id();
            $student->save();
        });

        return back()->with('message_success', 'Student dormitory assignment saved successfully.');
    }

    public function unassignStudent(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::findOrFail($request->student_id);

        DB::transaction(function () use ($student) {
            DormitoryBed::where('student_id', $student->id)->update(['student_id' => null]);

            $student->house_id = null;
            $student->dormitory_id = null;
            $student->bed_id = null;
            $student->updated_by = Auth::id();
            $student->save();
        });

        return back()->with('message_success', 'Student dormitory assignment removed.');
    }

    private function syncDormitoryBeds(Dormitory $dormitory, int $bedCount): void
    {
        $existingBeds = $dormitory->beds()->orderBy('id')->get();
        $currentCount = $existingBeds->count();

        if ($bedCount > $currentCount) {
            for ($i = $currentCount + 1; $i <= $bedCount; $i++) {
                DormitoryBed::create([
                    'dormitory_id' => $dormitory->id,
                    'bed_label' => 'Bed ' . $i,
                ]);
            }

            return;
        }

        if ($bedCount < $currentCount) {
            $bedsToRemove = $existingBeds->slice($bedCount);

            foreach ($bedsToRemove as $bed) {
                if ($bed->student_id) {
                    Student::where('id', $bed->student_id)->update([
                        'house_id' => null,
                        'dormitory_id' => null,
                        'bed_id' => null,
                    ]);
                }

                $bed->delete();
            }
        }

        $dormitory->fresh('beds')->beds->values()->each(function ($bed, $index) {
            $bed->update(['bed_label' => 'Bed ' . ($index + 1)]);
        });
    }
}
