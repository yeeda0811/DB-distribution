<?php
session_start();
session_write_close();
header('Access-Control-Allow-Origin: *');

header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$config['displayErrorDetails'] = false;
$config['addContentLengthHeader'] = false;

$config = [
    'settings' => [
        'displayErrorDetails' => true,
        'addContentLengthHeader' => false,
        "determineRouteBeforeAppMiddleware" => true
    ],
];
$app = new \Slim\App($config);
$container = $app->getContainer();

$container['db'] = function ($c) {
    $connection = new PDO('pgsql:host=140.127.49.168;dbname=sugoeat', 'sugoeat', '7172930');
    return $connection;
};

$container['view'] = __DIR__ . '/../templates/';
$container['upload_directory'] = __DIR__ . '/../uploads/';

class LoginCheckAdminMiddleware
{
    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */

    public function __invoke($request, $response, $next)
    {
        $_SESSION['user_id'] = 1;
        if (isset($_SESSION['user_id'])) {
            $response = $next($request, $response);
        } else {
            $response = $response->withRedirect('/admin');
        }
        return $response;
    }
}

$app->group('', function () use ($app) {
    $app->get('/',  \HomeController::class . ':home');
    $app->get('/all',  \HomeController::class . ':getAllItems');
    $app->group('show', function () use ($app) {
        $app->get('',  \HomeController::class . ':renderShowItem');
        $app->get('/{item_id}',  \HomeController::class . ':getShowItem');
    });
});

$app->group('/blog', function () use ($app) {
    $app->get('',  \BlogController::class . ':renderIndex');
    $app->get('s',  \BlogController::class . ':getBlogs');
    $app->get('/b/{article_id}',  \BlogController::class . ':getBrowseBlogs');
    $app->get('/browse',  \BlogController::class . ':renderbrowse');   // 網路傳值，顯示在網址（args)
});

$app->group('/admin', function () use ($app) {
    $app->get('',  \AdminController::class . ':renderlogin');
    $app->group('/login', function () use ($app) {
        $app->post('',  \AdminController::class . ':login');
        $app->get('/drawPic',  \AdminController::class . ':renderDrawpic');
        $app->post('/checkCode',  \AdminController::class . ':login');
    });
    $app->group('', function () use ($app) {
        $app->get('/',  \AdminController::class . ':admin');
        $app->get('/ownPermissions',  \AdminController::class . ':getOwnPermissions');
        $app->get('/user_name',  \AdminController::class . ':getOwnUserName');

        $app->group('/blogmanage', function () use ($app) {
            $app->get('',  \BlogController::class . ':renderAdminIndex');
            $app->get('/alltag',  \BlogController::class . ':getAllTag');
            $app->group('/blog', function () use ($app) {
                $app->get('s',  \BlogController::class . ':getBlogs');
                $app->get('/{article_id}',  \BlogController::class . ':getAdminBlog');
                $app->delete('/deleteblog',  \BlogController::class . ':deleteBlog');
                $app->patch('',  \BlogController::class . ':patchBlogs');
            });
            $app->group('/tags', function () use ($app) {
                $app->get('',  \BlogController::class . ':getAllTag');
            });
        });
        $app->group('/usermanage', function () use ($app) {
            $app->get('',  \AdminController::class . ':renderPage');
            $app->get('s',  \AdminController::class . ':getUsers');
            $app->post('/addrole',  \AdminController::class . ':AddRole');
            $app->group('/account', function () use ($app) {
                $app->get('s',  \AdminController::class . ':getAccounts');
                $app->post('/adduser',  \AdminController::class . ':addUsers');
                $app->delete('/deleteuser',  \AdminController::class . ':DeleteUser');
                $app->patch('/patchusers',  \AdminController::class . ':patchUsers');
            });
            $app->get('/allrole',  \AdminController::class . ':getAllRole');
            $app->get('/role',  \AdminController::class . ':getAccountRole');
            $app->get('/identity',  \AdminController::class . ':getAccountIdentity');
            
        });

        $app->group('/productmanage', function () use ($app) {
            $app->group('/item', function () use ($app) {
                $app->group('/import', function () use ($app) {
                    $app->get('s',  \ProductController::class . ':getImports');
                    $app->post('/addimport',  \ProductController::class . ':addImport');
                    $app->post('/editimport',  \ProductController::class . ':EditImport');
                    $app->post('/deleteimport',  \ProductController::class . ':DeleteImport');
                    $app->get('/{import_id}',  \ProductController::class . ':getImport');
                });
                $app->get('',  \ProductController::class . ':renderItem');
                $app->get('s',  \ProductController::class . ':getItems');
                $app->post('/additem',  \ProductController::class . ':addItem');
                $app->post('/edititem',  \ProductController::class . ':EditItem');
                $app->post('/deleteitem',  \ProductController::class . ':DeleteItem');
                $app->get('/itemTypes',  \ProductController::class . ':getItemTypes');
                $app->get('/userItems',  \ProductController::class . ':getUserItems');
                $app->get('/unit',  \ProductController::class . ':getUnit');
                $app->get('/exportitem',  \ProductController::class . ':ExportItem');
                $app->get('/ownItemTypes',  \ProductController::class . ':getOwnItemTypes');
                $app->post('/upload',  \ProductController::class . ':uploadFile');
                $app->get('/file/{file_id}',  \ProductController::class . ':getFile');
                $app->get('/{item_id}',  \ProductController::class . ':getItem');
            });
            $app->group('/order', function () use ($app) {
                $app->get('',  \ProductController::class . ':renderOrder');
                $app->get('s',  \ProductController::class . ':getOrders');
                $app->get('/payingTypes',  \ProductController::class . ':getPayingTypes');
                $app->post('/addorder',  \ProductController::class . ':addOrder');
                $app->post('/editorder',  \ProductController::class . ':editOrder');
                $app->get('/{order_id}',  \ProductController::class . ':getOrder');
                $app->delete('/deleteorder',  \ProductController::class . ':deleteOrders');
            });
            $app->group('/discount', function () use ($app) {
                $app->get('',  \ProductController::class . ':renderDiscount');
                $app->get('s',  \ProductController::class . ':getDiscounts');
                $app->get('/discountType',  \ProductController::class . ':getDiscountType');
                $app->post('/addDiscount',  \ProductController::class . ':addDiscount');
                $app->post('/editDiscount',  \ProductController::class . ':EditDiscount');
                $app->post('/deleteDiscount',  \ProductController::class . ':DeleteDiscount');
                $app->get('/{discount_id}',  \ProductController::class . ':getDiscount');
            });
        });
        $app->get('/test',  \AdminController::class . ':getAdminTest');
        $app->group('/authoritychange', function () use ($app) {
            $app->get('',  \AdminController::class . ':updateauthority');
            $app->get('/authorities',  \AdminController::class . ':getAuthorities');
        });
        $app->group('/newrole', function () use ($app) {
            $app->get('',  \AdminController::class . ':insertrole');
            $app->group('/role', function () use ($app) {
                $app->get('s',  \AdminController::class . ':getRoles');
                $app->get('',  \AdminController::class . ':getRole');
                $app->post('/deleterole',  \AdminController::class . ':DeleteRole');
            });
            $app->get('/permissions',  \AdminController::class . ':getPermissions');
        });
        $app->group('/findpass', function () use ($app) {
            $app->get('',  \AdminController::class . ':renderForgotPassword');
        });
        $app->group('/createaccount', function () use ($app) {
            $app->get('',  \AdminController::class . ':createAccount');
        });
    })->add('LoginCheckAdminMiddleware');
});

$app->run();
