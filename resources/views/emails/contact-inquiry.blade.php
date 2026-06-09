<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Inquiry</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f8fafc; padding: 24px; color: #0f172a;">
    <div style="max-width: 640px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 24px; border: 1px solid #e2e8f0;">
        <h2 style="margin: 0 0 12px;">New Contact Inquiry</h2>
        <p style="margin: 0 0 16px; color: #475569;">You have received a new inquiry from the contact form.</p>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
            <tr>
                <td style="padding: 8px 0; font-weight: 600; width: 140px;">Name</td>
                <td style="padding: 8px 0;">{{ $data['name'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Company</td>
                <td style="padding: 8px 0;">{{ $data['company'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Email</td>
                <td style="padding: 8px 0;">{{ $data['email'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Phone</td>
                <td style="padding: 8px 0;">{{ $data['phone'] ?? '-' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 600;">Subject</td>
                <td style="padding: 8px 0;">{{ $data['subject'] ?? '-' }}</td>
            </tr>
        </table>

        <div style="margin-top: 16px;">
            <p style="font-weight: 600; margin-bottom: 8px;">Message</p>
            <div style="background: #f1f5f9; padding: 12px 16px; border-radius: 12px; white-space: pre-wrap;">
                {{ $data['message'] ?? '-' }}
            </div>
        </div>
    </div>
</body>
</html>
