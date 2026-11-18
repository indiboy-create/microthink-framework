<?php

declare(strict_types=1);

namespace mini;

use RuntimeException;
use Throwable;

final class App
{
    private static ?self $instance = null;

    private array $config;
    private Request $request;
    private Container $container;
    private ?Db $db = null;
    private View $view;

    public static function start(string $root): self
    {
        return self::$instance = new self($root);
    }

    public static function instance(): self
    {
        return self::$instance ?? throw new RuntimeException('应用尚未启动');
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    public function container(): Container
    {
        return $this->container;
    }

    public function request(): Request
    {
        return $this->request;
    }

    public function db(): Db
    {
        if ($this->db === null) {
            $this->db = new Db($this->config['db']);
            $this->container->singleton(Db::class, $this->db);
        }

        return $this->db;
    }

    public function view(): View
    {
        return $this->view;
    }

    public function config(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->config;
        }

        $value = $this->config;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    private function __construct(private string $root)
    {
        $this->config = require $root . '/config/app.php';
        $this->request = Request::capture();
        $this->container = new Container();

        $this->view = new View($this->config['view']['path']);

        $this->container->singleton(Request::class, $this->request);
        $this->container->singleton(View::class, $this->view);
    }

    public function run(): void
    {
        try {
            $response = $this->requiresInstall()
                ? $this->notInstalled($this->request)
                : $this->handle($this->request);
        } catch (Throwable $e) {
            $response = $this->error($e);
        }

        $response->send();
    }

    private function requiresInstall(): bool
    {
        if (!(bool) $this->config('install.require_lock', true)) {
            return false;
        }

        if (in_array($this->request->path(), ['/install', '/install/complete'], true)) {
            return false;
        }

        return !is_file($this->root . '/database/install.lock');
    }

    private function notInstalled(Request $request): Response
    {
        if (in_array($request->method(), ['GET', 'HEAD'], true)) {
            return Response::redirect('/install');
        }

        return Response::json(['error' => '系统尚未安装'], 503);
    }

    private function handle(Request $request): Response
    {
        $next = fn (Request $request): Response => $this->dispatch($request);

        foreach (array_reverse($this->config['middleware'] ?? []) as $middleware) {
            $previous = $next;
            $next = static fn (Request $request): Response => Response::make($middleware($request, $previous));
        }

        return $next($request);
    }

    private function dispatch(Request $request): Response
    {
        $router = new Router(require $this->root . '/route/web.php');
        $matched = $router->match($request->method(), $request->path());

        if ($matched === null) {
            if ($router->hasPath($request->path())) {
                return Response::json(['error' => 'Method Not Allowed'], 405);
            }

            return Response::json(['error' => 'Not Found'], 404);
        }

        [$handler, $params] = $matched;
        [$class, $method] = $handler;

        $controller = $this->container->make($class);
        $result = $this->container->call([$controller, $method], $params);

        return Response::make($result);
    }

    private function error(Throwable $e): Response
    {
        $data = ['error' => 'Internal Server Error'];

        if ($this->config['debug'] ?? false) {
            $data['message'] = $e->getMessage();
            $data['file'] = $e->getFile();
            $data['line'] = $e->getLine();
            $data['trace'] = explode("\n", $e->getTraceAsString());
        }

        return Response::json($data, 500);
    }
}
