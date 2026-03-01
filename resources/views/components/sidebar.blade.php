@php
use App\Helpers\MenuHelper;
use App\Helpers\AuthHelper;
$menuItems = MenuHelper::getMenuItems('left_sidebar');
@endphp

<nav class="sidebar sidebar-offcanvas" id="sidebar">
  <ul class="nav">
    {{-- Logo do Tenant --}}
    @if(AuthHelper::check())
    <li class="nav-item nav-profile border-bottom">
      <div class="nav-link d-flex align-items-center py-3">
        <div class="me-3">
          @if(AuthHelper::hasTenantLogo())
            <img src="{{ AuthHelper::tenantLogoUrl() }}" 
                 alt="{{ AuthHelper::tenantName() ?? 'Logo' }}" 
                 class="img-fluid" 
                 style="max-height: 50px; max-width: 50px; object-fit: contain; border: 2px solid #e0e0e0; border-radius: 8px; padding: 6px; background-color: #fff;"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
            <i class="mdi mdi-hospital-building text-primary" style="font-size: 1.5rem; display: none; border: 2px solid #e0e0e0; border-radius: 8px; padding: 6px;"></i>
          @else
            <i class="mdi mdi-hospital-building text-primary" style="font-size: 1.5rem; border: 2px solid #e0e0e0; border-radius: 8px; padding: 6px;"></i>
          @endif
        </div>
        <div class="d-flex align-items-center flex-wrap flex-grow-1 gap-2">
          <div class="d-flex align-items-center">
            <i class="mdi mdi-office-building text-primary me-2" style="font-size: 0.875rem;"></i>
            <span class="nav-profile-name small fw-semibold">{{ AuthHelper::tenantName() ?? 'VisaoSis' }}</span>
          </div>
          @if(AuthHelper::locationName())
            <div class="d-flex align-items-center">
              <i class="mdi mdi-map-marker text-muted me-2" style="font-size: 0.75rem;"></i>
              <span class="text-muted" style="font-size: 0.7rem;">{{ AuthHelper::locationName() }}</span>
            </div>
          @endif
        </div>
      </div>
    </li>
    @endif
    
    <li class="nav-item nav-category">Menu</li>
    
    {{-- Menu dinâmico baseado nas permissões do Cerberus --}}
    @foreach($menuItems as $item)
        @php
          $hasChildren = !empty($item['children']) && is_array($item['children']);
          $itemKey = 'menu_' . ($item['id'] ?? uniqid());
          $hasActiveChild = false;
          // Verificar se algum filho está realmente ativo
          if ($hasChildren) {
            foreach ($item['children'] as $child) {
              if (isset($child['url']) && !empty($child['url']) && $child['url'] !== '#') {
                $url = MenuHelper::processUrl($child['url']);
                // Verificação mais rigorosa: URL não pode ser vazia, não pode ser #, e deve estar realmente ativa
                if (!empty($url) && $url !== '#' && $url !== '/' && MenuHelper::isRouteActive($url)) {
                  $hasActiveChild = true;
                  break;
                }
              }
            }
          }
          // Por padrão, todos os menus começam fechados (false)
          $isExpanded = $hasActiveChild ? 'true' : 'false';
          $showClass = $hasActiveChild ? 'show' : '';
        @endphp

        @if($hasChildren)
          {{-- Item com submenu --}}
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#{{ $itemKey }}" aria-expanded="{{ $isExpanded }}" aria-controls="{{ $itemKey }}">
              <span class="icon-bg"><i class="{{ $item['icon'] ?? 'mdi mdi-circle' }} menu-icon"></i></span>
              <span class="menu-title">{{ $item['name'] ?? $item['short_name'] ?? 'Menu' }}</span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse {{ $showClass }}" id="{{ $itemKey }}" data-initial-state="{{ $hasActiveChild ? 'open' : 'closed' }}">
              <ul class="nav flex-column sub-menu">
                @php
                  $children = $item['children'] ?? [];
                  usort($children, function ($a, $b) {
                    $orderA = $a['ordering'] ?? 999;
                    $orderB = $b['ordering'] ?? 999;
                    return $orderA <=> $orderB;
                  });
                @endphp
                @foreach($children as $child)
                  @php
                    $childUrl = MenuHelper::processUrl($child['url'] ?? '#');
                    if ($childUrl === '#') {
                      $childUrl = MenuHelper::resolveUrlFromItemKey($child);
                    }
                    $isActive = MenuHelper::isRouteActive($childUrl);
                  @endphp
                  <li class="nav-item">
                    <a class="nav-link {{ $isActive ? 'active' : '' }}" href="{{ $childUrl }}" target="{{ $child['target'] ?? '_self' }}">
                      <i class="{{ $child['icon'] ?? 'mdi mdi-circle' }} menu-icon me-2"></i>
                      {{ $child['name'] ?? $child['short_name'] ?? 'Item' }}
                    </a>
                  </li>
                @endforeach
              </ul>
            </div>
          </li>
        @else
          {{-- Item simples --}}
          @php
            $url = MenuHelper::processUrl($item['url'] ?? '#');
            if ($url === '#') {
              $url = MenuHelper::resolveUrlFromItemKey($item);
            }
            $isActive = MenuHelper::isRouteActive($url);
          @endphp
          <li class="nav-item">
            <a class="nav-link {{ $isActive ? 'active' : '' }}" href="{{ $url }}" target="{{ $item['target'] ?? '_self' }}">
              <span class="icon-bg"><i class="{{ $item['icon'] ?? 'mdi mdi-circle' }} menu-icon"></i></span>
              <span class="menu-title">{{ $item['name'] ?? $item['short_name'] ?? 'Item' }}</span>
            </a>
          </li>
        @endif
      @endforeach
   
    @auth
    <li class="nav-item sidebar-user-actions">
      <div class="sidebar-user-menu">
        @if(Route::has('logout'))
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start" style="cursor: pointer;">
            <i class="mdi mdi-logout menu-icon"></i>
            <span class="menu-title">Sair</span>
          </button>
        </form>
        @else
        <a href="#" class="nav-link">
          <i class="mdi mdi-logout menu-icon"></i>
          <span class="menu-title">Sair</span>
        </a>
        @endif
      </div>
    </li>
    @endauth
  </ul>
</nav>
