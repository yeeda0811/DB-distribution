<?php

use Slim\Views\PhpRenderer;

class ProductController
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
    }

    public function renderItem($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/backend/productmanage/item.html');
    }
    public function renderOrder($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/backend/productmanage/order.html');
    }
    public function renderDiscount($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/backend/productmanage/discount.html');
    }

    public function getItems($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $product = new Product($this->container);
        $result = $product->preDatatables($data);
        $result['data'] = $product->getItems($data);
        $result['recordsTotal'] = $product->getItemsTotal($data);
        $result['recordsFiltered'] = $result['recordsTotal'];
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getItem($request, $response, $args)
    {
        $data = $args;
        $product = new Product($this->container);
        $result = $product->getItem($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function addItem($request, $response, $args)
    {
        $data = $request->getParsedBody(); //$_post
        $product = new Product($this->container);
        $result = $product->addItem($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function EditItem($request, $response, $args)
    {
        $data = $request->getParsedBody(); //$_post
        $product = new Product($this->container);
        $result = $product->EditItem($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function DeleteItem($request, $response, $args)
    {
        $data = $request->getParsedBody(); //$_post
        $product = new Product($this->container);
        $result = $product->DeleteItem($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getItemTypes($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $product = new Product($this->container);
        $result = $product->getItemTypes($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getUnit($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $product = new Product($this->container);
        $result = $product->getUnit($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getUserItems($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $product = new Product($this->container);
        $result = $product->getUserItems($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getPayingTypes($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $product = new Product($this->container);
        $result = $product->getPayingTypes($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getImports($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $product = new Product($this->container);
        $result = $product->preDatatables($data);
        $result['data'] = $product->getImports($data);
        $result['recordsTotal'] = $product->getImportsTotal($data);
        $result['recordsFiltered'] = $result['recordsTotal'];
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getImport($request, $response, $args)
    {
        $data = $args;
        $product = new Product($this->container);
        $result = $product->getImport($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function addImport($request, $response, $args)
    {
        $data = $request->getParsedBody(); //$_post
        $product = new Product($this->container);
        $result = $product->addImport($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function EditImport($request, $response, $args)
    {
        $data = $request->getParsedBody(); //$_post
        $product = new Product($this->container);
        $data['user_id'] = $_SESSION['user_id'];
        $result = $product->EditImport($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function DeleteImport($request, $response, $args)
    {
        $data = $request->getParsedBody(); //$_post
        $product = new Product($this->container);
        $result = $product->DeleteImport($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getDiscounts($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $product = new Product($this->container);
        $result = $product->preDatatables($data);
        $result['data'] = $product->getDiscounts($data);
        $result['recordsTotal'] = $product->getDiscountsTotal($data);
        $result['recordsFiltered'] = $result['recordsTotal'];
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getDiscount($request, $response, $args)
    {
        $data = $args;
        $product = new Product($this->container);
        $result = $product->getDiscount($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getDiscountType($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $product = new Product($this->container);
        $result = $product->getDiscountType($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function addDiscount($request, $response, $args)
    {
        $data = $request->getParsedBody(); //$_post
        $product = new Product($this->container);
        $result = $product->addDiscount($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function EditDiscount($request, $response, $args)
    {
        $data = $request->getParsedBody(); //$_post
        $product = new Product($this->container);
        $result = $product->EditDiscount($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function DeleteDiscount($request, $response, $args)
    {
        $data = $request->getParsedBody(); //$_post
        $product = new Product($this->container);
        $result = $product->DeleteDiscount($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getOrders($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $product = new Product($this->container);
        $result = $product->preDatatables($data);
        $result['data'] = $product->getOrders($data);
        $result['recordsTotal'] = $product->getOrdersTotal($data);
        $result['recordsFiltered'] = $result['recordsTotal'];
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getOrder($request, $response, $args)
    {
        $data = $args;
        $product = new Product($this->container);
        $result = $product->getOrder($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function addOrder($request, $response, $args)
    {
        $data = $request->getParsedBody(); //$_post
        $product = new Product($this->container);
        $result = $product->addOrder($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function editOrder($request, $response, $args)
    {
        $data = $request->getParsedBody(); //$_post
        $product = new Product($this->container);
        $result = $product->editOrder($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function uploadFile($request, $response, $args)
    {
        $data = $request->getUploadedFiles(); //$_post
        $product = new Product($this->container);
        $file = $product->uploadFile($data);
        $result = $product->insertFile($file);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
    public function getFile($request, $response, $args)
    {
        $data = $args;
        $product = new Product($this->container);
        $file = $product->getFile($data);
        if (!file_exists($file)) {
            $response = $response->withStatus(500);
            return $response;
        }
        $source = $product->compressImage($file, $file, 100);
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        imagejpeg($source);
        $response = $response->withHeader('Content-Description', 'File Transfer')
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment;filename="' . "example.{$extension}" . '"')
            ->withHeader('Expires', '0')
            ->withHeader('Cache-Control', 'must-revalidate')
            ->withHeader('Pragma', 'public');
        return $response;
    }

    public function deleteOrders($request, $response, $args)
    {
        $data = $request->getParsedBody(); //$_post
        $product = new Product($this->container);
        $result = $product->deleteOrders($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
}
