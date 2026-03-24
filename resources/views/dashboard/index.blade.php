@extends('layouts.main')

@section('title', 'Dashboard')

@section('content')
<div id="app">
    <dashboard 
        :initial-stats='@json($stats)'
        :initial-classes='@json($classesToday)'
        :initial-tasks='@json($tasksUpcoming)'
        :initial-notes='@json($notesRecent)'
        :initial-reminders='@json($recentReminders)'
        user-name="{{ $userName }}"
    ></dashboard>
</div>
@endsection