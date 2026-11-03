<?php

namespace App\Http\Controllers\Sms;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\SmsMessage;
use App\Models\Staff;
use App\Models\Student;
use App\Services\MNotifyService;
use App\Services\Sms\SchoolSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class SmsController extends Controller
{
    public function __construct(
        private SchoolSmsService $sms,
        private MNotifyService $mnotify,
    ) {}

    public function index()
    {
        $messages = SmsMessage::with('creator')->latest()->limit(30)->get();

        return view('sms.send', [
            'configured' => $this->mnotify->isConfigured(),
            'senderId' => config('mnotify.sender_id'),
            'classes' => SchoolClass::where('status', 'Active')->orderBy('name')->get(),
            'staffMembers' => Staff::where('status', 'Active')->orderBy('surname')->orderBy('firstname')->get(['id', 'title', 'firstname', 'othername', 'surname', 'employee_id']),
            'students' => Student::where('status', 'Active')->orderBy('surname')->orderBy('firstname')->get(['id', 'firstname', 'othername', 'surname', 'student_id', 'class_name']),
            'messages' => $messages,
            'stats' => $this->stats(),
        ]);
    }

    public function recipients(Request $request)
    {
        try {
            $preview = $this->sms->preview(
                (string) $request->input('audience'),
                $request->only(['school_class_id', 'target_type', 'target_id', 'target_ids'])
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
                'count' => 0,
                'skipped' => 0,
                'sample' => [],
            ]);
        }

        return response()->json([
            'ok' => true,
            'label' => $preview['label'],
            'count' => count($preview['recipients']),
            'skipped' => $preview['skipped'],
            'sample' => collect($preview['recipients'])->take(8)->pluck('name')->values(),
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'audience' => 'required|in:teachers,staff,class,school,individual',
            'message' => 'required|string|min:3|max:1000',
            'school_class_id' => 'required_if:audience,class|nullable|exists:school_classes,id',
            'target_type' => 'required_if:audience,individual|nullable|in:staff,student',
            'target_ids' => 'required_if:audience,individual|nullable|array|min:1',
            'target_ids.*' => 'integer',
        ]);

        $audience = $request->input('audience');
        $options = $request->only(['school_class_id', 'target_type', 'target_ids']);
        $message = trim($request->input('message'));

        try {
            $preview = $this->sms->preview($audience, $options);
            $result = $this->sms->send($audience, $message, $options);
        } catch (RuntimeException $exception) {
            return back()->withInput()->with('message_error', $exception->getMessage());
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->with('message_error', 'Could not connect to the SMS gateway. The message was not sent.');
        }

        try {
            $this->recordAttempt($request, $audience, $message, $options, $result, 'sent');
        } catch (\Throwable $exception) {
            report($exception);
        }

        $note = $result['sent'].' message'.($result['sent'] === 1 ? '' : 's').' sent to '.$result['label'].'.';
        if ($preview['skipped'] > 0) {
            $note .= ' '.$preview['skipped'].' skipped (no phone number).';
        }

        return back()->with('message_success', $note);
    }

    /**
     * @param  array<string, mixed>  $options
     * @param  array{label: string, sent: int, skipped: int, recipient_count: int, response: mixed}  $result
     */
    private function recordAttempt(Request $request, string $audience, string $message, array $options, array $result, string $status): void
    {
        SmsMessage::create([
            'audience' => $audience,
            'school_class_id' => $request->input('school_class_id'),
            'target_type' => $request->input('target_type'),
            'target_id' => collect($request->input('target_ids', []))->filter()->first(),
            'audience_label' => $result['label'] ?? ucfirst($audience),
            'message' => $message,
            'recipient_count' => (int) ($result['recipient_count'] ?? 0),
            'sent_count' => (int) ($result['sent'] ?? 0),
            'skipped_count' => (int) ($result['skipped'] ?? 0),
            'status' => $status,
            'response' => $result['response'] ?? null,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * @return array{today: int, month: int, campaigns: int}
     */
    private function stats(): array
    {
        return [
            'today' => (int) SmsMessage::query()
                ->where('status', 'sent')
                ->where('created_at', '>=', now()->copy()->startOfDay())
                ->sum('sent_count'),
            'month' => (int) SmsMessage::query()
                ->where('status', 'sent')
                ->where('created_at', '>=', now()->copy()->startOfMonth())
                ->sum('sent_count'),
            'campaigns' => (int) SmsMessage::query()->where('status', 'sent')->count(),
        ];
    }
}
