<?php

namespace MVC;

class Router {
    public array $rutasGET = [];
    public array $rutasPOST = [];

    public function get(string $url, callable $fn, array $middlewares = []): void{
        $this->rutasGET[$url] = [
            'fn' => $fn,
            'middlewares' => $middlewares,
        ];
    }

    public function post(string $url, callable $fn, array $middlewares = []): void{
        $this->rutasPOST[$url] = [
            'fn' => $fn,
            'middlewares' => $middlewares,
        ];
    }

    public function comprobarRutas(): void{
        $urlActual = $_SERVER['PATH_INFO']??'/';
        $metodo = $_SERVER['REQUEST_METHOD'];
        /** @var array{fn: callable, middlewares: array}|null $ruta */
        $ruta = null;
        if($metodo === 'GET'){
            $ruta = $this->rutasGET[$urlActual] ?? null;
        }
        if($metodo === 'POST'){
            $ruta = $this->rutasPOST[$urlActual] ?? null;
        }
        if($ruta){
            foreach ($ruta['middlewares'] ?? [] as $middleware) {
                call_user_func($middleware, $urlActual, $metodo);
            }

            //La URL existe y hay una funcion asociada
            //Toma la funcion de la variable y la manda a llamar
            call_user_func($ruta['fn'], $this);
        }else{
            echo "Pagina No Encontrada";
        }
    }

    public function render(string $view, array $datos = [], ?string $layout = 'layout'): void{
        foreach($datos as $key => $value){
            $$key = $value;
        }
        ob_start();
        include __DIR__ . "/views/$view.php";
        $contenido = ob_get_clean();

        if ($layout === null) {
            echo $contenido;
            return;
        }

        include __DIR__ . "/views/$layout.php";
    }
}