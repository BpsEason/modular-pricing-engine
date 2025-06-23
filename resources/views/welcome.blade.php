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
                background-color: #f8fafc;
                color: #333;
                margin: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                text-align: center;
            }
            .container {
                padding: 2rem;
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            h1 {
                color: #4CAF50;
                margin-bottom: 1rem;
            }
            p {
                font-size: 1.1rem;
                line-height: 1.6;
            }
            a {
                color: #007bff;
                text-decoration: none;
            }
            a:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body class="antialiased">
        <div class="container">
            <h1>Welcome to Laravel Modular Pricing Engine!</h1>
            <p>This is a demonstration project showcasing **Strategy, Chain of Responsibility, and Decorator** design patterns within a Laravel application for complex order pricing.</p>
            <p>Explore the code on GitHub (after you upload it!) to see the implementation details.</p>
            <p>You can test the API by sending a POST request to `/api/calculate-order-price`.</p>
        </div>
    </body>
</html>
