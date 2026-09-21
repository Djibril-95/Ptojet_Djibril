<?php
require_once 'config/db.php';
include 'includes/header.php';

$message = '';
$erreur = '';

// Traitement du formulaire lorsqu'il est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_adherent = isset($_POST['id_adherent']) ? intval($_POST['id_adherent']) : 0;
    $id_livre = isset($_POST['id_livre']) ? intval($_POST['id_livre']) : 0;
    
    // Récupération des dates saisies par l'utilisateur (avec valeurs par défaut au cas où)
    $date_emprunt = !empty($_POST['date_emprunt']) ? $_POST['date_emprunt'] : date('Y-m-d');
    $date_retour_prevue = !empty($_POST['date_retour_prevue']) ? $_POST['date_retour_prevue'] : date('Y-m-d', strtotime('+14 days'));

    if ($id_adherent > 0 && $id_livre > 0) {
        try {
            // Utilisation d'une transaction pour s'assurer que tout se passe bien ensemble
            $pdo->beginTransaction();

            // 1. Insérer l'emprunt dans la table EMPRUNT avec les dates choisies
            $sqlEmprunt = "INSERT INTO EMPRUNT (id_adherent, id_livre, date_emprunt, date_retour_prevue) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sqlEmprunt);
            $stmt->execute([$id_adherent, $id_livre, $date_emprunt, $date_retour_prevue]);

            // 2. Mettre à jour le livre pour le passer à indisponible (disponible = 0)
            $sqlLivre = "UPDATE LIVRE SET disponible = 0 WHERE id_livre = ?";
            $stmtLivre = $pdo->prepare($sqlLivre);
            $stmtLivre->execute([$id_livre]);

            // Valider les modifications
            $pdo->commit();
            $message = "L'emprunt a été enregistré avec succès ! (Retour prévu le : $date_retour_prevue)";
        } catch (Exception $e) {
            // En cas d'erreur, on annule tout
            $pdo->rollBack();
            $erreur = "Erreur lors de l'enregistrement de l'emprunt : " . $e->getMessage();
        }
    } else {
        $erreur = "Veuillez sélectionner un adhérent et un livre valide.";
    }
}

// Récupérer la liste des adhérents pour le menu déroulant
$adherents = $pdo->query("SELECT id_adherent, nom, prenom FROM ADHERENT ORDER BY nom, prenom")->fetchAll(PDO::FETCH_ASSOC);

// Récupérer UNIQUEMENT les livres disponibles
$livresDispos = $pdo->query("SELECT id_livre, titre FROM LIVRE WHERE disponible = 1 ORDER BY titre")->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Enregistrer un Nouvel Emprunt</h2>

<?php if ($message): ?>
    <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
        <?= $message; ?>
    </div>
<?php endif; ?>

<?php if ($erreur): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
        <?= $erreur; ?>
    </div>
<?php endif; ?>

<form method="POST" action="emprunter.php" style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 600px;">
    
    <div style="margin-bottom: 15px;">
        <label for="id_adherent" style="display: block; font-weight: bold; margin-bottom: 5px;">Adhérent :</label>
        <select name="id_adherent" id="id_adherent" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="">-- Choisir un adhérent --</option>
            <?php foreach ($adherents as $adh): ?>
                <option value="<?= $adh['id_adherent']; ?>">
                    <?= htmlspecialchars($adh['nom'] . ' ' . $adh['prenom']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div style="margin-bottom: 15px;">
        <label for="id_livre" style="display: block; font-weight: bold; margin-bottom: 5px;">Livre disponible :</label>
        <select name="id_livre" id="id_livre" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
            <option value="">-- Choisir un livre --</option>
            <?php foreach ($livresDispos as $livre): ?>
                <option value="<?= $livre['id_livre']; ?>">
                    <?= htmlspecialchars($livre['titre']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (count($livresDispos) === 0): ?>
            <small style="color: #e74c3c;">Aucun livre n'est disponible actuellement pour un emprunt.</small>
        <?php endif; ?>
    </div>

    <!-- Ajout des champs personnalisés pour les dates -->
    <div style="margin-bottom: 15px;">
        <label for="date_emprunt" style="display: block; font-weight: bold; margin-bottom: 5px;">Date d'emprunt :</label>
        <input type="date" name="date_emprunt" id="date_emprunt" value="<?= date('Y-m-d'); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
    </div>

    <div style="margin-bottom: 20px;">
        <label for="date_retour_prevue" style="display: block; font-weight: bold; margin-bottom: 5px;">Date de retour prévue :</label>
        <input type="date" name="date_retour_prevue" id="date_retour_prevue" value="<?= date('Y-m-d', strtotime('+14 days')); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px;">
    </div>

    <button type="submit" class="btn btn-primary" <?= count($livresDispos) === 0 ? 'disabled' : ''; ?>>Valider l'emprunt</button>
</form>

<?php 
include 'includes/footer.php'; 
?>