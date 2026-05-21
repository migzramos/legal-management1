<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $conversations = Message::with(['sender:id,name,role','receiver:id,name,role'])
            ->where('sender_id', auth()->id())
            ->orWhere('receiver_id', auth()->id())
            ->latest()
            ->get()
            ->groupBy(function($m) {
                $otherId = $m->sender_id === auth()->id() ? $m->receiver_id : $m->sender_id;
                return $otherId;
            });

        $users = User::where('id','!=',auth()->id())
            ->where('is_active',true)
            ->get();

        $activeUserId = $request->get('user');
        $activeUser = $activeUserId ? User::find($activeUserId) : null;
        $messages = collect();

        if ($activeUser) {
            $messages = Message::where(function($q) use ($activeUserId) {
                $q->where('sender_id', auth()->id())->where('receiver_id', $activeUserId);
            })->orWhere(function($q) use ($activeUserId) {
                $q->where('sender_id', $activeUserId)->where('receiver_id', auth()->id());
            })->oldest()->get();

            Message::where('sender_id',$activeUserId)
                ->where('receiver_id',auth()->id())
                ->where('is_read',false)
                ->update(['is_read'=>true,'read_at'=>now()]);
        }

        return view('admin.messages', compact('conversations','users','activeUser','messages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'body'        => 'required|string|max:5000',
        ]);

        Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'body'        => $request->body,
        ]);

        return redirect()->route('admin.messages', ['user' => $request->receiver_id]);
    }
}