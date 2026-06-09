

document.addEventListener('DOMContentLoaded', function() {
   
    const loginForm = document.querySelector('.login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = this.querySelector('input[type="email"]').value;
            const password = this.querySelector('input[type="password"]').value;
            
            
            if (!email || !password) {
                showNotification('Please fill in all fields', 'error');
                return;
            }
            
            if (!validateEmail(email)) {
                showNotification('Please enter a valid email address', 'error');
                return;
            }
            
            if (password.length < 6) {
                showNotification('Password must be at least 6 characters', 'error');
                return;
            }
            
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging in...';
            
           
            setTimeout(() => {
                this.submit();
            }, 500);
        });
    }
    
    
    const signupForm = document.querySelector('.signup-form');
    if (signupForm) {
        signupForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const firstName = this.querySelector('input[name="first_name"]').value;
            const lastName = this.querySelector('input[name="last_name"]').value;
            const email = this.querySelector('input[name="email"]').value;
            const phone = this.querySelector('input[name="phone"]').value;
            const password = this.querySelector('input[name="password"]').value;
            const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
            
            
            const errors = [];
            
            if (!firstName || !lastName || !email || !phone || !password || !confirmPassword) {
                errors.push('Please fill in all fields');
            }
            
            if (!validateEmail(email)) {
                errors.push('Please enter a valid email address');
            }
            
            if (!validatePhone(phone)) {
                errors.push('Please enter a valid phone number');
            }
            
            if (password.length < 6) {
                errors.push('Password must be at least 6 characters');
            }
            
            if (password !== confirmPassword) {
                errors.push('Passwords do not match');
            }
            
            if (errors.length > 0) {
                showNotification(errors[0], 'error');
                return;
            }
            
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating account...';
            
            
            setTimeout(() => {
                this.submit();
            }, 500);
        });
        
        
        const passwordInput = signupForm.querySelector('input[name="password"]');
        const confirmPasswordInput = signupForm.querySelector('input[name="confirm_password"]');
        
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                const confirmPassword = this.value;
                const password = passwordInput.value;
                
                if (confirmPassword && password && confirmPassword !== password) {
                    this.setCustomValidity('Passwords do not match');
                } else {
                    this.setCustomValidity('');
                }
            });
        }
        
        
        const emailInput = signupForm.querySelector('input[name="email"]');
        if (emailInput) {
            emailInput.addEventListener('blur', function() {
                if (this.value && !validateEmail(this.value)) {
                    showNotification('Please enter a valid email address', 'error');
                }
            });
        }
        
       
        const phoneInput = signupForm.querySelector('input[name="phone"]');
        if (phoneInput) {
            phoneInput.addEventListener('blur', function() {
                if (this.value && !validatePhone(this.value)) {
                    showNotification('Please enter a valid phone number', 'error');
                }
            });
        }
    }
    
    
    const passwordInput = document.querySelector('input[name="password"]');
    const strengthIndicator = document.querySelector('.password-strength');
    
    if (passwordInput && strengthIndicator) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthIndicator.className = 'password-strength strength-' + strength;
            
            const strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'][strength] || '';
            strengthIndicator.textContent = strengthText;
        });
    }
});
