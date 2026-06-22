<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
    xmlns:o="urn:schemas-microsoft-com:office:office" lang="en">

<head>
    <!--[if gte mso 9]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"><!--<![endif]-->
    <title>{{$title}} | {{config('app.name')}}</title>

    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            background-color: #f5f7fa;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
            width: 100%;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
            -ms-interpolation-mode: bicubic;
            max-width: 100%;
            height: auto;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .email-wrapper {
            width: 100%;
            background-color: #f5f7fa;
            padding: 20px 0;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px 30px;
            text-align: center;
        }

        .logo-container {
            margin-bottom: 0;
        }

        .logo {
            max-width: 180px;
            height: auto;
        }

        .content-card {
            padding: 50px 40px;
            background-color: #ffffff;
        }

        .greeting {
            font-size: 24px;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .message-content {
            font-size: 16px;
            line-height: 1.8;
            color: #4a5568;
            margin-bottom: 30px;
        }

        .message-content p {
            margin-bottom: 15px;
        }

        .cta-button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin: 10px 0;
            transition: all 0.3s ease;
        }

        .footer {
            background-color: #1a202c;
            padding: 40px 30px;
            color: #a0aec0;
        }

        .footer-content {
            text-align: center;
        }

        .footer-logo {
            margin-bottom: 20px;
        }

        .social-links {
            margin: 25px 0;
            text-align: center;
        }

        .social-icon {
            display: inline-block;
            width: 40px;
            height: 40px;
            margin: 0 8px;
            border-radius: 50%;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 8px;
            transition: background-color 0.3s ease;
        }

        .social-icon:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .footer-text {
            font-size: 14px;
            line-height: 1.6;
            color: #a0aec0;
            margin-top: 20px;
        }

        .footer-links {
            margin: 20px 0;
        }

        .footer-link {
            color: #a0aec0;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }

        .copyright {
            font-size: 13px;
            color: #718096;
            margin-top: 20px;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 30px 0;
        }

        @media only screen and (max-width: 620px) {
            .email-container {
                border-radius: 0;
                margin: 0;
            }

            .header {
                padding: 15px 20px;
            }

            .content-card {
                padding: 30px 20px;
            }

            .greeting {
                font-size: 20px;
            }

            .message-content {
                font-size: 15px;
            }

            .footer {
                padding: 30px 20px;
            }

            .social-icon {
                width: 36px;
                height: 36px;
                margin: 0 6px;
            }
        }

        @media (prefers-color-scheme: dark) {
            .email-wrapper {
                background-color: #1a202c;
            }
        }
    </style>
    <!--[if !mso]><!-->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!--<![endif]-->

</head>

<body>
    <!--[if IE]><div class="ie-container"><![endif]-->
    <!--[if mso]><div class="mso-container"><![endif]-->
    
    <div class="email-wrapper">
        <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
            <tr>
                <td align="center">
                    
                    <!-- Email Container -->
                    <div class="email-container">
                        
                        <!-- Header with Gradient -->
                        <div class="header">
                            <div class="logo-container">
                                <img src="{{asset('img/logo.png')}}" alt="{{config('app.name')}}" class="logo" />
                            </div>
                        </div>

                        <!-- Main Content Card -->
                        <div class="content-card">
                            <h1 class="greeting">Hello {{$name}}! 👋</h1>
                            
                            <div class="message-content">
                                {!! $msg !!}
                            </div>

                            {{-- Uncomment if you need a CTA button
                            <a href="#" class="cta-button">Take Action</a>
                            --}}
                        </div>

                        <!-- Footer -->
                        <div class="footer">
                            <div class="footer-content">
                                
                                <!-- Footer Logo -->
                                <div class="footer-logo">
                                    <p style="font-size: 18px; font-weight: 600; color: #ffffff; margin-bottom: 5px;">
                                        {{config('app.name')}}
                                    </p>
                                </div>

                                <div class="divider"></div>

                                <!-- Contact Info -->
                                <div class="footer-text">
                                    <p><strong>Contact Us</strong></p>
                                    <p>{{config('app.email')}}</p>
                                </div>

                                <!-- Social Links -->
                                <div class="social-links">
                                    <a href="#" title="Facebook" class="social-icon">
                                        <img src="{{asset('images/image-3.png')}}" alt="Facebook" width="24" height="24" />
                                    </a>
                                    <a href="#" title="Twitter" class="social-icon">
                                        <img src="{{asset('images/image-6.png')}}" alt="Twitter" width="24" height="24" />
                                    </a>
                                    <a href="#" title="Instagram" class="social-icon">
                                        <img src="{{asset('images/image-5.png')}}" alt="Instagram" width="24" height="24" />
                                    </a>
                                    <a href="#" title="LinkedIn" class="social-icon">
                                        <img src="{{asset('images/image-4.png')}}" alt="LinkedIn" width="24" height="24" />
                                    </a>
                                </div>

                                <!-- Footer Links -->
                                <div class="footer-links">
                                    <a href="#" class="footer-link">Privacy Policy</a>
                                    <span style="color: #4a5568;">•</span>
                                    <a href="#" class="footer-link">Terms of Service</a>
                                    <span style="color: #4a5568;">•</span>
                                    <a href="#" class="footer-link">Unsubscribe</a>
                                </div>

                                <!-- Copyright -->
                                <div class="copyright">
                                    <p>&copy; {{ date('Y') }} {{config('app.name')}}. All Rights Reserved.</p>
                                </div>

                            </div>
                        </div>

                    </div>
                    <!-- End Email Container -->

                </td>
            </tr>
        </table>
    </div>

    <!--[if mso]></div><![endif]-->
    <!--[if IE]></div><![endif]-->
</body>

</html>
