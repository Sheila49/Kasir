<?php
session_start();

// Daftar produk (stok disimpan di file agar tetap berkurang setelah reset nota)
$stok_file = "stok.json";

// Jika file stok belum ada, buat dengan nilai awal
if (!file_exists($stok_file)) {
    $produk = [
        ["id" => 101, "nama" => "Minyak Goreng", "harga" => 20000, "stok" => 10],
        ["id" => 102, "nama" => "Beras 5kg", "harga" => 60000, "stok" => 5],
        ["id" => 103, "nama" => "Gula Pasir", "harga" => 15000, "stok" => 20],
        ["id" => 104, "nama" => "Teh Celup", "harga" => 12000, "stok" => 30],
        ["id" => 105, "nama" => "Kopi Sachet", "harga" => 5000, "stok" => 50]
    ];
    file_put_contents($stok_file, json_encode($produk));
} else {
    $produk = json_decode(file_get_contents($stok_file), true);
}

// Tambahkan ke keranjang
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["id_produk"], $_POST["jumlah"])) {
    $id_produk = (int)$_POST["id_produk"];
    $jumlah = (int)$_POST["jumlah"];

    foreach ($produk as &$p) {
        if ($p["id"] == $id_produk && $jumlah > 0 && $jumlah <= $p["stok"]) {
            $_SESSION["keranjang"][] = ["id" => $id_produk, "nama" => $p["nama"], "harga" => $p["harga"], "jumlah" => $jumlah];
            $p["stok"] -= $jumlah;
            file_put_contents($stok_file, json_encode($produk)); // Simpan stok terbaru ke file
            break;
        }
    }
}

// Reset keranjang saja, stok tetap berkurang
if (isset($_POST["reset"])) {
    $_SESSION["keranjang"] = [];
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Aplikasi Kasir</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Kasir Minimarket</h1>

    <table>
        <tr><th>ID Produk</th><th>Nama</th><th>Harga</th><th>Stok</th></tr>
        <?php foreach ($produk as $p): ?>
            <tr>
                <td><?= $p["id"] ?></td>
                <td><?= $p["nama"] ?></td>
                <td>Rp<?= number_format($p["harga"]) ?></td>
                <td><?= $p["stok"] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <form method="POST">
        <label>ID Produk:</label>
        <input type="number" name="id_produk" required>
        <label>Jumlah:</label>
        <input type="number" name="jumlah" required>
        <button type="submit">Tambahkan ke Keranjang</button>
    </form>

    <?php if (!empty($_SESSION["keranjang"])): ?>
        <div class="nota">
            <h2>Nota Belanja</h2>
            <?php 
            $total = 0;
            foreach ($_SESSION["keranjang"] as $item):
                $subtotal = $item["jumlah"] * $item["harga"];
                $total += $subtotal;
            ?>
                <?= "{$item["jumlah"]}x {$item["nama"]} - Rp" . number_format($subtotal) . "<br>" ?>
            <?php endforeach; ?>
            <hr>
            <strong>Total: Rp<?= number_format($total) ?></strong>
            <br>
            <div style="text-align: center;">
                <h8>Terimakasih</h8>
                    <br>
                <h8>Kasir : Sheila</h8>
            </div>
        </div>
        <form method="POST">
            <button type="submit" name="reset">Selesai & Reset</button>
        </form>
    <?php endif; ?>
</body>
</html>
