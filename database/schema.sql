-- FitTrack — Esquema da base de dados (SQLite)

PRAGMA foreign_keys = ON;

-- Utilizadores
CREATE TABLE IF NOT EXISTS utilizadores (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    nome          TEXT    NOT NULL,
    email         TEXT    NOT NULL UNIQUE,
    password_hash TEXT    NOT NULL,
    perfil        TEXT    NOT NULL DEFAULT 'utilizador',
    foto          TEXT,
    criado_em     TEXT    NOT NULL DEFAULT (datetime('now'))
);

-- Exercícios (catálogo, gerido pelo admin)
CREATE TABLE IF NOT EXISTS exercicios (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    nome           TEXT NOT NULL,
    grupo_muscular TEXT NOT NULL,
    tipo           TEXT NOT NULL
);

-- Treinos
CREATE TABLE IF NOT EXISTS treinos (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    utilizador_id INTEGER NOT NULL,
    nome          TEXT    NOT NULL,
    tipo          TEXT    NOT NULL,
    data          TEXT    NOT NULL,
    duracao_min   INTEGER NOT NULL DEFAULT 0,
    calorias      INTEGER NOT NULL DEFAULT 0,
    notas         TEXT,
    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE
);

-- Séries (exercício + reps + peso dentro de um treino)
CREATE TABLE IF NOT EXISTS series (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    treino_id    INTEGER NOT NULL,
    exercicio_id INTEGER NOT NULL,
    repeticoes   INTEGER NOT NULL DEFAULT 0,
    peso_kg      REAL    NOT NULL DEFAULT 0,
    FOREIGN KEY (treino_id)    REFERENCES treinos(id)    ON DELETE CASCADE,
    FOREIGN KEY (exercicio_id) REFERENCES exercicios(id) ON DELETE CASCADE
);

-- Metas semanais
CREATE TABLE IF NOT EXISTS metas (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    utilizador_id INTEGER NOT NULL,
    semana_inicio TEXT    NOT NULL,
    meta_treinos  INTEGER NOT NULL DEFAULT 0,
    meta_calorias INTEGER NOT NULL DEFAULT 0,
    meta_minutos  INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE,
    UNIQUE (utilizador_id, semana_inicio)
);

-- Planos de treino (podem ser públicos)
CREATE TABLE IF NOT EXISTS planos_treino (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    utilizador_id INTEGER NOT NULL,
    nome          TEXT    NOT NULL,
    descricao     TEXT,
    nivel         TEXT    NOT NULL DEFAULT 'Iniciante',
    publico       INTEGER NOT NULL DEFAULT 0,
    criado_em     TEXT    NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE
);

-- Registos de peso
CREATE TABLE IF NOT EXISTS registos_peso (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    utilizador_id INTEGER NOT NULL,
    data          TEXT    NOT NULL,
    peso_kg       REAL    NOT NULL,
    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE
);

-- Dados iniciais
INSERT INTO exercicios (nome, grupo_muscular, tipo) VALUES
    ('Supino Plano',      'Peito',   'Força'),
    ('Crucifixo',         'Peito',   'Força'),
    ('Puxada Frontal',    'Costas',  'Força'),
    ('Remada Curvada',    'Costas',  'Força'),
    ('Agachamento',       'Pernas',  'Força'),
    ('Leg Press',         'Pernas',  'Força'),
    ('Rosca Direta',      'Bíceps',  'Força'),
    ('Tríceps Corda',     'Tríceps', 'Força'),
    ('Elevação Lateral',  'Ombros',  'Força'),
    ('Passadeira',        'Cardio',  'Cardio'),
    ('Bicicleta',         'Cardio',  'Cardio'),
    ('Abdominais',        'Core',    'Força');
