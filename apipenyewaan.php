<?php
// Tampilkan semua error (untuk debug)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Koneksi database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sewa_alat_berat";

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

// GET ALL - menampilkan semua data penyewaan
function normal() {
    global $koneksi;
    $sql = "SELECT * FROM penyewaan ORDER BY id_sewa DESC";
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
            'id_sewa' => $baris['id_sewa'],
            'id_pelanggan' => $baris['id_pelanggan'],
            'id_alat' => $baris['id_alat'],
            'tanggal_sewa' => $baris['tanggal_sewa'],
            'tanggal_kembali' => $baris['tanggal_kembali'],
            'total_harga' => $baris['total_harga'],
            'status_sewa' => $baris['status_sewa']
        );
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// CREATE - menambah data penyewaan baru
function create() {
    global $koneksi;
    $id_pelanggan = isset($_POST['id_pelanggan']) ? mysqli_real_escape_string($koneksi, $_POST['id_pelanggan']) : '';
    $id_alat = isset($_POST['id_alat']) ? mysqli_real_escape_string($koneksi, $_POST['id_alat']) : '';
    $tanggal_sewa = isset($_POST['tanggal_sewa']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_sewa']) : '';
    $tanggal_kembali = isset($_POST['tanggal_kembali']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_kembali']) : '';
    $total_harga = isset($_POST['total_harga']) ? floatval($_POST['total_harga']) : 0;
    $status_sewa = isset($_POST['status_sewa']) ? mysqli_real_escape_string($koneksi, $_POST['status_sewa']) : 'Berjalan';
    $res = "Input data gagal.";

    if ($id_pelanggan && $id_alat && $tanggal_sewa && $total_harga > 0) {
        $sql = "INSERT INTO penyewaan(id_pelanggan, id_alat, tanggal_sewa, tanggal_kembali, total_harga, status_sewa) 
                VALUES('$id_pelanggan', '$id_alat', '$tanggal_sewa', '$tanggal_kembali', $total_harga, '$status_sewa')";
        if (mysqli_query($koneksi, $sql)) {
            $res = "Input data berhasil.";
        } else {
            $res = "Query INSERT gagal: " . mysqli_error($koneksi);
        }
    } else {
        $res = "ID pelanggan, ID alat, tanggal sewa, dan total harga wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DETAIL - mengambil data penyewaan berdasarkan id
function detail() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $res = array();

    if ($id) {
        $query = mysqli_query($koneksi, "SELECT * FROM penyewaan WHERE id_sewa='$id'");
        
        if (!$query) {
            $error = mysqli_error($koneksi);
            $data['data']['error'] = "Query gagal: " . $error;
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            return;
        }
        
        while ($baris = mysqli_fetch_assoc($query)) {
            $res[] = array(
                'id_sewa' => $baris['id_sewa'],
                'id_pelanggan' => $baris['id_pelanggan'],
                'id_alat' => $baris['id_alat'],
                'tanggal_sewa' => $baris['tanggal_sewa'],
                'tanggal_kembali' => $baris['tanggal_kembali'],
                'total_harga' => $baris['total_harga'],
                'status_sewa' => $baris['status_sewa']
            );
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// UPDATE - mengupdate data penyewaan
function update() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $id_pelanggan = isset($_POST['id_pelanggan']) ? mysqli_real_escape_string($koneksi, $_POST['id_pelanggan']) : null;
    $id_alat = isset($_POST['id_alat']) ? mysqli_real_escape_string($koneksi, $_POST['id_alat']) : null;
    $tanggal_sewa = isset($_POST['tanggal_sewa']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_sewa']) : null;
    $tanggal_kembali = isset($_POST['tanggal_kembali']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_kembali']) : null;
    $total_harga = isset($_POST['total_harga']) ? floatval($_POST['total_harga']) : null;
    $status_sewa = isset($_POST['status_sewa']) ? mysqli_real_escape_string($koneksi, $_POST['status_sewa']) : null;
    $res = "Gagal update data.";

    if ($id) {
        $set_sql = "";
        $updates = 0;
        if ($id_pelanggan !== null) { $set_sql .= "id_pelanggan='$id_pelanggan', "; $updates++; }
        if ($id_alat !== null) { $set_sql .= "id_alat='$id_alat', "; $updates++; }
        if ($tanggal_sewa !== null) { $set_sql .= "tanggal_sewa='$tanggal_sewa', "; $updates++; }
        if ($tanggal_kembali !== null) { $set_sql .= "tanggal_kembali='$tanggal_kembali', "; $updates++; }
        if ($total_harga !== null) { $set_sql .= "total_harga=$total_harga, "; $updates++; }
        if ($status_sewa !== null) { $set_sql .= "status_sewa='$status_sewa', "; $updates++; }
        if ($updates > 0) {
            $set_sql = rtrim($set_sql, ", ");
            $sql = "UPDATE penyewaan SET $set_sql WHERE id_sewa='$id'";
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

// DELETE - menghapus data penyewaan
function delete() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $res = "Gagal hapus data.";

    if ($id) {
        $sql = "DELETE FROM penyewaan WHERE id_sewa='$id'";
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