@extends('layouts.main')

@section('title', 'Homework & Tasks')

@section('content')
<div id="app">
    <task-manager 
        :initial-tasks='@json($tasks)'
        initial-filter="{{ $filter ?? 'all' }}"
    ></task-manager>
</div>
@endsection