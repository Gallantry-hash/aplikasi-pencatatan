</main> 

<script>
    // Modern menu interactions with smooth animations
    const menuToggle = document.getElementById('menu-toggle');
    const menuClose = document.getElementById('menu-close');
    const sidebar = document.getElementById('sidebar');

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
        document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
    }

    function closeSidebar() {
        sidebar.classList.remove('translate-x-0');
        sidebar.classList.add('-translate-x-full');
        document.body.classList.remove('overflow-hidden');
    }

    if(menuToggle) {
        menuToggle.addEventListener('click', openSidebar);
    }
    
    if(menuClose) {
        menuClose.addEventListener('click', closeSidebar);
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (window.innerWidth < 1024) { // Only on mobile
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target) && 
                !sidebar.classList.contains('-translate-x-full')) {
                closeSidebar();
            }
        }
    });

    // Handle submenu toggles with smooth animations
    const pythonToggle = document.getElementById('python-toggle');
    const pythonSubmenu = document.getElementById('python-submenu');
    const chevron = pythonToggle?.querySelector('.fa-chevron-down');

    if(pythonToggle) {
        pythonToggle.addEventListener('click', () => {
            pythonSubmenu.classList.toggle('submenu-hidden');
            pythonSubmenu.classList.toggle('submenu-visible');
            chevron.classList.toggle('rotate-180');
        });
    }

    // Add smooth scroll behavior
    document.documentElement.style.scrollBehavior = 'smooth';

    // Enhanced table hover effects
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('mouseenter', () => {
            row.style.transform = 'scale(1.001)';
            row.style.transition = 'all 0.2s ease';
        });
        
        row.addEventListener('mouseleave', () => {
            row.style.transform = 'scale(1)';
        });
    });

    // Loading states for buttons
    function addLoadingState(button, text = 'Loading...') {
        const originalText = button.innerHTML;
        button.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>${text}`;
        button.disabled = true;
        
        return () => {
            button.innerHTML = originalText;
            button.disabled = false;
        };
    }

    // Enhanced form validations with modern styling
    document.querySelectorAll('input, select, textarea').forEach(input => {
        input.addEventListener('focus', () => {
            input.classList.add('ring-2', 'ring-primary-500', 'border-primary-500');
        });
        
        input.addEventListener('blur', () => {
            input.classList.remove('ring-2', 'ring-primary-500', 'border-primary-500');
        });
    });

    // Modern notification system
    function showModernAlert(message, type = 'info', duration = 5000) {
        const alertContainer = document.getElementById('alert-placeholder') || 
                             document.querySelector('[data-alerts]') || 
                             document.body;

        const alertTypes = {
            success: {
                bg: 'bg-emerald-50 border-emerald-200',
                text: 'text-emerald-800',
                icon: 'fas fa-check-circle text-emerald-500'
            },
            error: {
                bg: 'bg-red-50 border-red-200',
                text: 'text-red-800',
                icon: 'fas fa-exclamation-circle text-red-500'
            },
            warning: {
                bg: 'bg-amber-50 border-amber-200',
                text: 'text-amber-800',
                icon: 'fas fa-exclamation-triangle text-amber-500'
            },
            info: {
                bg: 'bg-blue-50 border-blue-200',
                text: 'text-blue-800',
                icon: 'fas fa-info-circle text-blue-500'
            }
        };

        const alert = alertTypes[type] || alertTypes.info;
        
        const alertElement = document.createElement('div');
        alertElement.className = `${alert.bg} ${alert.text} border-l-4 p-4 rounded-lg mb-4 shadow-sm transition-all duration-300 transform translate-y-0 opacity-100`;
        alertElement.innerHTML = `
            <div class="flex items-center">
                <i class="${alert.icon} mr-3"></i>
                <div class="flex-1">
                    <p class="font-medium">${message}</p>
                </div>
                <button class="ml-4 text-current opacity-60 hover:opacity-80" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        alertContainer.insertBefore(alertElement, alertContainer.firstChild);

        // Auto remove
        setTimeout(() => {
            alertElement.style.transform = 'translateY(-10px)';
            alertElement.style.opacity = '0';
            setTimeout(() => alertElement.remove(), 300);
        }, duration);
    }

    // Make showModernAlert available globally
    window.showModernAlert = showModernAlert;
</script>
</body>
</html>