<?php

class RedisMessage extends Event implements ShouldBroadcast
{
    use SerializesModels;
    public $user;
    public $message;
    private $recipientsIds;
    public function __construct($message, array $recipients)
    {
        $ids = [];
        foreach ($recipients as $recipient) {
            $ids[] = $recipient['user']['id'];
        }
        $this->message       = $message;
        $this->recipientsIds = $ids;
    }
    public function broadcastOn()
    {
        $events = [];
        foreach ($this->recipientsIds as $res) {
            $events[] = 'user.' . $res;
        }
        return $events;
    }
    public function broadcastWith()
    {
        return ['message' => $this->message];
        //this is not necessary. All public properties are automatically broadcast
    }
}
