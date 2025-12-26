<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>سایت در حال به‌روزرسانی - ایلی استور</title>
    <style>
        @font-face {
            font-family: 'Vazirmatn';
            src: url('/fonts/vazirmatn-arabic-400-normal.woff') format('woff');
            font-weight: 400;
        }
        @font-face {
            font-family: 'Vazirmatn';
            src: url('/fonts/vazirmatn-arabic-800-normal.woff') format('woff');
            font-weight: 800;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Vazirmatn', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #f0f7ff 0%, #e0f2fe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            direction: rtl;
        }
        .container {
            max-width: 600px;
            width: 100%;
            text-align: center;
            background: white;
            border-radius: 24px;
            padding: 48px 32px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.6s ease-out;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .icon {
            width: 120px;
            height: 120px;
            margin: 0 auto 32px;
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s ease-in-out infinite;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
        }
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        .icon svg {
            width: 64px;
            height: 64px;
            color: white;
        }
        h1 {
            font-size: 32px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 16px;
        }
        .subtitle {
            font-size: 18px;
            color: #64748b;
            margin-bottom: 32px;
            line-height: 1.6;
        }
        .message {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
            border-right: 4px solid #3b82f6;
        }
        .message p {
            color: #475569;
            font-size: 16px;
            line-height: 1.8;
        }
        .loader {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #e2e8f0;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 24px;
        }
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
        .footer {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 14px;
        }
        .logo {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #3b82f6, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
        
        <div class="logo">✨ ایلی استور ✨</div>
        <h1>سایت در حال به‌روزرسانی</h1>
        <p class="subtitle">ما در حال بهبود تجربه شما هستیم</p>
        
        <div class="message">
            <p>
                متأسفانه در حال حاضر سایت در دسترس نیست و در حال به‌روزرسانی است.
                لطفاً چند دقیقه دیگر دوباره تلاش کنید.
            </p>
        </div>
        
        <div class="loader"></div>
        
        <div class="footer">
            <p>به زودی برمی‌گردیم! 💙</p>
        </div>
    </div>
</body>
</html>

