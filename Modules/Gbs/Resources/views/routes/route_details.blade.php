<div class="table-responsive">
    @foreach($route->days as $day)
        <h5 class="text-primary mt-3"><strong>{{ $days_ar[$day->day_of_week] ?? $day->day_of_week }}</strong></h5>
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
