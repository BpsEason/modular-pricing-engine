<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel Modular Pricing Engine</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <style>
            body {
                font-family: 'Figtree', sans-serif;
                background-color: #f7fafc;
                color: #2d3748;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0;
            }
            .container {
                text-align: center;
                background-color: #ffffff;
                padding: 3rem;
                border-radius: 0.5rem;
                box-shadow: 0 4px 6px rgba(0, 0,0, 0.1);
            }
            h1 {
                font-size: 2.5rem;
                color: #4a5568;
                margin-bottom: 1rem;
            }
            p {
                font-size: 1.25rem;
                color: #718096;
            }
            .links a {
                color: #6366f1;
                padding: 0 1rem;
                font-size: 0.875rem;
                font-weight: 600;
                text-decoration: none;
                text-transform: uppercase;
            }
            .links a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="container">
            <h1>Laravel Modular Pricing Engine</h1>
            <p>Your pricing logic is ready!</p>
            <div class="links">
                <a href="https://laravel.com/docs">Docs</a>
                <a href="https://laracasts.com">Laracasts</a>
                <a href="https://laravel-news.com">News</a>
                <a href="https://blog.laravel.com">Blog</a>
                <a href="https://github.com/laravel/laravel">GitHub</a>
            </div>
            <p style="margin-top: 2rem; font-size: 0.9rem;">
                Try testing the API: POST /api/calculate-order-price
            </p>
        </div>
    </body>
</html>
