
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Root POS') }}</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <!-- Bootstrap RTL -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css"
        integrity="sha384-dpuaG1suU0eT09tx5plTaGMLBsfDLzUCCUXOY2j/LSvXYuG6Bqs43ALlhIqAJVRb" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@200;300;400;500;700;800;900&display=swap"
        rel="stylesheet">


    <!-- Main CSS -->
    <style>
        :root {
            --main-color: #0c45f6;
            --secondary-color: #5662ff;
            --tertiary-color: #8381ff;
            --fourth-color: #aba1ff;
        }

        * {
            font-family: 'Tajawal', sans-serif;
            color: #fff;
        }

        .btn-main-custom {
            background-color: #fff;
            color: var(--secondary-color);
        }

        .btn-main-custom:hover {
            background-color: var(--tertiary-color);
            color: #fff;
        }

        .btn-main-custom:active {
            background-color: var(--fourth-color) !important;
            color: #fff !important;
            border: none !important;
        }

        body {
            background: rgb(86, 98, 255);
            background: linear-gradient(90deg, rgba(86, 98, 255, 1) 0%, rgba(131, 129, 255, 1) 50%, rgba(171, 161, 255, 1) 98%);
        }

        nav {
            background: rgb(86, 98, 255);
            background: linear-gradient(90deg, rgba(86, 98, 255, 1) 0%, rgba(131, 129, 255, 1) 50%, rgba(171, 161, 255, 1) 98%);
            border-bottom: 1px solid #ffffff40;
        }

        nav .navbar-brand {
            color: #fff;
        }

        .header {
            padding: 40px 0;
        }

        .header h1 {
            font-weight: 900;
        }

        .header h2 {
            font-weight: 700;
        }

        .header .logo {
            width: 3rem;
        }

        .waves {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 15vh;
            margin-bottom: 0;
            /*Fix for safari gap*/
            min-height: 100px;
            max-height: 150px;
        }

        /* Animation */

        .parallax>use {
            animation: move-forever 25s cubic-bezier(0.55, 0.5, 0.45, 0.5) infinite;
        }

        .parallax>use:nth-child(1) {
            animation-delay: -2s;
            animation-duration: 7s;
        }

        .parallax>use:nth-child(2) {
            animation-delay: -3s;
            animation-duration: 10s;
        }

        .parallax>use:nth-child(3) {
            animation-delay: -4s;
            animation-duration: 13s;
        }

        .parallax>use:nth-child(4) {
            animation-delay: -5s;
            animation-duration: 20s;
        }

        @keyframes move-forever {
            0% {
                transform: translate3d(-90px, 0, 0);
            }

            100% {
                transform: translate3d(85px, 0, 0);
            }
        }

        /*Shrinking for mobile*/
        @media (max-width: 768px) {
            .waves {
                height: 40px;
                min-height: 40px;
            }
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container">



         @if (file_exists(public_path('uploads/logo.png')))
            <img src="/uploads/logo.png" class="img-rounded" alt="Logo" width="150">
        @else
           
            <a class="navbar-brand" href="#">{{ config('app.name', 'Root POS') }}</a>
        @endif
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                </ul>


            @if (Route::has('login'))
                @if (!Auth::check())
                    
                               <a class="btn btn-main-custom" href="{{ route('login') }}">
                                     @lang('lang_v1.login')
                               </a>

                    @if (config('constants.allow_registration'))
                    
                                 <a class="btn btn-main-custom" href="{{ route('business.getRegister') }}">
                                     @lang('lang_v1.register')
                               </a>
                    @endif
                @endif
            @endif


             @if (Auth::check())
             

                        <a class="btn btn-main-custom" href="{{ action('HomeController@index') }}">
                                     @lang('home.home')
                               </a>
            @endif
                
            </div>
        </div>
    </nav>

    <!-- Header -->
    <section class="header">
        <div class="container-fluid d-flex justify-content-center align-items-center">
            <div class="row">
                <div class="col-lg-12 text-center">

                    @if (file_exists(public_path('uploads/logo.png')))
                                    <img src="/uploads/logo.png" class="img-rounded" alt="Logo" width="150">
                                @else
                                    <h3>أهلاً وسهلاً بك | <span>{{ config('app.name', 'Root POS') }}<span></h3>
                    
                        
                    @endif

                   
                    <h1 class="my-3">  شركة رووت لتكنولجيا المعلومات      </h1>

                    <h3>أختيار رجال الأعمال </h3>
                    <div class="row d-flex justify-content-center">
                        <div class="col-4">
                            <img class="logo" src="{{ asset('img/shield-01.svg') }}" alt="">
                            <h6>الحماية</h6>
                        </div>
                        <div class="col-4">
                            <img class="logo" src="{{ asset('img/hand-01.svg') }}" alt="">
                            <h6>القوة</h6>
                        </div>
                        <div class="col-4">
                            <img class="logo" src="{{ asset('img/circle.svg') }}" alt="">
                            <h6>الاستمرارية</h6>
                        </div>
                    </div>
                    <div class="my-5">

                     @if (Route::has('login'))
                               @if (!Auth::check())
                    
                                    <a class="btn btn-main-custom d-block" href="{{ route('login') }}">
                                            @lang('lang_v1.login')
                                    </a>

               
                               @endif
                     @endif

                      @if (Auth::check())
             

                        <a class="btn btn-main-custom d-block" href="{{ action('HomeController@index') }}">
                                     @lang('home.home')
                               </a>
                         @endif


                       
                    </div>
                </div>
            </div>

        </div>
    </section>
    <div>
        <svg class="waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
            viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
            <defs>
                <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
            </defs>
            <g class="parallax">
                <use xlink:href="#gentle-wave" x="48" y="0" fill="#5662ff" />
                <use xlink:href="#gentle-wave" x="48" y="3" fill="#8381ff" />
                <use xlink:href="#gentle-wave" x="48" y="5" fill="#aba1ff" />
                <use xlink:href="#gentle-wave" x="48" y="7" fill="#aba1ff" />
            </g>
        </svg>
    </div>

</body>








