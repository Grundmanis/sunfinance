# Project README

A Dockerized PHP application with CLI tools, API, database, migrations, seeding, and automated tests.

---

## 🚀 Quick Start

### 1. Install Docker

Make sure Docker is installed and running:

- [https://www.docker.com/](https://www.docker.com/)

### 2. Start the project

You can start the project using **Make** (recommended) or plain Docker Compose:

```bash
make up
```

or

```bash
docker compose up --build
```

### 3. Run database migrations

After the containers are up, run migrations **once**:

```bash
make migrate
```

### 4. Seed the database

Populate the database with initial / test data **once**:

```bash
make seed
```

The API will be available at:

```
POST http://localhost:8080/api/payment
```

Request example:

```
   {
    "firstname": "Lorem",
    "lastname": "Ipsum",
    "paymentDate": "2022-12-12T15:19:21+00:00",
    "amount": "99.99",
    "description": "Lorem ipsum dolorLN20221212 sit amet...",
    "refId": "dda8b637-b2e8-4f79-a4af-d1d68e266bf5"
   }
```

Another request example:

```
 {
    "firstname": "Lorem",
    "lastname": "Ipsum",
    "paymentDate": "2022-12-12T15:19:21+00:00",
    "amount": "99.99",
    "description": "LN20221212",
    "refId": "130f8a89-51c9-47d0-a6ef-1aea54924d34"
}
```

And another request example:

```
{
    "firstname": "Lorem",
    "lastname": "Ipsum",
    "paymentDate": "2022-12-12T15:19:21+00:00",
    "amount": "70",
    "description": "LN55522533",
    "refId": "130f8a89-51c9-47d0-a6ef-1aea54924d340"
}
```

---

## 📦 Available Make Commands

| Command                             | Description                             |
| ----------------------------------- | --------------------------------------- |
| `make up`                           | Start all containers                    |
| `make down`                         | Stop and remove containers              |
| `make logs`                         | View Docker logs                        |
| `make cli`                          | Enter the PHP container                 |
| `make test`                         | Run the test suite                      |
| `make migrate`                      | Run database migrations (once)          |
| `make seed`                         | Seed the database                       |
| `make drop`                         | Drop the databae                        |
| `make import file=path/to/file.csv` | Import payments from CSV (from app dir) |
| `make report date=YYYY-MM-DD`       | Generate a report for a specific date   |

---

## 🧪 Testing

Run all tests:

```bash
make test
```

---

## 🖥 CLI Usage

You can execute CLI commands directly inside the PHP container.

### Enter PHP container

```bash
make cli
```

or

```bash
docker compose exec php bash
```

### Run import manually

```bash
docker compose exec -w /var/www/app php php bin/console import path/to/file/in/app/folder
```

---

## 🗄 Database

### View database logs

```bash
docker compose logs -f db
```

### Run migrations (once)

```bash
make migrate
```

### Seed database

```bash
make seed
```

---

## 📡 API

Base URL:

```
http://localhost:8080
```

Make sure containers are running before accessing the API.

---

## 🏗 Architecture & Project Structure

Currently project stucture has technical grouping for tge easier navigation

---
