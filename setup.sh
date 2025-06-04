#!/bin/bash

echo "=== Setup Perpustakaan Digital Laravel ==="

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "Error: PHP is not installed. Please install PHP 8.1 or higher."
    exit 1
fi

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "Error: Composer is not installed. Please install Composer first."
    exit 1
fi

echo "1. Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

echo "2. Setting up environment file..."
if [ ! -f .env ]; then
    cp .env.example .env
    echo "Environment file created. Please configure your database settings in .env"
fi

echo "3. Generating application key..."
php artisan key:generate

echo "4. Creating storage link..."
php artisan storage:link

echo "5. Setting up database..."
read -p "Do you want to run database migrations? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    php artisan migrate --force
    echo "Database migrated successfully!"

    read -p "Do you want to seed the database with sample data? (y/n): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        php artisan db:seed
        echo "Database seeded successfully!"
    fi
fi

echo "6. Setting up file permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/app/public

echo "7. Creating required directories..."
mkdir -p storage/app/public/digital-books
mkdir -p public/storage

echo "=== Setup Complete! ==="
echo ""
echo "Next steps:"
echo "1. Configure your database settings in .env file"
echo "2. Run 'php artisan serve' to start the development server"
echo "3. Visit http://localhost:8000 to access the application"
echo ""
echo "Default login credentials:"
echo "Admin: admin@example.com / password"
echo "Dosen: dosen@example.com / password"
echo "Mahasiswa: mahasiswa@example.com / password"
