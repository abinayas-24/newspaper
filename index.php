<?php

require_once 'includes/RSSFeedHandler.php';

session_start();

// Initialize RSS feed handler
$feedHandler = new RSSFeedHandler();

// Get current page for pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : 'top_stories';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch news from RSS feed
$allNews = $feedHandler->fetchFeed($category);

// Apply search filter if provided
if (!empty($search)) {
    $allNews = array_filter($allNews, function($item) use ($search) {
        return stripos($item['title'], $search) !== false || 
               stripos($item['description'], $search) !== false;
    });
}

// Apply pagination
$totalNews = count($allNews);
$totalPages = ceil($totalNews / $perPage);
$offset = ($page - 1) * $perPage;
$news = array_slice($allNews, $offset, $perPage);

// Get featured article for top stories
$featuredArticle = null;
if ($category === 'top_stories' && !empty($news)) {
    $featuredArticle = array_shift($news);
}

// Function to get sentiment color
function getSentimentColor($score) {
    if ($score > 0.5) return 'success';
    if ($score > 0) return 'info';
    if ($score < -0.5) return 'danger';
    if ($score < 0) return 'warning';
    return 'secondary';
}

// Function to get sentiment icon
function getSentimentIcon($score) {
    if ($score > 0.5) return 'bi-emoji-smile';
    if ($score > 0) return 'bi-emoji-neutral';
    if ($score < -0.5) return 'bi-emoji-frown';
    if ($score < 0) return 'bi-emoji-expressionless';
    return 'bi-emoji-neutral';
}

