<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Micro Biz Admin')</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @media (max-width: 768px) {
            .sidebar-hidden {
                margin-left: -16rem;
            }
        }

        .dropdown-menu {
            display: none;
        }

        .dropdown-menu.active {
            display: block;
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
                <a href="{{ route('dashboard') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-indigo-800 {{ request()->routeIs('dashboard') ? 'bg-indigo-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('products') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-indigo-800 {{ request()->routeIs('products*') ? 'bg-indigo-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-box"></i>
                    <span>Products</span>
                </a>
                <a href="{{ route('applications') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-indigo-800 {{ request()->routeIs('applications*') ? 'bg-indigo-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-window-restore"></i>
                    <span>Applications</span>
                </a>
                <a href="{{ route('forms') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-indigo-800 {{ request()->routeIs('forms*') ? 'bg-indigo-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-file-alt"></i>
                    <span>Forms</span>
                </a>
                <a href="{{ route('agents') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-indigo-800 {{ request()->routeIs('agents*') ? 'bg-indigo-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Agents</span>
                </a>
                <a href="{{ route('settings') }}"
                   class="flex items-center space-x-3 p-3 rounded-lg hover:bg-indigo-800 {{ request()->routeIs('settings*') ? 'bg-indigo-800 border-l-4 border-white' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <header class="bg-white shadow-sm">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center space-x-4">
                    <button id="sidebarToggle" class="text-gray-500 hover:text-gray-600 focus:outline-none">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="relative">
                        <input type="text" placeholder="Search..."
                               class="w-64 pl-10 pr-4 py-2 rounded-lg border focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <!-- Profile Dropdown -->
                <div class="relative">
                    <button id="profileDropdown" class="flex items-center space-x-2 focus:outline-none">
                        <img src="/api/placeholder/32/32" alt="Profile" class="w-8 h-8 rounded-full">
                        <i class="fas fa-chevron-down text-gray-500"></i>
                    </button>
                    <div id="profileMenu"
                         class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                        <a href="{{ route('profile.edit') }}"
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        <a href="#"
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-key mr-2"></i> Change Password
                        </a>
                        <a href="#"
                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-lock mr-2"></i> Lock Screen
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

        <!-- Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
            <div class="container mx-auto px-6 py-8">
                <!-- Breadcrumb -->
                <div class="flex items-center text-gray-600 text-sm mb-6">
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600">Dashboard</a>
                    <span class="mx-2">/</span>
                    @yield('breadcrumb')
                </div>

                <!-- Content -->
                @yield('content')
            </div>
        </main>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        let isMobile = window.innerWidth <= 768;

        function toggleSidebar() {
            sidebar.classList.toggle('sidebar-hidden');
        }

        sidebarToggle.addEventListener('click', toggleSidebar);

        // Profile Dropdown Toggle
        const profileDropdown = document.getElementById('profileDropdown');
        const profileMenu = document.getElementById('profileMenu');

        profileDropdown.addEventListener('click', function (e) {
            e.stopPropagation();
            profileMenu.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (!profileDropdown.contains(e.target)) {
                profileMenu.classList.remove('active');
            }
        });

        // Handle window resize
        window.addEventListener('resize', function () {
            const wasModile = isMobile;
            isMobile = window.innerWidth <= 768;

            if (wasModile !== isMobile) {
                sidebar.classList.remove('sidebar-hidden');
            }
        });
    });
</script>
@stack('scripts')
</body>
</html>
