<?php
require_once 'config/db.php';
include 'includes/header.php';

// Récupération de tous les adhérents
$sql = "SELECT * FROM ADHERENT ORDER BY nom, prenom";
$stmt = $pdo->query($sql);
$adherents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin-bottom: 0;">Liste des Adhérents</h2>
    <a href="ajouter_adherent.php" class="btn btn-primary">+ Ajouter un adhérent</a>
</div>

<table>
    <thead>
        <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Téléphone</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($adherents) > 0): ?>
            <?php foreach ($adherents as $adherent): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($adherent['nom']); ?></strong></td>
                    <td><?= htmlspecialchars($adherent['prenom']); ?></td>
                    <td><?= htmlspecialchars($adherent['email'] ?? 'Non renseigné'); ?></td>
                    <td><?= htmlspecialchars($adherent['telephone'] ?? 'Non renseigné'); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="4" style="text-align: center;">Aucun adhérent enregistré.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php 
include 'includes/footer.php'; 
?>