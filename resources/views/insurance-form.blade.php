<!DOCTYPE html>
<html>

<head>
    <title>Insurance Check</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        input,
        button {
            padding: 8px;
            margin: 5px;
        }

        .result {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 10px;
        }
    </style>
</head>

<body>
    <h1>Check Insurance Details</h1>

    <form method="POST" action="/insurance/consent">
        @csrf
        <label>Insurer Name:</label>
        <input type="text" name="insurerName" required><br>

        <label>Insurer Username:</label>
        <input type="text" name="insurerUsername" required><br>

        <label>Insurer Password:</label>
        <input type="password" name="insurerPassword" required><br>

        <button type="submit">Submit & Connect</button>
    </form>

    @if (session('pull_id'))
        <div class="result">
            <h2>Pull Created</h2>
            <p>Pull ID: {{ session('pull_id') }}</p>
            <a href="/insurance/{{ session('pull_id') }}">Get Insurance Details</a>
        </div>
    @endif
</body>

</html>
