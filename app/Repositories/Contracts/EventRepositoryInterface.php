<?php

namespace App\Repositories\Contracts;

use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;

interface EventRepositoryInterface
{
    public function findByUser(int $userId): Collection;
    public function findByUserAndDateRange(int $userId, string $startDate, string $endDate): Collection;
    public function findByUserAndDay(int $userId, string $date): Collection;
    public function create(array $data): Event;
    public function update(Event $event, array $data): Event;
    public function delete(Event $event): bool;
}