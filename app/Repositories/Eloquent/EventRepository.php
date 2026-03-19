<?php

namespace App\Repositories\Eloquent;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EventRepository implements EventRepositoryInterface
{
    public function findByUser(int $userId): Collection
    {
        return Event::forUser($userId)->orderBy('start_date')->get();
    }

    public function findByUserAndDateRange(int $userId, string $startDate, string $endDate): Collection
    {
        return Event::forUser($userId)
            ->betweenDates($startDate, $endDate)
            ->orderBy('start_date')
            ->get();
    }

    public function findByUserAndDay(int $userId, string $date): Collection
    {
        $startOfDay = $date . ' 00:00:00';
        $endOfDay = $date . ' 23:59:59';
        
        return Event::forUser($userId)
            ->where(function ($query) use ($startOfDay, $endOfDay) {
                $query->whereBetween('start_date', [$startOfDay, $endOfDay])
                      ->orWhereBetween('end_date', [$startOfDay, $endOfDay])
                      ->orWhere(function ($q) use ($startOfDay, $endOfDay) {
                          $q->where('start_date', '<=', $startOfDay)
                            ->where('end_date', '>=', $endOfDay);
                      });
            })
            ->orderBy('start_date')
            ->get();
    }

    public function create(array $data): Event
    {
        return Event::create($data);
    }

    public function update(Event $event, array $data): Event
    {
        $event->update($data);
        return $event;
    }

    public function delete(Event $event): bool
    {
        return $event->delete();
    }
}