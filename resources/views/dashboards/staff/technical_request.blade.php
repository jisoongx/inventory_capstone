@extends('dashboards.staff.staff') 

@section('content')


    @livewire('technical-request')


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let chatContainer = document.getElementById("chat-container");
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    </script>

@endsection