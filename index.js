

document.addEventListener('DOMContentLoaded', function() { //attendre le chargement de html
    
    const anchorLinks = document.querySelectorAll('a[href^="#"]'); // selection tout les liens #
    
    anchorLinks.forEach(link => { //parcourir les liens 
        link.addEventListener('click', function(e) { //quand on clique sur lien le code eexecute
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId); //trouve element correspondant
            
            if (targetElement) {
                targetElement.scrollIntoView({ //scrolling smooth
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

 
    const navbar = document.querySelector('.navbar'); //selectione element de nav bar
    let lastScrollTop = 0;
    
    window.addEventListener('scroll', function() { //execute le code a chaque defilement
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        if (scrollTop > 100) { // si utilisateur a descendu la page nav bar devient transparent '
            navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            navbar.style.backdropFilter = 'blur(10px)';
        } else {
            navbar.style.background = 'var(--bg-white)';
            navbar.style.backdropFilter = 'none';
        }
        
        lastScrollTop = scrollTop;
    });


    const contactForm = document.querySelector('.contact-form form');//selectionne formulaire
    if (contactForm) { 
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            

            const formData = new FormData(this);//creer objet contenant du formulaire
            const name = this.querySelector('input[type="text"]').value;//recupére email message
            const email = this.querySelector('input[type="email"]').value;
            const message = this.querySelector('textarea').value;
            

            if (!name || !email || !message) {
                showNotification('Please fill in all fields', 'error');// si champs est vide envoyer notif
                return;
            }
            

            if (!validateEmail(email)) {
                showNotification('Please enter a valid email address', 'error');
                return;
            }
            

            showNotification('Message sent successfully! We will get back to you soon.', 'success');
            this.reset();
        });
    }


    const observerOptions = {//aniamtion en scroll
        threshold: 0.1, //anaimation declenche quand 10% est visible
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {//observe element visible
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1'; //elemenent rendre visible
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    const featureCards = document.querySelectorAll('.feature-card');//selectionne carte
    featureCards.forEach(card => {
        card.style.opacity = '0';//invisible au depart
        card.style.transform = 'translateY(30px)';//decalee vers le bas
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';//smooth
        observer.observe(card);
    });
});
