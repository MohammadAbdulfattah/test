<div class="table-responsive">
    @foreach($route->days as $day)
        @php
            $freqMap = [
                7 => __('gbs::lang.every_week'),
                10 => __('gbs::lang.every_10_days'),
                14 => __('gbs::lang.every_2_weeks'),
                21 => __('gbs::lang.every_3_weeks'),
                30 => __('gbs::lang.every_month'),
            ];
            $freqLabel = $freqMap[$day->interval_days ?? 7] ?? __('gbs::lang.every_week');
        @endphp
        <h5 class="text-primary mt-3">
            <strong>{{ $days_ar[$day->day_of_week] ?? $day->day_of_week }}</strong>
            <small class="text-muted"> - {{ $freqLabel }}</small>
        </h5>
        <ul class="list-unstyled">
            @foreach($day->clients as $routeClient)
                <li class="d-flex align-items-start mb-1">
                    <span class="mt-1 mr-2" style="width:8px; height:8px; background:#007bff; border-radius:50%; display:inline-block;"></span>
                    <span class="font-weight-bold">{{ optional($routeClient->contact)->name ?? '-' }}</span>
                </li>
            @endforeach
        </ul>
        <hr>
    @endforeach
</div>
