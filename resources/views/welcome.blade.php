
@php
    $currentLang = request()->get('lang', 'ar');
    app()->setLocale($currentLang);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_','-', $currentLang) }}" dir="{{ $currentLang === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name') }}</title>
    <link href="https://vjs.zencdn.net/8.20.0/video-js.css" rel="stylesheet" />

    <style>
        :root{
            --brand-dark:#1f2c3a;
            --brand-text:#1f2c3a;
            --brand-muted:#6b7a8c;
            --brand-green:#97b224;
            --brand-green-700:#7e971e;
            --brand-blue:#0a58ca;
            --surface:#ffffff;
            --surface-2:#f5f7fb;
            --divider:#e7edf3;
            --card-border:#e5e7eb;
            --shadow:0 8px 24px rgba(0,0,0,.08);
        }
          .video-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0;
        }

        .video-js {
            width: 100% !important;
            height: auto !important;
            aspect-ratio: 16/9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            background-color: black;
        }

        *{box-sizing:border-box}
        html,body{height:100%}
        body {
            margin: 0;
            font-family: 'Cairo','Tajawal','Montserrat', sans-serif;
            background-color: var(--surface);
            color: var(--brand-text);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Container */
        .container{
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: var(--surface);
            border-bottom: 1px solid var(--divider);
        }
        .nav{
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 16px;
            padding: 14px 0;
        }
        .logo a{
            display:inline-flex;
            align-items:center;
            gap:10px;
            text-decoration:none;
            color: var(--brand-dark);
            font-weight: 900;
            font-size: 1.6rem;
            letter-spacing: .2px;
        }
        .logo-badge{
            display:inline-block;
            width: 10px; height: 10px;
            background: var(--brand-blue);
            border-radius: 2px;
            transform: rotate(10deg);
        }

        .nav-links{
            display:flex;
            align-items:center;
            gap: 18px;
        }
        .nav-links a{
            text-decoration:none;
            color: var(--brand-dark);
            font-weight: 600;
            padding: 8px 10px;
            border-radius:8px;
            transition: color .2s ease, background .2s ease;
        }
        .nav-links a:hover{ color: var(--brand-blue); background: #eef4ff; }

        .language-dropdown{ position:relative; }
        .language-dropdown > button{
            background: transparent;
            border: 1px solid var(--divider);
            color: var(--brand-dark);
            padding: 8px 12px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
        }
        .language-dropdown ul{
            position:absolute;
            inset-inline-start: 0;
            top: calc(100% + 8px);
            list-style:none;
            margin:0;
            padding:8px;
            background:#fff;
            border:1px solid var(--divider);
            border-radius:12px;
            min-width: 160px;
            display:none;
            box-shadow: var(--shadow);
            z-index: 100;
        }
        .language-dropdown li a{
            display:block;
            padding:10px 12px;
            color: var(--brand-dark);
            text-decoration:none;
            border-radius:8px;
            transition: background .15s ease;
        }
        .language-dropdown li a:hover{ background:#f1f5f9; }

        .auth-actions{
            display:flex;
            align-items:center;
            gap:10px;
        }
        .btn{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:8px;
            text-decoration:none;
            border: 1px solid var(--divider);
            background:#fff;
            color: var(--brand-dark);
            padding:10px 14px;
            border-radius: 12px;
            font-weight:700;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease, color .15s ease, border-color .15s ease;
            white-space: nowrap;
        }
        .btn:hover{ transform: translateY(-1px); box-shadow: var(--shadow); }
        .btn-ghost{ background: #fff; border-color: var(--divider); }
        .btn-outline{ border-color: var(--brand-green); color: var(--brand-green); background:#fff; }
        .btn-primary{ border-color: transparent; background: var(--brand-green); color: #001e0e; }
        .btn-primary:hover{ background: var(--brand-green-700); color:#fff; }

        /* Mobile toggle */
        .nav-toggle{
            display:none;
            background:#fff;
            border:1px solid var(--divider);
            border-radius:10px;
            padding:8px 10px;
            font-weight:700;
        }

        /* Hero */
        .hero{
            background: radial-gradient(1200px 360px at 50% 0%, #e9f5ff 0%, rgba(233,245,255,0) 60%) no-repeat,
                        linear-gradient(#fff, #fff);
        }
        .hero-inner{
            display:grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 30px;
            align-items:center;
            padding: 54px 0 36px;
        }
        .eyebrow{
            display:inline-flex;
            align-items:center;
            gap:8px;
            background:#eef7ff;
            color:#0b4aa2;
            border:1px solid #d7e8ff;
            padding:8px 12px;
            border-radius:999px;
            font-weight:800;
            font-size:.9rem;
            margin-bottom:12px;
        }
        .hero h1{
            margin:0 0 12px;
            font-size: 2.4rem;
            line-height:1.3;
            color: var(--brand-dark);
            font-weight: 900;
            letter-spacing:.2px;
        }
        .hero p{
            margin:0;
            font-size: 1.06rem;
            color: var(--brand-muted);
            line-height:1.9;
        }
        .hero-cta{ margin-top: 24px; display:flex; gap:10px; flex-wrap:wrap; }
        .hero-card{
            background:#fff;
            border:1px solid var(--divider);
            border-radius: 16px;
            padding: 18px;
            box-shadow: var(--shadow);
        }
        .hero-figure{
            position:relative;
            min-height: 280px;
            display:flex;
            align-items:center;
            justify-content:center;
        }
        .device{
            width: 88%;
            max-width: 500px;
            aspect-ratio: 16/10;
            border-radius: 18px;
            background:
              linear-gradient(180deg,#0f1b2e,#182a43);
            box-shadow: 0 20px 40px rgba(12,46,99,.25);
            border: 10px solid #0b1422;
            position:relative;
            overflow:hidden;
        }
        .device::after{
            content:"";
            position:absolute;
            inset:0;
            background:
              radial-gradient(600px 120px at 50% -40px, rgba(41,101,255,.25), transparent 60%),
              radial-gradient(400px 100px at 70% 100%, rgba(34,197,94,.25), transparent 70%);
            pointer-events:none;
        }

        /* Section: Apps */
        section{ padding: 56px 0; }
        .section-title{
            text-align:center;
            margin: 0 0 18px;
            font-size: 1.9rem;
            color: var(--brand-dark);
            font-weight: 900;
        }
        .section-sub{
            text-align:center;
            max-width:780px;
            margin: 0 auto;
            color: var(--brand-muted);
            line-height:1.9;
        }
        .grid{
            display:grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 18px;
            margin-top: 28px;
        }
        .card{
            grid-column: span 4;
            background:#fff;
            border:1px solid var(--card-border);
            border-radius: 16px;
            padding: 18px;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .card:hover{ transform: translateY(-4px); box-shadow: var(--shadow); border-color:#dbe4ee; }
        .card h3{ margin:0 0 8px; font-size:1.1rem; color: var(--brand-dark); }
        .card p{ margin:0; color: var(--brand-muted); line-height:1.8; font-size: .96rem; }
        .card i{ font-style:normal; font-weight:900; color: var(--brand-blue); }

        /* Features tiles */
        .tiles{ grid-template-columns: repeat(12, 1fr); }
        .tile{ grid-column: span 3; background:#fff; border:1px solid var(--card-border); border-radius:16px; padding:18px; }
        .tile h4{ margin:0 0 8px; font-size:1.05rem; }
        .tile p{ margin:0; color: var(--brand-muted); line-height:1.8; font-size:.95rem; }

        /* CTA band */
        .cta-band{
            background: radial-gradient(1400px 400px at 60% 20%, rgba(13,110,253,.14), transparent 60%), #0f1f3a;
            border-radius: 20px;
            color:#fff;
            padding: 28px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 18px;
            box-shadow: var(--shadow);
        }
        .cta-band h3{ margin:0; font-size: 1.4rem; }
        .cta-band p{ margin:6px 0 0; color:#d7e2f2; }

        /* Footer */
        footer{
            margin-top:auto;
            background:#0f1f3a;
            color:#d7e2f2;
        }
        .footer-inner{
            padding: 44px 0 28px;
        }
        .footer-grid{
            display:grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 18px;
        }
        .foot-col{ grid-column: span 4; }
        .foot-col h5{ margin:0 0 10px; font-size:1.05rem; color:#fff; }
        .foot-links{ list-style:none; margin:0; padding:0; }
        .foot-links li a{
            display:block; text-decoration:none; color:#d7e2f2; padding:8px 0; border-radius:8px;
        }
        .foot-links li a:hover{ color:#fff; text-decoration:underline; }
        .store-row{ display:flex; gap:10px; margin-top:10px; flex-wrap:wrap; }
        .store-badge{
            display:inline-flex; align-items:center; gap:8px;
            background:#0b1422; color:#fff; padding:10px 12px; border-radius:12px; font-weight:800; text-decoration:none;
            border:1px solid #1f2c46;
        }

        .footer-bottom{
            border-top:1px solid #22304a;
            padding: 16px 0;
            font-size:.94rem;
            color:#a8b3c6;
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap: 14px;
            flex-wrap:wrap;
        }

        /* Responsive */
        @media (max-width: 1024px){
            .hero-inner{ grid-template-columns: 1fr; }
            .device{ width: 100%; }
        }
        @media (max-width: 860px){
            .nav-toggle{ display:inline-flex; }
            .nav-links{ display:none; }
            .nav.open .nav-links{ display:flex; position:absolute; inset-inline: 0; top:100%; background:#fff; border-bottom:1px solid var(--divider); padding:12px 20px; gap:10px; flex-wrap:wrap; }
            .auth-actions{ flex-wrap:wrap; }
            .card{ grid-column: span 6; }
            .tile{ grid-column: span 6; }
            .foot-col{ grid-column: span 6; }
        }
        @media (max-width: 560px){
            .card{ grid-column: span 12; }
            .tile{ grid-column: span 12; }
            .foot-col{ grid-column: span 12; }
            .cta-band{ flex-direction:column; align-items:flex-start; }
        }
    </style>
</head>

<body>
    <header>
        <div class="container">
            <div class="nav" id="mainNav">
                <div class="logo">
                    <a href="{{ url('/').'?lang='.$currentLang }}">
                        <span class="logo-badge"></span>
                        {{ config('app.name') }}
                    </a>
                </div>

                <button class="nav-toggle" id="navToggle">القائمة</button>

                <nav class="nav-links">
                    <a href="#apps">البرامج</a>
                    <a href="#industries">مجالات العمل</a>
                    <a href="#features">المزايا</a>
                    <a href="{{ route('pricing', ['lang' => $currentLang]) }}">الأسعار</a>
                    <a href="#contact">اتصل بنا</a>
                </nav>

                <div class="auth-actions">
                    <div class="language-dropdown">
                        <button>@lang('lang_v1.language') ▾</button>
                        <ul>
                            @foreach (config('constants.langs') as $key => $val)
                                <li><a href="{{ url()->current() }}?lang={{ $key }}">{{ $val['full_name'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>

                    @if (Route::has('login'))
                        @if (!Auth::check())
                            <a class="btn btn-outline" href="{{ route('login', ['lang' => $currentLang]) }}">@lang('lang_v1.login')</a>
                            @if (config('constants.allow_registration'))
                                <a class="btn btn-primary" href="{{ route('business.getRegister', ['lang' => $currentLang]) }}">ابدأ الاستخدام مجانًا</a>
                            @endif
                        @else
                            <a class="btn btn-ghost" href="{{ route('home', ['lang' => $currentLang]) }}">@lang('home.home')</a>
                            <a class="btn" href="{{ action([\App\Http\Controllers\Auth\LoginController::class, 'logout']) }}">@lang('lang_v1.sign_out')</a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </header>

    <main>
        <!-- HERO -->
        <section class="hero">
            <div class="container">
                <div class="hero-inner">
                    <div class="hero-copy">
                        <span class="eyebrow">واجهة سهلة الاستخدام بميزات متعددة</span>
                        <h1>نظام ERP متكامل لإدارة كافة أعمالك</h1>
                        <p>
                            نظام سحابي يدعم العربية لإدارة المبيعات والمخزون والحسابات والموظفين، مع تقارير مفصّلة ومرونة عالية للتخصيص.
                            جاهز للعمل فورًا وداعم للأمان والنسخ الاحتياطي.
                        </p>
                        <div class="hero-cta">
                            <a class="btn btn-primary" href="{{ route('business.getRegister', ['lang' => $currentLang]) }}">ابدأ الاستخدام مجانًا</a>
                            <a class="btn" href="{{ route('pricing', ['lang' => $currentLang]) }}">الأسعار</a>
                        </div>
                    </div>
                    <div class="hero-figure">
                        <div class="hero-card">
                            <div class="device"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- APPS -->
        <section id="apps">
            <div class="container">
                <h2 class="section-title">تطبيقات إدارة الأعمال المضمّنة</h2>
                <p class="section-sub">استخدم ما تحتاجه فقط أو فعّل مجموعة التطبيقات معًا بحسب احتياج أعمالك.</p>

                <div class="grid">
                    <div class="card">
                        <h3><i>▣</i> المبيعات</h3>
                        <p>الفواتير والضرائب، نقاط البيع، العروض، الأقساط والتسويات.</p>
                    </div>
                    <div class="card">
                        <h3><i>▣</i> المخزون</h3>
                        <p>المنتجات والخدمات، الموردون والمشتريات، المستودعات والجرد.</p>
                    </div>
                    <div class="card">
                        <h3><i>▣</i> المحاسبة العامة</h3>
                        <p>القيود اليومية، دليل الحسابات، التقارير والقوائم المالية.</p>
                    </div>
                    <div class="card">
                        <h3><i>▣</i> إدارة العمليات</h3>
                        <p>أوامر الشغل والحجوزات وتتبع الوقت ومراكز التكلفة.</p>
                    </div>
                    <div class="card">
                        <h3><i>▣</i> علاقات العملاء</h3>
                        <p>إدارة العملاء والمتابعة، النقاط والعضويات والاشتراكات.</p>
                    </div>
                    <div class="card">
                        <h3><i>▣</i> شؤون الموظفين</h3>
                        <p>الموظفون، الحضور، العقود، المرتبات، الطلبات والإجازات.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FEATURES -->
        <section id="features" style="background: var(--surface-2);">
            <div class="container">
                <h2 class="section-title">لماذا هذا النظام مناسب لك؟</h2>
                <p class="section-sub">واجهة سهلة، تخصيص كامل، أمان سحابي، وتحديثات دورية — كل ما تحتاجه لإدارة أعمالك بكفاءة.</p>

                <div class="grid tiles">
                    <div class="tile">
                        <h4>واجهة سهلة الاستخدام</h4>
                        <p>ابدأ العمل فورًا دون معرفة محاسبية عميقة.</p>
                    </div>
                    <div class="tile">
                        <h4>تجربة مخصّصة بالكامل</h4>
                        <p>فعّل التطبيقات المناسبة لمجال عملك وعدّل الإعدادات بحرية.</p>
                    </div>
                    <div class="tile">
                        <h4>الأمان والحماية</h4>
                        <p>سيرفرات موثوقة، تشفير واتساق نسخ احتياطي مستمر.</p>
                    </div>
                    <div class="tile">
                        <h4>توفـير الوقت والجهد</h4>
                        <p>أتمتة عملياتك الأساسية مع تقارير ومرئيات واضحة.</p>
                    </div>
                </div>

                <div style="margin-top:28px">
                    <div class="cta-band">
                        <div>
                            <h3>نظام متكامل قابل للتخصيص ليلائم أكثر من 50 مجالًا</h3>
                            <p>اختر التشكيلة المناسبة لتصميمات عملك وابدأ الآن.</p>
                        </div>
                        <div>
                            <a class="btn btn-primary" href="{{ route('business.getRegister', ['lang' => $currentLang]) }}">ابدأ الاستخدام مجانًا</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- INDUSTRIES -->
        <section id="industries">
            <div class="container">
                <h2 class="section-title">مجالات العمل المدعومة</h2>
                <p class="section-sub">متاجر، صالونات، الصيدليات، العيادات، المراكز التعليمية، شركات الأغذية وغيرها.</p>

                <div class="grid">
                    <div class="card"><h3>إدارة المتاجر</h3><p>المخزون، نقاط البيع، الشحن، العملاء.</p></div>
                    <div class="card"><h3>الصالونات والنوادي</h3><p>الحجوزات، العضويات، العروض والدورات.</p></div>
                    <div class="card"><h3>الصيدليات</h3><p>الأدوية، الموردون، صلاحيات الجرد.</p></div>
                    <div class="card"><h3>العيادات</h3><p>الملفات والمواعيد، الفوترة الطبية.</p></div>
                    <div class="card"><h3>الشركات</h3><p>المحاسبة العامة، التقارير، المصروفات.</p></div>
                    <div class="card"><h3>التعليم</h3><p>الطلاب، الدورات، التحصيلات.</p></div>
                </div>
            </div>
        </section>
        <section>
            <div class="video-container"> 
                <video id="my-video" class="video-js vjs-default-skin" controls preload="auto" data-setup='{}'> 
                    <source src="https://pg.rootq.dev/videos/learning_video2.mp4" type="video/mp4" /> 
                    متصفحك لا يدعم فيديو HTML5. 
                </video>
            </div>
        </section>
        <!-- CONTACT CTA -->
        <section id="contact" style="padding-top: 12px;">
            <div class="container">
                <div class="cta-band" style="background:#0a3a8a;">
                    <div>
                        <h3>جاهز للعمل فورًا</h3>
                        <p>تواصل معنا لأي استفسار واحصل على دعم فني مجاني.</p>
                    </div>
                    <div>
                        <a class="btn btn-primary" href="{{ route('pricing', ['lang' => $currentLang]) }}">اطّلع على الباقات</a>
                    </div>
                </div>
            </div>
        </section>
        
    </main>

    <footer>
        <div class="container footer-inner">
            <div class="footer-grid">
                <div class="foot-col">
                    <h5>البرامج</h5>
                    <ul class="foot-links">
                        <li><a href="#apps">برنامج المبيعات والفواتير</a></li>
                        <li><a href="#apps">برنامج إدارة المخزون</a></li>
                        <li><a href="#apps">برنامج الحسابات العامة</a></li>
                        <li><a href="#apps">برنامج دورة العمل</a></li>
                        <li><a href="#apps">برنامج إدارة علاقات العملاء</a></li>
                        <li><a href="#apps">برنامج شؤون الموظفين</a></li>
                    </ul>
                </div>
                <div class="foot-col">
                    <h5>دليل الموقع</h5>
                    <ul class="foot-links">
                        <li><a href="{{ route('pricing', ['lang' => $currentLang]) }}">الأسعار</a></li>
                        <li><a href="#contact">تواصل معنا</a></li>
                        <li><a href="#features">آخر التحديثات</a></li>
                        <li><a href="#apps">دليل API</a></li>
                    </ul>
                </div>
                <div class="foot-col">
                    <h5>تطبيقات الهواتف الذكية</h5>
                    <div class="store-row">
                        <a class="store-badge" href="#">AppGallery</a>
                        <a class="store-badge" href="#">Google Play</a>
                        <a class="store-badge" href="#">App Store</a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div>© {{ date('Y') }} {{ config('app.name') }}. جميع الحقوق محفوظة.</div>
                <div>هذا الموقع محمي بتقنيات حديثة ويدعم SSL.</div>
            </div>
        </div>
    </footer>
    <script src="https://vjs.zencdn.net/8.20.0/video.min.js"></script> 
    <script src="https://unpkg.com/feather-icons"></script> 
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        (function(){
            const navToggle = document.getElementById('navToggle');
            const mainNav = document.getElementById('mainNav');
            if(navToggle){
                navToggle.addEventListener('click', function(){
                    mainNav.classList.toggle('open');
                });
            }
        })();

        $(document).ready(function(){
            $('.language-dropdown > button').on('click', function(e){
                e.stopPropagation();
                $(this).siblings('ul').fadeToggle(100);
            });
            $(document).on('click', function(){
                $('.language-dropdown ul').fadeOut(80);
            });
        });
    </script>
</body>
</html>








