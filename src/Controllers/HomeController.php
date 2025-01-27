<?php

namespace App\Controllers;

use App\Model\Event;

class HomeController
{

    public Event $eventModel;
    public  function __construct()
    {
        $this->eventModel = new Event;
    }
    public function home()
    {
        $events = $this->eventModel->findAll();
        return view('home', [
            'events' => $events
        ]);
    }
}
