<?php
session_start();

use Slim\Views\PhpRenderer;

class AdminController
{
    protected $container;
    public function __construct()
    {
        global $container;
        $this->container = $container;
    }

    public function admin($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/backend/home/home.html');
    }

    public function renderPage($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/backend/usermanage/page.html');
    }

    public function getAccounts($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $account = new Admin($this->container);
        $result = $account->preDatatables($data);
        $result['data'] = $account->getAccounts($data);
        $result['recordsTotal'] = $account->getAccountsTotal($data);
        $result['recordsFiltered'] = $result['recordsTotal'];
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function DeleteUser($request, $response, $args)
    {
        $data = $request->getParsedBody(); 
        $product = new Admin($this->container);
        $result = $product->DeleteUser($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function DeleteRole($request, $response, $args)
    {
        $data = $request->getParsedBody(); 
        $product = new Admin($this->container);
        $result = $product->DeleteRole($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }


    public function getRoles($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $account = new Admin($this->container);
        $result = $account->preDatatables($data);
        $result['data'] = $account->getRoles($data);
        $result['recordsTotal'] = $account->getRolesTotal($data);
        $result['recordsFiltered'] = $result['recordsTotal'];
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getAllRole($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $admin = new Admin($this->container);
        $result = $admin->getAllRole($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }  

    public function getRole($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $admin = new Admin($this->container);
        $result = $admin->getRole($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getAccountRole($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $admin = new Admin($this->container);
        $result = $admin->getAccountRole($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getPermissions($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $admin = new Admin($this->container);
        $result = $admin->getPermissions($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getOwnPermissions($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $data += $_SESSION;
        $admin = new Admin($this->container);
        $result = $admin->getOwnPermissions($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getOwnUserName($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $data += $_SESSION;
        $admin = new Admin($this->container);
        $result = $admin->getOwnUserName($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getAccountIdentity($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $admin = new Admin($this->container);
        $result = $admin->getAccountIdentity($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getAuthorities($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $account = new Admin($this->container);
        $result = $account->preDatatables($data);
        $result['data'] = $account->getAuthorities($data);
        $result['recordsTotal'] = $account->getAuthoritiesTotal($data);
        $result['recordsFiltered'] = $result['recordsTotal'];
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function updateauthority($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/backend/usermanage/authoritychange.html');
    }

    public function insertrole($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/backend/usermanage/newrole.html');
    }

    public function renderlogin($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/backend/usermanage/login.html');
    }

    public function login($request, $response, $args)
    {
        $data = $request->getParsedBody(); //$_post
        $verify = $data["verification"];
        unset($data["verification"]);
        // $data = $request->getQueryParams(); //$_get
        $admin = new Admin($this->container);
        $verify_result = $admin->checkCode($verify);
        if ($verify_result === "success") {
            $result = $admin->login($data);
        }else{
            $result = [
                "status" => "failed",
                "message" => "驗證碼錯誤"
            ];
            $response = $response->withStatus(500);
            $response = $response->withHeader('Content-type', 'application/json');
            $response = $response->withJson($result);
            return $response;
        }
        if(array_key_exists("status",$result)){
            if($result["status"]==="failed"){
                $result = [
                    "status" => "failed",
                    "message" => "密碼錯誤"
                ];
                $response = $response->withStatus(500);
            }
        }
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function renderDrawpic($request, $response, $args)
    {
        $admin = new Admin($this->container);
        $response = $admin->drawPic();
        session_write_close();
        return $response;
    }

    public function renderForgotPassword($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/backend/usermanage/findpass.html');
    }

    public function createAccount($request, $response, $args)
    {
        $renderer = new PhpRenderer($this->container->view);
        return $renderer->render($response, '/backend/usermanage/createacc.html');
    }

    public function getAdminTest($request, $response, $args)
    {
        $admin = new Admin($this->container);
        $result = $admin->getAdminTest();
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function addRole($request, $response, $args)
    {
        $data = $request->getParsedBody(); 
        $product = new Admin($this->container);
        $result = $product->addRole($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function getUsers($request, $response, $args)
    {
        $data = $request->getQueryParams();
        $admin = new Admin($this->container);
        $result = $admin->getUsers($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function addUsers($request, $response, $args)
    {
        $data = $request->getParsedBody(); 
        $product = new Admin($this->container);
        $result = $product->addUsers($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function deleteUsers($request, $response, $args)
    {
        $data = $request->getParsedBody(); 
        $product = new Admin($this->container);
        $result = $product->deleteUsers($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }

    public function patchUsers($request, $response, $args)
    {
        $data = $request->getParsedBody();
        $admin = new Admin($this->container);
        $result = $admin->patchUsers($data);
        $response = $response->withHeader('Content-type', 'application/json');
        $response = $response->withJson($result);
        return $response;
    }
}
