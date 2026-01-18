<?php
session_start();
require_once 'config/database.php';

// Проверяем, что форма была отправлена
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

// Проверяем необходимые данные
if (!isset($_POST['user_id']) || !isset($_POST['answer'])) {
    die('<div class="container"><h2>Ошибка: Недостаточно данных</h2></div>');
}

$user_id = (int)$_POST['user_id'];
$answers = $_POST['answer'];

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    die('<div class="container"><h2>Ошибка подключения к базе данных</h2></div>');
}

// Сохраняем каждый ответ
$success_count = 0;
$error_count = 0;

foreach ($answers as $question_id => $answer_value) {
    $question_id = (int)$question_id;
    
    // Обрабатываем checkbox (массив значений) и radio/text (строка)
    if (is_array($answer_value)) {
        $answer_text = implode(', ', array_filter($answer_value));
    } else {
        $answer_text = trim($answer_value);
    }
    
    // Пропускаем пустые ответы
    if (empty($answer_text)) {
        continue;
    }
    
    try {
        $query = "INSERT INTO answers (user_id, question_id, answer_text) 
                  VALUES (:user_id, :question_id, :answer_text)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':question_id', $question_id, PDO::PARAM_INT);
        $stmt->bindParam(':answer_text', $answer_text);
        
        if ($stmt->execute()) {
            $success_count++;
        } else {
            $error_count++;
        }
    } catch (PDOException $e) {
        error_log("Error saving answer: " . $e->getMessage());
        $error_count++;
    }
}

// Очищаем сессию
$user_name = $_SESSION['full_name'] ?? 'Пользователь';
session_destroy();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Спасибо за участие!</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .success-box {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
        }
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            min-width: 150px;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            display: block;
        }
        .stat-success { color: #28a745; }
        .stat-error { color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-box">
            <h1>✅ Спасибо за участие, <?php echo htmlspecialchars($user_name); ?>!</h1>
            <p>Ваши ответы успешно сохранены в базе данных.</p>
        </div>
        
        <div class="stats">
            <div class="stat-item">
                <span class="stat-number stat-success"><?php echo $success_count; ?></span>
                <span>ответов сохранено</span>
            </div>
            
            <?php if ($error_count > 0): ?>
            <div class="stat-item">
                <span class="stat-number stat-error"><?php echo $error_count; ?></span>
                <span>ошибок при сохранении</span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary">Вернуться на главную</a>
            <a href="view_results.php" class="btn btn-secondary">Посмотреть все результаты</a>
            <a href="take_test.php?id=1" class="btn btn-outline">Пройти ещё один тест</a>
        </div>
        
        <div class="info-box" style="margin-top: 30px; padding: 15px; background: #e9ecef; border-radius: 5px;">
            <h3>Что дальше?</h3>
            <ul>
                <li>Администратор может просмотреть все ответы на странице "Результаты"</li>
                <li>Вы можете пройти другие доступные опросы</li>
                <li>Технические данные сохранены в PostgreSQL базе данных</li>
            </ul>
        </div>
    </div>
</body>
</html>