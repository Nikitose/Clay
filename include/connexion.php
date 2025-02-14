<?php
include('config.php');

function connexion()
{
  global $config;

  $pdo = new PDO(
    $config['driver'] . ':host=' . $config['serveur'] . ';dbname=' . $config['base'] . ';charset=utf8',
    $config['utilisateur'],
    $config['mdp']
  );
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
  if ($pdo) {
    return $pdo;
  } else {
    echo '<p>Connexion à la base de données impossible !</p>';
    exit;
  }
}
