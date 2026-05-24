// Simple Sidebar Scroll Fix
// Only prevents the sidebar from jumping to top when clicking menu items

document.addEventListener('DOMContentLoaded', function() {
    // Store sidebar scroll position
    let savedScrollPosition = 0;
    
    // Get the sidebar scroll container
    const sidebarContainer = document.querySelector('#scrollbar .simplebar-content-wrapper') || 
                            document.querySelector('#scrollbar');
    
    if (!sidebarContainer) return;
    
    // Save scroll position before navigation
    function savePosition() {
        savedScrollPosition = sidebarContainer.scrollTop;
    }
    
    // Restore scroll position after navigation
    function restorePosition() {
        setTimeout(() => {
            sidebarContainer.scrollTop = savedScrollPosition;
        }, 50);
    }
    
    // Add click listeners to ALL types of menu links
    function addMenuListeners() {
        // Main menu links
        const mainMenuLinks = document.querySelectorAll('#navbar-nav .nav-link');
        mainMenuLinks.forEach(link => {
            link.addEventListener('click', function() {
                savePosition();
                restorePosition();
            });
        });
        
        // Submenu links (inside collapsible sections)
        const subMenuLinks = document.querySelectorAll('#navbar-nav .nav-sm .nav-link');
        subMenuLinks.forEach(link => {
            link.addEventListener('click', function() {
                savePosition();
                restorePosition();
            });
        });
        
        // Two-column menu links
        const twoColumnLinks = document.querySelectorAll('#two-column-menu .nav-icon');
        twoColumnLinks.forEach(link => {
            link.addEventListener('click', function() {
                savePosition();
                restorePosition();
            });
        });
        
        // Any other nav links in the sidebar
        const allNavLinks = document.querySelectorAll('.navbar-menu .nav-link, .navbar-menu a[href]');
        allNavLinks.forEach(link => {
            link.addEventListener('click', function() {
                savePosition();
                restorePosition();
            });
        });
    }
    
    // Initial setup
    addMenuListeners();
    
    // Re-setup periodically to catch dynamically added menu items
    setInterval(addMenuListeners, 1000);
    
    // Override the scroll function that causes jumping
    const originalScrollTo = Element.prototype.scrollTo;
    Element.prototype.scrollTo = function(options) {
        // Don't scroll if this is the sidebar
        if (this === sidebarContainer || this.id === 'scrollbar') {
            return;
        }
        // Allow normal scrolling for other elements
        if (originalScrollTo) {
            originalScrollTo.call(this, options);
        }
    };
    
    // Also prevent scroll on the sidebar container itself
    if (sidebarContainer.scrollTo) {
        const originalSidebarScrollTo = sidebarContainer.scrollTo;
        sidebarContainer.scrollTo = function(options) {
            // Don't allow programmatic scrolling to top
            return;
        };
    }
});
