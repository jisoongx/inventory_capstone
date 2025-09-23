@extends('dashboards.owner.owner') 
<head>
    <title>Technical Request</title>
</head>
@section('content')


    @livewire('technical-request')

    <!-- <script>
        document.addEventListener("DOMContentLoaded", function() {
            let chatContainer = document.getElementById("chat-container");
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    </script> -->

@endsection