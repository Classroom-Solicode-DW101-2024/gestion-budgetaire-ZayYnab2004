<?php
session_start();
if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user']['id'];
$pdo = new PDO("mysql:host=localhost;dbname=gestion_budget", "root", "");


function getTotal($pdo, $userId, $type) {
    $sql = "
        SELECT SUM(t.montant) AS total 
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = :userId AND c.type = :type
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':userId' => $userId, ':type' => $type]);
    return $stmt->fetchColumn() ?: 0;
}


function getRecentTransactions($pdo, $userId) {
    $sql = "
        SELECT t.*, c.nom AS categorie, c.type 
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = :userId
        ORDER BY t.date_transaction DESC
        LIMIT 5
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':userId' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$totalRevenu = getTotal($pdo, $userId, 'revenu');
$totalDepense = getTotal($pdo, $userId, 'depense');
$solde = $totalRevenu - $totalDepense;
$recentTransactions = getRecentTransactions($pdo, $userId);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #f0f2f5;
    color: #333;
    padding: 20px;
    max-width: 900px;
    margin: auto;
}

h1 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 30px;
}

p {
    font-size: 18px;
    margin: 10px 0;
}

h2 {
    margin-top: 40px;
    color: #34495e;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #fff;
    box-shadow: 0 0 8px rgba(0,0,0,0.1);
}

table th, table td {
    padding: 10px;
    border: 1px solid #ccc;
    text-align: left;
}

table th {
    background-color: #3498db;
    color: white;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tr:hover {
    background-color: #ecf0f1;
}

    </style>
</head>
<body>
    <h1>Dashboard Financier</h1>

    <p><strong>Total Revenus:</strong> <?= number_format($totalRevenu, 2) ?> €</p>
    <p><strong>Total Dépenses:</strong> <?= number_format($totalDepense, 2) ?> €</p>
    <p><strong>Solde:</strong> <?= number_format($solde, 2) ?> €</p>

    <h2>Dernières Transactions</h2>
    <table border="1" cellpadding="5">
        <tr>
            <th>Type</th>
            <th>Catégorie</th>
            <th>Montant</th>
            <th>Description</th>
            <th>Date</th>
        </tr>
        <?php foreach ($recentTransactions as $t): ?>
        <tr>
            <td><?= $t['type'] == 'revenu' ? 'Revenu' : 'Dépense' ?></td>
            <td><?= htmlspecialchars($t['categorie']) ?></td>
            <td><?= number_format($t['montant'], 2) ?> €</td>
            <td><?= htmlspecialchars($t['description']) ?></td>
            <td><?= $t['date_transaction'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
