# Contacts CRM System

A modern Laravel-based contact management system with advanced merge functionality and custom fields support.

## Features

- **Contact Management**: Create, read, update, and delete contacts
- **Custom Fields**: Dynamic custom field creation with multiple data types
- **Contact Merging**: Advanced merge functionality with data preservation
- **File Uploads**: Profile images and document attachments
- **Search & Filter**: Advanced search with custom field filtering
- **Responsive Design**:  UI with Bootstrap 5

## Requirements for this assignment

- PHP 8.1+
- Laravel 10+
- SQLite/MySQL/PostgreSQL
- Composer

## Install flow

1. Clone the repository
```bash
git clone <repository-url>
cd contacts-crud-system
```

2. Install dependencies
```bash
composer install
npm install
```

3. Configure environment
```bash
cp .env.example .env
php artisan key:generate
```

4. Run migrations
```bash
php artisan migrate
```

5. Start the development server
```bash
php artisan serve
```

## Usage

### Contact Management
- Go to `/contacts` to view all contacts
- Use the search and filter options to find specific contacts
- Click "Add Contact" to create new contacts

### Custom Fields
- Go to `/custom-fields` to manage custom fields
- Create fields with different types: text, email, number, date, select
- Custom fields will appear in contact forms automatically

### Contact Merging
- Select two contacts using checkboxes
- Click "Merge Selected" to start the merge process
- Choose the master contact and confirm the merge
- All data is preserved in separate merge tables

## License

