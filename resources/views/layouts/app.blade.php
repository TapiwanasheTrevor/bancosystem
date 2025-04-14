<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <base href="{{ url('/') }}">
    <title>@yield('title', 'Micro Biz Admin')</title>
    @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" />

    <!-- jQuery (Required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Alpine.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- DataTables CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-background text-foreground antialiased">

<div class="flex h-screen">
    <!-- Sidebar -->
    <div id="sidebar" class="admin-sidebar">
        <div class="logo">
            <h1>Micro Biz</h1>
        </div>

        <!-- Navigation Menu -->
        <nav style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.2) transparent;">
            <div class="px-2 space-y-1" id="mainMenu">
                <a href="/dashboard" class="flex items-center space-x-2 py-2 px-3 text-sm rounded-lg hover:bg-indigo-800"
                   id="dashboardLink">
                    <i class="fas fa-home text-xs"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Products with submenu -->
                <div>
                    <button class="submenu-toggle" type="button" id="productsMenu">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-box text-xs"></i>
                            <span>Products</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div id="productsSubmenu" class="submenu">
                        <a href="/products"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="addProductLink">
                            <span>Add New</span>
                        </a>
                        <a href="/products/list"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="viewProductsLink">
                            <span>View All</span>
                        </a>
                    </div>
                </div>

                <!-- Categories with submenu -->
                <div>
                    <button class="submenu-toggle" type="button" id="categoriesMenu">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-tags text-xs"></i>
                            <span>Categories</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div id="categoriesSubmenu" class="submenu">
                        <a href="/microbiz/categories"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="microbizCategoriesLink">
                            <span>MicroBiz</span>
                        </a>
                        <a href="/hirepurchase/categories"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="hirePurchaseCategoriesLink">
                            <span>Hire Purchase</span>
                        </a>
                    </div>
                </div>

                <a href="/applications" class="flex items-center space-x-2 py-2 px-3 text-sm rounded-lg hover:bg-indigo-800"
                   id="applicationsLink">
                    <i class="fas fa-window-restore text-xs"></i>
                    <span>Applications</span>
                </a>

                <a href="/forms" class="flex items-center space-x-2 py-2 px-3 text-sm rounded-lg hover:bg-indigo-800"
                   id="formsLink">
                    <i class="fas fa-file-alt text-xs"></i>
                    <span>Forms</span>
                </a>

                <!-- Purchase Orders submenu -->
                <div>
                    <button class="submenu-toggle" type="button" id="poMenu">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-file-invoice text-xs"></i>
                            <span>Purchase Orders</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div id="poSubmenu" class="submenu">
                        <a href="{{ route('purchase-orders.create') }}"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="createPoLink">
                            <span>Create New</span>
                        </a>
                        <a href="{{ route('purchase-orders.index') }}"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="viewPoLink">
                            <span>View All</span>
                        </a>
                    </div>
                </div>

                <!-- Inventory submenu -->
                <div>
                    <button class="submenu-toggle" type="button" id="inventoryMenu">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-warehouse text-xs"></i>
                            <span>Inventory</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div id="inventorySubmenu" class="submenu">
                        <a href="{{ route('inventory.index') }}"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="viewInventoryLink">
                            <span>View Stock</span>
                        </a>
                        <a href="{{ route('inventory.warehouses.manage') }}"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="manageWarehousesLink">
                            <span>Warehouses</span>
                        </a>
                        <a href="{{ route('inventory.transfers.create') }}"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="transfersLink">
                            <span>Transfers</span>
                        </a>
                        <a href="{{ route('inventory.grn.create') }}"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="grnLink">
                            <span>Goods Receiving</span>
                        </a>
                        <a href="{{ route('inventory.search') }}"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="inventorySearchLink">
                            <span>Search</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('admin.deliveries.index') }}" class="flex items-center space-x-2 py-2 px-3 text-sm rounded-lg hover:bg-indigo-800"
                   id="deliveriesLink">
                    <i class="fas fa-truck text-xs"></i>
                    <span>Deliveries</span>
                </a>

                <!-- Agents submenu -->
                <div>
                    <button class="submenu-toggle" type="button" id="agentsMenu">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-users text-xs"></i>
                            <span>Agents</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div id="agentsSubmenu" class="submenu">
                        <a href="{{ route('agents.index') }}"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="viewAgentsLink">
                            <span>View Agents</span>
                        </a>
                        <a href="{{ route('agents.create') }}"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="addAgentLink">
                            <span>Add Agent</span>
                        </a>
                        <a href="{{ route('teams.index') }}"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="teamsLink">
                            <span>Teams</span>
                        </a>
                    </div>
                </div>

                <!-- Commissions submenu -->
                <div>
                    <button class="submenu-toggle" type="button" id="commissionsMenu">
                        <div class="flex items-center space-x-2">
                            <i class="fas fa-money-bill-wave text-xs"></i>
                            <span>Commissions</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div id="commissionsSubmenu" class="submenu">
                        <a href="{{ route('commissions.index') }}"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="viewCommissionsLink">
                            <span>View All</span>
                        </a>
                        <a href="{{ route('commissions.agent-report') }}"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="agentReportLink">
                            <span>Agent Reports</span>
                        </a>
                        <a href="{{ route('commissions.team-report') }}"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="teamReportLink">
                            <span>Team Reports</span>
                        </a>
                        <a href="{{ route('commissions.payment.create') }}"
                           class="flex items-center space-x-2 py-1 px-3 text-sm rounded-lg hover:bg-indigo-800"
                           id="processPaymentLink">
                            <span>Process Payment</span>
                        </a>
                    </div>
                </div>

                <a href="/settings" class="flex items-center space-x-2 py-2 px-3 text-sm rounded-lg hover:bg-indigo-800 mb-4"
                   id="settingsLink">
                    <i class="fas fa-cog text-xs"></i>
                    <span>Settings</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <header class="admin-header">
            <div class="flex items-center space-x-4">
                <button id="sidebarToggle" class="text-gray-500 hover:text-gray-600 focus:outline-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Profile Dropdown -->
            <div class="relative">
                <button id="profileDropdown" class="flex items-center space-x-2 focus:outline-none">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=4f46e5&color=fff&size=32"
                         alt="Profile" class="w-8 h-8 rounded-full">
                    <span class="font-medium text-sm mr-1 hidden sm:inline">Admin</span>
                    <i class="fas fa-chevron-down text-gray-500"></i>
                </button>
                <div id="profileMenu" class="dropdown-menu hidden mt-2 w-48 bg-white rounded-md shadow-lg py-1">
                    <a href="/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                    <a href="/change-password"
                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-key mr-2"></i> Change Password
                    </a>
                    <div class="border-t border-gray-100"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Content Section -->
        <main class="admin-content">
            @yield('content')
        </main>
    </div>
