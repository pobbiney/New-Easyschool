<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ParentPortal\ParentMessage;
use Illuminate\Http\Request;

class AdminParentMessageController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $search = trim($request->string('q')->toString());

        $query = ParentMessage::query()->with(['parentAccount', 'student']);

        if (in_array($status, [
            ParentMessage::STATUS_NEW,
            ParentMessage::STATUS_READ,
            ParentMessage::STATUS_REPLIED,
        ], true)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($inner) use ($like) {
                $inner->where('message', 'like', $like)
                    ->orWhere('admin_reply', 'like', $like)
                    ->orWhereHas('parentAccount', function ($parent) use ($like) {
                        $parent->where('guardian_name', 'like', $like)
                            ->orWhere('phone', 'like', $like);
                    })
                    ->orWhereHas('student', function ($student) use ($like) {
                        $student->where('firstname', 'like', $like)
                            ->orWhere('surname', 'like', $like)
                            ->orWhere('othername', 'like', $like);
                    });
            });
        }

        $messages = $query
            ->orderByRaw("CASE WHEN status = 'new' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all' => ParentMessage::query()->count(),
            'new' => ParentMessage::query()->where('status', ParentMessage::STATUS_NEW)->count(),
            'read' => ParentMessage::query()->where('status', ParentMessage::STATUS_READ)->count(),
            'replied' => ParentMessage::query()->where('status', ParentMessage::STATUS_REPLIED)->count(),
        ];

        return view('parent.admin-messages', [
            'messages' => $messages,
            'counts' => $counts,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function markRead(Request $request, ParentMessage $message)
    {
        $validated = $request->validate([
            'admin_reply' => 'nullable|string|max:1000',
        ]);

        $message->update([
            'status' => $validated['admin_reply']
                ? ParentMessage::STATUS_REPLIED
                : ParentMessage::STATUS_READ,
            'admin_reply' => $validated['admin_reply'] ?? $message->admin_reply,
            'read_at' => $message->read_at ?? now(),
        ]);

        return back()->with('message_success', 'Message updated.');
    }
}
