<?php

namespace Liliom\Inbox\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Liliom\Inbox\Models\Message;
use Liliom\Inbox\Models\Thread;

class MessageDispatched extends Notification
{
    public $thread, $message, $participant;

    /**
     * Create a new notification instance.
     *
     * @param Thread  $thread
     * @param Message $message
     * @param         $participant
     */
    public function __construct(Thread $thread, Message $message, $participant)
    {
        $this->thread = $thread;
        $this->message = $message;
        $this->participant = $participant;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed $notifiable
     *
     * @return array
     */
    public function via($notifiable)
    {
        return [
            'mail',
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     * @throws \Throwable
     */
    public function toMail($notifiable)
    {
        $buttonUrl = route(config('inbox.route.name') . 'inbox.show', $this->thread);
        $isReply = $this->thread->messages()->count() >= 2;
        $greeting = $isReply ? 'Re: ' . $this->thread->subject : $this->thread->subject;

        return (new MailMessage)
            ->success()
            ->subject($this->message->user->name . ' ' . trans('inbox::messages.notification.subject') . ' - ' . config('app.name'))
            ->greeting($greeting)
            ->line($this->message->body)
            ->action(trans('inbox::messages.notification.button'), $buttonUrl);
    }
}
