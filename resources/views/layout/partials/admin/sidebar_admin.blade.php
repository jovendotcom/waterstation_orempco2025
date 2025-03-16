<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion" style="background: linear-gradient(180deg, #28a745, #cddc39);">
    <div class="sb-sidenav-menu">

        <div class="nav-item text-white">
            <br>
            <a class="nav-link text-white">
                &nbsp&nbsp&nbsp&nbsp&nbsp<strong>ADMIN MODULE</strong> <!-- This is the text you want at the top -->
            </a>
        </div>

        <div class="nav">
            <br>
            <!-- Dashboard Link -->
            <a class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <div class="sb-nav-link-icon text-white">
                    <!-- Dashboard Icon SVG -->
                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="grid-2" role="img"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" class="svg-inline--fa fa-grid-2 fa-lg">
                        <path fill="currentColor"
                            d="M224 80c0-26.5-21.5-48-48-48L80 32C53.5 32 32 53.5 32 80l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96zm0 256c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96zM288 80l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48zM480 336c0-26.5-21.5-48-48-48l-96 0c-26.5 0-48 21.5-48 48l0 96c0 26.5 21.5 48 48 48l96 0c26.5 0 48-21.5 48-48l0-96z"
                            class=""></path>
                    </svg>
                </div>
                Dashboard
            </a>

            <a class="nav-link text-white {{ request()->routeIs('admin.usermanagement') ? 'active' : '' }}" href="{{ route('admin.usermanagement')}}">
                <div class="sb-nav-link-icon text-white"><i class="fa-solid fas fa-users"></i></div>
                User Management
            </a>

            <a class="nav-link text-white {{ request()->routeIs('admin.credit_transaction') ? 'active' : '' }}" href="{{ route('admin.credit_transaction') }}">
                <div class="sb-nav-link-icon text-white"><i class="fa-solid fa-file-waveform"></i></div>
                Credit Sales
            </a>

            <a class="nav-link text-white {{ request()->routeIs('admin.categories') ? 'active' : '' }}" href="{{ route('admin.categories') }}">
                <div class="sb-nav-link-icon text-white"><i class="fa-sharp-duotone fa-solid fa-droplet"></i></div>
                Category Management
            </a>

            <a class="nav-link text-white {{ request()->routeIs('admin.productInventory') ? 'active' : '' }}" href="{{ route('admin.productInventory') }}">
                <div class="sb-nav-link-icon text-white"><i class="fa-sharp-duotone fa-solid fa-droplet"></i></div>
                Product Inventory
            </a>

            <a class="nav-link text-white {{ request()->routeIs('admin.stocksCount') ? 'active' : '' }}" href="{{ route('admin.stocksCount') }}">
                <div class="sb-nav-link-icon text-white"><i class="fa-solid fa-arrow-trend-up"></i></div>
                Stocks Count
            </a>

            <a class="nav-link text-white {{ request()->routeIs('admin.customerlist') ? 'active' : '' }}" href="{{ route('admin.customerlist') }}">
                <div class="sb-nav-link-icon text-white"><i class="fa-solid fa-person-dress"></i></div>
                Customers List
            </a>

            <a class="nav-link text-white {{ request()->routeIs('sales.stocksCount') ? 'active' : '' }}" href="{{ route('sales.stocksCount') }}">
                <div class="sb-nav-link-icon text-white"><i class="fas fa-dollar-sign"></i></div>
                Reports
            </a>

            <a class="nav-link text-white {{ request()->routeIs('admin.userProfile') ? 'active' : '' }}" href="{{ route('admin.userProfile') }}">
                <div class="sb-nav-link-icon text-white"><i class="fa-regular fa-user"></i></div>
                User Profile
            </a>

            <!-- Add more admin links with respective icons here -->
        </div>
    </div>
    <div class="sb-sidenav-footer bg-transparent text-white">
        <div class="small">Logged in as:</div>
        @auth('admin')
            {{ Auth::guard('admin')->user()->full_name }}
        @else
            Guest
        @endauth
    </div>
</nav>
