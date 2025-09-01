<?php

namespace App\Enums;

interface AttendanceStatus
{
    const PRESENT   = 'present';
    const ABSENT    = 'absent';
    const LATE      = 'late';
    const HALF_DAY  = 'half_day';
    const LEAVE     = 'leave';
}
