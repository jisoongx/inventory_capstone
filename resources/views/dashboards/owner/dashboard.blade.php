@extends('dashboards.owner.owner') 
<head>
    <title>Dashboard</title>
</head>
@section('content')

    <div class="px-4 space-y-4">
        @livewire('expiration-container')
        
        @livewire('dashboard-graphs')

        <!-- tables dapit -->
        <div class="space-y-4 w-full">
            @livewire('stock-alert')
            @livewire('comparative-analysis')            
        </div>
    </div>

@endsection