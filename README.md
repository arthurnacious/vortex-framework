# Vortex-8 (V8)

**Vortex-8 (V8)** is a lightweight, modular **PHP micro-framework** inspired by the architectural principles of **NestJS**. It’s designed for simplicity, developer ergonomics, and rapid API development. Featuring **attribute-based routing**, **dependency injection**, **DTO-based request validation**, and **Symfony HTTP Foundation** support, V8 helps you build modern backends with minimal overhead.

---

## 🚀 Features

- **🔀 Attribute-Based Routing** using `#[Route]` and `#[Path]`
- **💉 Dependency Injection** for controllers and services
- **✅ Request Validation** via Data Transfer Objects (DTOs)
- **📦 Modular Architecture** with isolated modules
- **📨 Symfony HTTP Foundation Integration** for request/response handling
- **⚡ Minimal Setup** with expressive syntax and sensible defaults

---

## 📦 Installation

Install the framework using Composer:

```bash
composer create-project v8/core my-project
```

**Directory Structure:**

```
my-project/
├── app/
│   ├── Controllers/
│   ├── DTOs/
│   ├── Modules/
│   └── Services/
├── public/
│   └── index.php
├── vendor/
├── composer.json
└── .env
```

---

## ⚡ Quick Start

**1. Create a Controller**

```php
<?php
namespace V8\Modules\Hello\Controllers;

use V8\Attributes\Route;
use V8\Attributes\Path;
use V8\Controller\BaseController;

#[Route('/hello')]
class HelloController extends BaseController
{
    #[Path('/{name}', method: 'GET')]
    public function greet(string $name)
    {
        return $this->ok("Hello, $name!");
    }
}
```

**2. Register the Controller in a Module**

```php
<?php

use V8\Modules\Module;
use V8\Modules\Hello\Controllers\HelloController;

class HelloModule extends Module
{
    public function register(): void
    {
        $this->registerRoutes([HelloController::class]);
    }
}
```

**3. Configure Your Entry Point**

```php
// public/index.php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = new V8\Application(dirname(__DIR__));
$app->registerModules(require __DIR__ . '/../config/modules.php');
$app->run();

```

---

## 🧠 Core Concepts

### 1. **Routing with Attributes**

Define routes using PHP 8+ attributes:

```php
#[Route('/users')]
class UserController
{
    #[Path('/', method: 'POST')]
    public function create(UserDto $data) {
        // Automatically validated
    }
}
```

### 2. **DTOs & Validation**

DTOs define expected input with validation rules:

```php
use V8\Validation\Dto;

class UserDto extends Dto
{
    public string $name;
    public string $email;

    public function rules(): array
    {
        return [
            'name' => ['required', 'min:2'], // or 'required|min:2'
            'email' => ['required', 'email'], // or 'required|email'
        ];
    }
}
```

- Invalid requests automatically return a **422 Unprocessable Entity** with validation messages.

### 3. **Responses**

Controllers extend `BaseController` and can use helpers:

```php
return $this->created($user);    // 201 Created
return $this->ok($data);         // 200 OK
```

Arrays and DTOs are automatically converted to JSON.

### 4. **Modular Architecture**

Each feature lives inside its own **Module**:

```php
class UserModule extends Module
{
    public function register(): void
    {
        $this->container->singleton(UserService::class, fn() => new UserService());

        $this->registerRoutes([UserController::class]);
    }
}
```

---

## 📚 Examples

### Basic CRUD Controller

```php
#[Route('/users')]
class UserController extends BaseController
{
    public function __construct(private UserService $users) {}

    #[Path('/', method: 'GET')]
    public function index() {
        return $this->ok($this->users->all());
    }

    #[Path('/', method: 'POST')]
    public function store(UserDto $data) {
        return $this->created($this->users->create($data));
    }

    #[Path('/{id}', method: 'GET')]
    public function show(int $id) {
        return $this->ok($this->users->find($id));
    }

    #[Path('/{id}', method: 'PUT')]
    public function update(int $id, UserDto $data) {
        return $this->ok($this->users->update($id, $data));
    }

    #[Path('/{id}', method: 'DELETE')]
    public function destroy(int $id) {
        $this->users->delete($id);
        return $this->noContent();
    }
}
```

---

## 🔍 Comparison

| Feature            | Vortex-8 (V8)            | Laravel           | Symfony           |
| ------------------ | ------------------------ | ----------------- | ----------------- |
| **Routing**        | PHP Attributes           | Route files/Attrs | YML/XML/PHP       |
| **Modularity**     | Modules                  | Service Providers | Bundles           |
| **Validation**     | DTOs                     | Form Requests     | Validator Service |
| **Responses**      | Response Helpers         | Response classes  | HttpFoundation    |
| **Learning Curve** | 🔽 Low                   | 🟰 Medium          | 🔼 Steep          |
| **Philosophy**     | Minimalist, NestJS-style | Full-stack        | Enterprise-grade  |

---

## 🤝 Contributing

We welcome contributions from the community!

1. Fork the repo
2. Create a new branch (`git checkout -b feature/your-feature`)
3. Make your changes and write tests
4. Submit a pull request

Please follow PSR standards and write meaningful commits.

---

## 📬 Stay Connected

- **Website:** [Coming Soon]
- **Docs:** In progress
- **Twitter:** [@vortex8php](https://twitter.com/vortex8php) (placeholder)

---

Built with ❤️ for clean, modular PHP development.
