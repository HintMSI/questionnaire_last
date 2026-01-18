<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Получаем список опросов для фильтра
$questionnaires_query = "SELECT * FROM questionnaires ORDER BY title";
$questionnaires_stmt = $db->query($questionnaires_query);
$questionnaires = $questionnaires_stmt->fetchAll(PDO::FETCH_ASSOC);

// Определяем выбранный опрос (из GET параметра или первый)
$selected_questionnaire = isset($_GET['questionnaire_id']) ? (int)$_GET['questionnaire_id'] : 1;

// Получаем результаты
$results_query = "
    SELECT 
        u.full_name,
        u.created_at as user_created,
        qn.title as questionnaire_title,
        q.question_text,
        q.question_type,
        a.answer_text,
        a.answered_at
    FROM answers a
    JOIN users u ON a.user_id = u.id
    JOIN questions q ON a.question_id = q.id
    JOIN questionnaires qn ON q.questionnaire_id = qn.id
    WHERE qn.id = :questionnaire_id
    ORDER BY u.created_at DESC, q.question_order, a.answered_at
";

$results_stmt = $db->prepare($results_query);
$results_stmt->bindParam(':questionnaire_id', $selected_questionnaire, PDO::PARAM_INT);
$results_stmt->execute();
$results = $results_stmt->fetchAll(PDO::FETCH_ASSOC);

// Группируем результаты по пользователю
$grouped_results = [];
foreach ($results as $result) {
    $user_name = $result['full_name'];
    if (!isset($grouped_results[$user_name])) {
        $grouped_results[$user_name] = [
            'user_created' => $result['user_created'],
            'questionnaire_title' => $result['questionnaire_title'],
            'answers' => []
        ];
    }
    $grouped_results[$user_name]['answers'][] = $result;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Результаты опросов</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .user-results {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 30px;
            overflow: hidden;
        }
        .user-header {
            background: #007bff;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-answers {
            padding: 20px;
        }
        .answer-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .answer-item:last-child {
            border-bottom: none;
        }
        .question-text {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .answer-text {
            color: #555;
            padding-left: 20px;
        }
        .answer-meta {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 5px;
        }
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #007bff;
        }
        .export-buttons {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Результаты опросов</h1>
        
        <!-- Статистика -->
        <div class="stats-summary">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($grouped_results); ?></div>
                <div>участников</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($results); ?></div>
                <div>всего ответов</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($questionnaires); ?></div>
                <div>опросов</div>
            </div>
        </div>
        
        <!-- Фильтр по опросам -->
        <div class="filter-section">
            <h3>Фильтр по опросам:</h3>
            <form method="GET" action="" class="filter-form">
                <select name="questionnaire_id" onchange="this.form.submit()" class="form-control">
                    <?php foreach ($questionnaires as $q): ?>
                    <option value="<?php echo $q['id']; ?>" 
                        <?php echo ($q['id'] == $selected_questionnaire) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($q['title']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        
        <!-- Кнопки экспорта -->
        <div class="export-buttons">
            <a href="export_results.php?format=html&questionnaire_id=<?php echo $selected_questionnaire; ?>" 
               class="btn btn-secondary" target="_blank">📄 Экспорт в HTML</a>
            <button onclick="window.print()" class="btn btn-outline">🖨️ Печать</button>
        </div>
        
        <!-- Результаты по пользователям -->
        <?php if (empty($grouped_results)): ?>
            <div class="alert alert-info">
                <p>Пока нет результатов для этого опроса.</p>
                <a href="index.php" class="btn btn-primary">Вернуться на главную</a>
            </div>
        <?php else: ?>
            <?php foreach ($grouped_results as $user_name => $user_data): ?>
            <div class="user-results">
                <div class="user-header">
                    <div>
                        <h3 style="margin: 0;"><?php echo htmlspecialchars($user_name); ?></h3>
                        <small>Опрос: <?php echo htmlspecialchars($user_data['questionnaire_title']); ?></small>
                    </div>
                    <div>
                        <small>Прошел: <?php echo date('d.m.Y H:i', strtotime($user_data['user_created'])); ?></small>
                    </div>
                </div>
                
                <div class="user-answers">
                    <?php foreach ($user_data['answers'] as $index => $answer): ?>
                    <div class="answer-item">
                        <div class="question-text">
                            Вопрос <?php echo $index + 1; ?>: <?php echo htmlspecialchars($answer['question_text']); ?>
                            <span class="badge"><?php echo $answer['question_type']; ?></span>
                        </div>
                        <div class="answer-text">
                            <?php echo nl2br(htmlspecialchars($answer['answer_text'])); ?>
                        </div>
                        <div class="answer-meta">
                            Ответ дан: <?php echo date('d.m.Y H:i', strtotime($answer['answered_at'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary">На главную</a>
            <a href="clear_results.php" class="btn btn-danger" 
               onclick="return confirm('Вы уверены? Это удалит ВСЕ результаты!');">
               🗑️ Очистить все результаты
            </a>
        </div>
    </div>
</body>
</html>