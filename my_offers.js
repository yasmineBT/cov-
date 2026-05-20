
window.openCreateOfferModal = function() {
    document.getElementById('createOfferModal').style.display = 'flex';
};

window.closeCreateOfferModal = function() {
    document.getElementById('createOfferModal').style.display = 'none';
};

window.deleteOffer = function(offerId) {
    if (confirm('Are you sure you want to delete this offer?')) {
        window.location.href = 'my_offers.php?delete_offer=' + offerId;
    }
};

window.showReservations = function(offerId) {
    showLoadingSpinner(document.getElementById('reservationsList'));
    
    fetch('get_reservations.php?offer_id=' + offerId)
        .then(response => response.json())
        .then(data => {
            hideLoadingSpinner(document.getElementById('reservationsList'));
            
            if (data.success) {
                let html = '';
                if (data.reservations.length === 0) {
                    html = '<p>No reservations yet for this offer.</p>';
                } else {
                    data.reservations.forEach(reservation => {
                        html += `
                            <div class="reservation-item">
                                <div class="reservation-header">
                                    <div>
                                        <strong>${reservation.user_name}</strong><br>
                                        <small>Seats: ${reservation.seats} | Phone: ${reservation.phone}</small>
                                    </div>
                                    <span class="reservation-status status-${reservation.status}">${reservation.status}</span>
                                </div>
                                ${reservation.status === 'pending' ? `
                                    <div class="reservation-actions">
                                        <button class="btn-accept" onclick="updateReservationStatus(${reservation.id}, 'accepted')">Accept</button>
                                        <button class="btn-reject" onclick="updateReservationStatus(${reservation.id}, 'rejected')">Reject</button>
                                    </div>
                                ` : ''}
                            </div>
                        `;
                    });
                }
                document.getElementById('reservationsList').innerHTML = html;
                document.getElementById('reservationsModal').style.display = 'flex';
            } else {
                showNotification('Error loading reservations: ' + data.message, 'error');
            }
        })
        .catch(error => {
            hideLoadingSpinner(document.getElementById('reservationsList'));
            showNotification('Error loading reservations', 'error');
        });
};

window.closeReservationsModal = function() {
    document.getElementById('reservationsModal').style.display = 'none';
};

window.updateReservationStatus = function(reservationId, status) {
    fetch('update_reservation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `reservation_id=${reservationId}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Reservation ' + status + ' successfully!', 'success');
            location.reload();
        } else {
            showNotification('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        showNotification('Error updating reservation', 'error');
    });
};

document.addEventListener('DOMContentLoaded', function() {
    
    window.onclick = function(event) {
        const createModal = document.getElementById('createOfferModal');
        const reservationsModal = document.getElementById('reservationsModal');
        
        if (event.target === createModal) {
            closeCreateOfferModal();
        }
        if (event.target === reservationsModal) {
            closeReservationsModal();
        }
    };
    
    const createOfferForm = document.querySelector('#createOfferModal form');
    console.log('Create offer form found:', createOfferForm);
    
    if (createOfferForm) {
        createOfferForm.addEventListener('submit', function(e) {
            console.log('Form submitted');
            e.preventDefault();
            
            const from = this.querySelector('input[name="from"]').value;
            const to = this.querySelector('input[name="to"]').value;
            const date = this.querySelector('input[name="date"]').value;
            const time = this.querySelector('input[name="time"]').value;
            const seats = this.querySelector('input[name="seats"]').value;
            const price = this.querySelector('input[name="price"]').value;
            const phone = this.querySelector('input[name="phone"]').value;
            const description = this.querySelector('textarea[name="description"]').value;
            
            console.log('Form data:', { from, to, date, time, seats, price, phone, description });
            
            if (!from || !to || !date || !time || !seats || !price || !phone) {
                console.log('Validation failed: missing fields');
                showNotification('Please fill in all fields', 'error');
                return;
            }
            
            if (parseInt(seats) < 1) {
                console.log('Validation failed: seats less than 1');
                showNotification('Available seats must be at least 1', 'error');
                return;
            }
            
            if (parseFloat(price) < 0) {
                console.log('Validation failed: negative price');
                showNotification('Price cannot be negative', 'error');
                return;
            }
            
            console.log('Validation passed, submitting form');
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating...';
            
            setTimeout(() => {
                console.log('Submitting form to server');
                this.submit();
            }, 500);
        });
    } else {
        console.log('Create offer form not found!');
    }
});
