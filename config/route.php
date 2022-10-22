<?php
/**
 * Skeleton application for SimpleMVC
 * 
 * @link      http://github.com/simplemvc/skeleton
 * @copyright Copyright (c) Enrico Zimuel (https://www.zimuel.it)
 * @license   https://opensource.org/licenses/MIT MIT License
 */
declare(strict_types=1);

use App\Controller;

return [
    [ 'GET', '/', Controller\Home::class],
    [ 'GET', '/search', Controller\Search::class],
    [ 'POST', '/search', Controller\Search::class],
    [ 'GET', '/login', Controller\Login::class],
    [ 'POST', '/login', Controller\Login::class],
    [ 'GET', '/adduser', Controller\AddUser::class],
    [ 'POST', '/adduser', Controller\AddUser::class],
    [ 'GET', '/deleteuser', Controller\DeleteUser::class],
    [ 'POST', '/deleteuser', Controller\DeleteUser::class],
    [ 'GET', '/logout', Controller\Logout::class],
    [ 'GET', '/advanced', Controller\Advanced::class],
    [ 'GET', '/edititem', Controller\EditItem::class],
    [ 'POST', '/edititem', Controller\EditItem::class],
    [ 'GET', '/deleteitem', Controller\DeleteItem::class],
    [ 'GET', '/additem', Controller\AddItem::class],
    [ 'POST', '/additem', Controller\AddItem::class],   
    [ 'GET', '/pictures', Controller\Pictures::class],
    [ 'GET', '/editpics', Controller\EditPics::class],
    [ 'POST', '/editpics', Controller\EditPics::class]
];
