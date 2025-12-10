@extends('dashboards.staff.staff') 

@section('content')

    <div class="px-4 space-y-4">
        @livewire('expiration-container')
        
        @livewire('staff-dashboard')
    </div>

@endsection