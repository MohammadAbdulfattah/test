@php
    $currentLang = isset($currentLang) ? $currentLang : request()->get('lang', 'ar');
    app()->setLocale($currentLang);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_','-', $currentLang) }}" dir="{{ $currentLang === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name') }} - التسعير</title>
    <style>
        :root{ --brand-green:#97b224; --brand-green-700:#7e971e; --brand-dark:#1f2c3a; --divider:#e7edf3; }
        body{ margin:0; font-family:'Cairo','Tajawal','Montserrat',sans-serif; background:#fff; color:#1f2c3a; }
        .container{ max-width: 1100px; margin:0 auto; padding: 24px; }
        a{ color:inherit; text-decoration:none }
        .btn{ display:inline-block; padding:10px 14px; border-radius:10px; border:1px solid var(--divider); background:#fff; }
        .btn-primary{ background: var(--brand-green); border-color:transparent; color:#001e0e }
        .btn-primary:hover{ background: var(--brand-green-700); color:#fff }
        .grid{ display:grid; grid-template-columns: repeat(12,1fr); gap:18px; margin-top:24px }
        .card{ grid-column: span 4; border:1px solid #e5e7eb; border-radius:16px; padding:18px }
        .price{ font-size:2rem; font-weight:900; color:#111 }
        header{ border-bottom:1px solid var(--divider); }
        .nav{ display:flex; justify-content:space-between; align-items:center; padding:14px 0 }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div class="nav">
                <a href="{{ url('/').'?lang='.$currentLang }}">{{ config('app.name') }}</a>
                <a class="btn" href="{{ url('/').'?lang='.$currentLang }}">العودة للواجهة</a>
            </div>
        </div>
    </header>
    <main class="container">
        <h1>الباقات والأسعار</h1>
        <p>اختر الخطة المناسبة لاحتياجات عملك. جميع الباقات تدعم العربية وتأتي مع تحديثات دورية.</p>
        <div class="grid">
            <div class="card">
                <h3>أساسية</h3>
                <div class="price">$9<span style="font-size:1rem;font-weight:600;color:#6b7280">/شهر</span></div>
                <ul>
                    <li>مبيعات وفواتير</li>
                    <li>مخزون أساسي</li>
                    <li>تقارير مبسطة</li>
                </ul>
                <a class="btn btn-primary" href="{{ route('business.getRegister', ['lang' => $currentLang]) }}">ابدأ الآن</a>
            </div>
            <div class="card">
                <h3>احترافية</h3>
                <div class="price">$19<span style="font-size:1rem;font-weight:600;color:#6b7280">/شهر</span></div>
                <ul>
                    <li>كل ميزات الأساسية</li>
                    <li>محاسبة عامة</li>
                    <li>تهيئة متقدمة</li>
                </ul>
                <a class="btn btn-primary" href="{{ route('business.getRegister', ['lang' => $currentLang]) }}">ابدأ الآن</a>
            </div>
            <div class="card">
                <h3>شركات</h3>
                <div class="price">$39<span style="font-size:1rem;font-weight:600;color:#6b7280">/شهر</span></div>
                <ul>
                    <li>كل الميزات</li>
                    <li>دعم أولوية</li>
                    <li>تهيئة مخصصة</li>
                </ul>
                <a class="btn btn-primary" href="{{ route('business.getRegister', ['lang' => $currentLang]) }}">تواصل للمؤسسات</a>
            </div>
        </div>
    </main>
</body>
</html>
