@php
    $title = get_label('chat','Chat');
    if (isset($type)) {
        if ($type == 'project') {
            $title = get_label('project_discussion','Project Discussion');
        } elseif ($type == 'task') {
            $title = get_label('task_discussion','Task Discussion');
        }
    }
@endphp
<title>{{ $title }} - {{ $general_settings['company_title'] }}</title>
<link rel="icon" type="image/x-icon" href="{{ asset($general_settings['favicon']) }}" />
{{-- Meta tags --}}
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="id" content="{{ Auth::user()->id }}">
<meta name="messenger-color" content="{{ $messengerColor }}">
<meta name="messenger-theme" content="{{ $dark_mode }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="url" content="{{ url('').'/'.config('chatify.routes.prefix') }}" data-user="{{ Auth::user()->id }}">
{{-- scripts --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('js/chatify/font.awesome.min.js') }}"></script>
<script src="{{ asset('js/chatify/autosize.js') }}"></script>
<!-- <script type="module" src="{{ asset('js/app.js') }}"></script> -->
<script src='https://unpkg.com/nprogress@0.2.0/nprogress.js'></script>
{{-- styles --}}
<link rel='stylesheet' href='https://unpkg.com/nprogress@0.2.0/nprogress.css' />
<link href="{{ asset('css/chatify/style.css') }}" rel="stylesheet" />
<link href="{{ asset('css/chatify/'.$dark_mode.'.mode.css') }}" rel="stylesheet" />
<!-- <link href="{{ asset('css/app.css') }}" rel="stylesheet" /> -->
{{-- Setting messenger primary color to css --}}
<style>
    :root {
        --primary-color: {{$messengerColor}};
    }
</style>