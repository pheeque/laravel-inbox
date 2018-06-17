<?php

namespace Liliom\Inbox\Traits;

use Carbon\Carbon;
use Liliom\Inbox\Models\Participant;
use Liliom\Inbox\Models\Thread;

trait HasInbox
{
    protected $subject, $message;
    protected $recipients = [];
    protected $threadsTable, $messagesTable, $participantsTable;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array $attributes
     *
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->threadsTable = config('inbox.tables.threads');
        $this->messagesTable = config('inbox.tables.messages');
        $this->participantsTable = config('inbox.tables.participants');

        parent::__construct($attributes);
    }

    public function subject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function writes($message)
    {
        $this->message = $message;

        return $this;
    }

    public function to($users)
    {
        if (is_array($users)) {
            $this->recipients = array_merge($this->recipients, $users);
        } else {
            $this->recipients[] = $users;
        }

        return $this;
    }


    /**
     * Send new message
     *
     * @return mixed
     */
    public function send()
    {
        $thread = $this->threads()->create([
            'subject' => $this->subject,
        ]);

        // Message
        $thread->messages()->create([
            'user_id' => $this->id,
            'body' => $this->message
        ]);

        // Sender
        Participant::create([
            'user_id' => $this->id,
            'thread_id' => $thread->id,
            'seen_at' => Carbon::now()
        ]);

        if (count($this->recipients)) {
            $thread->addParticipants($this->recipients);
        }

        return $thread;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Thread $thread
     *
     * @return \Illuminate\Http\Response
     */
    public function reply($thread)
    {
        if ( ! is_object($thread)) {
            $thread = Thread::whereId($thread)->firstOrFail();
        }

        $thread->activateAllParticipants();

        $message = $thread->messages()->create([
            'user_id' => $this->id,
            'body' => $this->message
        ]);

        // todo: "$thread->participants()->firstOrCreate"
        // Add replier as a participant
        $participant = Participant::firstOrCreate([
            'thread_id' => $thread->id,
            'user_id' => $this->id
        ]);

        $participant->seen_at = Carbon::now();
        $participant->save();

        $thread->updated_at = Carbon::now();
        $thread->save();

        $participants = $thread->participants()->where('user_id', '!=', $this->id)->get();
        if ($participants->count()) {
            foreach ($participants as $participant) {
//                $participant->user->notify(new NewMessage($thread, $message, $participant));
            }
        }

        return $message;
    }

    /**
     * Get user threads
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function participated()
    {
        return $this->belongsToMany(Thread::class, 'participants', 'user_id', 'thread_id')
                    ->withPivot('seen_at')
                    ->withTimestamps();
    }

    /**
     *
     */
    public function _received()
    {
        return Thread::join($this->participantsTable, "{$this->threadsTable}.id", '=',
            "{$this->participantsTable}.thread_id")
                     ->where("{$this->participantsTable}.user_id", $this->id)
                     ->where("{$this->threadsTable}.user_id", '!=', $this->id)
                     ->whereNull("{$this->participantsTable}.deleted_at")
                     ->orderBy("{$this->threadsTable}.updated_at", 'desc')
                     ->select("{$this->threadsTable}.*");
    }

    /**
     * Get the threads that has been send to the user.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function received()
    {
        // todo: get only the received messages if they got an answer
        return $this->participated()
//                    ->has('messages', '>=', 2)
                    ->latest('updated_at');
    }

    /**
     * Get the threads that has been sent by user.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function sent()
    {
        return $this->threads()->latest('updated_at');
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function unread()
    {
        return $this->received()->whereNull('seen_at');
    }
}