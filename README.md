# Vortex PHP Framework

Vortex is a modular, expressive PHP framework inspired by NestJS, designed for building scalable and maintainable applications. It leverages modern PHP features, including attributes for routing and dependency injection, while ensuring a clean and predictable structure.

## Features

- **Modular Structure**: Keep your application organized with modules containing controllers, entities, DTOs, services, and repositories.
- **Attribute-Based Routing**: Define routes directly in controllers using PHP attributes.
- **Middleware Chaining**: Chain middleware to routes like Laravel.
- **Dependency Injection (DI)**: Uses a container for managing dependencies cleanly.
- **Database Abstraction**: Works with any ORM of your choice.
- **Configuration via .env**: Manage environment-specific settings easily.
- **Bootstrap File**: Keeps core logic separate from the entry point.

## Installation

`composer create-project vortex/vortex-app my-app
cd my-app
php please serve`

## Folder Structure

`
my-app/
├── app/
│ ├── Modules/
│ │ ├── User/
│ │ │ ├── Controllers/
│ │ │ │ ├── UserController.php
│ │ │ ├── Entities/
│ │ │ │ ├── User.php
│ │ │ ├── DTOs/
│ │ │ │ ├── UserDTO.php
│ │ │ ├── Services/
│ │ │ │ ├── UserService.php
│ │ │ ├── Repositories/
│ │ │ │ ├── UserRepository.php
├── core/
│ ├── Bootstrap.php
│ ├── Router.php
│ ├── Middleware/
│ ├── DependencyContainer.php
│ ├── Response.php
│ ├── Request.php
├── public/
│ ├── index.php
├── .env
├── composer.json
├── README.md

`

## Usage

### Defining a Controller

`namespace App\Modules\User\Controllers;

    use Vortex\Core\Attributes\Path;
    use Vortex\Core\Attributes\Get;
    use Vortex\Core\Response;

    #[Path('users')]
        class UserController { #[Get('/')]
        public function getUsers() {
            return Response::json(['user1', 'user2']);
        }
    }

`

### Middleware

`namespace App\Middleware;

    use Vortex\Core\Interfaces\MiddlewareInterface;
    use Vortex\Core\Request;
    use Vortex\Core\Response;

    class AuthMiddleware implements MiddlewareInterface {
        public function handle(Request $request, callable $next) {
            if (!$request->isAuthenticated()) {
                return Response::json(['error' => 'Unauthorized'], 401);
            }
            return $next($request);
        }
    }

`

### Dependency Injection

`namespace App\Modules\User\Services;

use App\Modules\User\Repositories\UserRepository;

class UserService {
public function \_\_construct(private UserRepository $userRepository) {}

    public function getAllUsers() {
        return $this->userRepository->findAll();
    }

}
`

### Creating a Module

`php please make:module User`

This will generate a User module with a controller, service, repository, entity, and DTO.

## Running the Server

`php please serve`

## Conclusion

Vortex provides a NestJS-like experience in PHP, making it easy to build structured, maintainable applications. Contributions are welcome!

### License

MIT License
