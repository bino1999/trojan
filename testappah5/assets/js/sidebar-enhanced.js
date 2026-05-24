// Enhanced Sidebar JavaScript
document.addEventListener('DOMContentLoaded', function() {
    console.log('Enhanced Sidebar JavaScript loaded');
    
    // Initialize sidebar functionality
    initializeSidebar();
    
    // Handle mobile menu toggle
    const mobileToggle = document.getElementById('vertical-hover');
    if (mobileToggle) {
        mobileToggle.addEventListener('click', toggleMobileSidebar);
    }
    
    // Handle window resize
    window.addEventListener('resize', handleResize);
});

// Initialize sidebar functionality
function initializeSidebar() {
    console.log('Initializing sidebar...');
    
    // Debug: Check all submenus
    const allSubmenus = document.querySelectorAll('.submenu');
    console.log('Found submenus:', allSubmenus.length);
    allSubmenus.forEach((submenu, index) => {
        console.log(`Submenu ${index}:`, submenu.id, 'Children:', submenu.children.length);
    });
    
    // Set initial state based on active menu items
    const activeCategories = document.querySelectorAll('.main-category-header.active');
    console.log('Found active categories:', activeCategories.length);
    
    activeCategories.forEach(category => {
        const onclickAttr = category.getAttribute('onclick');
        if (onclickAttr) {
            const match = onclickAttr.match(/'([^']+)'/);
            if (match) {
                const categoryId = match[1];
                const submenu = document.getElementById(categoryId);
                if (submenu) {
                    submenu.classList.add('open');
                    console.log('Opened category:', categoryId);
                }
            }
        }
    });
}

// Toggle category dropdown
function toggleCategory(categoryId) {
    console.log('toggleCategory called with:', categoryId);
    
    const submenu = document.getElementById(categoryId);
    const header = document.querySelector(`[onclick="toggleCategory('${categoryId}')"]`);
    
    console.log('Found submenu:', submenu);
    console.log('Found header:', header);
    
    if (submenu) {
        console.log('Submenu children count:', submenu.children.length);
        console.log('Submenu HTML:', submenu.innerHTML);
    }
    
    if (!submenu || !header) {
        console.error('Could not find submenu or header for category:', categoryId);
        return;
    }
    
    // Close all other categories
    const allSubmenus = document.querySelectorAll('.submenu');
    const allHeaders = document.querySelectorAll('.main-category-header');
    
    allSubmenus.forEach(menu => {
        if (menu.id !== categoryId) {
            menu.classList.remove('open');
        }
    });
    
    allHeaders.forEach(h => {
        if (h !== header) {
            h.classList.remove('active');
        }
    });
    
    // Toggle current category
    const isOpen = submenu.classList.contains('open');
    console.log('Category is currently open:', isOpen);
    
    if (isOpen) {
        submenu.classList.remove('open');
        header.classList.remove('active');
        console.log('Closed category:', categoryId);
    } else {
        submenu.classList.add('open');
        header.classList.add('active');
        console.log('Opened category:', categoryId);
    }
    
    // Store state in localStorage
    localStorage.setItem('sidebarState', JSON.stringify({
        openCategory: isOpen ? null : categoryId
    }));
}

// Toggle mobile sidebar
function toggleMobileSidebar() {
    const sidebar = document.querySelector('.sidebar-enhanced');
    if (sidebar) {
        sidebar.classList.toggle('mobile-open');
    }
}

// Handle window resize
function handleResize() {
    const sidebar = document.querySelector('.sidebar-enhanced');
    if (window.innerWidth > 768) {
        sidebar.classList.remove('mobile-open');
    }
}

// Restore sidebar state from localStorage
function restoreSidebarState() {
    const savedState = localStorage.getItem('sidebarState');
    if (savedState) {
        try {
            const state = JSON.parse(savedState);
            if (state.openCategory) {
                const submenu = document.getElementById(state.openCategory);
                const header = document.querySelector(`[onclick="toggleCategory('${state.openCategory}')"]`);
                
                if (submenu && header) {
                    submenu.classList.add('open');
                    header.classList.add('active');
                }
            }
        } catch (e) {
            console.warn('Could not restore sidebar state:', e);
        }
    }
}

// Smooth scroll for submenu items
function smoothScrollToActive() {
    const activeItem = document.querySelector('.submenu-item.active');
    if (activeItem) {
        activeItem.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
        });
    }
}

// Add keyboard navigation support
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Close all categories on escape
        const allSubmenus = document.querySelectorAll('.submenu');
        const allHeaders = document.querySelectorAll('.main-category-header');
        
        allSubmenus.forEach(menu => menu.classList.remove('open'));
        allHeaders.forEach(header => header.classList.remove('active'));
    }
});

// Initialize on page load
window.addEventListener('load', function() {
    restoreSidebarState();
    smoothScrollToActive();
});

// Add hover effects for better UX
document.addEventListener('DOMContentLoaded', function() {
    const submenuItems = document.querySelectorAll('.submenu-item');
    
    submenuItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        item.addEventListener('mouseleave', function() {
            if (!this.classList.contains('active')) {
                this.style.transform = 'translateX(0)';
            }
        });
    });
});

// Add loading animation
function showLoadingAnimation() {
    const sidebar = document.querySelector('.sidebar-enhanced');
    if (sidebar) {
        sidebar.style.opacity = '0.7';
        sidebar.style.pointerEvents = 'none';
        
        setTimeout(() => {
            sidebar.style.opacity = '1';
            sidebar.style.pointerEvents = 'auto';
        }, 300);
    }
}

// Export functions for global access
window.toggleCategory = toggleCategory;
window.toggleMobileSidebar = toggleMobileSidebar;
