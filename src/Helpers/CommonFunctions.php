<?php
use Config\Env;

Env::load(__DIR__.'/../../.env');

if (!function_exists('env')) {
    function env($key, $default = null) {
        return Env::get($key, $default);
    }
}