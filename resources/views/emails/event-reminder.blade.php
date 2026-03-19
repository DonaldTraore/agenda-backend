<!DOCTYPE html>
<html>
<head>
    <title>Rappel d'événement</title>
</head>
<body>
    <h2>Rappel: {{ $event->title }}</h2>
    <p><strong>Date:</strong> {{ $event->start_date->format('d/m/Y H:i') }}</p>
    @if($event->location)
        <p><strong>Lieu:</strong> {{ $event->location }}</p>
    @endif
    @if($event->description)
        <p><strong>Description:</strong> {{ $event->description }}</p>
    @endif
</body>
</html>