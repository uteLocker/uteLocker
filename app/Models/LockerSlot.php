<?php

namespace App\Models;

use App\Enums\LockerSlotType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LockerSlot extends Model
{
    public function locker(): BelongsTo
    {
        return $this->belongsTo(Locker::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public static function getCode($lockerId, $lockerSlotId)
    {
        $lockerSlots = self::where('locker_id', $lockerId)->get();
        $code = 0;
        foreach ($lockerSlots as $slot) {
            if ($slot->type == LockerSlotType::SLOT) {
                $code++;
            }
            if ($slot->id == $lockerSlotId) {
                break;
            }
        }
        return $code;
    }

    public static function caculatePriceBooking($lockerSlotId, $startTime, $endTime)
    {
        $lockerSlot = self::whereIn('id', $lockerSlotId)
            ->orWhere('id', function ($query) use ($lockerSlotId) {
                $query->select('id')
                    ->from('locker_slots')
                    ->where('type', LockerSlotType::CPU)
                    ->where('locker_id', $lockerSlotId[0]);
            })
            ->select('id', 'config', 'type')
            ->get();
        $configLocker = $lockerSlot->where('type', LockerSlotType::CPU)->first()->config ?? '{}';
        $configLocker = json_decode($configLocker, true);

        $totalHours = (strtotime($endTime) - strtotime($startTime)) / 3600;
        $price = 0;
        foreach ($lockerSlot as $slot) {
            if ($slot->type == LockerSlotType::SLOT) {
                $configSlot = json_decode($slot->config, true);
                $priceOfHour = $configSlot['price'] ?? $configLocker['price'] ?? 10000;
                $price += $priceOfHour * $totalHours;
            }
        }
        return $price;
    }
}
