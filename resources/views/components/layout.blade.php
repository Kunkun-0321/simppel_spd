<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'SIMPPEL SPD' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    
    <link rel="stylesheet" href="{{ asset('/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('/css/style.css') }}">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <x-header></x-header>
    <x-sidebar></x-sidebar>

    <div class="p-4 sm:ml-64">
        <div class="p-4 mt-14">
            {{ $slot }} </div>
    </div>

    <x-faq-button></x-faq-button>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
</body>

</html>