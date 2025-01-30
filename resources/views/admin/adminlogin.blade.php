<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="icon" href="{{ asset('images/orempcologo.png') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('style/styles.css') }}">
</head>
<body>
    <div class="login-container">
        <img src="{{ asset('images/orempcologo.png') }}" alt="OREMPCO Logo">
        <h3>OREMPCO: WATER STATION</h3>
        <h1>ADMIN MODULE</h1>
        
        <!-- Error or Success Messages -->
        @if(Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif
        @if(Session::has('fail'))
            <div class="alert alert-danger">{{ Session::get('fail') }}</div>
        @endif

        <form action="{{ route('admin.authenticate') }}" method="POST">
            @csrf
            <!-- Username Field -->
            <input type="text" id="username" name="username" placeholder="Enter Username" value="{{ old('username') }}" required>
            <span class="text-danger">@error('username') {{ $message }} @enderror</span>
            
            <!-- Password Field -->
            <input type="password" id="password" name="password" placeholder="Enter Password" required>
            <span class="text-danger">@error('password') {{ $message }} @enderror</span>
            
            <!-- Login Button -->
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
