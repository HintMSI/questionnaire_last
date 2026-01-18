DELETE FROM answers;
DELETE FROM question_options;
DELETE FROM questions;
DELETE FROM users;
DELETE FROM questionnaires;

-- 2. Сбрасываем последовательности
ALTER SEQUENCE questionnaires_id_seq RESTART WITH 1;
ALTER SEQUENCE questions_id_seq RESTART WITH 1;
ALTER SEQUENCE question_options_id_seq RESTART WITH 1;
ALTER SEQUENCE users_id_seq RESTART WITH 1;
ALTER SEQUENCE answers_id_seq RESTART WITH 1;

-- 3. Вставляем опросы
INSERT INTO questionnaires (title, description) VALUES 
('DevOps основы', 'Тест по основам DevOps практик'),
('Linux администрация', 'Вопросы по администрированию Linux'),
('Сетевые технологии', 'Вопросы по сетям и протоколам');

-- 4. Вопросы для опроса 1 (DevOps)
INSERT INTO questions (questionnaire_id, question_text, question_type, question_order) VALUES
(1, 'Что означает аббревиатура CI/CD?', 'text', 1),
(1, 'Какие инструменты контейнеризации вы использовали?', 'checkbox', 2),
(1, 'Какой у вас уровень знаний в DevOps?', 'radio', 3),
(1, 'Какие облачные платформы вы знаете?', 'checkbox', 4),
(1, 'Опишите ваш опыт работы с Docker', 'text', 5);

-- 5. Варианты ответов для вопросов опроса 1
-- Для вопроса 2 (checkbox)
INSERT INTO question_options (question_id, option_text) VALUES
(2, 'Docker'),
(2, 'Kubernetes'),
(2, 'Podman'),
(2, 'LXC/LXD'),
(2, 'OpenShift');

-- Для вопроса 3 (radio)
INSERT INTO question_options (question_id, option_text) VALUES
(3, 'Начинающий'),
(3, 'Средний'),
(3, 'Продвинутый'),
(3, 'Эксперт');

-- Для вопроса 4 (checkbox)
INSERT INTO question_options (question_id, option_text) VALUES
(4, 'AWS'),
(4, 'Azure'),
(4, 'Google Cloud'),
(4, 'Yandex Cloud'),
(4, 'OpenStack'),
(4, 'DigitalOcean');

-- 6. Вопросы для опроса 2 (Linux)
INSERT INTO questions (questionnaire_id, question_text, question_type, question_order) VALUES
(2, 'Как посмотреть запущенные процессы в Linux?', 'text', 1),
(2, 'Как изменить права доступа к файлу?', 'text', 2),
(2, 'Какие дистрибутивы Linux вы использовали?', 'checkbox', 3),
(2, 'Какой у вас опыт работы с командной строкой?', 'radio', 4);

-- 7. Варианты для опроса 2
-- Для вопроса 7 (checkbox - третий вопрос второго опроса)
INSERT INTO question_options (question_id, option_text) VALUES
(7, 'Ubuntu/Debian'),
(7, 'CentOS/RHEL'),
(7, 'Fedora'),
(7, 'Arch Linux'),
(7, 'AlmaLinux/Rocky Linux'),
(7, 'OpenSUSE');

-- Для вопроса 8 (radio)
INSERT INTO question_options (question_id, option_text) VALUES
(8, 'Базовый (знаю основные команды)'),
(8, 'Средний (пишу простые скрипты)'),
(8, 'Продвинутый (автоматизация, скрипты)'),
(8, 'Эксперт (системное администрирование)');

-- 8. Вопросы для опроса 3 (Сети)
INSERT INTO questions (questionnaire_id, question_text, question_type, question_order) VALUES
(3, 'Что такое модель OSI?', 'text', 1),
(3, 'Какие протоколы вы знаете?', 'checkbox', 2),
(3, 'Как настроить статический IP в Linux?', 'text', 3);

-- 9. Варианты для опроса 3
INSERT INTO question_options (question_id, option_text) VALUES
(10, 'TCP'),
(10, 'UDP'),
(10, 'HTTP/HTTPS'),
(10, 'DNS'),
(10, 'SSH'),
(10, 'FTP'),
(10, 'ICMP');

-- 10. Проверка
SELECT 'Опросы:' as category, COUNT(*) FROM questionnaires
UNION ALL
SELECT 'Вопросы:', COUNT(*) FROM questions
UNION ALL
SELECT 'Варианты ответов:', COUNT(*) FROM question_options;

-- Подробный отчет
SELECT 
    qn.id as опрос_id,
    qn.title as название_опроса,
    COUNT(DISTINCT q.id) as всего_вопросов,
    SUM(CASE WHEN q.question_type = 'text' THEN 1 ELSE 0 END) as текстовых,
    SUM(CASE WHEN q.question_type = 'radio' THEN 1 ELSE 0 END) as радио,
    SUM(CASE WHEN q.question_type = 'checkbox' THEN 1 ELSE 0 END) as чекбоксов,
    COUNT(DISTINCT o.id) as всего_вариантов
FROM questionnaires qn
LEFT JOIN questions q ON qn.id = q.questionnaire_id
LEFT JOIN question_options o ON q.id = o.question_id
GROUP BY qn.id, qn.title
ORDER BY qn.id;
