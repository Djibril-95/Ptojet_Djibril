<?php
require_once 'config/db.php';
include 'includes/header.php';

// Récupération des statistiques globales pour le tableau de bord
$totalLivres = $pdo->query("SELECT COUNT(*) FROM LIVRE")->fetchColumn();
$totalAdherents = $pdo->query("SELECT COUNT(*) FROM ADHERENT")->fetchColumn();
$totalEmprunts = $pdo->query("SELECT COUNT(*) FROM EMPRUNT WHERE date_retour IS NULL")->fetchColumn();

// Calcul en temps réel des retards avec la date du jour
$totalRetards = $pdo->query("SELECT COUNT(*) FROM EMPRUNT WHERE date_retour IS NULL AND date_retour_prevue < CURDATE()")->fetchColumn();
?>

<h2>Bienvenue sur la Médiathèque Numérique</h2>

<p style=" margin-bottom: 25px;">Vous pouvez consultez les livres, ceux qui sont emprunté et suivre leur retour .</p>

<!-- Grille des statistiques principales -->
<div class="card-grid">
    <div class="item-card">
        <div>
            <h3>📚 Total Livres</h3>
            <p style="font-size: 2.2rem; font-weight: 700; color: var(--accent); margin-top: 10px;"><?= $totalLivres; ?></p>
        </div>
        <div style="margin-top: 15px;">
            <span class="badge dispo">Catalogue actif</span>
        </div>
    </div>

    <div class="item-card">
        <div>
            <h3>👤 Total Adhérents</h3>
            <p style="font-size: 2.2rem; font-weight: 700; color: var(--accent); margin-top: 10px;"><?= $totalAdherents; ?></p>
        </div>
        <div style="margin-top: 15px;">
            <span class="badge dispo">Inscrits</span>
        </div>
    </div>

    <div class="item-card">
        <div>
            <h3>🔄 Emprunts en Cours</h3>
            <p style="font-size: 2.2rem; font-weight: 700; color: var(--warning); margin-top: 10px;"><?= $totalEmprunts; ?></p>
        </div>
        <div style="margin-top: 15px;">
            <span class="badge emprunte">Actifs</span>
        </div>
    </div>

    <div class="item-card <?= $totalRetards > 0 ? 'retard' : ''; ?>">
        <div>
            <h3>⚠️ Retards Actuels</h3>
            <p style="font-size: 2.2rem; font-weight: 700; color: var(--danger); margin-top: 10px;"><?= $totalRetards; ?></p>
        </div>
        <div style="margin-top: 15px;">
            <?php if ($totalRetards > 0): ?>
                <span class="badge retard">À traiter d'urgence</span>
            <?php else: ?>
                <span class="badge dispo">Aucun retard</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Section d'accès rapide -->
<h2 style="margin-top: 40px; font-size: 1.4rem;">Actions Rapides</h2>

<div style="display: flex; gap: 15px; flex-wrap: wrap;">
    <a href="gerer_livre.php" class="btn btn-primary">+ Ajouter un livre</a>
    <a href="ajouter_adherent.php" class="btn btn-primary">+ Ajouter un adhérent</a>
    <a href="emprunts.php" class="btn" style="background-color: var(--sidebar-hover); color: white;">Voir les emprunts</a>
</div>

<?php 
include 'includes/footer.php'; 
?>