// Function to format date
function formatDate($dateString) {
    return date('M d, Y H:i', strtotime($dateString));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Times of India News Feed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
    /* Background Image */
    body {
        background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)),
                    url('https://images.unsplash.com/photo-1504711434969-e33886168f5c?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
        background-size: cover;
        background-attachment: fixed;
        background-position: center;
        min-height: 100vh;
    }

    /* Search Bar Styles */
    .search-container {
        position: relative;
        max-width: 600px;
        margin: 20px auto;
        padding: 0 15px;
    }

    .search-form {
        display: flex;
        gap: 10px;
    }

    .search-input {
        flex: 1;
        padding: 12px 20px;
        border: 2px solid rgba(0, 0, 0, 0.1);
        border-radius: 25px;
        font-size: 1rem;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(5px);
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 10px rgba(0, 123, 255, 0.2);
    }

    .search-button {
        padding: 12px 25px;
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
        border: none;
        border-radius: 25px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .search-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .search-container {
            margin: 15px auto;
        }
        
        .search-form {
            flex-direction: column;
        }
        
        .search-button {
            width: 100%;
        }
    }

    /* Update tech news styles */
    .tech-news-section {
        padding: 20px;
    }

    .tech-news-list {
        display: flex;
        flex-direction: column;
        gap: 25px; /* Increased gap between items */
    }

    .tech-news-item {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 20px;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .tech-news-item:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .tech-news-icon {
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }

    .tech-news-icon i {
        font-size: 24px;
        color: #fff;
    }

    .tech-news-content {
        margin-top: 15px;
    }

    .tech-news-content h5 {
        color: #fff;
        font-size: 1.2rem;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .tech-news-content p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.9rem;
        margin-bottom: 15px;
        line-height: 1.5;
    }

    .tech-news-links {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 15px;
    }

    .tech-news-links a {
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        font-size: 0.9rem;
        padding: 8px 12px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .tech-news-links a:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        transform: translateX(5px);
    }

    .video-links {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .video-link {
        display: flex;
        align-items: center;
        color: rgba(255, 255, 255, 0.7);
        text-decoration: none;
        font-size: 0.9rem;
        padding: 8px 12px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 6px;
        transition: all 0.3s ease;
    }

    .video-link i {
        margin-right: 8px;
        color: #ff0000;
    }

    .video-link:hover {
        background: rgba(255, 255, 255, 0.2);
        color: #fff;
        transform: translateX(5px);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .tech-news-list {
            gap: 20px;
        }

        .tech-news-item {
            padding: 15px;
        }

        .tech-news-icon {
            width: 40px;
            height: 40px;
        }

        .tech-news-icon i {
            font-size: 20px;
        }

        .tech-news-content h5 {
            font-size: 1.1rem;
        }

        .tech-news-content p {
            font-size: 0.85rem;
        }
    }

    /* Update left navbar styles */
    .left-navbar {
        position: fixed;
        left: -400px;
        top: 0;
        width: 400px;
        height: 100vh;
        background: linear-gradient(135deg, rgba(40, 40, 40, 0.9) 0%, rgba(60, 60, 60, 0.9) 100%);
        backdrop-filter: blur(8px);
        color: white;
        z-index: 999;
        transition: left 0.3s ease;
        overflow: hidden;
        margin: 0;
        padding: 0;
    }

    .left-navbar.expanded {
        left: 0;
    }

    .menu-content {
        padding: 20px;
        height: 100%;
        overflow-y: auto;
        margin: 0;
    }

    /* Update main content to start from the left edge */
    .main-content {
        margin-left: 0;
        padding-left: 0;
        transition: margin-left 0.3s ease;
    }

    .main-content.expanded {
        margin-left: 400px;
    }

    /* Update container padding */
    .container {
        padding-left: 0;
        padding-right: 0;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .left-navbar {
            width: 100%;
            left: -100%;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
    }

    /* Navbar Styles */
    .navbar {
        padding: 1rem 0;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        background: linear-gradient(135deg, rgba(40, 40, 40, 0.9) 0%, rgba(60, 60, 60, 0.9) 100%);
        backdrop-filter: blur(8px);
    }

    .navbar .container {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .navbar-brand-container {
        display: flex;
        align-items: center;
    }

    .menu-toggle {
        width: 40px;
        height: 40px;
        background: transparent;
        border: none;
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
    }

    .menu-toggle i {
        font-size: 1.5rem;
    }

    .globe-icon {
        font-size: 2rem;
        color: #ff0000;
        margin-right: 10px;
        transition: transform 0.3s ease;
    }

    .globe-icon:hover {
        transform: rotate(360deg);
    }

    .brand-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #fff;
        text-align: center;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    /* Search and Login Container */
    .search-login-container {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .search-container {
        position: relative;
    }

    .search-form {
        display: flex;
        align-items: center;
    }

    .search-input {
        padding: 8px 15px;
        border: none;
        border-radius: 20px;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        width: 200px;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        background: rgba(255, 255, 255, 0.2);
        width: 250px;
    }

    .search-input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .search-button {
        background: transparent;
        border: none;
        color: white;
        padding: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .search-button:hover {
        color: #ff0000;
    }

    .login-icon-container {
        margin-left: 10px;
    }

    .login-icon {
        font-size: 2rem;
        color: white;
        transition: all 0.3s ease;
    }

    .login-icon:hover {
        color: #ff0000;
        transform: scale(1.1);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .navbar .container {
            flex-direction: column;
            gap: 10px;
        }

        .search-login-container {
            width: 100%;
            justify-content: center;
        }

        .search-input {
            width: 150px;
        }

        .search-input:focus {
            width: 200px;
        }
    }

    /* Update card styles */
    .card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(3px);
        border: 1px solid rgba(220, 220, 220, 0.3);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .card-body {
        background: rgba(255, 255, 255, 0.75);
    }

    /* Update left navbar */
    .left-navbar {
        background: linear-gradient(135deg, rgba(40, 40, 40, 0.9) 0%, rgba(60, 60, 60, 0.9) 100%);
        backdrop-filter: blur(8px);
    }

    /* Update modal styles */
    .modal-content {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
    }

    .modal-title {
        color: #333;
        font-weight: 600;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .form-label {
        color: #555;
        font-weight: 500;
    }

    .form-control {
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        padding: 0.75rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .btn-close {
        opacity: 0.5;
        transition: opacity 0.3s ease;
    }

    .btn-close:hover {
        opacity: 1;
    }

    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
        padding: 0.75rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }

    /* Update tech news items */
    .tech-news-item, .article-item {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(3px);
    }

    .tech-news-content h5 {
        color: #fff;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
    }

    .tech-news-content p {
        color: rgba(255, 255, 255, 0.8);
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    }

    /* Add a subtle overlay to the background image */
    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(245, 245, 245, 0.3);
        pointer-events: none;
        z-index: -1;
    }

    /* Category Navigation Styles */
    .category-nav {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(5px);
        padding: 1rem 0;
        margin-bottom: 2rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .category-item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        color: #333;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s ease;
        margin: 0 10px;
    }

    .category-item:hover {
        background: rgba(0, 0, 0, 0.05);
        transform: translateY(-2px);
    }

    .category-item.active {
        background: rgba(0, 0, 0, 0.1);
    }

    .category-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin-right: 10px;
        color: white;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .category-nav {
            padding: 0.5rem 0;
        }
        
        .category-item {
            padding: 8px 12px;
            margin: 5px;
        }
        
        .category-icon {
            width: 30px;
            height: 30px;
        }
    }

    /* Add dropdown styles */
    .category-dropdown {
        position: relative;
        margin: 0 10px;
    }

    .category-menu-icon {
        margin-left: 10px;
        font-size: 0.8rem;
        transition: transform 0.3s ease;
    }

    .category-dropdown:hover .category-menu-icon {
        transform: rotate(180deg);
    }

    .category-topics {
        position: absolute;
        top: 100%;
        left: 0;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(5px);
        border-radius: 8px;
        padding: 15px;
        min-width: 200px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        display: none;
        z-index: 1000;
    }

    .videos-section {
        margin-bottom: 15px;
    }

    .videos-section:last-child {
        margin-bottom: 0;
    }

    .section-title {
        color: #666;
        font-size: 0.9rem;
        margin-bottom: 8px;
        padding-bottom: 5px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }

    .video-link {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        color: #333;
        text-decoration: none;
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .video-link i {
        margin-right: 8px;
        color: #ff0000;
    }

    .video-link:hover {
        background: rgba(0, 0, 0, 0.05);
        color: #007bff;
        transform: translateX(5px);
    }

    .category-dropdown:hover .category-topics {
        display: block;
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .category-dropdown {
            margin: 5px;
        }
        
        .category-topics {
            position: static;
            display: none;
            width: 100%;
            margin-top: 5px;
        }
        
        .category-dropdown:hover .category-topics {
            display: block;
        }
    }

    /* Update video modal styles */
    .video-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 1100;
    }

    .modal-content {
        position: relative;
        background: white;
        margin: 50px auto;
        padding: 20px;
        width: 90%;
        max-width: 800px;
        border-radius: 8px;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .close-modal {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #666;
    }

    .video-container {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
        height: 0;
        overflow: hidden;
    }

    .video-container iframe {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 4px;
    }

    /* Update video link styles */
    .video-link {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        color: #333;
        text-decoration: none;
        border-radius: 4px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .video-link i {
        margin-right: 8px;
        color: #ff0000;
    }

    .video-link:hover {
        background: rgba(0, 0, 0, 0.05);
        color: #007bff;
        transform: translateX(5px);
    }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <div class="navbar-brand-container">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="navbar-brand" href="#">
                    <i class="fas fa-globe-americas globe-icon"></i>
                    <span class="brand-title">WorldView Today</span>
                </a>
            </div>
            <div class="search-login-container">
                <div class="search-container">
                    <form class="search-form" action="" method="GET">
                        <input type="text" name="search" class="search-input" placeholder="Search for news..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="search-button">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="login-icon-container">
                    <a href="login.php" class="login-link">
                        <i class="fas fa-user-circle login-icon"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Left Navbar -->
    <div class="left-navbar">
        <div class="menu-content" id="menuContent">
            <div class="tech-news-section">
                <h3 class="section-heading">
                    <i class="fas fa-microchip"></i> Technology News
                </h3>
                <div class="tech-news-list">
                    <div class="tech-news-item">
                        <div class="tech-news-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="tech-news-content">
                            <h5>AI & Machine Learning</h5>
                            <p>Latest developments in artificial intelligence and machine learning</p>
                            <div class="tech-news-links">
                                <a href="https://www.technologyreview.com/artificial-intelligence" target="_blank">MIT Tech Review</a>
                                <a href="https://www.wired.com/tag/artificial-intelligence" target="_blank">Wired AI</a>
                            </div>
                            <div class="video-links">
                                <a href="#" class="video-link" data-video="https://www.youtube.com/embed/dQw4w9WgXcQ">
                                    <i class="fas fa-play-circle"></i> AI News
                                </a>
                                <a href="#" class="video-link" data-video="https://www.youtube.com/embed/aircAruvnKk">
                                    <i class="fas fa-play-circle"></i> Machine Learning
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="tech-news-item">
                        <div class="tech-news-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <div class="tech-news-content">
                            <h5>Mobile Technology</h5>
                            <p>New smartphone releases and mobile technology updates</p>
                            <div class="tech-news-links">
                                <a href="https://www.theverge.com/mobile" target="_blank">The Verge Mobile</a>
                                <a href="https://www.gsmarena.com" target="_blank">GSMArena</a>
                            </div>
                            <div class="video-links">
                                <a href="#" class="video-link" data-video="https://www.youtube.com/embed/9t1Ln0yQxQw">
                                    <i class="fas fa-play-circle"></i> Latest Phones
                                </a>
                                <a href="#" class="video-link" data-video="https://www.youtube.com/embed/8mmtU8mzTaQ">
                                    <i class="fas fa-play-circle"></i> Mobile Tech
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="tech-news-item">
                        <div class="tech-news-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="tech-news-content">
                            <h5>Cybersecurity</h5>
                            <p>Latest security threats and protection measures</p>
                            <div class="tech-news-links">
                                <a href="https://www.bleepingcomputer.com" target="_blank">BleepingComputer</a>
                                <a href="https://www.securityweek.com" target="_blank">SecurityWeek</a>
                            </div>
                            <div class="video-links">
                                <a href="#" class="video-link" data-video="https://www.youtube.com/embed/inWWhr5tnEA">
                                    <i class="fas fa-play-circle"></i> Security News
                                </a>
                                <a href="#" class="video-link" data-video="https://www.youtube.com/embed/JdfmV2KW11I">
                                    <i class="fas fa-play-circle"></i> Cyber Threats
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="tech-news-item">
                        <div class="tech-news-icon">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <div class="tech-news-content">
                            <h5>Web Development</h5>
                            <p>Latest trends in web development and design</p>
                            <div class="tech-news-links">
                                <a href="https://www.smashingmagazine.com" target="_blank">Smashing Magazine</a>
                                <a href="https://www.css-tricks.com" target="_blank">CSS-Tricks</a>
                            </div>
                            <div class="video-links">
                                <a href="#" class="video-link" data-video="https://www.youtube.com/embed/1Rs2ND1ryYc">
                                    <i class="fas fa-play-circle"></i> Web Dev Tips
                                </a>
                                <a href="#" class="video-link" data-video="https://www.youtube.com/embed/YqQx75OPRa0">
                                    <i class="fas fa-play-circle"></i> Design Trends
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="tech-news-item">
                        <div class="tech-news-icon">
                            <i class="fas fa-cloud"></i>
                        </div>
                        <div class="tech-news-content">
                            <h5>Cloud Computing</h5>
                            <p>Cloud technology advancements and implementations</p>
                            <div class="tech-news-links">
                                <a href="https://www.infoworld.com/category/cloud-computing" target="_blank">InfoWorld Cloud</a>
                                <a href="https://www.zdnet.com/topic/cloud" target="_blank">ZDNet Cloud</a>
                            </div>
                            <div class="video-links">
                                <a href="#" class="video-link" data-video="https://www.youtube.com/embed/M988_fsOSWo">
                                    <i class="fas fa-play-circle"></i> Cloud News
                                </a>
                                <a href="#" class="video-link" data-video="https://www.youtube.com/embed/3hHmUj1Gpgc">
                                    <i class="fas fa-play-circle"></i> Cloud Tech
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="tech-news-item">
                        <div class="tech-news-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <div class="tech-news-content">
                            <h5>Data Science</h5>
                            <p>Big data analytics and data science innovations</p>
                            <div class="tech-news-links">
                                <a href="https://www.kdnuggets.com" target="_blank">KDnuggets</a>
                                <a href="https://www.datasciencecentral.com" target="_blank">Data Science Central</a>
                            </div>
                            <div class="video-links">
                                <a href="#" class="video-link" data-video="https://www.youtube.com/embed/ua-CiDNNj30">
                                    <i class="fas fa-play-circle"></i> Data Science
                                </a>
                                <a href="#" class="video-link" data-video="https://www.youtube.com/embed/1uV5iMqUQr4">
                                    <i class="fas fa-play-circle"></i> Big Data
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category Navigation -->
    <div class="category-nav">
        <div class="container">
            <div class="d-flex justify-content-center flex-wrap">
                <div class="category-dropdown">
                    <a href="?category=top_stories" class="category-item <?php echo $category === 'top_stories' ? 'active' : ''; ?>">
                        <div class="category-icon bg-primary">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <span>Top Stories</span>
                        <i class="fas fa-chevron-down category-menu-icon"></i>
                    </a>
                    <div class="category-topics">
                        <div class="videos-section">
                            <h6 class="section-title">Latest Videos</h6>
                            <a href="https://www.oneindia.com/videos/national-c50/" class="video-link" target="_blank">
                                <i class="fas fa-play-circle"></i> National News
                            </a>
                            <a href="https://www.indiatoday.in/politics/videos" class="video-link" target="_blank">
                                <i class="fas fa-play-circle"></i> Political Updates
                            </a>
                        </div>
                    </div>
                </div>
                <div class="category-dropdown">
                    <a href="?category=india" class="category-item <?php echo $category === 'india' ? 'active' : ''; ?>">
                        <div class="category-icon bg-danger">
                            <i class="fas fa-flag"></i>
                        </div>
                        <span>India</span>
                        <i class="fas fa-chevron-down category-menu-icon"></i>
                    </a>
                    <div class="category-topics">
                        <div class="videos-section">
                            <h6 class="section-title">India Videos</h6>
                            <a href="https://www.oneindia.com/videos/india-c2/" class="video-link" target="_blank">
                                <i class="fas fa-play-circle"></i> India News
                            </a>
                            <a href="https://www.indiatoday.in/politics/videos" class="video-link" target="_blank">
                                <i class="fas fa-play-circle"></i> Political News
                            </a>
                        </div>
                    </div>
                </div>
                <div class="category-dropdown">
                    <a href="?category=business" class="category-item <?php echo $category === 'business' ? 'active' : ''; ?>">
                        <div class="category-icon bg-success">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span>Business</span>
                        <i class="fas fa-chevron-down category-menu-icon"></i>
                    </a>
                    <div class="category-topics">
                        <div class="videos-section">
                            <h6 class="section-title">Business Videos</h6>
                            <a href="https://www.reuters.com/world/india/us-tariffs-hit-indias-gdp-growth-prompt-more-rate-cuts-2025-04-04/" class="video-link" target="_blank">
                                <i class="fas fa-play-circle"></i> Economy Updates
                            </a>
                            <a href="https://www.oneindia.com/videos/business-c3/" class="video-link" target="_blank">
                                <i class="fas fa-play-circle"></i> Business News
                            </a>
                        </div>
                    </div>
                </div>
                <div class="category-dropdown">
                    <a href="?category=agriculture" class="category-item <?php echo $category === 'agriculture' ? 'active' : ''; ?>">
                        <div class="category-icon" style="background: linear-gradient(135deg, #8BC34A 0%, #4CAF50 100%);">
                            <i class="fas fa-seedling"></i>
                        </div>
                        <span>Agriculture</span>
                        <i class="fas fa-chevron-down category-menu-icon"></i>
                    </a>
                    <div class="category-topics">
                        <div class="videos-section">
                            <h6 class="section-title">Agriculture Videos</h6>
                            <a href="https://www.oneindia.com/videos/national-c50/" class="video-link" target="_blank">
                                <i class="fas fa-play-circle"></i> Farming News
                            </a>
                            <a href="https://www.oneindia.com/videos/india-c2/" class="video-link" target="_blank">
                                <i class="fas fa-play-circle"></i> Agricultural Updates
                            </a>
                        </div>
                    </div>
                </div>
                <div class="category-dropdown">
                    <a href="?category=climate" class="category-item <?php echo $category === 'climate' ? 'active' : ''; ?>">
                        <div class="category-icon" style="background: linear-gradient(135deg, #00BCD4 0%, #0097A7 100%);">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <span>Climate & Environment</span>
                        <i class="fas fa-chevron-down category-menu-icon"></i>
                    </a>
                    <div class="category-topics">
                        <div class="videos-section">
                            <h6 class="section-title">Climate Videos</h6>
                            <a href="https://www.oneindia.com/videos/national-c50/" class="video-link" target="_blank">
                                <i class="fas fa-play-circle"></i> Environmental News
                            </a>
                            <a href="https://www.oneindia.com/videos/india-c2/" class="video-link" target="_blank">
                                <i class="fas fa-play-circle"></i> Climate Updates
                            </a>
                        </div>
                    </div>
                </div>
                <div class="category-dropdown">
                    <a href="?category=sports" class="category-item <?php echo $category === 'sports' ? 'active' : ''; ?>">
                        <div class="category-icon bg-warning">
                            <i class="fas fa-running"></i>
                        </div>
                        <span>Sports</span>
                        <i class="fas fa-chevron-down category-menu-icon"></i>
                    </a>
                    <div class="category-topics">
                        <div class="videos-section">
                            <h6 class="section-title">Sports Videos</h6>
                            <a href="https://www.ndtv.com/video/sports" class="video-link" target="_blank">
                                <i class="fas fa-play-circle"></i> Sports Highlights
                            </a>
                            <a href="https://www.oneindia.com/videos/sports-c4/" class="video-link" target="_blank">
                                <i class="fas fa-play-circle"></i> Live Matches
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update main content to start from the left edge -->
    <div class="main-content">
        <div class="container mt-4">
            <?php if ($featuredArticle): ?>
                <div class="featured-article mb-4">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($featuredArticle['image']); ?>" class="card-img-top featured-img" alt="<?php echo htmlspecialchars($featuredArticle['title']); ?>">
                        <div class="card-body">
                            <h2 class="card-title">
                                <a href="<?php echo htmlspecialchars($featuredArticle['link']); ?>" target="_blank" class="text-decoration-none">
                                    <?php echo htmlspecialchars($featuredArticle['title']); ?>
                                </a>
                            </h2>
                            <p class="card-text"><?php echo htmlspecialchars($featuredArticle['summary']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-primary">
                                    <i class="bi bi-newspaper"></i> Featured Story
                                </span>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i> <?php echo formatDate($featuredArticle['pubDate']); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <?php if (empty($news)): ?>
                    <div class="alert alert-info">
                        No news found. Please try a different category or search term.
                    </div>
                <?php else: ?>
                    <div class="news-grid">
                        <?php foreach ($news as $item): ?>
                            <div class="card">
                                <div class="card-image-container">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                    <div class="card-category">
                                        <span class="badge bg-primary"><?php echo ucfirst($category); ?></span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($item['title']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars($item['summary']); ?></p>
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge <?php echo getSentimentColor($item['sentiment']); ?>">
                                                <i class="fas <?php echo getSentimentIcon($item['sentiment']); ?>"></i>
                                                <?php echo number_format($item['sentiment'], 2); ?>
                                            </span>
                                            <?php if (!empty($item['entities']['people'])): ?>
                                                <span class="badge bg-info">
                                                    <i class="fas fa-user"></i>
                                                    <?php echo count($item['entities']['people']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($item['entities']['states'])): ?>
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                    <?php echo count($item['entities']['states']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($item['pubDate'])); ?>
                                        </small>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($item['link']); ?>" class="btn btn-primary mt-2" target="_blank">Read More</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?category=<?php echo $category; ?>&page=<?php echo ($page - 1); ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?category=<?php echo $category; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?category=<?php echo $category; ?>&page=<?php echo ($page + 1); ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Video Modal -->
    <div class="video-modal" id="videoModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Video Player</h5>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="video-container">
                    <iframe id="videoFrame" width="100%" height="400" frameborder="0" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const leftNavbar = document.querySelector('.left-navbar');
        const dragHandle = document.createElement('div');
        dragHandle.className = 'drag-handle';
        leftNavbar.appendChild(dragHandle);

        let isDragging = false;
        let startX;
        let startLeft;

        menuToggle.addEventListener('click', function() {
            leftNavbar.classList.toggle('expanded');
        });

        dragHandle.addEventListener('mousedown', function(e) {
            isDragging = true;
            startX = e.clientX;
            startLeft = leftNavbar.offsetLeft;
            document.body.style.cursor = 'col-resize';
        });

        document.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            
            const left = startLeft + (e.clientX - startX);
            if (left >= -400 && left <= 0) {
                leftNavbar.style.left = left + 'px';
            }
        });

        document.addEventListener('mouseup', function() {
            if (!isDragging) return;
            
            isDragging = false;
            document.body.style.cursor = '';
            
            // Snap to edges if close enough
            const currentLeft = leftNavbar.offsetLeft;
            if (currentLeft > -200) {
                leftNavbar.classList.add('expanded');
            } else {
                leftNavbar.classList.remove('expanded');
            }
        });

        const videoModal = document.getElementById('videoModal');
        const videoFrame = document.getElementById('videoFrame');
        const closeModal = document.querySelector('.close-modal');
        const videoLinks = document.querySelectorAll('.video-link');

        // Handle video link clicks
        videoLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const videoUrl = this.getAttribute('href');
                
                // Extract video ID from URL
                let videoId = '';
                if (videoUrl.includes('youtube.com')) {
                    videoId = videoUrl.split('v=')[1];
                    if (videoId.includes('&')) {
                        videoId = videoId.split('&')[0];
                    }
                } else if (videoUrl.includes('youtu.be')) {
                    videoId = videoUrl.split('/').pop();
                }
                
                // Set iframe source
                if (videoId) {
                    videoFrame.src = `https://www.youtube.com/embed/${videoId}`;
                    videoModal.style.display = 'block';
                } else {
                    // If not a YouTube video, open in new tab
                    window.open(videoUrl, '_blank');
                }
            });
        });

        // Close modal when clicking close button
        closeModal.addEventListener('click', function() {
            videoModal.style.display = 'none';
            videoFrame.src = ''; // Stop video when closing
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === videoModal) {
                videoModal.style.display = 'none';
                videoFrame.src = ''; // Stop video when closing
            }
        });
    });
    </script>
</body>
</html> 