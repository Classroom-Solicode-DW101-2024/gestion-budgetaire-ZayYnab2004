<?php
session_start();
if (!isset($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user']['id'];
$pdo = new PDO("mysql:host=localhost;dbname=gestion_budget", "root", "");


$selectedYear = $_GET['year'] ?? null;
$selectedMonth = $_GET['month'] ?? null;


$whereClause = "t.user_id = :userId";
$params = [':userId' => $userId];

if ($selectedYear && $selectedMonth) {
    $whereClause .= " AND YEAR(t.date_transaction) = :year AND MONTH(t.date_transaction) = :month";
    $params[':year'] = $selectedYear;
    $params[':month'] = $selectedMonth;
}

function getTotal($pdo, $userId, $type, $whereClause, $params) {
    $sql = "
        SELECT SUM(t.montant) AS total 
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE $whereClause AND c.type = :type
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($params, [':type' => $type]));
    return $stmt->fetchColumn() ?: 0;
}

function getRecentTransactions($pdo, $whereClause, $params) {
    $sql = "
        SELECT t.*, c.nom AS categorie, c.type 
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE $whereClause
        ORDER BY t.date_transaction DESC
        LIMIT 5
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


$totalRevenu = getTotal($pdo, $userId, 'revenu', $whereClause, $params);
$totalDepense = getTotal($pdo, $userId, 'depense', $whereClause, $params);
$solde = $totalRevenu - $totalDepense;
$recentTransactions = getRecentTransactions($pdo, $whereClause, $params);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Gestion Budget</title>
    <style>
        * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f6fa;
    color: #333;
    padding: 30px;
}

h1 {
    text-align: center;
    font-size: 36px;
    color: #4a90e2;
    margin-bottom: 30px;
}

form {
    text-align: center;
    margin-bottom: 30px;
}

form label {
    margin: 0 10px;
    font-weight: bold;
}

form select, form button {
    padding: 8px 12px;
    font-size: 16px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

form button {
    background-color: #4a90e2;
    color: white;
    cursor: pointer;
    transition: background 0.3s ease;
}

form button:hover {
    background-color: #357abd;
}

.stats-container {
    display: flex;
    justify-content: space-around;
    gap: 20px;
    margin-bottom: 40px;
}

.stats-container > div {
    flex: 1;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.stats-container .revenus {
    background-color: #e0ffe0;
    color: #2e7d32;
}

.stats-container .depenses {
    background-color: #ffe0e0;
    color: #c62828;
}

.stats-container .solde {
    background-color: #e0e0ff;
    color: #283593;
}

h2 {
    font-size: 28px;
    color: #4a90e2;
    margin-top: 40px;
    margin-bottom: 20px;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    background-color: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

table th, table td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: left;
}

table th {
    background-color: #4a90e2;
    color: white;
    font-weight: bold;
}

table tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tr:hover {
    background-color: #eef;
}

.site-footer {
    margin-top: 50px;
    text-align: center;
    color: #999;
    font-size: 14px;
}

    </style>
</head>
<body>
    <h1>Dashboard Financier</h1>

   
    <form method="get" >
        <label>Année:
            <select name="year">
                <option value="">-- Tous --</option>
                <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </label>

        <label>Mois:
            <select name="month">
                <option value="">-- Tous --</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= sprintf("%02d", $m) ?>" <?= $selectedMonth == sprintf("%02d", $m) ? 'selected' : '' ?>>
                        <?= date("F", mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </label>

        <button type="submit">Filtrer</button>
    </form>

    
    <div>
        <div >
            <h3>Total Revenus</h3>
            <p><?= number_format($totalRevenu, 2) ?> €</p>
        </div>
        <div>
            <h3>Total Dépenses</h3>
            <p><?= number_format($totalDepense, 2) ?> €</p>
        </div>
        <div>
            <h3>Solde</h3>
            <p><strong><?= number_format($solde, 2) ?> €</strong></p>
        </div>
    </div>

    <h2>Dernières Transactions</h2>
    <table border="1" cellpadding="8">
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

    <footer class="site-footer">
        <p>&copy; <?= date("Y") ?> MonApp. Tous droits réservés.</p>
    </footer>
</body>
</html>
