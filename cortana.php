<?php
// Structure de la table patrimoines : id (int, auto-increment), commune (varchar), identifiant (int), patrimoine (varchar), principal (varchar), departement (int)
//  Structure de la table logs : id (auto-increment), log (varchar)

ini_set('display_errors', 0);
define("DB_NAME", "m2"); // Nom base de donnée
define("DB_USER", "root"); // Utilisateur
define("DB_PASSWORD", "sio"); // Mot de passe

function updateBDD()
{
    $pdo = new PDO("mysql:host=localhost;charset=UTF8;dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $fichierJSON = json_decode(file_get_contents("https://www.data.gouv.fr/fr/datasets/r/cb99a6b9-7e59-4ea4-9d58-a1d89a7a39a6", false, stream_context_create(array("ssl" => array("verify_peer" => false, "verify_peer_name" => false)))));
    $pdo->query("TRUNCATE TABLE patrimoines"); // <-- Vide les données déjà présentes dans la table, pour éviter de les avoir en double après une mise à jour
    foreach ($fichierJSON as $patrimoines) {
        $stm = $pdo->prepare("INSERT INTO patrimoines (commune, identifiant, patrimoine, principal, departement)
        VALUES (:commune, :identifiant, :patrimoine, :principal, :departement)");
        $stm->bindValue(":commune", $patrimoines->fields->commune);
        $stm->bindValue(":identifiant", $patrimoines->fields->identifian);
        $stm->bindValue(":patrimoine", $patrimoines->fields->elem_patri);
        if (array_key_exists("elem_princ", $patrimoines->fields)) // Verif car parfois l'élément "principal" n'existe pas et cause des bugs
            $stm->bindValue(":principal", $patrimoines->fields->elem_princ);
        else
            $stm->bindValue(":principal", "Pas d'infos");
        $stm->bindValue(":departement", substr($patrimoines->fields->identifian, 0, 2));
        $stm->execute();

        $stm = $pdo->prepare("INSERT INTO logs (log, date) VALUES (:txt, :date)"); // Ajout d'une ligne à la table logs.
        $stm->bindValue(":txt", "Mise à jour de la base de donnée");
        $stm->bindValue(":date", date("d-m-Y"));
        $stm->execute();
    }
}

function getData($commune = null, $departement = null, $type = null)
{
    $pdo = new PDO("mysql:host=localhost;charset=UTF8;dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $sql = "SELECT commune, identifiant, patrimoine, principal, departement FROM patrimoines WHERE 1=1";
    $txt = "Search [ ";
    if ($commune != null) {
        $sql .= " AND commune='" . $commune."'";
        $txt .= "Commune: " . $commune;
    }
    if ($departement != null) {
        $sql .= " AND departement=" . $departement;
        $txt .= "Département:" . $departement;
    }
    if ($type != null) {
        $sql .= " AND patrimoine LIKE '" . $type . "%';";
        $txt .= "Type:" . $type;
    }
    $data = $pdo->query($sql);
    $stm = $pdo->prepare("INSERT INTO logs (log, date) VALUES (:txt, :date)"); // Ajout d'une ligne à la table logs.
    $stm->bindValue(":txt", $txt . "]");
    $stm->bindValue(":date", date("d-m-Y"));
    $stm->execute();
    return json_encode($data->fetchAll(), JSON_UNESCAPED_UNICODE);
}

if (isset($_GET['updateBDD'])) updateBDD();
if (isset($_GET['search'])) print_r(getData($_GET['commune'], $_GET['departement'], $_GET['type']));
else echo "<h2>Documentation API Patrimoine des bords de Seine-et-Marne</h2><br /><b>Paramètres disponibles</b><ul><li><b>search</b> (paramètre obligatoire pour que l'API fonctionne, à laisser vide. Si elle est appelée toute seule, sans critères, alors tous les patrimoines sont récupérés)</li><li>commune (exemple: Melun)</li><li>departement (exemple : 77)</li><li>patrimoine (le nom du patrimoine, exemple : Pont de Seine)</li><li>type (le type du patrimoine, exemple : pont, viaduc, front, passerelle, etc.)</li></ul> <br /><b>Exemples</b> <ul><li>?search : Récupère toutes les données de la table patrimoine <a href='?search'>Tester</a></li><li>?search&<b>commune=Paris</b>&<b>type=Pont</b> : Récupère les ponts de Paris <a href='?search&commune=Paris&type=Pont'>Tester</a></li><li>?search&<b>departement=77</b> : Récupère tout du département 77 <a href='?search&departement=77'>Tester</a></li></ul><br /><b>Structure des tables</b><ul><li>Table patrimoines : id (int, auto-increment), commune (varchar), identifiant (int), patrimoine (varchar), principal (varchar), departement (int)</li><li>Table logs : id (auto-increment), log (varchar)</li></ul><br /><b>Identifiants base de donnée actuel</b><ul><li>Host: localhost</li><li>Utilisateur: " . DB_USER . "</li><li>Mot de passe: " . DB_PASSWORD . "</li><li>Nom base de donnée: " . DB_NAME . "</li></ul>";
