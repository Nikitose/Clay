<?php

require_once('fonctions.php');

class Theme
{
	public $id;
	public $nom;
	public $description;
	public $image;

	function __construct()
	{
		$this->id = intval($this->id);
	}

	// readAll
	static function readAll()
	{
		$sql = 'SELECT * FROM theme';
		$pdo = connexion();
		$query = $pdo->prepare($sql);
		$query->execute();
		return $query->fetchAll(PDO::FETCH_CLASS, 'Theme');
	}

	// readAll
	static function readAll_2last()
	{
		$sql = 'SELECT * FROM theme ORDER BY id DESC limit 2';
		$pdo = connexion();
		$query = $pdo->prepare($sql);
		$query->execute();
		return $query->fetchAll(PDO::FETCH_CLASS, 'Theme');
	}

	// readOne
	static function readOne($id)
	{
		$sql = 'SELECT * FROM theme WHERE id = :id';
		$pdo = connexion();
		$query = $pdo->prepare($sql);
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		$query->execute();
		return $query->fetchObject('Theme');
	}

	// Création d'un nouveau thème
	function create()
	{
		$sql = "INSERT INTO theme (nom) VALUES (:nom)";
		$pdo = connexion();
		$query = $pdo->prepare($sql);
		$query->bindValue(':nom', $this->nom, PDO::PARAM_STR);
		$query->execute();
		$this->id = $pdo->lastInsertId();
	}

	// Mise à jour d'un thème
	function update()
	{
		$sql = "UPDATE theme SET nom = :nom, description = :description, image = :image WHERE id = :id";
		$pdo = connexion();
		$query = $pdo->prepare($sql);
		$query->bindValue(':id', $this->id, PDO::PARAM_INT);
		$query->bindValue(':nom', $this->nom, PDO::PARAM_STR);
		$query->bindValue(':description', $this->description, PDO::PARAM_STR);
		$query->bindValue(':image', $this->image, PDO::PARAM_STR);
		$query->execute();
	}

	// Suppression d'un thème et de son image
	function delete()
	{
		if (!empty($this->image)) unlink('upload/' . $this->image);
		$sql = "DELETE FROM theme WHERE id = :id";
		$pdo = connexion();
		$query = $pdo->prepare($sql);
		$query->bindValue(':id', $this->id, PDO::PARAM_INT);
		$query->execute();
	}

	// Charge les données du formulaire
	function chargePOST()
	{
		$this->id = postInt('id');
		$this->nom = postString('nom');
		$this->description = postString('description');
		$this->image = postString('old-image');

		$image = chargeFILE();
		if (!empty($image)) {
			unlink('upload/' . $this->image);
			$this->image = $image;
		}
	}

	static function controleur($action, $id, &$view, &$data)
	{
		$data['theme'] = Theme::readOne($id);
		$data['articles'] = Article::readAllByTheme($id);
		$view = 'categorie.twig';
	}

	static function controleurAdmin($action, $id, &$view, &$data)
	{
		$data['liste_themes'] = Theme::readAll();

		switch ($action) {
			case 'read':
				if ($id > 0) {
					$view = 'theme/detail_theme.twig';
					$data['theme'] = Theme::readOne($id);
					$data['liste_articles'] = Article::readAllByTheme($id);
				} else {
					$view = 'theme/liste_themes.twig';
				}
				break;
			case 'new':
				$view = "theme/form_theme.twig";
				break;
			case 'create':
				$theme = new Theme();
				$theme->chargePOST();
				$theme->create();
				header('Location: admin.php?page=theme');
				break;
			case 'edit':
				$view = "theme/edit_theme.twig";
				$data['theme'] = Theme::readOne($id);
				break;
			case 'update':
				$theme = new Theme();
				$theme->chargePOST();
				$theme->update();
				header('Location: admin.php?page=theme');
				break;
			case 'delete':
				$theme = Theme::readOne($id);
				$theme->delete();
				header('Location: admin.php?page=theme');
				break;
			default:
				$view = 'theme/liste_themes.twig';
				break;
		}
	}

	// Création de la table themes si nécessaire
	static function init()
	{
		$pdo = connexion();

		$sql = 'DROP TABLE IF EXISTS theme';
		$query = $pdo->prepare($sql);
		$query->execute();

		$sql = 'CREATE TABLE theme (
				id SERIAL PRIMARY KEY,
				nom VARCHAR(128),
				description VARCHAR(512),
				image VARCHAR(512))';
		$query = $pdo->prepare($sql);
		$query->execute();
	}
}
?>
