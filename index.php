<?php
session_start();

require_once('include/twig.php');
require_once('include/fonctions.php');
require_once('include/connexion.php');
require_once('include/theme.php');
require_once('include/article.php');
require_once('include/element.php');

$twig = init_twig();

$page = $_GET['page'] ?? '';
$action = $_GET['action'] ?? 'read';
$id = intval($_GET['id'] ?? 0);

$data = [];
$data['liste_themes'] = Theme::readAll();

switch ($page) {
    case 'theme':
        Theme::controleur($action, $id, $view, $data);
        break;

    case 'article':
        Article::controleur($action, $id, $view, $data);
        break;

    case 'detail_element':
        $element = Element::readOne($id);
        $view = 'element/detail_element.twig';
        $data['element'] = $element;
        break;

    case 'a-propos':
        $view = 'a-propos.twig';
        break;

    case '404':
        $view = '404.twig';
        break;

    case 'form_acheter':
        if (isset($_GET['id'])) {
            $id = $_GET['id']; 
            $element = Element::readOne($id);  
            $view = 'element/form_acheter.twig'; 
            $data = ['element' => $element];  
        }
        break;

    case 'login':
        $view = 'login.twig';
        break;

    case 'valid_login':
        if (isset($_POST['login']) && isset($_POST['role'])) {
            $login = postString('login');
            $role = postString('role');

            if ($role === 'admin' && $login === "adminpass") {
                $_SESSION['login'] = $login;
                $_SESSION['role'] = $role;
                $view = 'admin.twig';
                $data['role'] = 'admin';
            } elseif ($role === 'utilisateur' && $login === "propriopass") {
                $_SESSION['login'] = $login;
                $_SESSION['role'] = $role;
                $view = 'admin.twig';
                $data['role'] = 'utilisateur';
            } else {
                unset($_SESSION['login']);
                header('Location: index.php?page=login');
                exit;
            }
        } else {
            header('Location: index.php');
            exit;
        }
        break;

    default:
        $view = 'accueil.twig';
        $data['liste_2elements'] = Element::readAll_2last();
        break;
}

echo $twig->render($view, $data);
