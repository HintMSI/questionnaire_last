<?php
session_start();
require_once 'config/database.php';

// Получаем ID опроса из URL
$questionnaire_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['full_name'])) {
    // Сохраняем ФИО в сессию
    $_SESSION['full_name'] = trim($_POST['full_name']);
    $_SESSION['questionnaire_id'] = $questionnaire_id;
    
    // Сохраняем пользователя в БД
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        $query = "INSERT INTO users (full_name) VALUES (:full_name) RETURNING id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':full_name', $_SESSION['full_name']);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['user_id'] = $user['id'];
    }
}

// Если ФИО не введено, показываем форму для ввода
if (!isset($_SESSION['full_name'])) {
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Ввод данных - DevOps Опросник</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Перед началом теста</h1>
        <p>Для прохождения опроса необходимо ввести ваше ФИО</p>
        
        <form method="POST" action="">
            <input type="hidden" name="questionnaire_id" value="<?php echo $questionnaire_id; ?>">
            
            <div class="form-group">
                <label for="full_name">ФИО (полное имя):</label>
                <input type="text" id="full_name" name="full_name" required 
                       class="form-control" placeholder="Иванов Иван Иванович">
            </div>
            
            <button type="submit" class="btn btn-primary">Начать тест</button>
            <a href="index.php" class="btn btn-secondary">На главную</a>
        </form>
    </div>
</body>
</html>
<?php
    exit();
}

// Если ФИО есть, показываем вопросы
$database = new Database();
$db = $database->getConnection();

// Получаем информацию об опросе
$query = "SELECT * FROM questionnaires WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $questionnaire_id);
$stmt->execute();
$questionnaire = $stmt->fetch(PDO::FETCH_ASSOC);

// Получаем вопросы
$query = "SELECT q.*, 
          (SELECT COUNT(*) FROM question_options WHERE question_id = q.id) as has_options
          FROM questions q 
          WHERE q.questionnaire_id = :id 
          ORDER BY q.question_order";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $questionnaire_id);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($questionnaire['title']); ?></title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($questionnaire['title']); ?></h1>
        <p><?php echo htmlspecialchars($questionnaire['description']); ?></p>
        <p class="user-info">Участник: <strong><?php echo htmlspecialchars($_SESSION['full_name']); ?></strong></p>
        
        <form method="POST" action="save_results.php" id="questionnaire-form">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
            <input type="hidden" name="questionnaire_id" value="<?php echo $questionnaire_id; ?>">
            
            <?php foreach ($questions as $index => $question): ?>
            <div class="question-card">
                <h3>Вопрос <?php echo $index + 1; ?>:</h3>
                <p class="question-text"><?php echo htmlspecialchars($question['question_text']); ?></p>
                
                <?php if ($question['question_type'] === 'text'): ?>
                    <div class="form-group">
                        <label for="answer_<?php echo $question['id']; ?>">Ваш ответ:</label>
                        <textarea id="answer_<?php echo $question['id']; ?>" 
                                  name="answer[<?php echo $question['id']; ?>]" 
                                  rows="3" class="form-control" required></textarea>
                    </div>
                
                <?php elseif ($question['question_type'] === 'radio'): ?>
                    <div class="options-group">
                        <?php
                        $options_query = "SELECT * FROM question_options WHERE question_id = :qid";
                        $options_stmt = $db->prepare($options_query);
                        $options_stmt->bindParam(':qid', $question['id']);
                        $options_stmt->execute();
                        $options = $options_stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($options as $option):
                        ?>
                        <div class="radio-option">
                            <input type="radio" 
                                   id="option_<?php echo $option['id']; ?>"
                                   name="answer[<?php echo $question['id']; ?>]" 
                                   value="<?php echo htmlspecialchars($option['option_text']); ?>" 
                                   required>
                            <label for="option_<?php echo $option['id']; ?>">
                                <?php echo htmlspecialchars($option['option_text']); ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                
                <?php elseif ($question['question_type'] === 'checkbox'): ?>
                    <div class="options-group">
                        <?php
                        $options_query = "SELECT * FROM question_options WHERE question_id = :qid";
                        $options_stmt = $db->prepare($options_query);
                        $options_stmt->bindParam(':qid', $question['id']);
                        $options_stmt->execute();
                        $options = $options_stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($options as $option):
                        ?>
                        <div class="checkbox-option">
                            <input type="checkbox" 
                                   id="option_<?php echo $option['id']; ?>"
                                   name="answer[<?php echo $question['id']; ?>][]" 
                                   value="<?php echo htmlspecialchars($option['option_text']); ?>">
                            <label for="option_<?php echo $option['id']; ?>">
                                <?php echo htmlspecialchars($option['option_text']); ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                        <small>Можно выбрать несколько вариантов</small>
                    </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-large">Отправить ответы</button>
                <button type="reset" class="btn btn-secondary">Очистить форму</button>
                <a href="index.php" class="btn btn-link">Отмена</a>
            </div>
        </form>
    </div>
</body>
</html>
<?php
// Очищаем сессию после показа формы (очистим полностью после сохранения)
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    session_destroy();
}
?>