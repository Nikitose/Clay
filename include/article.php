<?php

use phpDocumentor\Reflection\DocBlock\Tags\Var_;

require_once('fonctions.php');

class Article
{
    public $id;
    public $ordre;
    public $titre;
    public $redacteur;
    public $accroche;
    public $image;
    public $id_theme;

    // Le constructeur corrige les données récupérées de la BDD
    function __construct()
    {
        $this->id = intval($this->id);
        $this->ordre = intval($this->ordre);
        $this->id_theme = intval($this->id_theme);
    }

    // readAll
    static function readAll()
    {
        $sql = 'SELECT * FROM article ORDER BY ordre';
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_CLASS, 'Article');
    }

    // readOne
    static function readOne($id)
    {
        $sql = 'SELECT * FROM article WHERE id = :id';
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchObject('Article');
    }

    // Récupère les articles d'un thème
    static function readAllByTheme($id)
    {
        $sql = 'SELECT * FROM article WHERE id_theme = :id ORDER BY ordre';
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_CLASS, 'Article');
    }

    // Contrôleur
    static function controleurAdmin($action, $id, &$view, &$data)
    {
        switch ($action) {
            case 'read':
                if ($id > 0) {
                    $view = 'article/detail_article.twig';
                    $data = [
                        'article' => Article::readOne($id),
                        'liste_elements' => Element::readAllByArticle($id)
                    ];
                } else {
                    header('Location: admin.php?page=theme');
                }
                break;

            case 'new':
                $view = "article/form_article.twig";
                $data = ['id_theme' => $id];
                break;

            case 'create':
                $article = new Article();
                $article->chargePOST();
                $article->create();
                header('Location: admin.php?page=theme&id=' . $article->id_theme);
                exit;

            case 'edit':
                $view = "article/edit_article.twig";
                $data = ['article' => self::readOne($id)];
                break;

            case 'update':
                $article = new Article();
                $article->chargePOST();
                $article->update();
                header('Location: admin.php?page=theme&id=' . $article->id_theme);
                exit;
            
            case 'exchange':
                $article = Article::readOne($id);
                $article->exchangeOrder();
                $view = 'theme/detail_theme.twig';
                header('Location: admin.php?page=theme&id=' . $article->id_theme);
                break;

            case 'delete':
                $article = self::readOne($id);
                $article->delete();
                header('Location: admin.php?page=theme&id=' . $article->id_theme);
                exit;

            default:
                $view = '404.twig';
                break;
        }
    }

	function exchangeOrder()
	{
		$sql = 'SELECT * FROM article
				WHERE id_theme = :id_theme AND ordre < :ordre ORDER BY ordre DESC';
		$pdo = connexion();
		$query = $pdo->prepare($sql);
		$query->bindValue(':id_theme', $this->id_theme, PDO::PARAM_INT);
		$query->bindValue(':ordre', $this->ordre, PDO::PARAM_INT);
		$query->execute();

		$before = $query->fetchObject('Article');

		if ($before) {
			$tmp = $this->ordre;
			$this->ordre = $before->ordre;
			$this->update();
			$before->ordre = $tmp;
			$before->update();
		}
	}

    static function controleur($action, $id, &$view, &$data)
    {
        $view = 'categorie.twig';
        self::handleRead($id, $view, $data);
    }

    // Gère l'action 'read'
    static function handleRead($id, &$view, &$data)
    {
        $article = self::readOne($id);
    
        $data['elements'] = Element::readAllByArticle($id); 
        $data['articles'] = self::readAllByTheme($article->id_theme);
        $data['theme'] = Theme::readOne($article->id_theme);
    }

    // Create
    function create()
    {
        $maximum = self::readOrderMax($this->id_theme);
        $this->ordre = $maximum + 1;

        $sql = "INSERT INTO article (ordre, titre, accroche, redacteur, image, id_theme)
                VALUES (:ordre, :titre, :accroche, :redacteur, :image, :id_theme)";
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->bindValue(':ordre', $this->ordre, PDO::PARAM_INT);
        $query->bindValue(':titre', $this->titre, PDO::PARAM_STR);
        $query->bindValue(':accroche', $this->accroche, PDO::PARAM_STR);
        $query->bindValue(':redacteur', $this->redacteur, PDO::PARAM_STR);
        $query->bindValue(':image', $this->image, PDO::PARAM_STR);
        $query->bindValue(':id_theme', $this->id_theme, PDO::PARAM_INT);
        $query->execute();
        $this->id = $pdo->lastInsertId();
    }

    // Update
    function update()
    {
        $sql = "UPDATE article
                SET ordre=:ordre, titre=:titre, accroche=:accroche, redacteur=:redacteur, image=:image
                WHERE id=:id";
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->bindValue(':ordre', $this->ordre, PDO::PARAM_INT);
        $query->bindValue(':titre', $this->titre, PDO::PARAM_STR);
        $query->bindValue(':accroche', $this->accroche, PDO::PARAM_STR);
        $query->bindValue(':redacteur', $this->redacteur, PDO::PARAM_STR);
        $query->bindValue(':image', $this->image, PDO::PARAM_STR);
        $query->execute();
    }

    // Delete
    function delete()
    {
        if (!empty($this->image)) unlink('upload/' . $this->image);

        $sql = "DELETE FROM article WHERE id=:id";
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->execute();
    }

    // Charge les données POST dans l'objet
    function chargePOST()
    {
        $this->id = postInt('id');
        $this->ordre = postInt('ordre');
        $this->titre = postString('titre');
        $this->redacteur = postString('redacteur');
        $this->accroche = postString('accroche');
        $this->image = postString('old-image');
        $this->id_theme = postInt('id_theme');

        $image = chargeFILE();
        if (!empty($image)) {
            unlink('upload/' . $this->image);
            $this->image = $image;
        }
    }

    // Récupère l'ordre maximal des articles d'un thème
    static function readOrderMax($id)
    {
        $sql = 'SELECT max(ordre) AS maximum FROM article WHERE id_theme = :id';
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $objet = $query->fetchObject();
        return intval($objet->maximum);
    }

    // Initialise la table articles
    static function init()
    {
        $pdo = connexion();

        $sql = 'DROP TABLE IF EXISTS article';
        $query = $pdo->prepare($sql);
        $query->execute();

        $sql = 'CREATE TABLE article (
                id SERIAL PRIMARY KEY,
                ordre INT,
                titre VARCHAR(128),
                redacteur VARCHAR(512),
                accroche TEXT,
                image VARCHAR(512),
                id_theme BIGINT UNSIGNED,
                FOREIGN KEY (id_theme) REFERENCES theme(id))';
        $query = $pdo->prepare($sql);
        $query->execute();
    }
}
