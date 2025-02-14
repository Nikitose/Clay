<?php
session_start();

require_once('include/twig.php');
require_once('include/fonctions.php');
require_once('include/connexion.php');
require_once('include/theme.php');
require_once('include/article.php');
require_once('include/element.php');
require_once('include/location.php');

$twig = init_twig();

if (isset($_GET['page'])) $page = $_GET['page'];
else $page = '';
if (isset($_GET['action'])) $action = $_GET['action'];
else $action = 'read';
if (isset($_GET['id'])) $id = intval($_GET['id']);
else $id = 0;

$login = '';
if (isset($_SESSION['login'])) $login = $_SESSION['login'];
$role = '';
if (isset($_SESSION['role'])) $role = $_SESSION['role'];

if ($role == '') {
	header('Location: index.php');
}

$view = '';
$data = ['role' => $role];

switch ($page) {
	case 'theme':
		Theme::controleurAdmin($action, $id, $view, $data);
		break;
	case 'article':
		Article::controleurAdmin($action, $id, $view, $data);
		break;
	case 'element':
		Element::controleurAdmin($action, $id, $view, $data);
		break;
	case 'location':
		Location::controleur($action, $id, $view, $data);
		break;
	case 'logout':
		unset($_SESSION['login']);
		$view = 'accueil.twig';
		break;
	default:
		$view = 'admin.twig';
		$data = [];
}

$data['liste_themes'] = Theme::readAll();
$data['articles'] = Article::readAll();
echo $twig->render($view, $data);
