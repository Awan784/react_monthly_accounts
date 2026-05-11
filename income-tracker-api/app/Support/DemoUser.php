<?php

namespace App\Support;

class DemoUser
{
    public static function id(): int
    {
        $authId = auth()->id();

        if ($authId !== null) {
            return (int) $authId;
        }

        return (int) env('DEMO_USER_ID', 1);
    }
}

