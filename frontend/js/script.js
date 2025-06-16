document.addEventListener('DOMContentLoaded', function() {
    // Toggle submenus
    const navItems = document.querySelectorAll('.nav-section ul > li');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Don't toggle if clicking on a link
            if (e.target.tagName === 'A') return;
            
            // Close all other open submenus
            if (!this.classList.contains('active')) {
                navItems.forEach(i => i.classList.remove('active'));
            }
            
            // Toggle current item
            this.classList.toggle('active');
        });
    });
    
    // Toggle AI activation
    const aiToggle = document.getElementById('ai-toggle');
    aiToggle.addEventListener('change', function() {
        if (this.checked) {
            console.log('AI activated');
            // Add your AI activation logic here
        } else {
            console.log('AI deactivated');
            // Add your AI deactivation logic here
        }
    });
    
    // Mobile menu toggle (for responsive design)
    const hamburger = document.createElement('div');
    hamburger.className = 'hamburger-menu';
    hamburger.innerHTML = '<i class="fas fa-bars"></i>';
    document.querySelector('.top-bar').prepend(hamburger);
    
    hamburger.addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
    });
});