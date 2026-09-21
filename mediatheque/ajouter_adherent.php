<?php
require_once 'config/db.php';
include 'includes/header.php';

$message = "";
$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!empty($nom) && !empty($prenom)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO ADHERENT (nom, prenom, email) VALUES (?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email]);
            $message = "Adhérent ajouté avec succès !";
        } catch (PDOException $e) {
            // Code 23000 ou 1062 = Violation d'unicité (doublon)
            if ($e->getCode() == 23000 || strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $erreur = "Cet email est déjà utilisé par un autre adhérent. Veuillez en choisir un autre.";
            } else {
                $erreur = "Erreur lors de l'ajout : " . $e->getMessage();
            }
        }
    } else {
        $erreur = "Le nom et le prénom sont obligatoires.";
    }
}
?>

<h2>Ajouter un Adhérent</h2>

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
    <label for="nom">Nom :</label>
    <input type="text" name="nom" id="nom" required>

    <label for="prenom">Prénom :</label>
    <input type="text" name="prenom" id="prenom" required>

    <label for="email">Email :</label>
    <input type="text" name="email" id="email" placeholder="exemple@email.com">

    <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Enregistrer l'adhérent</button>
</form>

<?php include 'includes/footer.php'; ?>