<?php
session_start();

require_once('include/twig.php');
require_once('include/connexion.php');
require_once('include/theme.php');
require_once('include/article.php');
require_once('include/element.php');

// Initialisation de Twig
$twig = init_twig();

// Vérification des droits administrateur
// Charge le login stocké dans la session
if (isset($_SESSION['login'])) $login = $_SESSION['login'];

// Si le login est incorrect : retour à la page d'accueil
if ($login != 'adminpass') {
	header('Location: index.php');
}

echo $twig->render('createDB.twig', []);
