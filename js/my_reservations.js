

document.addEventListener('DOMContentLoaded', function() {
    
    window.filterReservations = function(status) {
       
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
        
        
        const reservations = document.querySelectorAll('.reservation-card');
        reservations.forEach(reservation => {
            if (status === 'all' || reservation.dataset.status === status) {
                reservation.style.display = 'block';
            } else {
                reservation.style.display = 'none';
            }
        });
    };
    
    
    window.cancelReservation = function(reservationId) {
        if (confirm('Are you sure you want to cancel this reservation?')) {
            window.location.href = 'my_reservations.php?cancel_reservation=' + reservationId;
        }
    };
    
    
    const reservationCards = document.querySelectorAll('.reservation-card');
    reservationCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.15)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--shadow)';
        });
    });
    
    const actionButtons = document.querySelectorAll('.action-btn');
    actionButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
});
