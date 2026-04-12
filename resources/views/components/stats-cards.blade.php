@props(['stats'])

<div @class(['grid gap-6 w-full']) style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px;">
    @foreach($stats as $index => $stat)
    <div style="border-radius: 32px;" class="bg-white dark:bg-[#1A1A31] p-8 shadow-sm  dark:border-white/5 flex flex-col justify-between  relative overflow-hidden group hover:shadow-md transition-all">
        <div class="flex items-center justify-between relative z-10">
            <div class="space-y-1">
                <p class="text-xs font-bold text-[#1A1A31] dark:text-slate-400 opacity-60 ">{{ $stat['label'] }}</p>
                <div class="flex items-baseline gap-2 ">
                    <h3 class="text-3xl font-black text-[#1A1A31] dark:text-white">{{ $stat['value'] }}</h3>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold border" 
                          style="background-color: {{ $stat['color'] }}10; color: {{ $stat['color'] }}; border-color: {{ $stat['color'] }}20;">
                        {{ $stat['percentage'] }}%+
                    </span>
                </div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-slate-50 dark:bg-white/5 flex items-center justify-center text-slate-400 group-hover:scale-110 transition-transform">
                @if($stat['icon'] == 'total')
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                @elseif($stat['icon'] == 'sent')
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                @elseif($stat['icon'] == 'scheduled')
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                @else
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                @endif
            </div>
        </div>
        
        <div class="absolute bottom-0 left-0 right-0 h-20 opacity-30 group-hover:opacity-50 transition-opacity">
            <canvas id="sparkline-{{ $index }}" class="w-full h-full"></canvas>
        </div>
    </div>
    @endforeach
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stats = @json($stats);
        stats.forEach((stat, index) => {
            const ctx = document.getElementById(`sparkline-${index}`)?.getContext('2d');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: stat.sparkline.map((_, i) => i),
                        datasets: [{
                            data: stat.sparkline,
                            borderColor: stat.color,
                            borderWidth: 2,
                            fill: true,
                            backgroundColor: stat.color + '10',
                            tension: 0.4,
                            pointRadius: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false }, tooltip: { enabled: false } },
                        scales: { x: { display: false }, y: { display: false } }
                    }
                });
            }
        });
    });
</script>
@endpush
