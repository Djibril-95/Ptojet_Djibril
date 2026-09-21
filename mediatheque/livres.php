<?php
require_once 'config/db.php';
include 'includes/header.php';

// Gestion de la recherche par titre
$recherche = isset($_GET['q']) ? trim($_GET['q']) : '';

// Requête SQL avec les noms de tables au singulier (et sans 'annee')
$sql = "SELECT l.id_livre, l.titre, l.disponible, 
               GROUP_CONCAT(CONCAT(a.prenom, ' ', a.nom) SEPARATOR ', ') AS auteurs,
               c.libelle AS categorie
        FROM livre l
        LEFT JOIN livre_auteur la ON l.id_livre = la.id_livre
        LEFT JOIN auteur a ON la.id_auteur = a.id_auteur
        LEFT JOIN categorie c ON l.id_categorie = c.id_categorie";

if ($recherche !== '') {
    $sql .= " WHERE l.titre LIKE :recherche";
}

$sql .= " GROUP BY l.id_livre, l.titre, l.disponible, c.libelle";

$stmt = $pdo->prepare($sql);

if ($recherche !== '') {
    $stmt->execute(['recherche' => '%' . $recherche . '%']);
} else {
    $stmt->execute();
}

$livres = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
    <h2 style="margin-bottom: 0;">Catalogue des Livres</h2>
    <a href="gerer_livre.php" class="btn btn-primary">+ Ajouter un livre</a>
</div>

<!-- Barre de recherche -->
<form method="GET" action="livres.php" style="margin-bottom: 20px; display: flex; gap: 10px;">
    <input type="text" name="q" placeholder="Rechercher par titre..." value="<?= htmlspecialchars($recherche); ?>" style="padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 4px;">
    <button type="submit" class="btn btn-primary" style="padding: 8px 15px;">Rechercher</button>
    <?php if ($recherche !== ''): ?>
        <a href="livres.php" style="padding: 8px 15px; background: #ccc; color: #333; text-decoration: none; border-radius: 4px; display: inline-block;">Réinitialiser</a>
    <?php endif; ?>
</form>

<table>
    <thead>
        <tr>
            <th>Titre</th>
            <th>Auteur(s)</th>
            <th>Catégorie</th>
            <th>Disponibilité</th>
            <th>Actions</th> <!-- Ajout de l'en-tête pour le bouton -->
        </tr>
    </thead>
    <tbody>
        <?php if (count($livres) > 0): ?>
            <?php foreach ($livres as $livre): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($livre['titre']); ?></strong></td>
                    <td><?= htmlspecialchars($livre['auteurs'] ?? 'Auteur inconnu'); ?></td>
                    <td><?= htmlspecialchars($livre['categorie'] ?? 'Non classé'); ?></td>
                    <td>
                        <?php if ($livre['disponible']): ?>
                            <span style="color: green; font-weight: bold;">Disponible</span>
                        <?php else: ?>
                            <span style="color: red; font-weight: bold;">Emprunté</span>
                        <?php endif; ?>
                    </td>
                    <!-- Le bouton est maintenant bien enfermé dans un <td> -->
                    <td>
                        <a href="gerer_livre.php?id=<?= $livre['id_livre']; ?>" class="btn" style="background-color: var(--sidebar-hover); color: white; padding: 6px 12px; font-size: 0.8rem; text-decoration: none; border-radius: 4px;">
                            Modifier
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center; color: #666;">Aucun livre trouvé.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php 
include 'includes/footer.php'; 
?>