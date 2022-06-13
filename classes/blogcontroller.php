<?php

use Slim\Views\PhpRenderer;

class BlogController
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
    }

    public function renderIndex($request, $response, $args) {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/frontend/blog/home.html');
    }
    public function renderbrowse($request, $response, $args) {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/frontend/blog/browseblog.html');
    }
    public function renderAdminIndex($request, $response, $args){
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/backend/blogmanage/home.html');
    }
    public function getBlogs($request, $response, $args){   
        $data = $request->getQueryParams();
        $blog = new Blog($this->container);
        $result = $blog->preDatatables($data);
        $result['data'] = $blog->getBlogs($data);
        $result['recordsTotal'] = $blog->getBlogsTotal($data); //總共幾筆
        $result['recordsFiltered'] = $result['recordsTotal'];   //限制幾筆
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getBrowseBlogs($request, $response, $args){   
        $data = $request->getQueryParams(); 
        $blog = new Blog($this->container);
        $result = $blog->getBrowseBlogs($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getAdminBlogs($request, $response, $args){
        $data = $request->getQueryParams();
        $blog = new Blog($this->container);
        $result = $blog->preDatatables($data);
        $result['data'] = $blog-> getAdminBlogs($data);
        $result['recordsTotal'] = $blog->getAdminBlogsTotal($data); //總共幾筆
        $result['recordsFiltered'] = $result['recordsTotal'];   //限制幾筆
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getAdminBlog($request, $response, $args)
    {   
        $data = $args;
        //$data = $request->getQueryParams(); //前端抓data
        $blog = new Blog($this->container);
        $result = $blog->getAdminBlog($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function deleteBlog($request, $response, $args)
    {
        $data = $request->getParsedBody(); 
        $product = new Blog($this->container);
        $result = $product->deleteBlog($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patchBlogs($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $admin = new Blog($this->container);
        $result = $admin->patchBlogs($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getAllTag($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $admin = new Blog($this->container);
        $result = $admin->getAllTag($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }  
 
}