<?php
session_start();

require_once('include/twig.php');
require_once('include/fonctions.php');
require_once('include/connexion.php');
require_once('include/theme.php');
require_once('include/article.php');
require_once('include/element.php');

$twig = init_twig();

$page = isset($_GET['page']) ? $_GET['page'] : '';
$action = isset($_GET['action']) ? $_GET['action'] : 'read';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$login = isset($_SESSION['login']) ? $_SESSION['login'] : '';
if ($login !== 'propriopass') {
    header('Location: index.php');
    exit;
}
$login = '';
if (isset($_SESSION['login'])) $login = $_SESSION['login'];
$role = '';
if (isset($_SESSION['role'])) $role = $_SESSION['role'];

$view = '';
$data = [];

$data['liste_themes'] = Theme::readAll();
$data['liste_articles'] = Article::readAll();

switch ($page) {
    case 'element':
        Element::controleurAdmin($action, $id, $view, $data);
        break;

    case 'logout':
        unset($_SESSION['login']);
        header('Location: index.php');
        exit;

    default:
        $view = 'admin.twig';
        break;
}

switch ($action) {
    case 'create':
        $element = new Element();
        $element->chargePOST();
        $element->create();
        header('Location: proprietaire.php?page=element&id_article=' . $element->id_article);
        exit;
}

$data['liste_themes'] = Theme::readAll();
$data['articles'] = Article::readAll();
echo $twig->render($view, $data);
