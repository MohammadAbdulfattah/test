
<link href="{{ asset('css/tailwind/app.css?v='.$asset_v) }}" rel="stylesheet">

<link rel="stylesheet" href="{{ asset('css/vendor.css?v='.$asset_v) }}">
  <link rel="stylesheet" href="https://cdn.datatables.net/colreorder/1.6.2/css/colReorder.dataTables.min.css">
@if( in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) )
	<link rel="stylesheet" href="{{ asset('css/rtl.css?v='.$asset_v) }}">
@endif

@yield('css')

<!-- app css -->
<link rel="stylesheet" href="{{ asset('css/app.css?v='.$asset_v) }}">

@if(isset($pos_layout) && $pos_layout)
	<style type="text/css">
		.content{
			padding-bottom: 0px !important;
		}
	</style>
@endif
<style type="text/css">
.dataTables_wrapper .dt-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    direction: rtl;
    padding: 4px 0;
}

.dataTables_wrapper .dt-buttons .tw-dw-btn {
    min-width: 80px;
    height: 30px;
    line-height: 1.1;
    padding: 0 6px !important;
    margin: 0 !important;
    border: 1px solid #ccc;
    background-color: #f9f9f9;
    color: #555;
    border-radius: 4px !important;
    text-align: center;
    font-size: 11px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    transition: background-color 0.2s ease;
}

.dataTables_wrapper .dt-buttons .tw-dw-btn:hover {
    background-color: #eee;
}

@media (max-width: 768px) {
    .dataTables_wrapper .dt-buttons {
        flex-wrap: wrap;
        flex-direction: row; /* Stay in a row */
        justify-content: center; /* Optional: center align */
    }

    .dataTables_wrapper .dt-buttons .tw-dw-btn {
        width: auto;
        max-width: 100%;
        margin-bottom: 0;
        flex: 1 1 auto;
    }
}
html[dir="ltr"] .dataTables_wrapper .dt-buttons {
    justify-content: flex-start;
}
html[dir="rtl"] .dataTables_wrapper .dt-buttons {
    justify-content: flex-end;
}

	/*
	* Pattern lock css
	* Pattern direction
	* http://ignitersworld.com/lab/patternLock.html
	*/
	.patt-wrap {
	  z-index: 10;
	}
	.patt-circ.hovered {
	  background-color: #cde2f2;
	  border: none;
	}
	.patt-circ.hovered .patt-dots {
	  display: none;
	}
	.patt-circ.dir {
	  background-image: url("{{asset('/img/pattern-directionicon-arrow.png')}}");
	  background-position: center;
	  background-repeat: no-repeat;
	}
	.patt-circ.e {
	  -webkit-transform: rotate(0);
	  transform: rotate(0);
	}
	.patt-circ.s-e {
	  -webkit-transform: rotate(45deg);
	  transform: rotate(45deg);
	}
	.patt-circ.s {
	  -webkit-transform: rotate(90deg);
	  transform: rotate(90deg);
	}
	.patt-circ.s-w {
	  -webkit-transform: rotate(135deg);
	  transform: rotate(135deg);
	}
	.patt-circ.w {
	  -webkit-transform: rotate(180deg);
	  transform: rotate(180deg);
	}
	.patt-circ.n-w {
	  -webkit-transform: rotate(225deg);
	   transform: rotate(225deg);
	}
	.patt-circ.n {
	  -webkit-transform: rotate(270deg);
	  transform: rotate(270deg);
	}
	.patt-circ.n-e {
	  -webkit-transform: rotate(315deg);
	  transform: rotate(315deg);
	}
</style>
@if(!empty($__system_settings['additional_css']))
    {!! $__system_settings['additional_css'] !!}
@endif

