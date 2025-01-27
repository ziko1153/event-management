<?php

namespace App\Controllers;

class AuthController
{

    public function  showLogin($params)
    {
        echo "<pre>";
        var_dump($params);
        echo "<pre>";
        echo "HELLO LOGIN";
    }
}