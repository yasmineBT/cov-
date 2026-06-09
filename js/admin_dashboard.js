

document.addEventListener('DOMContentLoaded', function() {
    
    if (typeof Chart !== 'undefined') {
        initializeCharts();
    } else {
        
        setTimeout(function() {
            if (typeof Chart !== 'undefined') {
                initializeCharts();
            }
        }, 100);
    }
    
    function initializeCharts() {
       
        const reservationCtx = document.getElementById('reservationChart');
        if (reservationCtx) {
            new Chart(reservationCtx.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: ['Accepted', 'Rejected', 'Pending'],
                    datasets: [{
                        data: [
                            parseInt(document.getElementById('acceptedCount')?.textContent || 0),
                            parseInt(document.getElementById('rejectedCount')?.textContent || 0),
                            parseInt(document.getElementById('pendingCount')?.textContent || 0)
                        ],
                        backgroundColor: ['#4CAF50', '#F44336', '#FF9800'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        
        const routesCtx = document.getElementById('routesChart');
        if (routesCtx) {
            const routeLabels = JSON.parse(document.getElementById('routeLabels')?.textContent || '[]');
            const routeData = JSON.parse(document.getElementById('routeData')?.textContent || '[]');
            
            new Chart(routesCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: routeLabels,
                    datasets: [{
                        label: 'Number of Requests',
                        data: routeData,
                        backgroundColor: '#C23C5a',
                        borderWidth: 0,
                        borderRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `Requests: ${context.parsed.y}`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }
    
    
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.addEventListener('click', function() {
            const targetPage = this.dataset.target;
            if (targetPage) {
                window.location.href = targetPage;
            }
        });
        
        
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.2)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'var(--shadow)';
        });
    });
    
    
    setInterval(function() {
        refreshDashboardData();
    }, 300000);
    
    function refreshDashboardData() {
        
        const loadingIndicator = document.createElement('div');
        loadingIndicator.className = 'refresh-indicator';
        loadingIndicator.textContent = 'Refreshing data...';
        loadingIndicator.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--primary-color);
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            z-index: 1000;
        `;
        document.body.appendChild(loadingIndicator);
        
        
        fetch('admin_dashboard_api.php')
            .then(response => response.json())
            .then(data => {
                
                updateStatCards(data);
                
                
                updateCharts(data);
                
                
                setTimeout(() => {
                    if (loadingIndicator.parentNode) {
                        loadingIndicator.parentNode.removeChild(loadingIndicator);
                    }
                }, 1000);
            })
            .catch(error => {
                console.error('Error refreshing dashboard data:', error);
                if (loadingIndicator.parentNode) {
                    loadingIndicator.parentNode.removeChild(loadingIndicator);
                }
            });
    }
    
    function updateStatCards(data) {
        
        const cards = {
            'users': data.total_users,
            'offers': data.total_offers,
            'reservations': data.total_reservations,
            'revenue': data.total_revenue
        };
        
        Object.keys(cards).forEach(key => {
            const element = document.getElementById(`${key}Count`);
            if (element) {
                element.textContent = cards[key];
            }
        });
    }
    
    function updateCharts(data) {
        
        
    }
});
