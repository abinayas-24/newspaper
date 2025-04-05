# News Aggregator

A PHP-based news aggregator that collects and displays news from various sources including Times of India, The Hindu, and Hindustan Times.

## Features

- News scraping from multiple sources
- Category-based filtering
- Search functionality
- Pagination
- Sentiment analysis
- Modern and responsive UI

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Web server (Apache/Nginx)

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd news-aggregator
```

2. Install dependencies:
```bash
composer install
```

3. Create the database:
```bash
mysql -u root -p < database/schema.sql
```

4. Configure the database connection:
Edit `config/database.php` with your database credentials.

5. Set up your web server:
- Point your web server's document root to the project directory
- Ensure the web server has write permissions to the project directory

## Usage

1. Access the application through your web browser
2. The news will be automatically scraped and displayed
3. Use the search bar to find specific news
4. Filter news by category using the sidebar
5. Navigate through pages using the pagination controls

## Project Structure

```
news-aggregator/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── includes/
│   ├── NewsScraper.php
│   └── NewsProcessor.php
├── index.php
├── composer.json
└── README.md
```

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.