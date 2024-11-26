<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Climate Change Dashboard | Msasa Academy</title>

        <style>
        @import url('https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400..700;1,400..700&family=DM+Serif+Display:ital@0;1&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Arimo", sans-serif;
            background-color: #052B2B;
            color: #EBE5D5;
            line-height: 1.6;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .nav-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2rem 4rem;
            border-bottom: 0.5px solid rgba(235, 229, 213, 0.4);
            margin-bottom: 2rem;
        }

        .nav-bar h2 {
            color: #FECE63;
            font-family: "DM Serif Display", serif;
        }

        .right-nav {
            display: flex;
            align-items: center;
            gap: 3rem;
        }

        .right-nav a {
            color: #EBE5D5;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .right-nav a:hover {
            color: #FECE63;
        }

        header {
            text-align: center;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        h1 {
            font-family: "DM Serif Display", serif;
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #FECE63;
        }

        header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .dashboard {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        @media (max-width: 1024px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: rgba(235, 229, 213, 0.05);
            border-radius: 15px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(235, 229, 213, 0.1);
            height: auto;
            max-height: none;
            overflow-y: visible;
        }

        .card-header {
            border-bottom: 1px solid rgba(235, 229, 213, 0.2);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }

        .card-header h2 {
            color: #FECE63;
            font-family: "DM Serif Display", serif;
            font-size: 1.8rem;
        }

        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .tab {
            padding: 0.8rem 1.5rem;
            border: none;
            background: rgba(235, 229, 213, 0.1);
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #EBE5D5;
            font-size: 0.9rem;
        }

        .tab:hover {
            background: rgba(254, 206, 99, 0.2);
        }

        .tab.active {
            background: #FECE63;
            color: #052B2B;
        }

        .data-section {
            display: none;
        }

        .data-section.active {
            display: block;
        }

        .chart-container {
            height: auto;
            overflow-y: visible;
        }

        .iframe-container {
            margin-bottom: 2rem;
            background: rgba(235, 229, 213, 0.02);
            border-radius: 10px;
            overflow: hidden;
            height: 400px;
        }

        .iframe-container:last-child {
            margin-bottom: 0;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: transparent;
        }

        .news-section {
            max-height: 600px;
            overflow-y: auto;
            padding-right: 1rem;
        }

        .news-section::-webkit-scrollbar {
            width: 8px;
        }

        .news-section::-webkit-scrollbar-track {
            background: rgba(235, 229, 213, 0.05);
            border-radius: 4px;
        }

        .news-section::-webkit-scrollbar-thumb {
            background: rgba(254, 206, 99, 0.3);
            border-radius: 4px;
        }

        .news-item {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(235, 229, 213, 0.1);
            transition: all 0.3s ease;
        }

        .news-item:hover {
            background: rgba(235, 229, 213, 0.05);
        }

        .news-link {
            text-decoration: none;
            color: inherit;
        }

        .news-section-tag {
            color: #FECE63;
            font-size: 0.8rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .news-date {
            color: rgba(235, 229, 213, 0.6);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .news-title {
            font-size: 1.1rem;
            font-weight: 500;
            line-height: 1.4;
            margin-bottom: 0.5rem;
        }

        footer {
            text-align: center;
            padding: 2rem;
            color: rgba(235, 229, 213, 0.6);
            border-top: 1px solid rgba(235, 229, 213, 0.1);
            margin-top: 2rem;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: rgba(235, 229, 213, 0.6);
        }

        .error-message {
            color: #ff6b6b;
            padding: 1.5rem;
            text-align: center;
            background: rgba(255, 107, 107, 0.1);
            border-radius: 10px;
        }

        .card::-webkit-scrollbar {
            width: 8px;
        }

        .card::-webkit-scrollbar-track {
            background: rgba(235, 229, 213, 0.05);
            border-radius: 4px;
        }

        .card::-webkit-scrollbar-thumb {
            background: rgba(254, 206, 99, 0.3);
            border-radius: 4px;
        }

        .logout-btn {
            color: #EBE5D5;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 8px;
            border: 1px solid #FECE63;
            transition: all 0.3s;
        }
        </style>
    </head>

    <body>
        <nav class="nav-bar">
            <a href="../index.html">
                <h2>Msasa.</h2>
            </a>
            <div class="right-nav">
                <a href="forum/index.php">Forum</a>
                <?php if ($_SESSION['role'] === 'student'): ?>
                <a href="student/available_quizzes.php">Quizzes</a>
                <?php elseif ($_SESSION['role'] === 'teacher'): ?>
                <a href="teacher/dashboard.php">Quizzes</a>
                <?php endif; ?>
                <a href="../auth/logout.php" class="logout-btn">Logout</a>
            </div>
        </nav>

        <div class="container">
            <header>
                <h1>Climate Change Dashboard</h1>
                <p>Real-time climate data visualization and news</p>
            </header>

            <div class="dashboard">
                <div class="card">
                    <div class="card-header">
                        <h2>Key Climate Indicators</h2>
                    </div>
                    <div class="tabs">
                        <button class="tab active" data-tab="temperature">Temperature</button>
                        <button class="tab" data-tab="greenhouse">Greenhouse Gases</button>
                        <button class="tab" data-tab="tracker">Climate Change Tracker</button>
                    </div>
                    <div class="chart-container">
                        <!-- Temperature Section -->
                        <div id="temperature-section" class="data-section active">
                            <div class="iframe-container">
                                <iframe
                                    src="https://cdn.climatechangetracker.org/embedding/yearly-average-temperature-anomaly"
                                    scrolling="no" frameBorder="0"></iframe>
                            </div>
                            <div class="iframe-container">
                                <iframe src="https://cdn.climatechangetracker.org/embedding/rate-of-change"
                                    scrolling="no" frameBorder="0"></iframe>
                            </div>
                        </div>

                        <div id="greenhouse-section" class="data-section">
                            <div class="iframe-container">
                                <iframe
                                    src="https://cdn.climatechangetracker.org/embedding/monthly-greenhouse-gases-impact-on-energy-balance"
                                    scrolling="no" frameBorder="0"></iframe>
                            </div>
                            <div class="iframe-container">
                                <iframe
                                    src="https://cdn.climatechangetracker.org/embedding/human-induced-yearly-co2-emissions"
                                    scrolling="no" frameBorder="0"></iframe>
                            </div>
                        </div>

                        <div id="tracker-section" class="data-section">
                            <div class="iframe-container">
                                <iframe src="https://cdn.climatechangetracker.org/embedding/warmingstripes"
                                    scrolling="no" frameBorder="0"></iframe>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Latest Environmental News</h2>
                    </div>
                    <div id="news-container" class="news-section">
                        <div class="loading">Loading news...</div>
                    </div>
                </div>
            </div>

            <footer>
                <p>Data sources: Climate Change Tracker, Climate Home News</p>
                <p>Last updated: <span id="last-updated"></span></p>
            </footer>
        </div>

        <script>
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.data-section').forEach(section =>
                    section.classList.remove('active')
                );

                tab.classList.add('active');

                const sectionId = `${tab.dataset.tab}-section`;
                document.getElementById(sectionId).classList.add('active');
            });
        });

        const GUARDIAN_API_KEY = '8d9fd1f9-c9d9-4228-87e0-8a92d0cdd5a2';
        const GUARDIAN_API_URL = 'https://content.guardianapis.com/search';

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        async function fetchGuardianNews() {
            const newsContainer = document.getElementById('news-container');

            try {
                const params = new URLSearchParams({
                    'api-key': GUARDIAN_API_KEY,
                    'section': 'environment',
                    'order-by': 'newest',
                    'page-size': 10,
                    'show-fields': 'all',
                    'q': 'climate OR environment OR sustainability OR "climate change"'
                });

                const response = await fetch(`${GUARDIAN_API_URL}?${params}`);
                const data = await response.json();

                if (data.response.status === 'ok') {
                    newsContainer.innerHTML = '';

                    data.response.results.forEach(article => {
                        const newsItem = document.createElement('div');
                        newsItem.className = 'news-item';

                        const articleLink = document.createElement('a');
                        articleLink.href = article.webUrl;
                        articleLink.className = 'news-link';
                        articleLink.target = '_blank';

                        articleLink.innerHTML = `
                            <div class="news-section-tag">${article.sectionName}</div>
                            <div class="news-date">${formatDate(article.webPublicationDate)}</div>
                            <div class="news-title">${article.webTitle}</div>
                        `;

                        newsItem.appendChild(articleLink);
                        newsContainer.appendChild(newsItem);
                    });
                } else {
                    throw new Error('Failed to fetch news');
                }
            } catch (error) {
                console.error('Error fetching news:', error);
                newsContainer.innerHTML = `
                    <div class="error-message">
                        Unable to load news at this time. Please try again later.
                    </div>
                `;
            }
        }

        fetchGuardianNews();

        setInterval(fetchGuardianNews, 300000);

        document.getElementById('last-updated').textContent = new Date().toLocaleDateString();
        </script>
        <script src="../assets/js/auth.js"></script>
    </body>
</html>