</div>
<!-- Navigation script loaded via Vite -->
@stack('scripts')

<!-- Direct fix for submenus -->
<script>
    // Define the toggleSubmenu function directly in the global scope
    window.toggleSubmenu = function(submenuId) {
        console.log('Toggle submenu called for:', submenuId);
        const submenu = document.getElementById(submenuId);
        if (!submenu) {
            console.error('Submenu not found:', submenuId);
            return;
        }

        // Toggle between showing/hiding the submenu
        if (submenu.style.maxHeight) {
            // Currently open, so close it
            submenu.style.maxHeight = null;
            submenu.classList.remove('open');
            submenu.classList.remove('active');

            // Find the toggle button and rotate the chevron back
            const toggleButton = document.getElementById(submenuId).previousElementSibling;
            if (toggleButton) {
                toggleButton.classList.remove('active');

                const chevron = toggleButton.querySelector('.fa-chevron-down');
                if (chevron) {
                    chevron.style.transform = 'rotate(0deg)';
                }
            }
        } else {
            // Currently closed, so open it
            submenu.style.maxHeight = '500px';
            submenu.classList.add('open');
            submenu.classList.add('active');

            // Find the toggle button and rotate the chevron
            const toggleButton = document.getElementById(submenuId).previousElementSibling;
            if (toggleButton) {
                toggleButton.classList.add('active');

                const chevron = toggleButton.querySelector('.fa-chevron-down');
                if (chevron) {
                    chevron.style.transform = 'rotate(180deg)';
                }
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function() {
        // Immediately apply fixes
        document.querySelectorAll('.sub-menu, .submenu').forEach(menu => {
            // Make sure it has the right padding
            menu.classList.add('pl-4');

            // Get the current URL path
            const path = window.location.pathname;

            // Check if this submenu should be open based on URL
            if ((menu.id === 'productsSubmenu' && path.includes('/products')) ||
                (menu.id === 'categoriesSubmenu' && (path.includes('/categories') || path.includes('/microbiz') || path.includes('/hirepurchase'))) ||
                (menu.id === 'poSubmenu' && path.includes('/purchase-orders')) ||
                (menu.id === 'inventorySubmenu' && path.includes('/inventory')) ||
                (menu.id === 'agentsSubmenu' && (path.includes('/agents') || path.includes('/teams'))) ||
                (menu.id === 'commissionsSubmenu' && (path.includes('/commissions') || path.includes('/commission-payments')))) {

                // Open this submenu
                menu.style.maxHeight = '500px';
                menu.classList.add('open');
                menu.classList.add('active');

                // Mark the button as active
                const toggleButton = menu.previousElementSibling;
                if (toggleButton) {
                    toggleButton.classList.add('active');

                    // Rotate the chevron
                    const chevron = toggleButton.querySelector('.fa-chevron-down');
                    if (chevron) {
                        chevron.style.transform = 'rotate(180deg)';
                    }
                }
            }
        });

        // Mark active links
        document.querySelectorAll('a').forEach(link => {
            if (link.getAttribute('href') === window.location.pathname) {
                link.classList.add('active');
            }
        });

        // Fix all submenu toggle buttons
        document.querySelectorAll('.submenu-toggle').forEach(button => {
            // Remove the old onclick handler
            button.removeAttribute('onclick');

            // Add a new direct click event listener
            button.addEventListener('click', function(event) {
                event.preventDefault();

                // Find the submenu ID by looking at the button's ID or the next element
                let submenuId;
                if (this.id === 'productsMenu') submenuId = 'productsSubmenu';
                else if (this.id === 'categoriesMenu') submenuId = 'categoriesSubmenu';
                else if (this.id === 'poMenu') submenuId = 'poSubmenu';
                else if (this.id === 'inventoryMenu') submenuId = 'inventorySubmenu';
                else if (this.id === 'agentsMenu') submenuId = 'agentsSubmenu';
                else if (this.id === 'commissionsMenu') submenuId = 'commissionsSubmenu';
                else {
                    // Try to find the next sibling element which should be the submenu
                    const nextEl = this.nextElementSibling;
                    if (nextEl && (nextEl.classList.contains('submenu') || nextEl.classList.contains('sub-menu'))) {
                        submenuId = nextEl.id;
                    }
                }

                if (submenuId) {
                    console.log('Toggling submenu via click handler:', submenuId);
                    toggleSubmenu(submenuId);
                } else {
                    console.error('Could not determine submenu ID for button:', this);
                }
            });

            // Log that we've attached a listener
            console.log('Attached click listener to:', button.id);
        });
    });
</script>
</body>
</html>
