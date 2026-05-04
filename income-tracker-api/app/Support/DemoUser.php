<?php

namespace App\Support;

class DemoUser
{
    public static function id(): int
    {
        $raw = env('DEMO_USER_ID', 1);
        return (int) $raw;
    }
}

