<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <title>The NEWs</title>
</head>
<body>
    <h1 class="news-title">{{ $new['title'] }}</h1>
    <h3 class="news-content">{{ $new['content'] }}</h3>
</body>
</html>