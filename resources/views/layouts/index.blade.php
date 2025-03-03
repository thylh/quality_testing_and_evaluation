<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    @include('layouts.header')
    <main>
        <h1>Content is here</h1>
        <img src="{{ asset('images/bear.jpg') }}" alt="Ảnh gấu">
        {{ $x=1 }}
        @if ($x%2==0)
            <p>x là số chẵn.</p>
        @elseif($x%2==1)
            <p>x là số lẻ.</p>
        @endif
        @unless(empty($name))
            <h3>Name is not empty.</h3>
        @endunless
        {{-- unless(empty(...)) == if(!empty(...)) --}}

        @switch($name)
            @case('Thin1')
                <h3>This is Thinh.</h3>
                @break
            @default
                <h3>No one selected!</h3>       
        @endswitch

        {{-- @for ($i = 0; $i <= 5; $i++)
            <h2>i = {{ $i }}</h2>
        @endfor --}}

        @foreach ($names as $eachName)
            <p>each name : {{ $eachName }}</p>
        @endforeach
    </main>
    @include('layouts.footer')
</body>
</html>