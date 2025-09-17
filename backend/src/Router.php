<?php

namespace HayyaAlSalah;

class Router
{
    private array $routes = [];

    public function __construct()
    {
        $this->defineRoutes();
    }

    private function defineRoutes(): void
    {
        // Authentication routes (optional)
        $this->routes['POST']['/auth/register'] = 'Controllers\AuthController@register';
        $this->routes['POST']['/auth/login'] = 'Controllers\AuthController@login';
        $this->routes['POST']['/auth/refresh'] = 'Controllers\AuthController@refresh';
        $this->routes['GET']['/auth/profile'] = 'Controllers\AuthController@profile';
        $this->routes['PUT']['/auth/profile'] = 'Controllers\AuthController@updateProfile';

        // Topics routes
        $this->routes['GET']['/topics'] = 'Controllers\TopicController@index';
        $this->routes['GET']['/topics/(\d+)'] = 'Controllers\TopicController@show';
        $this->routes['POST']['/topics'] = 'Controllers\TopicController@store';
        $this->routes['PUT']['/topics/(\d+)'] = 'Controllers\TopicController@update';
        $this->routes['DELETE']['/topics/(\d+)'] = 'Controllers\TopicController@delete';

        // Lectures routes
        $this->routes['GET']['/lectures'] = 'Controllers\LectureController@index';
        $this->routes['GET']['/lectures/(\d+)'] = 'Controllers\LectureController@show';
        $this->routes['GET']['/lectures/popular'] = 'Controllers\LectureController@popular';
        $this->routes['GET']['/lectures/recent'] = 'Controllers\LectureController@recent';
        $this->routes['GET']['/topics/(\d+)/lectures'] = 'Controllers\LectureController@byTopic';
        $this->routes['POST']['/lectures'] = 'Controllers\LectureController@store';
        $this->routes['PUT']['/lectures/(\d+)'] = 'Controllers\LectureController@update';
        $this->routes['DELETE']['/lectures/(\d+)'] = 'Controllers\LectureController@delete';

        // User favorites routes (requires auth)
        $this->routes['GET']['/user/favorites'] = 'Controllers\FavoriteController@index';
        $this->routes['GET']['/user/favorites/ids'] = 'Controllers\FavoriteController@getFavoriteIds';
        $this->routes['GET']['/user/favorites/check/(\d+)'] = 'Controllers\FavoriteController@check';
        $this->routes['POST']['/user/favorites/(\d+)'] = 'Controllers\FavoriteController@add';
        $this->routes['DELETE']['/user/favorites/(\d+)'] = 'Controllers\FavoriteController@remove';
        $this->routes['DELETE']['/user/favorites'] = 'Controllers\FavoriteController@clear';

        // Public statistics
        $this->routes['GET']['/stats/most-favorited'] = 'Controllers\FavoriteController@mostFavorited';

        // File upload routes (require admin authentication)
        $this->routes['POST']['/upload/video'] = 'Controllers\UploadController@uploadVideo';
        $this->routes['POST']['/upload/image'] = 'Controllers\UploadController@uploadImage';
        $this->routes['POST']['/upload/document'] = 'Controllers\UploadController@uploadDocument';
        $this->routes['DELETE']['/upload/file'] = 'Controllers\UploadController@deleteFile';
        $this->routes['GET']['/upload/files'] = 'Controllers\UploadController@getUploadedFiles';

        // Admin routes (require admin authentication)
        $this->routes['GET']['/admin/dashboard'] = 'Controllers\AdminController@dashboard';
        $this->routes['GET']['/admin/users'] = 'Controllers\AdminController@getUsers';
        $this->routes['GET']['/admin/users/(\d+)'] = 'Controllers\AdminController@getUser';
        $this->routes['POST']['/admin/users'] = 'Controllers\AdminController@createUser';
        $this->routes['PUT']['/admin/users/(\d+)'] = 'Controllers\AdminController@updateUser';
        $this->routes['DELETE']['/admin/users/(\d+)'] = 'Controllers\AdminController@deleteUser';
        $this->routes['GET']['/admin/users/recent'] = 'Controllers\AdminController@getRecentUsers';
        $this->routes['GET']['/admin/users/activity'] = 'Controllers\AdminController@getUserActivity';

        // Alias for backward compatibility (admin panel expects /users endpoint)
        $this->routes['GET']['/users'] = 'Controllers\AdminController@getUsers';
    }

    public function route(string $method, string $path): void
    {
        try {
            if (!isset($this->routes[$method])) {
                $this->sendResponse(['error' => 'Method not allowed'], 405);
                return;
            }

            foreach ($this->routes[$method] as $route => $handler) {
                $pattern = '#^' . $route . '$#';
                if (preg_match($pattern, $path, $matches)) {
                    array_shift($matches); // Remove full match
                    $this->executeHandler($handler, $matches);
                    return;
                }
            }

            $this->sendResponse(['error' => 'Route not found'], 404);
        } catch (\Exception $e) {
            $this->sendResponse([
                'error' => 'Internal server error',
                'message' => $_ENV['APP_DEBUG'] === 'true' ? $e->getMessage() : 'Something went wrong'
            ], 500);
        }
    }

    private function executeHandler(string $handler, array $params): void
    {
        [$controllerName, $method] = explode('@', $handler);
        $controllerClass = "HayyaAlSalah\\{$controllerName}";

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller {$controllerClass} not found");
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $method)) {
            throw new \Exception("Method {$method} not found in {$controllerClass}");
        }

        $controller->$method(...$params);
    }

    private function sendResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
