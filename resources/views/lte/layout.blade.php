@php($brand = app(\App\Services\WhitelabelService::class)->viewData())
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — {{ $brand['name'] }}</title>
    <link rel="icon" href="{{ $brand['favicon'] }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('adminlte/css/adminlte.min.css') }}">
    @stack('css')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    {{-- Navbar --}}
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#" role="button" aria-label="Toggle sidebar"><i class="fas fa-bars"></i></a></li>
            <li class="nav-item d-none d-sm-inline-block"><a href="{{ route('home') }}" class="nav-link" target="_blank" rel="noopener">{{ __('Lihat Situs') }}</a></li>
        </ul>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true">
                    <i class="fas fa-user-circle mr-1"></i> {{ auth()->user()?->name }} <small class="text-muted">({{ auth()->user()?->role }})</small>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <form method="POST" action="{{ route('lte.logout') }}">@csrf
                        <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt mr-2"></i>{{ __('Keluar') }}</button>
                    </form>
                </div>
            </li>
        </ul>
    </nav>

    {{-- Sidebar --}}
    <aside class="main-sidebar sidebar-dark-primary elevation-2">
        <a href="{{ route('lte.dashboard') }}" class="brand-link">
            @if($brand['logo'])
                <img src="{{ $brand['logo'] }}" alt="{{ $brand['name'] }}" class="brand-image img-circle elevation-1" style="opacity:.9">
            @else
                <span class="brand-image img-circle bg-primary text-white d-inline-flex align-items-center justify-content-center font-weight-bold" style="font-size:.7rem">{{ $brand['initials'] }}</span>
            @endif
            <span class="brand-text font-weight-bold">{{ $brand['name'] }}</span>
        </a>

        <div class="sidebar">
            <nav class="mt-3">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('lte.dashboard') }}" class="nav-link {{ request()->routeIs('lte.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i><p>{{ __('Dashboard') }}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('lte.vehicles.index') }}" class="nav-link {{ request()->routeIs('lte.vehicles.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-car"></i><p>{{ __('Armada') }}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('lte.bookings.index') }}" class="nav-link {{ request()->routeIs('lte.bookings.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-calendar-check"></i><p>{{ __('Reservasi') }}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('lte.orders.index') }}" class="nav-link {{ request()->routeIs('lte.orders.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-contract"></i><p>{{ __('Order Sewa') }}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('lte.customers.index') }}" class="nav-link {{ request()->routeIs('lte.customers.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-users"></i><p>{{ __('Pelanggan') }}</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('lte.invoices.index') }}" class="nav-link {{ request()->routeIs('lte.invoices.*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-file-invoice-dollar"></i><p>{{ __('Invoice') }}</p>
                        </a>
                    </li>
                    <li class="nav-header">{{ __('Sistem') }}</li>
                    <li class="nav-item">
                        <a href="{{ route('filament.admin.home') }}" class="nav-link">
                            <i class="nav-icon fas fa-cogs"></i><p>{{ __('Panel Legacy (Filament)') }}</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    {{-- Content --}}
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6"><h1 class="m-0">@yield('title', __('Dashboard'))</h1></div>
                    <div class="col-sm-6 text-right">@yield('actions')</div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $errors->first() }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif
                @yield('content')
            </div>
        </section>
    </div>

    {{-- Footer --}}
    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">{{ $brand['name'] }}</div>
        <strong>&copy; {{ date('Y') }} {{ $brand['name'] }}.</strong> {{ $brand['copyright'] }}
    </footer>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('adminlte/js/adminlte.min.js') }}"></script>
@stack('js')
</body>
</html>
