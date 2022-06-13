<?php

use Slim\Views\PhpRenderer;

class HomeController
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
    }

    public function home($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/frontend/home/home.html');
    }
    public function renderShowItem($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/frontend/home/home.html');
    }

    public function getAllItems($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $product = new Home($this->container);
        $result = $product->preDatatables($data);
        $result['data'] = $product->getAllItems($data);
        $result['recordsTotal'] = $product->getAllItemsTotal($data);
        $result['recordsFiltered'] = $result['recordsTotal'];
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getShowItem($request, $response, $args)
    {
        $data = $args;
        $home = new Home($this->container);
        $result = $home->getShowItem($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
}
