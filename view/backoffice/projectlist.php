<?php
include '../../controller/ProjectController.php';
$projectC = new ProjectController();
$list = $projectC->listProjects();

if ($list instanceof PDOStatement) {
    $list = $list->fetchAll(PDO::FETCH_ASSOC);
} elseif ($list instanceof Traversable) {
    $list = iterator_to_array($list);
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $needle = mb_strtolower($search);
    $list = array_filter($list, function ($project) use ($needle) {
        $haystack = mb_strtolower(
            ($project['nom'] ?? '') . ' ' .
            ($project['developpeur'] ?? '') . ' ' .
            ($project['categorie'] ?? '') . ' ' .
            ($project['lieu'] ?? '')
        );
        return strpos($haystack, $needle) !== false;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>GameHub | Projects</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/lineicons.css" />
    <link rel="stylesheet" href="styles.css" />
    <style>
        .hero-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 24px;
            padding: 36px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            background: linear-gradient(130deg, rgba(255,0,199,0.2), rgba(0,255,234,0.12));
            box-shadow: 0 20px 60px rgba(0,0,0,0.45);
        }
        .hero-card h1 {
            font-family: Orbitron, sans-serif;
            font-size: 2.5rem;
            margin: 0;
            background: linear-gradient(90deg, #ff00c7, #00ffea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-card p { color: var(--text-light); max-width: 540px; }
        .btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 26px;
            border-radius: 999px;
            border: 1px solid var(--border);
            color: var(--text);
            text-decoration: none;
            transition: var(--transition-fast);
        }
        .btn-ghost:hover {
            border-color: var(--primary);
            box-shadow: 0 0 20px rgba(255,0,199,0.35);
        }
        .table-wrapper { margin-top: 32px; }
        .project-id {
            font-family: Orbitron, sans-serif;
            font-size: 0.95rem;
            color: var(--cyan);
        }
        .platform-chip {
            display: inline-flex;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(0, 255, 234, 0.12);
            border: 1px solid var(--border-cyan);
            color: var(--cyan);
            font-size: 0.8rem;
            margin: 2px;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .action-buttons button,
        .action-buttons a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: none;
            border-radius: 999px;
            padding: 8px 18px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            color: #fff;
        }
        .btn-outline {
            background: rgba(255, 0, 199, 0.15);
            border: 1px solid rgba(255, 0, 199, 0.6);
            color: #fff;
        }
        .btn-outline:hover {
            border-color: var(--primary);
            color: #fff;
            box-shadow: 0 0 20px rgba(255,0,199,0.4);
        }
        .btn-danger-ghost {
            background: rgba(255, 51, 92, 0.15);
            border: 1px solid rgba(255, 51, 92, 0.5);
            color: #ff6b81;
        }
        .btn-danger-ghost:hover {
            background: rgba(255, 51, 92, 0.3);
            color: #fff;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-light);
        }
        .empty-state h3 {
            font-family: Orbitron, sans-serif;
            margin-bottom: 16px;
        }
        .neon-field {
            background: rgba(5, 0, 20, 0.75);
            border: 1px solid rgba(255, 0, 199, 0.2);
            color: #fff;
            border-radius: var(--radius-sm);
            padding: 10px 16px;
            transition: var(--transition-fast);
        }
        .neon-field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 18px rgba(255, 0, 199, 0.35);
            background: rgba(0, 0, 0, 0.65);
        }
    </style>
</head>
<body class="admin-body">

<header class="admin-header">
    <div class="container">
        <div class="admin-logo">
            <img src="../frontoffice/assests/logo.png" alt="GameHub Logo">
            GameHub Admin
        </div>
        <nav class="admin-nav">
            <a href="admindashboard.php" class="nav-link">Dashboard</a>
            <a href="projectlist.php" class="nav-link active">Projects</a>
            <a href="addProject.php" class="nav-link">Add Project</a>
        </nav>
    </div>
</header>

<main class="admin-main">
    <div class="container">

        <section class="hero-card">
            <div>
                <h1>Catalogue projets</h1>
                <p>Visualisez et maintenez tous les projets enregistr&eacute;s sur GameHub.
                    Filtrez rapidement, ouvrez les fiches ou supprimez les entr&eacute;es obsol&egrave;tes.</p>
            </div>
            <div class="d-flex flex-column gap-2">
                <a href="addProject.php" class="btn-validate text-decoration-none text-center">+ Ajouter un projet</a>
                <a href="admindashboard.php" class="btn-ghost"><i class="lni lni-arrow-left"></i> Retour dashboard</a>
            </div>
        </section>

        <section class="admin-section table-wrapper">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div>
                    <h2>Liste des projets</h2>
                    <p class="section-subtitle"><?= count($list) ?> entr&eacute;es affich&eacute;es<?= $search !== '' ? " &bull; filtre &laquo; " . htmlspecialchars($search) . " &raquo;" : ''; ?></p>
                </div>
                <form class="d-flex gap-2" method="GET" action="">
                    <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" class="form-control neon-field" placeholder="Rechercher un jeu, un studio...">
                    <button type="submit" class="btn-outline">Filtrer</button>
                </form>
            </div>

            <?php if (count($list) === 0): ?>
                <div class="empty-state">
                    <h3>Aucun projet trouv&eacute;</h3>
                    <p>Essayez de retirer le filtre ou ajoutez une nouvelle fiche.</p>
                    <a href="addProject.php" class="btn-validate text-decoration-none mt-3 d-inline-flex align-items-center gap-2">
                        <i class="lni lni-plus"></i> Cr&eacute;er un projet
                    </a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="admin-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom</th>
                            <th>D&eacute;veloppeur</th>
                            <th>Date cr&eacute;ation</th>
                            <th>Cat&eacute;gorie</th>
                            <th>&Acirc;ge</th>
                            <th>Lieu</th>
                            <th>Plateformes</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($list as $project) : ?>
                            <?php $plateformes = json_decode($project['plateformes'], true) ?? []; ?>
                            <tr>
                                <td><span class="project-id">#<?= $project['id']; ?></span></td>
                                <td><?= htmlspecialchars($project['nom']); ?></td>
                                <td><?= htmlspecialchars($project['developpeur']); ?></td>
                                <td><?= htmlspecialchars($project['date_creation']); ?></td>
                                <td><?= htmlspecialchars($project['categorie']); ?></td>
                                <td><?= $project['age_recommande'] ?? "--"; ?></td>
                                <td><?= $project['lieu'] ?? "--"; ?></td>
                                <td>
                                    <?php if (count($plateformes) > 0): ?>
                                        <?php foreach ($plateformes as $platform): ?>
                                            <span class="platform-chip"><?= htmlspecialchars(trim($platform)); ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">--</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons justify-content-end">
                                        <form method="POST" action="updateProject.php">
                                            <input type="hidden" name="id" value="<?= $project['id']; ?>">
                                            <button type="submit" class="btn-outline">
                                                <i class="lni lni-pencil"></i>
                                                <span>Modifier</span>
                                            </button>
                                        </form>
                                        <a href="showproject.php?id=<?= $project['id']; ?>" class="btn-outline" title="Voir">
                                            <i class="lni lni-eye"></i>
                       		                <span>Voir</span>
                                        </a>
                                        <a href="deleteProject.php?id=<?= $project['id']; ?>" class="btn-danger-ghost" onclick="return confirm('Supprimer ce projet ?')">
                                            <i class="lni lni-trash-can"></i>
                                            <span>Supprimer</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<footer class="admin-footer">
    GameHub Admin &bull; Propuls&eacute; par la team XR Labs
</footer>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
