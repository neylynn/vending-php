<?php
#[Attribute] class Route { public function __construct(public string $method, public string $path) {} }

class Router {
  private array $routes = [];

  public function registerController(object $controller) {
    $rc = new ReflectionClass($controller);
    foreach($rc->getMethods() as $m) {
      foreach($m->getAttributes(Route::class) as $attr) {
        $r = $attr->newInstance();
        [$regex,$vars] = $this->compile($r->path);
        $this->routes[] = [$r->method, $regex, $vars, [$controller, $m->getName()]];
      }
    }
  }

  private function compile(string $path): array {
    $vars=[];
    $regex=preg_replace_callback('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', function($m) use (&$vars){
      $vars[]=$m[1]; return '([A-Za-z0-9_-]+)';
    }, $path);
    return ['#^'.$regex.'$#', $vars];
  }

  public function dispatch(string $method, string $uri) {
    $path = parse_url($uri, PHP_URL_PATH);
    foreach($this->routes as [$m,$regex,$vars,$handler]){
      if($m !== $method) continue;
      if(preg_match($regex,$path,$matches)){
        array_shift($matches);
        $params = array_combine($vars,$matches) ?: [];
        return call_user_func($handler, $params);
      }
    }
    http_response_code(404); echo "Not Found";
  }
}
