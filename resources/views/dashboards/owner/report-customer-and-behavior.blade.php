@extends('dashboards.owner.owner')

@section('content')

    <div class="">
        <div class="px-4 pb-4">
            @livewire('expiration-container')
        </div>

        @livewire('report-customer')

    </div>


@endsection