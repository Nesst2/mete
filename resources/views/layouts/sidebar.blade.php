<nav class="sidebar bg-dark text-white vh-100 p-3 d-none d-md-block" style="width: 250px; overflow-y: auto; position: fixed;">
    <h4 class="text-center">Maunya Metee</h4>
    <ul class="nav flex-column">
        @if(Auth::user()->role == 'admin')
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link text-white">
                    <i class="bi bi-speedometer2"></i> Dashboard Admin
                </a>
            </li>
        @else
            <li class="nav-item">
                <a href="{{ route('sales.dashboard') }}" class="nav-link text-white">
                    <i class="bi bi-speedometer2"></i> Dashboard Sales
                </a>
            </li>
        @endif

        <!-- Master Data -->
        <li class="nav-item">
            <button class="nav-link text-white w-100 text-start" data-bs-toggle="collapse" data-bs-target="#masterData" aria-expanded="false">
                <i class="bi bi-folder2"></i> Master Data ▾
            </button>
            <ul class="collapse list-unstyled ps-3" id="masterData">
                @if(Auth::user()->role == 'admin')
                    <li>
                        <a href="{{ route('user.index') }}" class="nav-link text-white">
                            <i class="bi bi-person"></i> User
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('daerah.index') }}" class="nav-link text-white">
                            <i class="bi bi-geo"></i> Daerah
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ route('wilayah.index') }}" class="nav-link text-white">
                        <i class="bi bi-map"></i> Wilayah
                    </a>
                </li> 
                <li>
                    <a href="{{ route('vendor.index') }}" class="nav-link text-white">
                        <i class="bi bi-shop"></i> Vendor
                    </a>
                </li>   
                <li>
                    <a href="{{ route('request.index') }}" class="nav-link text-white">
                        <i class="bi bi-envelope"></i> Request
                    </a>
                </li>             
            </ul>
        </li>

        <!-- Tagihan -->
        <li class="nav-item">
            <a href="{{ route('tagihan.index') }}" class="nav-link text-white">
                <i class="bi bi-receipt"></i> Tagihan
            </a>
        </li>

        <!-- Log Activity (Admin Only) -->
        @if(Auth::user()->role == 'admin')
            <li class="nav-item">
                <a href="{{ route('log_activity.index') }}" class="nav-link text-white">
                    <i class="bi bi-journal-text"></i> Log Activity
                </a>
            </li>
        @endif

        <!-- Logout Button -->
        <li class="nav-item mt-auto">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger w-100">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </li>
    </ul>
</nav>
