@extends('layouts.admin')

@section('content')
<div id="admin-app">
    <admin-classes></admin-classes>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/admin.js'])
@endpush