<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class EventController
{
    use AuthorizesRequests;

    public function __construct(
        private EventRepositoryInterface $eventRepository
    ) {}
    public function index(Request $request)
    {
        try {
            if ($request->has(['start_date', 'end_date'])) {
                $events = $this->eventRepository->findByUserAndDateRange(
                    auth()->id(),
                    $request->start_date,
                    $request->end_date
                );
            } else {
                $events = $this->eventRepository->findByUser(auth()->id());
            }

            return EventResource::collection($events);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve events'], 500);
        }
    }

    public function store(EventRequest $request): JsonResource|JsonResponse
    {
        try {
            $event = $this->eventRepository->create([
                ...$request->validated(),
                'user_id' => auth()->id(),
            ]);

            return new EventResource($event);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create event'], 500);
        }
    }

    public function show(Event $event)
    {
        try {
            $this->authorize('view', $event);
            return new EventResource($event);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['error' => 'Unauthorized'], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve event'], 500);
        }
    }

    public function update(EventRequest $request, Event $event): JsonResource|JsonResponse
    {
        try {
            $this->authorize('update', $event);
            $updatedEvent = $this->eventRepository->update($event, $request->validated());
            return new EventResource($updatedEvent);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['error' => 'Unauthorized'], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update event'], 500);
        }
    }

    public function destroy(Event $event): JsonResponse
    {
        try {
            $this->authorize('delete', $event);
            $this->eventRepository->delete($event);
            return response()->json(['message' => 'Event deleted successfully']);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json(['error' => 'Unauthorized'], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete event'], 500);
        }
    }

    public function getEventsByDay(string $date)
    {
        try {
            $events = $this->eventRepository->findByUserAndDay(auth()->id(), $date);
            return EventResource::collection($events);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve events for day'], 500);
        }
    }
}