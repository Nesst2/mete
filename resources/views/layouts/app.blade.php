<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <!-- Tambahkan viewport-fit=cover untuk mendukung safe area -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title') Sistem Panel</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <!-- Font Awesome & Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    
    <style>
        /* Global Base Style */
        body {
            font-family: Arial, sans-serif;
        }
        
        /* Optimasi Tabel */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        table {
            width: 100%;
            display: block;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Form Filter & Search Global */
        .filter-form .form-control,
        .filter-form .form-select {
            width: 100%;
        }
        
         /* Main Content (Desktop) */
         .container-fluid.main-content {
            margin-left: 250px; /* Sama dengan lebar sidebar */
            padding: 1rem;
            padding-bottom: calc(70px + env(safe-area-inset-bottom));
            margin-bottom: 50px;
        }
        

        @media (max-width: 768px) {
            .container-fluid.main-content {
                margin-left: 0;
                padding: 1rem;
                padding-bottom: calc(60px + env(safe-area-inset-bottom));
            }
        }
        
        /* Margin bawah untuk elemen pagination */
        .pagination {
            margin-bottom: 80px !important;
        }
    
        /* Sidebar (desktop) */
        .sidebar {
            background-color: #2C3E50;
            min-height: 100vh;
            transition: all 0.3s;
        }
        .sidebar .nav-link {
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .sidebar .nav-link:hover {
            background-color: #34495E;
            text-decoration: none;
        }
        
        /* Bottom Navigation (Mobile) */
        .bottom-nav {
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.2);
            background-color: #2C3E50;
            min-height: calc(80px + env(safe-area-inset-bottom));
            padding-bottom: env(safe-area-inset-bottom);
        }
        .bottom-nav a {
            text-decoration: none;
            color: #ccc;
            font-size: 14px; /* Sedikit lebih besar untuk kenyamanan tap */
        }
        .bottom-nav a:hover {
            color: #fff;
        }
        /* Dropdown Master Data yang diperbesar */
        #bottomMasterDataMenu {
            background-color: #2C3E50;
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            min-width: 160px;
            text-align: center;
            display: none;
            font-size: 18px;
        }
        #bottomMasterDataMenu li {
            margin: 5px 0;
        }
        #bottomMasterDataMenu li a {
            color: #fff;
            padding: 10px 15px;
            display: block;
        }
        #bottomMasterDataMenu li a:hover {
            background-color: #444;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar (tampil di desktop) -->
        @include('layouts.sidebar')
        
        <!-- Main Content -->
        <div class="container-fluid main-content p-4">
            @yield('content')
        </div>
    </div>
    
    <!-- jQuery HARUS sebelum Bootstrap JS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Global Script untuk Dropdown dan Toggle Filter -->
    <script>
        $(document).ready(function() {
            // Toggle dropdown Master Data pada Bottom Navigation
            $('#bottomMasterDataToggle').on('click', function(e) {
                e.preventDefault();
                $('#bottomMasterDataMenu').slideToggle();
            });
            $(document).on('click', function(event) {
                if (!$(event.target).closest('#bottomMasterDataToggle, #bottomMasterDataMenu').length) {
                    $('#bottomMasterDataMenu').slideUp();
                }
            });
            
            // Toggle filter global jika diperlukan
            $('.filter-form-toggle').on('click', function() {
                $('.filter-form').collapse('toggle');
            });
        });
    </script>
    
    <!-- Bottom Navigation Bar (untuk mobile) -->
    <nav class="bottom-nav bg-dark text-white d-md-none d-flex justify-content-around align-items-center py-3" 
         style="position: fixed; bottom: 0; left: 0; width: 100%; border-top: 1px solid #444; z-index: 1000;">
         @if (Auth::user()->role == 'admin')
            <a href="{{ route('admin.dashboard') }}" class="text-white text-center">
                <i class="bi bi-speedometer2 fs-4"></i><br>Dashboard
            </a>
         @else
            <a href="{{ route('sales.dashboard') }}" class="text-white text-center">
                <i class="bi bi-speedometer2 fs-4"></i><br>Dashboard
            </a>
         @endif
        <div class="text-center position-relative">
            <a href="#" class="text-white" id="bottomMasterDataToggle">
                <i class="bi bi-folder2 fs-4"></i><br>Master Data
            </a>
            <!-- Dropdown Master Data -->
            <ul class="dropdown-menu" id="bottomMasterDataMenu" style="position: absolute; bottom: 70px; left: 50%; transform: translateX(-50%);">
                @if(Auth::user()->role == 'admin')
                    <li><a href="{{ route('user.index') }}">User</a></li>
                    <li><a href="{{ route('daerah.index') }}">Daerah</a></li>
                @endif
                <li><a href="{{ route('wilayah.index') }}">Wilayah</a></li>
                <li><a href="{{ route('vendor.index') }}">Vendor</a></li>
                <li><a href="{{ route('request.index') }}">Request</a></li>
            </ul>
        </div>
        <a href="{{ route('tagihan.index') }}" class="text-white text-center">
            <i class="bi bi-receipt fs-4"></i><br>Tagihan
        </a>
        @if(Auth::user()->role == 'admin')
            <a href="{{ route('log_activity.index') }}" class="text-white text-center">
                <i class="bi bi-journal-text fs-4"></i><br>Log
            </a>
        @endif
        <a href="{{ route('logout') }}" class="text-white text-center"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="bi bi-box-arrow-right fs-4"></i><br>Logout
        </a>
        
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </nav>
    
    @stack('scripts')
</body>
</html>
