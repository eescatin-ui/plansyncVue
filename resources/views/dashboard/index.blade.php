@extends('layouts.main')

@section('title', 'Dashboard')

@section('content')
<div id="app">
    <dashboard 
        :initial-stats='@json($stats)'
        :initial-classes='@json($classesToday)'
        :initial-tasks='@json($tasksUpcoming)'
        :initial-notes='@json($notesRecent)'
    ></dashboard>
</div>
@endsection