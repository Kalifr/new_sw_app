# Selina Agricultural Marketplace

A modern B2B agricultural marketplace built with Laravel, Vue.js, and Tailwind CSS. The platform connects buyers and sellers in the agricultural sector, facilitating trade through RFQs, secure messaging, and escrow-based order processing.

## Features

### User Management
- Email verification
- Profile completion workflow
- Role-based access control (Admin, Buyer, Seller, Inspector)
- Organization profiles

### Product Management
- Detailed product listings with multiple images
- Product categories and specifications
- Price and availability management
- Search and filtering capabilities

### RFQ System
- Request for Quote (RFQ) creation
- Quote submission and management
- Real-time notifications
- Quote comparison tools

### Order Processing
- Escrow-based payment system
- Document management
- Inspection integration
- Digital signatures
- Payment tracking

### Communication
- Real-time messaging system
- Email notifications
- Thread-based conversations
- File attachments

### Analytics
- User-specific analytics dashboard
- Platform-wide metrics for admins
- Search analytics
- Performance tracking

## Tech Stack

- **Backend:** Laravel 11
- **Frontend:** Vue.js 3 with Inertia.js
- **CSS Framework:** Tailwind CSS
- **Database:** MySQL
- **Search:** Elasticsearch
- **Real-time:** Laravel WebSockets
- **Queue System:** Redis
- **File Storage:** AWS S3 (optional)

## Requirements

- PHP >= 8.3
- Node.js >= 18
- Composer
- MySQL >= 8.0
- Elasticsearch >= 8.0
- Redis

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/selina-marketplace.git
cd selina-marketplace
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
npm install
```

4. Copy environment file and configure:
```bash
cp .env.example .env
# Edit .env with your database and other service credentials
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Run migrations and seeders:
```bash
php artisan migrate --seed
```

7. Link storage:
```bash
php artisan storage:link
```

8. Build assets:
```bash
npm run build
```

## Development

1. Start the Laravel development server:
```bash
php artisan serve
```

2. Start the Vite development server:
```bash
npm run dev
```

3. Start the queue worker:
```bash
php artisan queue:work
```

4. Start WebSocket server (if using local WebSockets):
```bash
php artisan websockets:serve
```

## Testing

Run the test suite:
```bash
php artisan test
```

## Deployment

1. Configure your production environment
2. Set up SSL certificates
3. Configure your web server (Nginx recommended)
4. Set up your queue worker as a service
5. Configure your WebSocket server
6. Set up your Elasticsearch instance
7. Configure your Redis instance

## Security

- All routes are protected with appropriate middleware
- CSRF protection enabled
- XSS protection through proper escaping
- SQL injection protection through Laravel's query builder
- File upload validation and sanitization
- Rate limiting on sensitive routes

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please email support@example.com or open an issue in the GitHub repository.
