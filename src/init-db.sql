-- Полная структура базы данных для опросника
-- Выполнить: sudo -u postgres psql -d questionnaire_db -f init-db.sql

-- Таблица пользователей (только ФИО)
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица опросов
CREATE TABLE IF NOT EXISTS questionnaires (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица вопросов
CREATE TABLE IF NOT EXISTS questions (
    id SERIAL PRIMARY KEY,
    questionnaire_id INTEGER REFERENCES questionnaires(id),
    question_text TEXT NOT NULL,
    question_type VARCHAR(50) DEFAULT 'text',
    question_order INTEGER NOT NULL
);

-- Таблица вариантов ответов (для radio/checkbox)
CREATE TABLE IF NOT EXISTS question_options (
    id SERIAL PRIMARY KEY,
    question_id INTEGER REFERENCES questions(id),
    option_text VARCHAR(255) NOT NULL
);

-- Таблица ответов пользователей
CREATE TABLE IF NOT EXISTS answers (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    question_id INTEGER REFERENCES questions(id),
    answer_text TEXT NOT NULL,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Вставляем тестовые данные
INSERT INTO questionnaires (title, description) VALUES 
('DevOps основы', 'Тест по основам DevOps практик'),
('Linux администрация', 'Вопросы по администрированию Linux');

-- Вопросы для первого опроса (DevOps)
INSERT INTO questions (questionnaire_id, question_text, question_type, question_order) VALUES
(1, 'Что означает CI/CD?', 'text', 1),
(1, 'Какие инструменты контейнеризации вы знаете?', 'checkbox', 2),
(1, 'Какой у вас уровень знаний в DevOps?', 'radio', 3),
(1, 'Какие облачные платформы вы использовали?', 'checkbox', 4),
(1, 'Опишите ваш опыт с системами мониторинга', 'text', 5);

-- Варианты для checkbox и radio вопросов
INSERT INTO question_options (question_id, option_text) VALUES
(2, 'Docker'),
(2, 'Kubernetes'),
(2, 'Podman'),
(2, 'LXC/LXD'),
(3, 'Начинающий'),
(3, 'Средний'),
(3, 'Продвинутый'),
(3, 'Эксперт'),
(4, 'AWS'),
(4, 'Azure'),
(4, 'Google Cloud'),
(4, 'Yandex Cloud'),
(4, 'OpenStack');

-- Вопросы для второго опроса (Linux)
INSERT INTO questions (questionnaire_id, question_text, question_type, question_order) VALUES
(2, 'Как посмотреть список запущенных процессов?', 'text', 1),
(2, 'Как изменить права доступа к файлу?', 'text', 2),
(2, 'Какие дистрибутивы Linux вы использовали?', 'checkbox', 3);

INSERT INTO question_options (question_id, option_text) VALUES
(7, 'Ubuntu/Debian'),
(7, 'CentOS/RHEL'),
(7, 'Fedora'),
(7, 'Arch Linux'),
(7, 'AlmaLinux/Rocky Linux');

-- Индексы для ускорения запросов
CREATE INDEX idx_answers_user_id ON answers(user_id);
CREATE INDEX idx_answers_question_id ON answers(question_id);
CREATE INDEX idx_questions_questionnaire_id ON questions(questionnaire_id);

-- Проверочный запрос
SELECT 'База данных успешно инициализирована!' as status;
SELECT COUNT(*) as questionnaires FROM questionnaires;
SELECT COUNT(*) as questions FROM questions;