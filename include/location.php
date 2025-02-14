<?php

require_once('fonctions.php');

class Location
{
    public $id;
    public $id_article;
    public $adresse;
    public $date;
    public $heure_debut;
    public $heure_fin;
    public $email;
    public $telephone;

    function __construct()
    {
        $this->id = intval($this->id);
        $this->id_article = intval($this->id_article);
        $this->adresse = (string)$this->adresse;
        $this->date = (string)$this->date;
        $this->heure_debut = (string)$this->heure_debut;
        $this->heure_fin = (string)$this->heure_fin;
        $this->email = (string)$this->email;
        $this->telephone = (string)$this->telephone;
    }

    // Fonction de création d'une réservation
    function creerReservation()
    {
        $sql = "INSERT INTO locations (id_article, adresse, date, heure_debut, heure_fin, email, telephone)
                VALUES (:id_article, :adresse, :date, :heure_debut, :heure_fin, :email, :telephone)";
        $pdo = connexion();
        $query = $pdo->prepare($sql);
        $query->bindValue(':id_article', $this->id_article, PDO::PARAM_INT);
        $query->bindValue(':adresse', $this->adresse, PDO::PARAM_STR);
        $query->bindValue(':date', $this->date, PDO::PARAM_STR);
        $query->bindValue(':heure_debut', $this->heure_debut, PDO::PARAM_STR);
        $query->bindValue(':heure_fin', $this->heure_fin, PDO::PARAM_STR);
        $query->bindValue(':email', $this->email, PDO::PARAM_STR);
        $query->bindValue(':telephone', $this->telephone, PDO::PARAM_STR);
        $query->execute();
        $this->id = $pdo->lastInsertId(); // Récupère l'ID de la réservation créée
    }

    // Méthode pour charger les données du POST dans l'objet
    function chargePOST()
    {
        $this->id_article = postInt('id_article');
        $this->adresse = postString('adresse');
        $this->date = postString('date');
        $this->heure_debut = postString('heure_debut');
        $this->heure_fin = postString('heure_fin');
        $this->email = postString('email');
        $this->telephone = postString('telephone');
    }

    static function controleur($action, $id, &$view, &$data)
    {
        switch ($action) {
            case 'reservation':
                $location = new Location();
                $location->chargePOST();
                $location->creerReservation();
                $view = 'accueil.twig';
                break;
            default:
                $view = 'form_acheter.twig'; 
                break;
        }
    }
}

?>
