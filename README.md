# Telenavi Todo API – Backend Documentation

A RESTful backend service built with Laravel 12 to support Todo management operations, including advanced filtering and Excel report generation.

Backend ini dibuat untuk memenuhi kebutuhan aplikasi Telenavi dengan arsitektur yang scalable, maintainable, dan siap untuk production-level workflow.

## Table of Contents

- [Overview](#overview)
- [Tech Stack](#tech-stack)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Environment Configuration](#environment-configuration)
- [Database Schema](#database-schema)
- [Project Structure](#project-structure)
- [API Endpoints](#api-endpoints)
- [Validation Rules](#validation-rules)
- [Excel Export](#excel-export)
- [Error Handling](#error-handling)
- [Running the Application](#running-the-application)

---

## Overview

Telenavi Todo API menyediakan backend services untuk:

- Pembuatan Todo
- Pengambilan data Todo dengan filter lengkap
- Export Excel lengkap dengan summary
- Pengelolaan data secara RESTful

Aplikasi ini dirancang mengikuti prinsip clean code, separation of concerns, dan standard API response formatting.

---

## Tech Stack

| Component | Version |
|-----------|---------|
| PHP | 8.3.28 |
| Laravel | 12.x |
| MySQL | 8.0+ |
| Maatwebsite/Excel | 3.1.x |

---

## Features

✅ Create, Read, Update, Delete Todos

✅ Advanced query filtering:
- title (LIKE)
- assignee (multiple values)
- priority (enum filter)
- status (enum filter)
- date range
- time_tracked range

✅ Excel export with summary row

✅ Strong request validation

✅ Professional error format

✅ Consistent API contracts

---

## Requirements

- PHP 8.3+
- Composer
- MySQL 8.0+
- Laragon / XAMPP / Docker (optional)
- Postman (untuk testing)

---

## Installation

### Clone repository:

```bash
git clone <your-backend-repository-url>
cd be_telenavi
```

### Install dependencies:

```bash
composer install
```

### Copy environment file:

```bash
cp .env.example .env
```

### Generate app key:

```bash
php artisan key:generate
```

### Run migrations:

```bash
php artisan migrate
```

### Start development server:

```bash
php artisan serve
```

Backend berjalan di: **http://localhost:8000**

---

## Environment Configuration

Edit file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=todo_db
DB_USERNAME=root
DB_PASSWORD=
```

Jika menggunakan Laragon, biarkan default.

---

## Database Schema

### Table: todos

| Field | Type | Nullable | Default | Description |
|-------|------|----------|---------|-------------|
| id | bigint | ❌ | AUTO | Primary Key |
| title | string | ❌ | - | Judul Todo |
| assignee | string | ✅ | NULL | Orang yang ditugaskan |
| due_date | date | ❌ | - | Deadline tugas |
| time_tracked | int | ❌ | 0 | Waktu yang sudah dihabiskan |
| status | enum | ❌ | pending | Status pekerjaan |
| priority | enum | ❌ | - | Prioritas tugas |
| created_at | timestamp | ❌ | current | Creation time |
| updated_at | timestamp | ❌ | current | Update time |

### SQL schema (migrasi):

```php
Schema::create('todos', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->string('assignee')->nullable();
    $table->date('due_date');
    $table->integer('time_tracked')->default(0);
    $table->enum('status', ['pending','open','in_progress','completed'])->default('pending');
    $table->enum('priority', ['low','medium','high']);
    $table->timestamps();
});
```

---

## Project Structure

```
be_telenavi/
├── app/
│   ├── Models/
│   │   └── Todo.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── TodoController.php
│   │   └── Requests/
│   ├── Exports/
│   │   └── TodosExport.php
│   └── Traits/
│       └── ApiResponse.php
├── routes/
│   └── api.php
├── database/
│   ├── migrations/
│   └── seeders/
├── storage/
├── .env
└── composer.json
```

---

## API Endpoints

### 1. Create Todo

**Endpoint:** `POST /api/todos`

**Request Body:**

```json
{
  "title": "Belajar Laravel",
  "assignee": "Jordan",
  "due_date": "2025-12-31",
  "priority": "high"
}
```

**Success Response (201):**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Belajar Laravel",
    "assignee": "Jordan",
    "due_date": "2025-12-31",
    "time_tracked": 0,
    "status": "pending",
    "priority": "high"
  },
  "message": "Todo created successfully"
}
```

---

### 2. Get Todos (with Filtering)

**Endpoint:** `GET /api/todos`

**Supported filters:**

| Parameter | Type | Example |
|-----------|------|---------|
| title | string | ?title=laravel |
| assignee | list | ?assignee=john,alice |
| status | list | ?status=pending,in_progress |
| priority | list | ?priority=high,low |
| start | date | ?start=2025-01-01 |
| end | date | ?end=2025-12-31 |
| min | number | ?min=2 |
| max | number | ?max=10 |

**Example request:**

```bash
GET /api/todos?priority=high&status=pending,in_progress&start=2025-01-01&end=2025-12-31
```

**Success Response (200):**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Belajar Laravel",
      "assignee": "Jordan",
      "due_date": "2025-12-31",
      "time_tracked": 5,
      "status": "in_progress",
      "priority": "high"
    }
  ],
  "total": 1
}
```

---

### 3. Export Excel

**Endpoint:** `GET /api/todos/export`

**Response:**

Automatically downloads `todo-report.xlsx`

Contains:
- Semua data Todo
- Summary row: total todos & total time tracked

Gunakan Send and Download di Postman.

---

## Validation Rules

### Create Todo

| Field | Rules |
|-------|-------|
| title | required, string, max:255 |
| assignee | nullable, string |
| due_date | required, date, after_or_equal:today |
| priority | required, in:low,medium,high |
| status | optional (default: pending) |

---

## Excel Export

Menggunakan Maatwebsite/Excel:

```php
return Excel::download(new TodosExport($todos), 'todo-report.xlsx');
```

**Kelebihan:**
- Ringan
- Mudah di-maintain
- Cocok untuk enterprise reporting

---

## Error Handling

Seluruh error diformat secara konsisten:

### Contoh 422 Validation Error:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "due_date": [
      "The due date must be a date after or equal to today."
    ]
  }
}
```

### Contoh 404 Error:

```json
{
  "success": false,
  "message": "Todo not found"
}
```

---

## Running the Application

### Development server:

```bash
php artisan serve
```

### Testing with Postman:

- `POST /api/todos` - Create Todo
- `GET /api/todos` - Get all Todos
- `GET /api/todos?priority=high` - Get with filter
- `GET /api/todos/export` - Export to Excel

Excel export menggunakan Send and Download.

---

## Author

**Jordan Theovandy**

**Telenavi - Fullstack Internship Test**

**November 2025**

---

## License

This project is part of Telenavi internship program. All rights reserved.