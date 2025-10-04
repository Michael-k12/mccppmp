<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Your account</title>
</head>
<body style="font-family: Arial, sans-serif; color:#111;">
  <div style="max-width:600px;margin:0 auto;padding:20px;">
    <h2 style="color:#1f2937">Welcome to Project Procurement Management System</h2>
    <p>Hi {{ $user->name }},</p>

    <p>The administrator has created an account for you.</p>

    <table style="width:100%;margin:16px 0;padding:0;">
      <tr><td style="font-weight:600">Email:</td><td>{{ $user->email }}</td></tr>
      <tr><td style="font-weight:600">Password:</td><td>{{ $plainPassword }}</td></tr>
    </table>

    <p>
      <a href="{{ url('/') }}" style="display:inline-block;background:#2563eb;color:#fff;padding:10px 16px;text-decoration:none;border-radius:6px;">
        Login to the system
      </a>
    </p>

    <p style="color:#6b7280;font-size:13px">Please change your password after logging in.</p>
    <hr>
    <p style="font-size:12px;color:#8892a6">This message was sent by Project Procurement Management System.</p>
  </div>
</body>
</html>
