<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Open = 'open';
    case Voting = 'voting';
    case Closed = 'closed';
}
