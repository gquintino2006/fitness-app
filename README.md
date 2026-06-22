# FitTrack

Aplicação web de acompanhamento fitness.
Stack: **PHP** · **SQLite** · **Chart.js**

## Requisitos

- PHP 8.0 ou superior
- Extensão PDO SQLite ativada

## Correr (PowerShell)

```powershell
cd C:\xampp\htdocs\fitness-app
php -S localhost:8000
```

Depois no browser:

1. `http://localhost:8000/seed.php` — cria a BD e dados de demonstração (só uma vez)
2. `http://localhost:8000` — entrar na aplicação

> Com XAMPP a correr: `http://localhost/fitness-app/seed.php`

## Contas de demonstração

| Email | Password | Perfil |
|-------|----------|--------|
| admin@fittrack.pt | admin123 | Admin |
| joao@fittrack.pt | joao123 | Utilizador |

## Funcionalidades

- Registo e login com sessões PHP
- CRUD de treinos com séries de exercícios
- Metas semanais com barras de progresso
- Gráfico de calorias (Chart.js)
- Evolução de peso com registo via AJAX
- Planos de treino públicos e privados
- Admin gere o catálogo de exercícios
- Filtro e paginação na listagem de treinos



## Estrutura


```
fitness-app/
├── config/db.php           ligação PDO ao SQLite
├── includes/
│   ├── functions.php       autenticação, flash, escape
│   ├── header.php
│   └── footer.php
├── assets/css/style.css
├── assets/js/main.js
├── database/schema.sql
├── data/fittrack.db        (gerado automaticamente)
├── errors/403.php, 404.php
├── index.php               dashboard
├── login.php / register.php / logout.php
├── treinos.php / treino_form.php / treino_ver.php / treino_eliminar.php
├── metas.php
├── evolucao.php / peso_registar.php
├── planos.php
├── exercicios.php          só admin
└── seed.php                dados de demonstração
```



## Autores

- Rui Silva — nº50050
- Francisco Luis — nº50102

