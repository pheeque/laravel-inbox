<?php

namespace Liliom\Inbox\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Liliom\Inbox\Models\Participant;
use Liliom\Inbox\Models\Thread;

class InboxController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $threads = auth()->user();
        if (request()->has('sent')) {
            $threads = $threads->sent();
        } else {
            $threads = $threads->received();
        }

        $threads = $threads->paginate(config('inbox.paginate', 10));


        return view('inbox::index', compact('threads'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required',
            'body' => 'required',
            'recipients' => 'required|array',
        ]);

        $thread = auth()->user()
                        ->subject($request->subject)
                        ->writes($request->body)
                        ->to($request->recipients)
                        ->send();

        return redirect()
            ->route('inbox.index')
            ->with('message', [
                'type' => $thread ? 'success' : 'error',
                'text' => $thread ? trans('inbox::messages.thread.sent') : trans('inbox::messages.thread.whoops'),
            ]);
    }

    /**
     * Display the specified resource.
     *
     * @param Thread $thread
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Thread $thread)
    {
        $messages = $thread->messages()->get(); // ->withTrashed()

        $seen = $thread->participants()
                       ->where('user_id', auth()->id())
                       ->first();

        if ($seen && $seen->pivot) {
            $seen->pivot->seen_at = Carbon::now();
            $seen->pivot->save();
        } else {
            return abort(404);
        }

        return view('inbox::show', compact('messages', 'thread'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param Thread                    $thread
     *
     * @return \Illuminate\Http\Response
     */
    public function reply(Request $request, Thread $thread)
    {
        $request->validate([
            'body' => 'required',
        ]);

        $message = auth()->user()
                         ->writes($request->body)
                         ->reply($thread);

        return redirect()
            ->route('inbox.show', $thread)
            ->with('message', [
                'type' => $message ? 'success' : 'error',
                'text' => $message ? trans('inbox::messages.message.sent') : trans('inbox::messages.message.whoops'),
            ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Thread $thread
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Thread $thread)
    {
        $message = Participant::where('user_id', auth()->id())
                              ->where('thread_id', $thread->id)
                              ->firstOrFail();

        $deleted = $message->delete();

        return redirect()
            ->route('inbox.index')
            ->with('message', [
                'type' => $deleted ? 'success' : 'error',
                'text' => $deleted ? trans('inbox::messages.thread.deleted') : trans('inbox::messages.thread.whoops'),
            ]);
    }
}
