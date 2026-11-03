<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentPortal\ParentCommunicationLog;
use App\Models\ParentPortal\ParentMessage;
use App\Models\SchoolSetting;
use App\Models\SmsMessage;
use App\Models\Student;
use App\Services\ParentPortal\ParentStudentService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class ParentCommunicationsController extends Controller
{
    public function __construct(private ParentStudentService $parentStudentService) {}

    public function index(Request $request, ?Student $student = null)
    {
        $parent = Auth::guard('parent')->user();
        $children = $this->parentStudentService->childrenFor($parent);

        $selectedStudent = null;

        if ($student) {
            $selectedStudent = $this->parentStudentService->findOwnedStudent($parent, $student->id);

            if (! $selectedStudent) {
                abort(403);
            }
        }

        $timeline = $this->buildTimeline($parent, $children, $selectedStudent);

        return view('parent.communications', [
            'parent' => $parent,
            'student' => $selectedStudent,
            'children' => $children,
            'timeline' => $timeline,
            'school' => SchoolSetting::current(),
        ]);
    }

    public function storeMessage(Request $request)
    {
        $parent = Auth::guard('parent')->user();

        $validated = $request->validate([
            'message' => 'required|string|min:5|max:1000',
            'student_id' => 'nullable|exists:students,id',
        ]);

        if (! empty($validated['student_id'])) {
            $child = $this->parentStudentService->findOwnedStudent($parent, (int) $validated['student_id']);

            if (! $child) {
                abort(403);
            }
        }

        ParentMessage::create([
            'parent_account_id' => $parent->id,
            'student_id' => $validated['student_id'] ?? null,
            'message' => $validated['message'],
            'status' => ParentMessage::STATUS_NEW,
        ]);

        return back()->with('message_success', 'Your message has been sent to the school.');
    }

    private function buildTimeline($parent, Collection $children, ?Student $selectedStudent): Collection
    {
        $logs = ParentCommunicationLog::query()
            ->where('parent_account_id', $parent->id)
            ->when($selectedStudent, fn ($q) => $q->where('student_id', $selectedStudent->id))
            ->orderByDesc('sent_at')
            ->limit(100)
            ->get();

        $smsCampaigns = SmsMessage::query()
            ->where('status', 'sent')
            ->where(function ($query) use ($children, $selectedStudent) {
                $query->where('audience', 'school');

                $classIds = ($selectedStudent ? collect([$selectedStudent]) : $children)
                    ->pluck('school_class_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                if ($classIds !== []) {
                    $query->orWhere(function ($q) use ($classIds) {
                        $q->where('audience', 'class')
                            ->whereIn('school_class_id', $classIds);
                    });
                }

                $studentIds = ($selectedStudent ? collect([$selectedStudent->id]) : $children->pluck('id'))->all();

                if ($studentIds !== []) {
                    $query->orWhere(function ($q) use ($studentIds) {
                        $q->where('audience', 'individual')
                            ->where('target_type', 'student')
                            ->whereIn('target_id', $studentIds);
                    });
                }
            })
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $items = collect();

        foreach ($logs as $log) {
            $items->push([
                'type' => 'log',
                'channel' => $log->channel,
                'message' => $log->message,
                'sent_at' => $log->sent_at,
                'student_id' => $log->student_id,
            ]);
        }

        foreach ($smsCampaigns as $campaign) {
            $items->push([
                'type' => 'sms',
                'channel' => 'sms',
                'message' => $campaign->message,
                'sent_at' => $campaign->created_at,
                'student_id' => null,
                'label' => $campaign->audience_label,
            ]);
        }

        return $items->sortByDesc('sent_at')->values();
    }
}
