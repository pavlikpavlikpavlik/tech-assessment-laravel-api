<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <style>
         .copy-box {
             margin: 50px;
             padding: 10px;
             border: 1px solid #ccc;
             background-color: #f9f9f9;
             display: flex;
             justify-content: space-between;
             align-items: center;
             border-radius: 5px;
         }

        .copy-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        .copy-button:hover {
            background-color: #45a049;
        }
        .header-message {
            margin: 50px;
        }
    </style>
</head>
<body>
    <h2 class="header-message">Copy the token to successfully complete the request</h2>
    <div class="copy-box">
        <span id="token">{{ $token }}</span>
        <button class="copy-button" onclick="copyToClipboard()">Copy</button>
    </div>

    <script>
        function copyToClipboard() {
            var tokenText = document.getElementById("token").textContent;
            navigator.clipboard.writeText(tokenText).then(function() {
                alert("Token copied to clipboard!");
            }).catch(function(error) {
                console.error("Error copying text: ", error);
                alert("Failed to copy token.");
            });
        }
    </script>
</body>
