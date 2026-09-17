@php
    $navItems = [
        'servis' => ['label' => 'Servis', 'route' => 'settings.deletedRecords.servis'],
        'kasa' => ['label' => 'Kasa', 'route' => 'settings.deletedRecords.kasa'],
        'diger' => ['label' => 'Diğer', 'route' => 'settings.deletedRecords.diger'],
    ];
@endphp
<ul class="nav nav-tabs px-3 pt-2" role="tablist">
    @foreach($navItems as $key => $item)
        <li class="nav-item" role="presentation">
            <a href="{{ route($item['route']) }}" class="nav-link {{ ($active ?? null) === $key ? 'active' : '' }}">{{ $item['label'] }}</a>
        </li>
    @endforeach
</ul>
