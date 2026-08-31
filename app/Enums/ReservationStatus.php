<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case RESERVED = 'reserved';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
