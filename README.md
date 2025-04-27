# Schat

**Schat** is a lightweight PHP-based chat application designed as a learning example for friends and fellow developers. It demonstrates how to build a simple web chat using PHP and SQLite3, without relying on external frameworks.

## Features

- Minimalist real-time chat interface
- Built with plain PHP and SQLite3
- Easy to set up and run locally

## Getting Started

### Prerequisites

- PHP 7.4 or higher
- SQLite3 or compatible database
- Composer (optional, if dependencies are added later)

### Installation

1. **Clone the repository:**

   ```bash
   git clone https://github.com/ssleert/schat.git
   cd schat
   ```

2. **Create the database:**

   ```bash
   php ./scripts/create_db.php
   ```

   This script sets up the necessary tables in your SQLite3 database.

3. **Start the PHP built-in server:**

   ```bash
   PHP_CLI_SERVER_WORKERS=8 php -S localhost:8080 -t ./src/www
   ```

   This command starts the PHP built-in server with 8 worker threads, serving files from the `./src/www` directory.

4. **Access the application:**

   Open your browser and navigate to [http://localhost:8080](http://localhost:8080) to start chatting.

## Project Structure

```
schat/
├── scripts/
│   └── create_db.php  # Database setup script
├── src/
│   └── www/           # Public web files
├── composer.json      # Composer configuration (if used)
└── .gitignore         # Git ignore file
```

## License

This project is open-source and available under the MIT License.

