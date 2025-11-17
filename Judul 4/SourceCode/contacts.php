<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$success_message = '';
if (isset($_SESSION['flash_success'])) {
    $success_message = $_SESSION['flash_success'];
    unset($_SESSION['flash_success']);
}

if (!isset($_SESSION['contacts'])) {
    $_SESSION['contacts'] = [];
}
if (!isset($_SESSION['next_id'])) {
    $_SESSION['next_id'] = 1;
}

$errors = [];
$editing_contact = null;

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    foreach ($_SESSION['contacts'] as $i => $c) {
        if ($c['id'] === $id) {
            unset($_SESSION['contacts'][$i]);
            $_SESSION['contacts'] = array_values($_SESSION['contacts']);
            $_SESSION['flash_success'] = "Kontak berhasil dihapus.";
            break;
        }
    }

    header("Location: contacts.php");
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    foreach ($_SESSION['contacts'] as $c) {
        if ($c['id'] === $id) {
            $editing_contact = $c;
            break;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id        = $_POST['id'] ?? '';
    $nama      = trim($_POST['nama'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telepon   = trim($_POST['telepon'] ?? '');
    $alamat    = trim($_POST['alamat'] ?? '');

    if ($nama === '') {
        $errors[] = "Nama harus diisi.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email tidak valid.";
    }
    if (!preg_match("/^[0-9+\s-]{8,20}$/", $telepon)) {
        $errors[] = "Nomor telepon tidak valid.";
    }

    if (empty($errors)) {
        if ($id !== '') {
            $id = (int)$id;
            foreach ($_SESSION['contacts'] as $i => $c) {
                if ($c['id'] === $id) {
                    $_SESSION['contacts'][$i] = [
                        'id'      => $id,
                        'nama'    => $nama,
                        'email'   => $email,
                        'telepon' => $telepon,
                        'alamat'  => $alamat
                    ];
                }
            }
            $_SESSION['flash_success'] = "Kontak berhasil diperbarui.";
        }
        else {
            $_SESSION['contacts'][] = [
                'id'      => $_SESSION['next_id']++,
                'nama'    => $nama,
                'email'   => $email,
                'telepon' => $telepon,
                'alamat'  => $alamat
            ];
            $_SESSION['flash_success'] = "Kontak berhasil ditambahkan.";
        }

        header("Location: contacts.php");
        exit();
    }

}

$show_form = false;

if (!empty($errors)) {
    $show_form = true;                          
} elseif ($editing_contact !== null) {
    $show_form = true;                            
} elseif (isset($_GET['action']) && $_GET['action'] === 'add') {
    $show_form = true;                            
}

$value_nama    = $editing_contact['nama']    ?? ($_POST['nama']    ?? '');
$value_email   = $editing_contact['email']   ?? ($_POST['email']   ?? '');
$value_telepon = $editing_contact['telepon'] ?? ($_POST['telepon'] ?? '');
$value_alamat  = $editing_contact['alamat']  ?? ($_POST['alamat']  ?? '');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Manajemen Kontak</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="header">
    <h1>Sistem Manajemen Kontak</h1>
</div>

<div class="wrapper">

    <div class="welcome-card">
        <span class="welcome-text-bar">
            Selamat Datang, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong>!
        </span>
        <a href="logout.php" class="logout-btn-bar">Logout</a>
    </div>

    <?php if (!$show_form): ?>
        <a href="contacts.php?action=add#form" class="btn-add">
            ＋ Tambah Kontak Baru
        </a>
    <?php endif; ?>

    <?php if ($show_form): ?>
    <div class="card" id="form">
        <h3><?= $editing_contact ? "Edit Kontak" : "Tambah Kontak Baru"; ?></h3>

        <?php if ($errors): ?>
            <div class="error">
                <?php foreach ($errors as $e): ?>
                    <div><?= htmlspecialchars($e); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <?php if ($editing_contact): ?>
                <input type="hidden" name="id" value="<?= $editing_contact['id']; ?>">
            <?php endif; ?>

            <label>Nama Lengkap *</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($value_nama); ?>" required>

            <label>Email *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($value_email); ?>" required>

            <label>Telepon *</label>
            <input type="text" name="telepon" value="<?= htmlspecialchars($value_telepon); ?>" required>

            <label>Alamat</label>
            <textarea name="alamat"><?= htmlspecialchars($value_alamat); ?></textarea>

            <button type="submit"><?= $editing_contact ? "Simpan Perubahan" : "Simpan"; ?></button>
            <a href="contacts.php" class="btn-second">Batal</a>
        </form>
    </div>
    <?php endif; ?>

    <?php if (!$show_form): ?>
    <div class="card">
        <h3>Daftar Kontak (<?= count($_SESSION['contacts']); ?>)</h3>

        <?php if ($success_message): ?>
            <div class="success" style="margin-bottom: 10px;">
                <?= htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($_SESSION['contacts'])): ?>
            <p class="empty-state">Belum ada kontak. Tambahkan kontak pertama Anda!</p>
        <?php else: ?>

        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Telepon</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($_SESSION['contacts'] as $c): ?>
                <tr>
                    <td><?= $c['id']; ?></td>
                    <td><?= htmlspecialchars($c['nama']); ?></td>
                    <td><?= htmlspecialchars($c['email']); ?></td>
                    <td><?= htmlspecialchars($c['telepon']); ?></td>
                    <td><?= nl2br(htmlspecialchars($c['alamat'])); ?></td>
                    <td class="action">
                        <a class="btn-action"
                           href="contacts.php?action=edit&id=<?= $c['id']; ?>">
                           Edit
                        </a>
                        <span class="separator">|</span>
                        <a class="btn-action delete"
                           href="contacts.php?action=delete&id=<?= $c['id']; ?>"
                           onclick="return confirm('Yakin ingin menghapus kontak ini?');">
                           Hapus
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <?php endif; ?>
    </div>
    <?php endif; ?>

</div>

</body>
</html>
