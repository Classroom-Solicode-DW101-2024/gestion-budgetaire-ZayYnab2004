<?php 
session_start();
if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user']['id'];
require 'config.php';
include 'functions.php';

$categories = getCategories($pdo); 
$errors = [];
$success = "";


if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    deleteTransaction($pdo, $id, $userId);
    $success = "Transaction supprimée avec succès.";
}

$editData = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editData = getTransactionById($pdo, $id, $userId);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $nom = $_POST['nom'];
    $montant = $_POST['montant'];
    $description = $_POST['description'];
    $date = $_POST['date_transaction'];

    if (empty($type) || empty($nom) || empty($montant) || empty($date)) {
        $errors[] = "Tous les champs sont requis.";
    } elseif (!is_numeric($montant)) {
        $errors[] = "Le montant doit être un nombre.";
    } else {
        if (isset($_POST['transaction_id'])) {
            
            updateTransaction($pdo, $_POST['transaction_id'], $userId, $nom, $type, $montant, $description, $date);
            $success = "Transaction modifiée avec succès.";
        } else {
           
            addTransaction($pdo, $userId, $nom, $type, $montant, $description, $date);
            $success = "Transaction ajoutée avec succès.";
        }
    }
}

$stmt = $pdo->prepare("SELECT t.*, c.nom AS categorie, c.type 
                       FROM transactions t 
                       JOIN categories c ON t.category_id = c.id 
                       WHERE t.user_id = ? 
                       ORDER BY t.date_transaction DESC");
$stmt->execute([$userId]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Transactions</title>
    <link rel="stylesheet" href="transactions.css">
</head>
<body>
    <h1>Gestion des Transactions Financières</h1>

    <?php foreach ($errors as $e): ?>
        <p style="color:red"><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>
    <?php if ($success): ?>
        <p style="color:green"><?= $success ?></p>
    <?php endif; ?>

    <form method="post">
        <?php if ($editData): ?>
            <input type="hidden" name="transaction_id" value="<?= $editData['id'] ?>">
        <?php endif; ?>

        <label>Type :
            <select name="type">
                <option value="revenu" <?= $editData && $editData['categorie_type'] === 'revenu' ? 'selected' : '' ?>>Revenu</option>
                <option value="depense" <?= $editData && $editData['categorie_type'] === 'depense' ? 'selected' : '' ?>>Dépense</option>
            </select>
        </label><br>

        <label>Catégorie :
            <select name="nom">
                <?php foreach ($categories as $type => $cats): ?>
                    <optgroup label="<?= $type == 'revenu' ? 'Revenus' : 'Dépenses' ?>">
                        <?php foreach ($cats as $cat): ?>
                            <option value="<?= $cat ?>" <?= $editData && $editData['categorie_nom'] === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </label><br>

        <label>Montant :
            <input type="number" step="0.01" name="montant" value="<?= $editData ? $editData['montant'] : '' ?>" required>
        </label><br>

        <label>Description :
            <input type="text" name="description" value="<?= $editData ? htmlspecialchars($editData['description']) : '' ?>">
        </label><br>

        <label>Date :
            <input type="date" name="date_transaction" value="<?= $editData ? $editData['date_transaction'] : '' ?>" required>
        </label><br>

        <button type="submit"><?= $editData ? 'Modifier' : 'Ajouter' ?></button>
        <?php if ($editData): ?>
            <a href="transactions.php">Annuler</a>
        <?php endif; ?>
    </form>

    <h2>Liste des Transactions</h2>
    <table border="1">
        <tr>
            <th>Type</th>
            <th>Catégorie</th>
            <th>Montant</th>
            <th>Description</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($transactions as $t): ?>
            <tr>
                <td><?= $t['type'] == 'revenu' ? 'Revenu' : 'Dépense' ?></td>
                <td><?= htmlspecialchars($t['categorie']) ?></td>
                <td><?= $t['montant'] ?> €</td>
                <td><?= htmlspecialchars($t['description']) ?></td>
                <td><?= $t['date_transaction'] ?></td>
                <td class="table-actions">
                        <a href="?edit=<?= $t['id'] ?>" class="edit-btn">Modifier</a>
                        <a href="?delete=<?= $t['id'] ?>" class="delete-btn" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
</td>

            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>