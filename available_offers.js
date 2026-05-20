
document.addEventListener('DOMContentLoaded', function() {

    window.openReservationModal = function(offerId, from, to, availableSeats) {
        document.getElementById('offer_id').value = offerId;
        document.getElementById('route_info').textContent = from + ' → ' + to;
        
      
        const seatsSelect = document.getElementById('seats');
        seatsSelect.innerHTML = '<option value="">Select seats</option>';
        for (let i = 1; i <= availableSeats; i++) {
            seatsSelect.innerHTML += `<option value="${i}">${i} seat${i > 1 ? 's' : ''}</option>`;
        }
        
        document.getElementById('reservationModal').style.display = 'flex';
    };
    
    window.closeReservationModal = function() {
        document.getElementById('reservationModal').style.display = 'none';
        
        const form = document.getElementById('reservationForm');
        if (form) {
            form.reset();
        }
    };
    
    // Handle reservation form submission
    const reservationForm = document.getElementById('reservationForm');
    if (reservationForm) {
        reservationForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const seats = formData.get('seats');
            const message = formData.get('message');
            
            // Validation
            if (!seats) {
                showNotification('Please select number of seats', 'error');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            fetch('process_reservation.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                
                if (data.success) {
                    showNotification('Reservation successful!', 'success');
                    closeReservationModal();
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            });
        });
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('reservationModal');
        if (event.target === modal) {
            closeReservationModal();
        }
    };
    
    // Add hover effects to offer cards
    const offerCards = document.querySelectorAll('.offer-card');
    offerCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--shadow)';
        });
    });
    
    // Search and filter functionality
    const searchInput = document.querySelector('input[name="search"]');
    const filterSelect = document.querySelector('select[name="filter"]');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterOffers();
        });
    }
    
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            filterOffers();
        });
    }
    
    function filterOffers() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const filterValue = filterSelect ? filterSelect.value : 'all';
        
        const offers = document.querySelectorAll('.offer-card');
        offers.forEach(offer => {
            const text = offer.textContent.toLowerCase();
            const matchesSearch = !searchTerm || text.includes(searchTerm);
            const matchesFilter = filterValue === 'all' || offer.dataset.category === filterValue;
            
            if (matchesSearch && matchesFilter) {
                offer.style.display = 'block';
            } else {
                offer.style.display = 'none';
            }
        });
    }
});
