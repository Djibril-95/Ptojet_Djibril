<?php
require_once 'config/db.php';
include 'includes/header.php';

$message = '';
$erreur = '';

// Traitement du retour lorsqu'on clique sur le bouton "Valider le retour"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_emprunt = isset($_POST['id_emprunt']) ? intval($_POST['id_emprunt']) : 0;
    $id_livre = isset($_POST['id_livre']) ? intval($_POST['id_livre']) : 0;
    $date_jour = date('Y-m-d');

    if ($id_emprunt > 0 && $id_livre > 0) {
        try {
            // Utilisation d'une transaction pour la sécurité des données
            $pdo->beginTransaction();

            // 1. Mettre à jour l'emprunt en y ajoutant la date de retour du jour
            $sqlEmprunt = "UPDATE EMPRUNT SET date_retour = ? WHERE id_emprunt = ?";
            $stmt = $pdo->prepare($sqlEmprunt);
            $stmt->execute([$date_jour, $id_emprunt]);

            // 2. Remettre le livre en mode disponible (disponible = 1)
            $sqlLivre = "UPDATE LIVRE SET disponible = 1 WHERE id_livre = ?";
            $stmtLivre = $pdo->prepare($sqlLivre);
            $stmtLivre->execute([$id_livre]);

            // Valider les changements
            $pdo->commit();
            $message = "Le retour du livre a bien été enregistré !";
        } catch (Exception $e) {
            $pdo->rollBack();
            $erreur = "Erreur lors de l'enregistrement du retour : " . $e->getMessage();
        }
    } else {
        $erreur = "Informations invalides pour ce retour.";
    }
}

// Récupérer la liste des emprunts en cours (où date_retour est NULL)
$sql = "SELECT e.id_emprunt, e.date_emprunt, e.date_retour_prevue, 
               l.id_livre, l.titre, 
               CONCAT(a.prenom, ' ', a.nom) AS adherent
        FROM EMPRUNT e
        JOIN LIVRE l ON e.id_livre = l.id_livre
        JOIN ADHERENT a ON e.id_adherent = a.id_adherent
        WHERE e.date_retour IS NULL
        ORDER BY e.date_retour_prevue ASC";

$stmt = $pdo->query($sql);
$empruntsEnCours = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Enregistrer un Retour de Livre</h2>

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

<table>
    <thead>
        <tr>
            <th>Livre</th>
            <th>Emprunté par</th>
            <th>Date d'emprunt</th>
            <th>Retour prévu</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($empruntsEnCours) > 0): ?>
            <?php foreach ($empruntsEnCours as $emp): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($emp['titre']); ?></strong></td>
                    <td><?= htmlspecialchars($emp['adherent']); ?></td>
                    <td><?= htmlspecialchars($emp['date_emprunt']); ?></td>
                    <td><?= htmlspecialchars($emp['date_retour_prevue']); ?></td>
                    <td>
                        <form method="POST" action="retour.php" style="margin: 0;">
                            <input type="hidden" name="id_emprunt" value="<?= $emp['id_emprunt']; ?>">
                            <input type="hidden" name="id_livre" value="<?= $emp['id_livre']; ?>">
                            <button type="submit" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.85rem;">Livre rendu</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center;">Aucun livre est à rendre pour le moment.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php 
include 'includes/footer.php'; 
?>