<?php
session_start();

// ==============================
// Date globale și produse
// ==============================
define('ADMIN_PASS', 'admin123');        // În producție, pune în .env!
$produse = [
    1 => [
        'nume'      => 'Icoană Sf. Gheorghe (20×15 cm)',
        'pret'      => 120.00,
        'descriere' => 'Icoană pe lemn, pictată manual.',
        'img'       => 'https://via.placeholder.com/200x150?text=Icoana',
        'stoc'      => 25
    ],
    2 => [
        'nume'      => 'Rugăciuni zilnice',
        'pret'      => 45.00,
        'descriere' => 'Carte de rugăciuni pentru fiecare zi.',
        'img'       => 'https://via.placeholder.com/200x150?text=Carte',
        'stoc'      => 100
    ],
    3 => [
        'nume'      => 'Luminări (10 buc.)',
        'pret'      => 30.00,
        'descriere' => 'Pachet de 10 luminări tradiționale.',
        'img'       => 'https://via.placeholder.com/200x150?text=Luminari',
        'stoc'      => 50
    ],
];

function add_to_cart($id) {
    $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
}
function remove_from_cart($id) {
    if (!empty($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]--;
        if ($_SESSION['cart'][$id] <= 0) {
            unset($_SESSION['cart'][$id]);
        }
    }
}
function cart_total($prod) {
    $sum = 0;
    foreach ($_SESSION['cart'] ?? [] as $id => $cant) {
        $sum += $prod[$id]['pret'] * $cant;
    }
    return number_format($sum, 2);
}

// ==============================
// Rute și acțiuni
// ==============================
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Admin login/logout
if ($path === '/admin') {
    // logout
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: /admin');
        exit;
    }
    // login form
    if (empty($_SESSION['is_admin'])) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['pass'] ?? '') === ADMIN_PASS) {
            $_SESSION['is_admin'] = true;
        } else {
            echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Admin Login</title>
                  <style>body{font-family:sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;}
                         form{background:#faf5eb;padding:2rem;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);}
                         input{width:100%;padding:0.5rem;margin-bottom:1rem;}</style>
                  </head><body>
                  <form method="post">
                    <h2>Admin Login</h2>
                    <input type="password" name="pass" placeholder="Parola" required>
                    <button>Login</button>
                  </form>
                  </body></html>';
            exit;
        }
    }
    // panou admin
    if ($_SESSION['is_admin']) {
        // înregistrează o comandă demo dacă există coș
        if (!empty($_SESSION['cart'])) {
            $_SESSION['orders'][] = [
                'data'  => date('Y-m-d H:i:s'),
                'cart'  => $_SESSION['cart'],
                'total' => cart_total($produse)
            ];
            unset($_SESSION['cart']);
        }
        // afișează comenzi
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Admin Panel</title>
              <style>body{font-family:Lato,sans-serif;padding:2rem;} table{width:100%;border-collapse:collapse;}
                     th,td{border:1px solid #ddd;padding:0.5rem;text-align:left;}
                     th{background:#b38b4d;color:#fff;}
                     a{margin-top:1rem;display:inline-block;}
              </style></head><body>';
        echo '<h1>Panou Administrativ</h1>';
        echo '<a href="?logout=1">Logout</a>';
        echo '<h2>Comenzi plasate:</h2>';
        $ords = $_SESSION['orders'] ?? [];
        if (empty($ords)) {
            echo '<p>Nicio comandă.</p>';
        } else {
            echo '<table><tr><th>#</th><th>Data</th><th>Total (lei)</th><th>Detalii</th></tr>';
            foreach ($ords as $i => $o) {
                echo '<tr>';
                echo '<td>'.($i+1).'</td>';
                echo '<td>'.$o['data'].'</td>';
                echo '<td>'.$o['total'].'</td>';
                echo '<td><pre>'.print_r($o['cart'], true).'</pre></td>';
                echo '</tr>';
            }
            echo '</table>';
        }
        echo '</body></html>';
        exit;
    }
}

// Adaugă / șterge din coș
if (isset($_GET['add'])) {
    add_to_cart((int)$_GET['add']);
    header('Location: /');
    exit;
}
if (isset($_GET['remove'])) {
    remove_from_cart((int)$_GET['remove']);
    header('Location: /');
    exit;
}

// ==============================
// Pagina principală
// ==============================
?>
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Magazin Bisericesc</title>
  <style>
    /* Paletă: bej (#faf5eb), bordo (#5a2e2e), auriu (#b38b4d), albastru închis (#2e5a2e) */
    body{margin:0;font-family:Lato,sans-serif;background:#faf5eb;color:#333;}
    header{background:#5a2e2e;color:#fff;padding:1rem;text-align:center;}
    header h1{font-family:'Playfair Display',serif;margin:0;}
    .container{display:flex;flex-wrap:wrap;padding:1rem;}
    .card{background:#fff;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);margin:1rem;padding:1rem;width:200px;}
    .card img{width:100%;border-radius:4px;}
    .card h2{font-size:1.1rem;margin:0.5rem 0 0.2rem;}
    .card p{margin:0.3rem 0;}
    .btn{display:inline-block;padding:0.5rem 1rem;margin-top:0.5rem;background:#b38b4d;color:#fff;text-decoration:none;border-radius:4px;transition:background 0.3s;}
    .btn:hover{background:#8b6235;}
    .cart{position:fixed;right:1rem;top:5rem;background:#fff;padding:1rem;border-radius:8px;box-shadow:0 2px 4px rgba(0,0,0,0.1);width:200px;}
    footer{text-align:center;padding:1rem;font-size:0.9rem;}
  </style>
</head>
<body>
  <header>
    <h1>Magazin Bisericesc</h1>
    <p><a href="/admin" style="color:#f0e6d6;text-decoration:none;">Panou Admin</a></p>
  </header>
  <div class="container">
    <?php foreach ($produse as $id => $p): ?>
      <div class="card">
        <img src="<?= $p['img'] ?>" alt="<?= htmlspecialchars($p['nume']) ?>">
        <h2><?= htmlspecialchars($p['nume']) ?></h2>
        <p><?= htmlspecialchars($p['descriere']) ?></p>
        <p><strong><?= $p['pret'] ?> lei</strong></p>
        <p>Stoc: <?= $p['stoc'] ?></p>
        <a class="btn" href="?add=<?= $id ?>">Adaugă în coș</a>
      </div>
    <?php endforeach; ?>
  </div>
  <aside class="cart">
    <h3>Coșul tău</h3>
    <?php if (empty($_SESSION['cart'])): ?>
      <p>Coș gol.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($_SESSION['cart'] as $id => $cant): ?>
          <li><?= htmlspecialchars($produse[$id]['nume']) ?> x <?= $cant ?>
            <a href="?remove=<?= $id ?>" style="color:#8b0000;">[șterge]</a>
          </li>
        <?php endforeach; ?>
      </ul>
      <p><strong>Total: <?= cart_total($produse) ?> lei</strong></p>
      <p><a class="btn" href="/admin">Finalizează comanda</a></p>
    <?php endif; ?>
  </aside>
  <footer>&copy; 2025 Magazin Bisericesc</footer>
</body>
</html>

