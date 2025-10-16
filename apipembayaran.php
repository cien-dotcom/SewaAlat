<?php
// Tampilkan semua error (untuk debug)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "alatberat";

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die(json_encode(array('data' => array('result' => 'Koneksi database gagal'))));
}

// Ambil parameter 'op' dari URL
$op = isset($_GET['op']) ? $_GET['op'] : '';

// Switch operation
switch ($op) {
    case 'create': create(); break;
    case 'detail': detail(); break;
    case 'update': update(); break;
    case 'delete': delete(); break;
    default: normal(); break;
}

// GET ALL - menampilkan semua data pembayaran
function normal() {
    global $koneksi;
    $sql = "SELECT * FROM pembayaran ORDER BY id_pembayaran DESC";
    $query = mysqli_query($koneksi, $sql);

    if (!$query) {
        $error = mysqli_error($koneksi);
        $data['data']['error'] = "Query gagal: " . $error;
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        return;
    }

    $res = array();
    while ($baris = mysqli_fetch_assoc($query)) {
        $res[] = array(
            'id_pembayaran' => $baris['id_pembayaran'],
            'id_sewa' => $baris['id_sewa'],
            'tanggal_bayar' => $baris['tanggal_bayar'],
            'jumlah_bayar' => $baris['jumlah_bayar'],
            'metode' => $baris['metode'],
            'status_pembayaran' => $baris['status_pembayaran']
        );
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// CREATE - menambah data pembayaran baru
function create() {
    global $koneksi;
    $id_sewa = isset($_POST['id_sewa']) ? mysqli_real_escape_string($koneksi, $_POST['id_sewa']) : '';
    $tanggal_bayar = isset($_POST['tanggal_bayar']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_bayar']) : '';
    $jumlah_bayar = isset($_POST['jumlah_bayar']) ? floatval($_POST['jumlah_bayar']) : 0;
    $metode = isset($_POST['metode']) ? mysqli_real_escape_string($koneksi, $_POST['metode']) : '';
    $status_pembayaran = isset($_POST['status_pembayaran']) ? mysqli_real_escape_string($koneksi, $_POST['status_pembayaran']) : 'Belum Lunas';
    $res = "Input data gagal.";

    if ($id_sewa && $tanggal_bayar && $jumlah_bayar > 0) {
        $sql = "INSERT INTO pembayaran(id_sewa, tanggal_bayar, jumlah_bayar, metode, status_pembayaran) 
                VALUES('$id_sewa', '$tanggal_bayar', $jumlah_bayar, '$metode', '$status_pembayaran')";
        if (mysqli_query($koneksi, $sql)) {
            $res = "Input data berhasil.";
        } else {
            $res = "Query INSERT gagal: " . mysqli_error($koneksi);
        }
    } else {
        $res = "ID sewa, tanggal bayar, dan jumlah bayar wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DETAIL - mengambil data pembayaran berdasarkan id
function detail() {
    global $koneksi;
    $id = isset($_GET['id_pembayaran']) ? $_GET['id_pembayaran'] : '';
    $res = array();

    if ($id) {
        $query = mysqli_query($koneksi, "SELECT * FROM pembayaran WHERE id_pembayaran='$id'");
        
        if (!$query) {
            $error = mysqli_error($koneksi);
            $data['data']['error'] = "Query gagal: " . $error;
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            return;
        }
        
        while ($baris = mysqli_fetch_assoc($query)) {
            $res[] = array(
                'id_pembayaran' => $baris['id_pembayaran'],
                'id_sewa' => $baris['id_sewa'],
                'tanggal_bayar' => $baris['tanggal_bayar'],
                'jumlah_bayar' => $baris['jumlah_bayar'],
                'metode' => $baris['metode'],
                'status_pembayaran' => $baris['status_pembayaran']
            );
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// UPDATE - mengupdate data pembayaran
function update() {
    global $koneksi;
    $id = isset($_GET['id_pembayaran']) ? $_GET['id_pembayaran'] : '';
    $id_sewa = isset($_POST['id_sewa']) ? mysqli_real_escape_string($koneksi, $_POST['id_sewa']) : null;
    $tanggal_bayar = isset($_POST['tanggal_bayar']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_bayar']) : null;
    $jumlah_bayar = isset($_POST['jumlah_bayar']) ? floatval($_POST['jumlah_bayar']) : null;
    $metode = isset($_POST['metode']) ? mysqli_real_escape_string($koneksi, $_POST['metode']) : null;
    $status_pembayaran = isset($_POST['status_pembayaran']) ? mysqli_real_escape_string($koneksi, $_POST['status_pembayaran']) : null;
    $res = "Gagal update data.";

    if ($id) {
        $set_sql = "";
        $updates = 0;
        if ($id_sewa !== null) { $set_sql .= "id_sewa='$id_sewa', "; $updates++; }
        if ($tanggal_bayar !== null) { $set_sql .= "tanggal_bayar='$tanggal_bayar', "; $updates++; }
        if ($jumlah_bayar !== null) { $set_sql .= "jumlah_bayar=$jumlah_bayar, "; $updates++; }
        if ($metode !== null) { $set_sql .= "metode='$metode', "; $updates++; }
        if ($status_pembayaran !== null) { $set_sql .= "status_pembayaran='$status_pembayaran', "; $updates++; }
        if ($updates > 0) {
            $set_sql = rtrim($set_sql, ", ");
            $sql = "UPDATE pembayaran SET $set_sql WHERE id_pembayaran='$id'";
            if (mysqli_query($koneksi, $sql)) {
                $res = "Data berhasil update.";
            } else {
                $res = "Query UPDATE gagal: " . mysqli_error($koneksi);
            }
        } else {
            $res = "Tidak ada field yang diupdate!";
        }
    } else {
        $res = "ID wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DELETE - menghapus data pembayaran
function delete() {
    global $koneksi;
    $id = isset($_GET['id_pembayaran']) ? $_GET['id_pembayaran'] : '';
    $res = "Gagal hapus data.";

    if ($id) {
        $sql = "DELETE FROM pembayaran WHERE id_pembayaran='$id'";
        if (mysqli_query($koneksi, $sql)) {
            $res = "Hapus data berhasil.";
        } else {
            $res = "Query DELETE gagal: " . mysqli_error($koneksi);
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}
?>