<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    // Other existing methods...

    public static function getAll($event_id) {
        return self::where('event_id', $event_id)
                    ->groupBy('id')
                    ->orderByRaw("FIELD(day, 'Friday', 'Saturday', 'Sunday'), start_time")
                    ->get();
    }
}