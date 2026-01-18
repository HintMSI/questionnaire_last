<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Получаем список опросов
$query = "SELECT * FROM questionnaires ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$questionnaires = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Статистика
$stats = [];
if ($db) {
    $stats['users'] = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $stats['answers'] = $db->query("SELECT COUNT(*) FROM answers")->fetchColumn();
    $stats['questionnaires'] = $db->query("SELECT COUNT(*) FROM questionnaires")->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevOps Опросник</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 20px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 40px;
        }
        .hero h1 {
            font-size: 3em;
            margin-bottom: 20px;
        }
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }
        .stat-item {
            background: white;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
            display: block;
        }
        .questionnaire-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .questionnaire-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            transition: transform 0.3s;
        }
        .questionnaire-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .questionnaire-card h3 {
            margin-top: 0;
            color: #333;
        }
        .questionnaire-card .description {
            color: #666;
            margin: 10px 0;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            background: #e9ecef;
            border-radius: 12px;
            font-size: 0.8em;
            margin-right: 5px;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .system-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Шапка -->
        <div class="hero">
            <h1>DevOps Опросник</h1>
            <p>Платформа для проведения опросов и тестирования знаний в области DevOps</p>
            <p>Используется в учебных целях для проекта по DevOps</p>
        </div>
        
        <!-- Статистика -->
        <div class="stats-bar">
            <div class="stat-item">
                <span class="stat-number"><?php echo $stats['questionnaires'] ?? 0; ?></span>
                <span>доступных опросов</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $stats['users'] ?? 0; ?></span>
                <span>участников</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $stats['answers'] ?? 0; ?></span>
                <span>ответов</span>
            </div>
        </div>
        
        <!-- Доступные опросы -->
        <h2>Доступные опросы</h2>
        <div class="questionnaire-grid">
            <?php foreach ($questionnaires as $q): ?>
            <div class="questionnaire-card">
                <h3><?php echo htmlspecialchars($q['title']); ?></h3>
                <div class="description">
                    <?php echo htmlspecialchars($q['description']); ?>
                </div>
                <div class="meta">
                    <span class="badge">ID: <?php echo $q['id']; ?></span>
                    <span class="badge">Создан: <?php echo date('d.m.Y', strtotime($q['created_at'])); ?></span>
                </div>
                <div class="action-buttons">
                    <a href="take_test.php?id=<?php echo $q['id']; ?>" class="btn btn-primary">
                        Пройти опрос
                    </a>
                    <a href="view_results.php?questionnaire_id=<?php echo $q['id']; ?>" class="btn btn-secondary">
                        Результаты
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Карточка для добавления нового опроса (заглушка) -->
            <div class="questionnaire-card" style="border-style: dashed; border-color: #6c757d;">
                <h3 style="color: #6c757d;">+ Новый опрос</h3>
                <div class="description" style="color: #6c757d;">
                    Хотите создать собственный опрос?
                </div>
                <div class="action-buttons">
                    <a href="#" class="btn btn-outline" onclick="alert('Функция в разработке')">
                        Создать опрос
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Основные действия -->
        <div class="action-buttons" style="justify-content: center; margin: 40px 0;">
            <a href="view_results.php" class="btn btn-primary btn-large">📊 Посмотреть все результаты</a>
            <a href="admin.php" class="btn btn-secondary btn-large">⚙️ Панель администратора</a>
        </div>
        
        <!-- Информация о системе -->
        <div class="system-info">
            <h3>Информация о системе</h3>
            <p><strong>Сервер:</strong> AlmaLinux <?php echo php_uname('r'); ?></p>
            <p><strong>Веб-сервер:</strong> Nginx + PHP <?php echo phpversion(); ?></p>
            <p><strong>База данных:</strong> PostgreSQL (questionnaire_db)</p>
            <p><strong>Директория приложения:</strong> /var/www/devops-questionnaire</p>
            <p><strong>Статус БД:</strong> 
                <?php if ($db): ?>
                    <span style="color: green;">✓ Подключена</span>
                <?php else: ?>
                    <span style="color: red;">✗ Ошибка подключения</span>
                <?php endif; ?>
            </p>
        </div>
        
        <!-- Футер -->
        <footer style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; color: #6c757d;">
            <p>DevOps Questionnaire Project &copy; 2025</p>
            <p>Учебный проект по развертыванию веб-приложений</p>
            <p>AlmaLinux | Nginx | PostgreSQL | PHP</p>
        </footer>
    </div>
</body>
</html>