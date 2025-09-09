@extends('dashboards.super_admin.super_admin') 

@section('content')


    @livewire('technical-resolve')


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let chatContainer = document.getElementById("chat-container");
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    </script>

@endsection