/**
 * Admin Navigation System
 * Handles sidebar functionality, submenu toggling, and active state management
 */

document.addEventListener('DOMContentLoaded', function() {
    // Profile dropdown toggling
    initProfileDropdown();
    
    // Sidebar toggling
    initSidebarToggle();
    
    // Active menu highlighting based on URL
    highlightActiveMenus();
});

/**
 * Initialize the profile dropdown toggle functionality
 */
function initProfileDropdown() {
    const profileDropdown = document.getElementById('profileDropdown');
    const profileMenu = document.getElementById('profileMenu');
    
    if (!profileDropdown || !profileMenu) return;
    
    profileDropdown.addEventListener('click', function(event) {
        event.stopPropagation();
        profileMenu.classList.toggle('active');
    });
    
    document.addEventListener('click', function(event) {
        if (!profileDropdown.contains(event.target) && !profileMenu.contains(event.target)) {
            profileMenu.classList.remove('active');
        }
    });
}

/**
 * Initialize sidebar toggle functionality
 */
function initSidebarToggle() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    if (!sidebar || !sidebarToggle) return;
    
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('sidebar-hidden');
    });
}

/**
 * Toggle submenu visibility
 * @param {string} submenuId - The ID of the submenu to toggle
 */
window.toggleSubmenu = function(submenuId) {
    const submenu = document.getElementById(submenuId);
    if (!submenu) {
        console.warn(`Submenu with ID "${submenuId}" not found`);
        return;
    }
    
    // Toggle both possible class names for backward compatibility
    submenu.classList.toggle('open');
    submenu.classList.toggle('active');
    
    // Log the class list for debugging
    console.log(`Submenu ${submenuId} classes:`, submenu.className);
    
    // Handle chevron rotation
    const button = submenu.previousElementSibling;
    if (!button) return;
    
    const chevron = button.querySelector('.fa-chevron-down');
    if (!chevron) return;
    
    // Check for either class name
    if (submenu.classList.contains('open') || submenu.classList.contains('active')) {
        chevron.style.transform = 'rotate(180deg)';
    } else {
        chevron.style.transform = 'rotate(0deg)';
    }
};

/**
 * Highlight active menu items based on current URL
 */
function highlightActiveMenus() {
    const path = window.location.pathname;
    
    // Dashboard
    if (path.includes('/dashboard')) {
        activateLink('dashboardLink');
    }
    
    // Products
    if (path.includes('/products')) {
        activateMenu('productsMenu', 'productsSubmenu');
        
        if (path.includes('/products/list')) {
            activateLink('viewProductsLink');
        } else {
            activateLink('addProductLink');
        }
    }
    
    // Agents
    if (path.includes('/agents')) {
        activateMenu('agentsMenu', 'agentsSubmenu');
        
        if (path.includes('/agents/create')) {
            activateLink('addAgentLink');
        } else {
            activateLink('viewAgentsLink');
        }
    }
    
    // Categories
    if (path.includes('/categories') || path.includes('/microbiz/categories') || path.includes('/hirepurchase/categories')) {
        activateMenu('categoriesMenu', 'categoriesSubmenu');
        
        if (path.includes('/microbiz/categories')) {
            activateLink('microbizCategoriesLink');
        } else if (path.includes('/hirepurchase/categories')) {
            activateLink('hirePurchaseCategoriesLink');
        }
    }
    
    // Applications
    if (path.includes('/applications')) {
        activateLink('applicationsLink');
    }
    
    // Forms
    if (path.includes('/forms')) {
        activateLink('formsLink');
    }
    
    // Purchase Orders
    if (path.includes('/purchase-orders')) {
        activateMenu('poMenu', 'poSubmenu');
        
        if (path.includes('/purchase-orders/create')) {
            activateLink('createPoLink');
        } else {
            activateLink('viewPoLink');
        }
    }
    
    // Inventory
    if (path.includes('/inventory')) {
        activateMenu('inventoryMenu', 'inventorySubmenu');
        
        if (path.includes('/inventory/warehouses')) {
            activateLink('manageWarehousesLink');
        } else if (path.includes('/inventory/transfers')) {
            activateLink('transfersLink');
        } else if (path.includes('/inventory/grn')) {
            activateLink('grnLink');
        } else if (path.includes('/inventory/search')) {
            activateLink('inventorySearchLink');
        } else {
            activateLink('viewInventoryLink');
        }
    }
    
    // Deliveries
    if (path.includes('/admin/deliveries')) {
        activateLink('deliveriesLink');
    }
    
    // Commissions
    if (path.includes('/commissions') || path.includes('/commission-payments')) {
        activateMenu('commissionsMenu', 'commissionsSubmenu');
        
        if (path.includes('/commissions/agent-report')) {
            activateLink('agentReportLink');
        } else if (path.includes('/commissions/team-report')) {
            activateLink('teamReportLink');
        } else if (path.includes('/commissions/payment/create')) {
            activateLink('processPaymentLink');
        } else {
            activateLink('viewCommissionsLink');
        }
    }
    
    // Teams
    if (path.includes('/teams')) {
        activateMenu('agentsMenu', 'agentsSubmenu');
        activateLink('teamsLink');
    }
    
    // Settings
    if (path.includes('/settings')) {
        activateLink('settingsLink');
    }
}

/**
 * Activate a submenu toggle button and expand the submenu
 * @param {string} menuId - The ID of the menu button to activate
 * @param {string} submenuId - The ID of the submenu to expand
 */
function activateMenu(menuId, submenuId) {
    const menu = document.getElementById(menuId);
    const submenu = document.getElementById(submenuId);
    
    if (menu) {
        menu.classList.add('active');
        console.log(`Activated menu: ${menuId}`);
    } else {
        console.warn(`Menu with ID "${menuId}" not found`);
    }
    
    if (submenu) {
        submenu.classList.add('open');
        submenu.classList.add('active'); // Add both for backward compatibility
        console.log(`Opened submenu: ${submenuId}`);
    } else {
        console.warn(`Submenu with ID "${submenuId}" not found`);
    }
}

/**
 * Activate a menu link
 * @param {string} linkId - The ID of the link to activate
 */
function activateLink(linkId) {
    const link = document.getElementById(linkId);
    if (link) {
        link.classList.add('active');
        console.log(`Activated link: ${linkId}`);
    } else {
        console.warn(`Link with ID "${linkId}" not found`);
    }
}

// Fix legacy class issues
function fixLegacyClasses() {
    // Find all submenus and handle their classes
    document.querySelectorAll('.submenu, .sub-menu').forEach(el => {
        // Make sure open submenus are actually open
        if (el.closest('.open') || el.classList.contains('active')) {
            el.classList.add('open');
        }
    });
    
    // Convert any old classes to active class
    document.querySelectorAll('[class*="bg-indigo-800"], [class*="border-l-4"], [class*="border-white"]').forEach(el => {
        el.classList.remove('bg-indigo-800', 'border-l-4', 'border-white');
        el.classList.add('active');
    });
}

// Run the legacy class fixes immediately
fixLegacyClasses();

// Run again after a slight delay to catch any dynamically rendered elements
setTimeout(fixLegacyClasses, 200);