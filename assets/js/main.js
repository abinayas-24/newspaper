document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const successAnimation = document.getElementById('successAnimation');
    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));

    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Here you would typically send the login data to your server
        // For demonstration, we'll just show the success animation
        
        // Close the login modal
        loginModal.hide();
        
        // Show the success animation
        successAnimation.style.display = 'flex';
        
        // Hide the success animation after 3 seconds
        setTimeout(function() {
            successAnimation.style.display = 'none';
        }, 3000);
    });
}); 