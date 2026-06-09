# CoRide - Carpooling Website

A modern carpooling platform built with PHP, HTML, CSS, and JavaScript, using JSON files for data storage.

## Features

### User Features
- **User Registration & Authentication**: Sign up and login system with role-based access
- **Browse Available Offers**: Search and filter carpool offers
- **Create Offers**: Users can create their own carpool offers
- **Make Reservations**: Book seats on available trips
- **Manage Reservations**: View, cancel, and track reservation history
- **Profile Management**: Update personal information and password

### Admin Features
- **Dashboard**: Statistics with charts (total users, trips, reservations)
- **User Management**: View, activate/deactivate, and delete user accounts
- **Trip Management**: View and delete carpool trips
- **Reservation Management**: View and filter all reservations with status tracking

## Technical Stack

- **Backend**: PHP (no database, uses JSON files)
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Modern CSS with custom properties
- **Data Storage**: JSON files for users, offers, and reservations
- **Charts**: Chart.js for admin dashboard statistics

## File Structure

```
├── index.php                 # Welcome page
├── login.php                 # Login page
├── signup.php                # Registration page
├── dashboard.php             # Route to appropriate dashboard
├── logout.php                # Logout handler
├── profile.php               # User profile page
├── available_offers.php      # Browse available offers
├── my_offers.php             # Manage user's offers
├── my_reservations.php       # Manage user's reservations
├── admin_dashboard.php       # Admin dashboard with statistics
├── admin_users.php           # User management (admin)
├── admin_trips.php           # Trip management (admin)
├── admin_reservations.php    # Reservation management (admin)
├── process_reservation.php   # Handle reservation creation
├── get_reservations.php      # API to get reservations for an offer
├── update_reservation.php    # API to update reservation status
├── css/
│   └── style.css             # Main stylesheet
├── js/
│   └── main.js               # Main JavaScript file
├── data/
│   ├── users.json            # User data
│   ├── offers.json           # Trip offers data
│   └── reservations.json     # Reservation data
└── images/
    └── logo.png              # CoRide logo
```

## Installation

1. **Requirements**: PHP 7.4 or higher, web server (Apache/Nginx)
2. **Setup**:
   - Place all files in your web server directory
   - Ensure the `data/` directory is writable by the web server
   - Add the CoRide logo to the `images/` directory
3. **Access**: Open `index.php` in your web browser

## Default Admin Account

- **Email**: admin@gmail.com
- **Password**: admin123

## Color Palette

The website uses the following color scheme:
- Primary: #425749 (Dark green)
- Secondary: #C23C5a (Coral red)
- Accent: #D5A18E (Light peach)
- Light Accent: #DEC3BE (Very light peach)
- Muted: #758B7C (Muted green)

## Key Features

### Responsive Design
- Mobile-friendly layout
- Adaptive grid systems
- Touch-friendly interface

### Security
- Session-based authentication
- Input validation and sanitization
- Role-based access control

### User Experience
- Modern, clean interface
- Smooth animations and transitions
- Interactive modals and forms
- Real-time search and filtering

### Data Management
- JSON-based storage system
- Automatic data validation
- Efficient data retrieval and filtering

## Usage

1. **For Users**:
   - Register an account or login
   - Browse available carpool offers
   - Make reservations for trips
   - Create your own offers
   - Manage your profile and reservations

2. **For Admins**:
   - Login with admin credentials
   - View dashboard statistics
   - Manage users, trips, and reservations
   - Monitor platform activity

## Future Enhancements

- Email notifications
- Rating system for users
- Payment integration
- Mobile app development
- Real-time chat between drivers and passengers
- GPS integration for route tracking

## Support

For issues or questions, please refer to the code comments or contact the development team.
