<?php

require_once('fonctions.php');

class Element
{
    public $id;
    public $ordre;
    public $nom;
    public $description;
    public $image;
    public $id_article;
    public $prix;
    public $prix_heure;
    public $vendeur;

    function __construct()
    {
        $this->id = intval($this->id);
        $this->ordre = intval($this->ordre);
        $this->id_article = intval($this->id_article);
        $this->prix = (string)$this->prix;
        $this->prix_heure = (string)$this->prix_heure;
        $this->vendeur = (string)$this->vendeur;
    }

    static function readOne($id)
    {
        $sql = 'SELECT * FROM element WHERE id = :id';
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchObject('Element');
    }

    // readAll
    static function readAll_2last()
    {
        $sql = 'SELECT * FROM element ORDER BY id DESC limit 3';
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_CLASS, 'Element');
    }

    static function readAllByArticle($id)
    {
        $sql = 'SELECT * FROM element WHERE id_article = :id ORDER BY ordre';
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_CLASS, 'Element');
    }

    static function controleur($action, $id, &$view, &$data)
    {
        switch ($action) {
            default:
                $theme = Theme::readOne($id);
                $articles = Article::readAllByTheme($id);

                if (isset($_GET['id_article'])) {
                    $id_article = $_GET['id_article'];
                    $elements = Element::readAllByArticle($id_article);
                } else {
                    $elements = [];
                }

                var_dump($data);
                die();
                $view = 'categorie.twig';
                $data = [
                    'theme' => $theme,
                    'articles' => $articles,
                    'elements' => $elements
                ];
                break;
        }
    }

    static function readOrderMax($id)
    {
        $sql = 'SELECT max(ordre) AS maximum FROM element WHERE id_article = :id';
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->bindValue(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $objet = $query->fetchObject();
        return intval($objet->maximum);
    }

    function exchangeOrder()
    {
        $sql = 'SELECT * FROM element WHERE id_article = :id_article AND ordre < :ordre ORDER BY ordre DESC';
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->bindValue(':id_article', $this->id_article, PDO::PARAM_INT);
        $query->bindValue(':ordre', $this->ordre, PDO::PARAM_INT);
        $query->execute();

        $before = $query->fetchObject('Element');
        if ($before) {
            $tmp = $this->ordre;
            $this->ordre = $before->ordre;
            $this->update();
            $before->ordre = $tmp;
            $before->update();
        }
    }

    function create()
    {
        if (empty($this->ordre)) {
            $maximum = self::readOrderMax($this->id_article);
            $this->ordre = $maximum + 1;
        }

        $sql = "INSERT INTO element (ordre, nom, description, image, id_article, prix, prix_heure, vendeur)
                VALUES (:ordre, :nom, :description, :image, :id_article, :prix, :prix_heure, :vendeur)";
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->bindValue(':ordre', $this->ordre, PDO::PARAM_INT);
        $query->bindValue(':nom', $this->nom, PDO::PARAM_STR);
        $query->bindValue(':description', $this->description, PDO::PARAM_STR);
        $query->bindValue(':image', $this->image, PDO::PARAM_STR);
        $query->bindValue(':id_article', $this->id_article, PDO::PARAM_INT);
        $query->bindValue(':prix', $this->prix, PDO::PARAM_STR);
        $query->bindValue(':prix_heure', $this->prix_heure, PDO::PARAM_STR);
        $query->bindValue(':vendeur', $this->vendeur, PDO::PARAM_STR);
        $query->execute();
        $this->id = $pdo->lastInsertId();
    }

    function update()
    {
        if (empty($this->image)) {
            $this->image = postString('old-image');
        }

        $sql = "UPDATE element
                SET ordre = :ordre, nom = :nom, description = :description, image = :image, id_article = :id_article, prix = :prix, prix_heure = :prix_heure, vendeur = :vendeur
                WHERE id = :id";
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->bindValue(':ordre', $this->ordre, PDO::PARAM_INT);
        $query->bindValue(':nom', $this->nom, PDO::PARAM_STR);
        $query->bindValue(':description', $this->description, PDO::PARAM_STR);
        $query->bindValue(':image', $this->image, PDO::PARAM_STR);
        $query->bindValue(':id_article', $this->id_article, PDO::PARAM_INT);
        $query->bindValue(':prix', $this->prix, PDO::PARAM_STR);
        $query->bindValue(':prix_heure', $this->prix_heure, PDO::PARAM_STR);
        $query->bindValue(':vendeur', $this->vendeur, PDO::PARAM_STR);
        $query->execute();
    }

    function delete()
    {
        if (!empty($this->image)) unlink('upload/' . $this->image);

        $sql = "DELETE FROM element WHERE id=:id";
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->bindValue(':id', $this->id, PDO::PARAM_INT);
        $query->execute();
    }

    function chargePOST()
    {
        $this->id = postInt('id');
        $this->ordre = postInt('ordre');
        $this->nom = postString('nom');
        $this->description = postString('description');
        $this->image = postString('old-image');
        $this->id_article = postInt('id_article');
        $this->prix = postString('prix');
        $this->prix_heure = postString('prix_heure');
        $this->vendeur = postString('vendeur');

        $image = chargeFILE();
        if (!empty($image)) {
            unlink('upload/' . $this->image);
            $this->image = $image;
        }
    }

    static function controleurAdmin($action, $id, &$view, &$data)
    {
        switch ($action) {
            case 'read':
                header('Location: admin.php?page=article&id=' . $id);
                break;
            case 'new':
                $view = "element/form_element.twig";
                $data = ['id_article' => $id];
                break;
            case 'create':
                $element = new Element();
                $element->chargePOST();
                $element->create();
                header('Location: admin.php?page=article&id=' . $element->id_article);
                break;
            case 'edit':
                $view = "element/edit_element.twig";
                $data = ['element' => Element::readOne($id)];
                break;
            case 'update':
                $element = new Element();
                $element->chargePOST();
                $element->update();
                header('Location: admin.php?page=article&id=' . $element->id_article);
                break;
            case 'delete':
                $element = Element::readOne($id);
                $element->delete();
                header('Location: admin.php?page=article&id=' . $element->id_article);
                break;
            case 'exchange':
                $element = Element::readOne($id);
                $element->exchangeOrder();
                header('Location: admin.php?page=article&id=' . $element->id_article);
                break;
            default:
                header('Location: admin.php?page=article&id=' . $id);
        }
    }

    static function init()
    {
        $pdo = connexion();

        $sql = 'drop table if exists element';
        $query = $pdo->prepare($sql);
        $query->execute();

        $sql = 'create table element (
                id serial primary key,
                ordre int,
                nom varchar(128),
                description text,
                image varchar(512),
                id_article bigint unsigned,
                prix varchar(128),
                prix_heure varchar(128),
                vendeur varchar(128),
                foreign key (id_article) references article(id))';
        $query = $pdo->prepare($sql);
        $query->execute();
    }
}
