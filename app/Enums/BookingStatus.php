<?php

namespace App\Enums;

enum BookingStatus: string
{
    case CONFIRMED = 'CONFIRMED';
    case WAITING = 'WAITING';
    case CANCELLED = 'CANCELLED';

    public function isActive(): bool
    {
        return in_array($this, [
            self::CONFIRMED,
            self::WAITING,
        ], true);
    }
}
