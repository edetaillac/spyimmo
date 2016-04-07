<?php

namespace SpyimmoBundle\Notifier;

use Pushbullet\Pushbullet;

class PushbulletNotifier
{
    /** @var Pushbullet */
    protected $pushbullet;

    public function __construct($pushbulletApiKey)
    {
        $this->pushbullet = new Pushbullet($pushbulletApiKey);
    }


    public function notify($title, $url, $body)
    {
        $this->pushbullet->allDevices()->pushLink($title, $url, $body);
    }


}