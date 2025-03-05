<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Micro Biz Admin')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- jQuery (Required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Alpine.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- DataTables CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <style>
        @media (max-width: 768px) {
            .sidebar-hidden {
                margin-left: -16rem;
            }
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 50;
        }

        .dropdown-menu.active {
            display: block;
        }

        .sub-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .sub-menu.active {
            max-height: 200px;
        }

        .sub-menu-item {
            padding-left: 2.5rem;
        }
    </style>
</head>
<body class="bg-gray-100">

<div class="flex h-screen">
    <!-- Sidebar -->
    <div id="sidebar" class="w-64 bg-indigo-900 text-white flex flex-col transition-all duration-300 ease-in-out">
        <div class="p-6">
            <h1 class="text-2xl font-bold">Micro Biz</h1>
        </div>

        <!-- Navigation Menu -->
        <nav class="mt-6 flex-1">
            <div class="px-4 space-y-3">
                <a href="/dashboard" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-indigo-800"
                   id="dashboardLink">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>

                <!-- Products with submenu -->
                <div class="space-y-1">
                    <button class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-indigo-800"
                            onclick="toggleSubmenu('productsSubmenu')" id="productsMenu">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-box"></i>
                            <span>Products</span>
                        </div>
                        <i class="fas fa-chevron-down text-sm transition-transform duration-200"></i>
                    </button>
                    <div id="productsSubmenu" class="sub-menu">
                        <a href="/products"
                           class="flex items-center space-x-3 p-3 sub-menu-item rounded-lg hover:bg-indigo-800"
                           id="addProductLink">
                            <i class="fa fa-angle-right"></i>
                            <span>Add New</span>
                        </a>
                        <a href="/products/list"
                           class="flex items-center space-x-3 p-3 sub-menu-item rounded-lg hover:bg-indigo-800"
                           id="viewProductsLink">
                            <i class="fa fa-angle-right"></i>
                            <span>View All</span>
                        </a>
                    </div>
                </div>

                <!-- Categories with submenu -->
                <div class="space-y-1">
                    <button class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-indigo-800"
                            onclick="toggleSubmenu('categoriesSubmenu')" id="categoriesMenu">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-tags"></i>
                            <span>Categories</span>
                        </div>
                        <i class="fas fa-chevron-down text-sm transition-transform duration-200"></i>
                    </button>
                    <div id="categoriesSubmenu" class="sub-menu">
                        <a href="/microbiz/categories"
                           class="flex items-center space-x-3 p-3 sub-menu-item rounded-lg hover:bg-indigo-800"
                           id="microbizCategoriesLink">
                            <i class="fa fa-angle-right"></i>
                            <span>MicroBiz</span>
                        </a>
                        <a href="/hirepurchase/categories"
                           class="flex items-center space-x-3 p-3 sub-menu-item rounded-lg hover:bg-indigo-800"
                           id="hirePurchaseCategoriesLink">
                            <i class="fa fa-angle-right"></i>
                            <span>Hire Purchase</span>
                        </a>
                    </div>
                </div>

                <a href="/applications" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-indigo-800"
                   id="applicationsLink">
                    <i class="fas fa-window-restore"></i>
                    <span>Applications</span>
                </a>

                <a href="/forms" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-indigo-800"
                   id="formsLink">
                    <i class="fas fa-file-alt"></i>
                    <span>Forms</span>
                </a>
                
                <a href="{{ route('admin.deliveries.index') }}" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-indigo-800"
                   id="deliveriesLink">
                    <i class="fas fa-truck"></i>
                    <span>Deliveries</span>
                </a>

                <div class="space-y-1">
                    <button class="w-full flex items-center justify-between p-3 rounded-lg hover:bg-indigo-800"
                            onclick="toggleSubmenu('agentsSubmenu')" id="agentsMenu">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-users"></i>
                            <span>Agents</span>
                        </div>
                        <i class="fas fa-chevron-down text-sm transition-transform duration-200"></i>
                    </button>
                    <div id="agentsSubmenu" class="sub-menu">
                        <a href="{{ route('agents.index') }}"
                           class="flex items-center space-x-3 p-3 sub-menu-item rounded-lg hover:bg-indigo-800"
                           id="viewAgentsLink">
                            <i class="fa fa-angle-right"></i>
                            <span>View Agents</span>
                        </a>
                        <a href="{{ route('agents.create') }}"
                           class="flex items-center space-x-3 p-3 sub-menu-item rounded-lg hover:bg-indigo-800"
                           id="addAgentLink">
                            <i class="fa fa-angle-right"></i>
                            <span>Add Agent</span>
                        </a>
                    </div>
                </div>

                <a href="/settings" class="flex items-center space-x-3 p-3 rounded-lg hover:bg-indigo-800"
                   id="settingsLink">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <header class="bg-gray-100 shadow-sm">
            <div class="flex items-center justify-between p-4">
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
            </div>
        </header>

        <!-- Main Content Section -->
        <main class="p-6 bg-white flex-1 overflow-y-auto">
            @yield('content')
        </main>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // **PROFILE DROPDOWN TOGGLE**
        const profileDropdown = document.getElementById('profileDropdown');
        const profileMenu = document.getElementById('profileMenu');

        profileDropdown.addEventListener('click', function (event) {
            event.stopPropagation(); // Prevents click from closing immediately
            profileMenu.classList.toggle('active'); // Toggle dropdown visibility
        });

        document.addEventListener('click', function (event) {
            if (!profileDropdown.contains(event.target) && !profileMenu.contains(event.target)) {
                profileMenu.classList.remove('active'); // Close dropdown when clicking outside
            }
        });

        // Ensure dropdown menu is properly styled when active
        const dropdownStyle = document.createElement("style");
        dropdownStyle.innerHTML = `
            .dropdown-menu { display: none; }
            .dropdown-menu.active { display: block; }
        `;
        document.head.appendChild(dropdownStyle);

        // **SIDEBAR TOGGLE**
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');

        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('sidebar-hidden');
        });

        // **SUBMENU TOGGLE**
        window.toggleSubmenu = function (submenuId) {
            const submenu = document.getElementById(submenuId);
            submenu.classList.toggle('active');

            // Find the chevron inside the button and rotate it when expanded
            const chevron = document.querySelector(`#${submenuId}`).previousElementSibling.querySelector('.fa-chevron-down');
            if (submenu.classList.contains('active')) {
                chevron.style.transform = 'rotate(180deg)';
            } else {
                chevron.style.transform = 'rotate(0deg)';
            }
        };

        // **HIGHLIGHT ACTIVE MENU ITEMS**
        let path = window.location.pathname;

        if (path.includes('/dashboard')) {
            document.getElementById('dashboardLink').classList.add('bg-indigo-800', 'border-l-4', 'border-white');
        }
        if (path.includes('/products')) {
            document.getElementById('productsMenu').classList.add('bg-indigo-800', 'border-l-4', 'border-white');
            document.getElementById('productsSubmenu').classList.add('active');

            if (path.includes('/products/list')) {
                document.getElementById('viewProductsLink').classList.add('bg-indigo-800');
            } else {
                document.getElementById('addProductLink').classList.add('bg-indigo-800');
            }
        }
        if (path.includes('/agents')) {
            document.getElementById('agentsMenu').classList.add('bg-indigo-800', 'border-l-4', 'border-white');
            document.getElementById('agentsSubmenu').classList.add('active');

            if (path.includes('/agents/create')) {
                document.getElementById('addAgentLink').classList.add('bg-indigo-800');
            } else {
                document.getElementById('viewAgentsLink').classList.add('bg-indigo-800');
            }
        }
        if (path.includes('/categories') || path.includes('/microbiz/categories') || path.includes('/hirepurchase/categories')) {
            document.getElementById('categoriesMenu').classList.add('bg-indigo-800', 'border-l-4', 'border-white');
            document.getElementById('categoriesSubmenu').classList.add('active');

            if (path.includes('/microbiz/categories')) {
                document.getElementById('microbizCategoriesLink').classList.add('bg-indigo-800');
            } else if (path.includes('/hirepurchase/categories')) {
                document.getElementById('hirePurchaseCategoriesLink').classList.add('bg-indigo-800');
            }
        }
        if (path.includes('/applications')) {
            document.getElementById('applicationsLink').classList.add('bg-indigo-800', 'border-l-4', 'border-white');
        }
        if (path.includes('/forms')) {
            document.getElementById('formsLink').classList.add('bg-indigo-800', 'border-l-4', 'border-white');
        }
        if (path.includes('/admin/deliveries')) {
            document.getElementById('deliveriesLink').classList.add('bg-indigo-800', 'border-l-4', 'border-white');
        }
        if (path.includes('/settings')) {
            document.getElementById('settingsLink').classList.add('bg-indigo-800', 'border-l-4', 'border-white');
        }
    });
</script>
@stack('scripts')
</body>
</html>
