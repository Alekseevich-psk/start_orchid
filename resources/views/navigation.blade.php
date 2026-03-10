<nav class="main-nav">
    <ul class="nav-list">
        @if(isset($siteMenuTree) && is_array($siteMenuTree))
            @foreach ($siteMenuTree as $item)
                <li class="nav-item">
                    <a href="{{ $item['url'] }}" class="nav-link">
                        <i class="icon {{ $item['icon'] }}"></i>
                        <span>{{ $item['title'] }}</span>
                    </a>
                    @if (isset($item['children']) && count($item['children']) > 0)
                        <ul class="sub-nav">
                            @foreach ($item['children'] as $child)
                                <li class="nav-item">
                                    <a href="{{ $child['url'] }}" class="nav-link">{{ $child['title'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        @endif
    </ul>
</nav>