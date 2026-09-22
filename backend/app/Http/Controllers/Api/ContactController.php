<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    /**
     * Submit a contact/inquiry message from the frontend.
     */
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'subject_type' => ['nullable', 'string', 'in:general,candidate,employer,tech'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:5', 'max:5000'],
        ]);

        $authenticatedUser = $request->user('sanctum');

        $message = ContactMessage::create([
            'user_id' => $authenticatedUser?->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'subject_type' => $validated['subject_type'] ?? 'general',
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'is_read' => false,
            'ip_address' => $request->ip(),
        ]);

        // Trigger real database notification for all admin users
        $admins = User::query()->where('role', 'admin')->get();
        $inquiryLabel = match ($message->subject_type) {
            'candidate' => 'Candidate Support',
            'employer' => 'Employer / Hiring Inquiry',
            'tech' => 'Technical Support Issue',
            default => 'General Inquiry',
        };

        foreach ($admins as $admin) {
            $admin->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\ContactMessageReceived',
                'data' => [
                    'title' => "New Inquiry: {$message->subject}",
                    'message' => "Message from {$message->name} ({$message->email}): " . Str::limit($message->message, 120),
                    'body' => $message->message,
                    'sender_name' => $message->name,
                    'sender_email' => $message->email,
                    'sender_phone' => $message->phone,
                    'subject' => $message->subject,
                    'subject_type' => $message->subject_type,
                    'inquiry_label' => $inquiryLabel,
                    'category' => 'message',
                    'level' => 'info',
                    'contact_message_id' => $message->id,
                    'link' => '/admin-resources/messages',
                ],
                'read_at' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Your message has been sent successfully. Our team will get back to you soon!',
            'data' => $message,
        ], 201);
    }

    /**
     * Admin: List all contact messages.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ContactMessage::query()->with('user:id,name,email');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->input('subject_type'));
        }

        if ($request->has('is_read')) {
            $query->where('is_read', filter_var($request->input('is_read'), FILTER_VALIDATE_BOOLEAN));
        }

        $messages = $query->latest()->paginate($request->input('per_page', 20));

        return response()->json([
            'status' => 'success',
            'data' => $messages,
        ]);
    }

    /**
     * Admin: Show contact message and mark as read.
     */
    public function show(ContactMessage $contactMessage): JsonResponse
    {
        if (!$contactMessage->is_read) {
            $contactMessage->update(['is_read' => true]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $contactMessage->load('user:id,name,email'),
        ]);
    }

    /**
     * Admin: Delete contact message.
     */
    public function destroy(ContactMessage $contactMessage): JsonResponse
    {
        $contactMessage->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Message deleted successfully',
        ]);
    }
}