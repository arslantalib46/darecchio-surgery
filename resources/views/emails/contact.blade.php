<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Verify OTP</title>
</head>
<body style="font-size: 2em; text-align: center">
    <h1>Verify OTP</h1>

    <p>Hi {{ $details['name'] }}, Your OTP is </p> 
    
    <h1 style="background-color: crimson; color: #fff; display: inline-block;">{{ $details['message'] }}</h1>
        
    <p>Please enter this code to verify your account.</p>

    <p>Thank you for using our service!</p>
</body>
</html>