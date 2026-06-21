<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reply to Your Inquiry</title>
    <style>
        @media only screen and (max-width: 600px) {
            .email-container {
                padding: 15px !important;
            }
            .email-content {
                padding: 25px !important;
            }
            .logo-container {
                width: 55px !important;
                height: 55px !important;
            }
            .logo-icon {
                font-size: 22px !important;
            }
            .header-title {
                font-size: 22px !important;
            }
            .reply-box {
                padding: 20px !important;
            }
            .signature-text {
                font-size: 16px !important;
            }
            .original-message {
                padding: 15px !important;
            }
        }
    </style>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; background: linear-gradient(135deg, #fce7f3 0%, #fbcfe8 100%);">
    <div class="email-content" style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px rgba(236, 72, 153, 0.15);">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px;">
            <table role="presentation" align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto 15px;">
                <tr>
                    <td class="logo-container" style="width: 70px; height: 70px; background: linear-gradient(135deg, #ec4899 0%, #f472b6 100%); border-radius: 50%; text-align: center; vertical-align: middle; line-height: 70px;">
                        <span class="logo-icon" style="color: white; font-size: 32px; display: inline-block; vertical-align: middle;">✉</span>
                    </td>
                </tr>
            </table>
            <h1 class="header-title" style="color: #ec4899; margin: 0; font-size: 28px; font-weight: 700;">Hello {{ $inquiry->name }}!</h1>
            <p style="color: #666; margin: 5px 0 0; font-size: 14px;">Thank you for reaching out</p>
        </div>

        <!-- Reply Message -->
        <div style="background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%); padding: 25px; border-radius: 15px; border: 2px solid #fbcfe8; margin: 25px 0;">
            <p style="margin: 0 0 10px 0; color: #ec4899; font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: 1px;">My Response</p>
            <p style="margin: 0; white-space: pre-wrap; color: #374151; font-size: 15px; line-height: 1.8;">{{ $replyMessage }}</p>
        </div>

        <!-- Signature -->
        <div style="text-align: center; margin-top: 30px; padding-top: 25px; border-top: 2px dashed #fbcfe8;">
            <p style="margin: 0; color: #ec4899; font-weight: 600; font-size: 16px;">Best regards,</p>
            <p style="margin: 5px 0 0; color: #374151; font-weight: 700; font-size: 18px;">{{ config('app.name') }}</p>
        </div>

        <!-- Original Message -->
        <div style="margin-top: 35px; padding: 20px; background: #f9fafb; border-radius: 12px; border-left: 4px solid #e5e7eb;">
            <p style="margin: 0 0 15px 0; color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">Original Message</p>
            <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 10px;">
                <p style="margin: 0 0 8px 0; color: #374151; font-size: 13px; font-weight: 600;"><strong>Subject:</strong> {{ $inquiry->subject ?? 'Website Inquiry' }}</p>
                <p style="margin: 0; white-space: pre-wrap; color: #6b7280; font-size: 14px; line-height: 1.6;">{{ $inquiry->message }}</p>
            </div>
        </div>

        <!-- Footer -->
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #fbcfe8;">
            <p style="margin: 0; color: #9ca3af; font-size: 12px;">This email was sent from {{ config('app.name') }}</p>
        </div>
    </div>
</body>
</html>
