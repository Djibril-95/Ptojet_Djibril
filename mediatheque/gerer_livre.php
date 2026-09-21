<?php
require_once 'config/db.php';
include 'includes/header.php';

$id_livre = $_GET['id'] ?? null;
$titre = '';
$isbn = '';
$annee_publication = '';
$id_categorie = '';
$message = '';
$erreur = '';

// Récupération des catégories si ta table CATEGORIE existe (pour les menus déroulants)
try {
    $categories = $pdo->query("SELECT * FROM CATEGORIE")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $categories = [];
}

// Si on est en modification, on charge les données du livre
if ($id_livre) {
    $stmt = $pdo->prepare("SELECT * FROM LIVRE WHERE id_livre = ?");
    $stmt->execute([$id_livre]);
    $livre = $stmt->fetch();
    if ($livre) {
        $titre = $livre['titre'] ?? '';
        $isbn = $livre['isbn'] ?? '';
        $annee_publication = $livre['annee_publication'] ?? '';
        $id_categorie = $livre['id_categorie'] ?? '';
    }
}

// Traitement du formulaire à la soumission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $annee_publication = !empty($_POST['annee_publication']) ? $_POST['annee_publication'] : null;
    $id_categorie = !empty($_POST['id_categorie']) ? $_POST['id_categorie'] : null;

    if (!empty($titre)) {
        try {
            if ($id_livre) {
                // Modification
                $stmt = $pdo->prepare("UPDATE LIVRE SET titre = ?, isbn = ?, annee_publication = ?, id_categorie = ? WHERE id_livre = ?");
                $stmt->execute([$titre, $isbn, $annee_publication, $id_categorie, $id_livre]);
                $message = "Livre mis à jour avec succès !";
            } else {
                // Insertion (on met disponible à 1 par défaut)
                $stmt = $pdo->prepare("INSERT INTO LIVRE (titre, isbn, annee_publication, id_categorie, disponible) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([$titre, $isbn, $annee_publication, $id_categorie]);
                $message = "Livre ajouté avec succès au catalogue !";
            }
        } catch (PDOException $e) {
            $erreur = "Erreur SQL : " . $e->getMessage();
        }
    } else {
        $erreur = "Le titre du livre est obligatoire.";
    }
}
?>

<h2><?= $id_livre ? 'Modifier le Livre' : 'Ajouter un Livre'; ?></h2>

<?php if ($message): ?>
    <div style="background-color: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
        <?= $message; ?>
    </div>
<?php endif; ?>

<?php if ($erreur): ?>
    <div style="background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
        <?= $erreur; ?>
    </div>
<?php endif; ?>

<form method="POST">
    <label for="titre">Titre du livre :</label>
    <input type="text" name="titre" id="titre" value="<?= htmlspecialchars($titre); ?>" required>

    <label for="isbn">ISBN :</label>
    <input type="text" name="isbn" id="isbn" value="<?= htmlspecialchars($isbn); ?>" placeholder="Ex: 978-2070401574">

    <label for="annee_publication">Année de publication :</label>
    <input type="text" name="annee_publication" id="annee_publication" value="<?= htmlspecialchars($annee_publication); ?>" placeholder="Ex: 2024">

    <?php if (count($categories) > 0): ?>
        <label for="id_categorie">Catégorie :</label>
        <select name="id_categorie" id="id_categorie">
            <option value="">-- Choisir une catégorie --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id_categorie']; ?>" <?= ($id_categorie == $cat['id_categorie']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($cat['libelle'] ?? $cat['nom'] ?? 'Catégorie ' . $cat['id_categorie']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary" style="margin-top: 10px;">
        <?= $id_livre ? 'Mettre à jour' : 'Enregistrer le livre'; ?>
    </button>
</form>

<?php include 'includes/footer.php'; ?>