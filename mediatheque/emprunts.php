<?php
require_once 'config/db.php';
include 'includes/header.php';

// Récupération des emprunts en cours
$sql = "SELECT e.id_emprunt, e.date_emprunt, e.date_retour_prevue,
               l.titre, 
               CONCAT(a.prenom, ' ', a.nom) AS adherent
        FROM EMPRUNT e
        JOIN LIVRE l ON e.id_livre = l.id_livre
        JOIN ADHERENT a ON e.id_adherent = a.id_adherent
        WHERE e.date_retour IS NULL
        ORDER BY e.date_retour_prevue ASC";

$stmt = $pdo->query($sql);
$emprunts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$aujourdhui = date('Y-m-d');
?>

<h2>Gestion des Emprunts en Cours</h2>

<table>
    <thead>
        <tr>
            <th>Livre emprunté</th>
            <th>Adhérent</th>
            <th>Date d'emprunt</th>
            <th>Retour prévu</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($emprunts) > 0): ?>
            <?php foreach ($emprunts as $emprunt): ?>
                <?php 
                    // Vérification du retard
                    $enRetard = ($emprunt['date_retour_prevue'] < $aujourdhui);
                ?>
                <tr class="<?= $enRetard ? 'retard' : ''; ?>">
                    <td><strong><?= htmlspecialchars($emprunt['titre']); ?></strong></td>
                    <td><?= htmlspecialchars($emprunt['adherent']); ?></td>
                    <td><?= htmlspecialchars($emprunt['date_emprunt']); ?></td>
                    <td><?= htmlspecialchars($emprunt['date_retour_prevue']); ?></td>
                    <td>
                        <?php if ($enRetard): ?>
                            <!-- Utilisation de la bonne classe .badge et .retard -->
                            <span class="badge retard">EN RETARD</span>
                        <?php else: ?>
                            <span class="badge emprunte">En cours</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center; color: var(--text-muted);">Aucun emprunt en cours actuellement.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php 
include 'includes/footer.php'; 
?>