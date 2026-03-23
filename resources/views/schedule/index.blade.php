@extends('layouts.main')

@section('title', 'Class Schedule')

@section('content')
<div id="app">
    <class-schedule 
        :initial-classes='@json($classes)'
        :initial-unique-names='@json($uniqueClassNames)'
    ></class-schedule>
</div>
@endsection