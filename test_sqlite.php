<?php
try {
    // Créer une nouvelle base de données SQLite (elle sera créée si elle n'existe pas)
    $pdo = new PDO('sqlite:/tmp/test.db');

    // Créer une table simple
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, name TEXT)");

    // Insérer une ligne
    $pdo->exec("INSERT INTO users (name) VALUES ('John Doe')");

    // Lire la ligne
    $stmt = $pdo->query("SELECT * FROM users");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }

    echo "SQLite fonctionne correctement!";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
