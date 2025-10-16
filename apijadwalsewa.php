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

// GET ALL - menampilkan semua data jadwal_sewa
function normal() {
    global $koneksi;
    $sql = "SELECT * FROM jadwal_sewa ORDER BY id_jadwal DESC";
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
            'id_jadwal' => $baris['id_jadwal'],
            'id_sewa' => $baris['id_sewa'],
            'tanggal_mulai' => $baris['tanggal_mulai'],
            'tanggal_selesai' => $baris['tanggal_selesai'],
            'lokasi_pengiriman' => $baris['lokasi_pengiriman'],
            'lokasi_pengembalian' => $baris['lokasi_pengembalian'],
            'status_jadwal' => $baris['status_jadwal'],
            'id_petugas' => $baris['id_petugas']
        );
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// CREATE - menambah data jadwal_sewa baru
function create() {
    global $koneksi;
    $id_sewa = isset($_POST['id_sewa']) ? mysqli_real_escape_string($koneksi, $_POST['id_sewa']) : '';
    $tanggal_mulai = isset($_POST['tanggal_mulai']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']) : '';
    $tanggal_selesai = isset($_POST['tanggal_selesai']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_selesai']) : '';
    $lokasi_pengiriman = isset($_POST['lokasi_pengiriman']) ? mysqli_real_escape_string($koneksi, $_POST['lokasi_pengiriman']) : '';
    $lokasi_pengembalian = isset($_POST['lokasi_pengembalian']) ? mysqli_real_escape_string($koneksi, $_POST['lokasi_pengembalian']) : '';
    $status_jadwal = isset($_POST['status_jadwal']) ? mysqli_real_escape_string($koneksi, $_POST['status_jadwal']) : 'Dijadwalkan';
    $id_petugas = isset($_POST['id_petugas']) ? mysqli_real_escape_string($koneksi, $_POST['id_petugas']) : '';
    $res = "Input data gagal.";

    if ($tanggal_mulai && $tanggal_selesai) {
        $sql = "INSERT INTO jadwal_sewa(id_sewa, tanggal_mulai, tanggal_selesai, lokasi_pengiriman, lokasi_pengembalian, status_jadwal, id_petugas) 
                VALUES('$id_sewa', '$tanggal_mulai', '$tanggal_selesai', '$lokasi_pengiriman', '$lokasi_pengembalian', '$status_jadwal', '$id_petugas')";
        if (mysqli_query($koneksi, $sql)) {
            $res = "Input data berhasil.";
        } else {
            $res = "Query INSERT gagal: " . mysqli_error($koneksi);
        }
    } else {
        $res = "Tanggal mulai dan tanggal selesai wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DETAIL - mengambil data jadwal_sewa berdasarkan id
function detail() {
    global $koneksi;
    $id = isset($_GET['id_jadwal']) ? $_GET['id_jadwal'] : '';
    $res = array();

    if ($id) {
        $query = mysqli_query($koneksi, "SELECT * FROM jadwal_sewa WHERE id_jadwal='$id'");
        
        if (!$query) {
            $error = mysqli_error($koneksi);
            $data['data']['error'] = "Query gagal: " . $error;
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            return;
        }
        
        while ($baris = mysqli_fetch_assoc($query)) {
            $res[] = array(
                'id_jadwal' => $baris['id_jadwal'],
                'id_sewa' => $baris['id_sewa'],
                'tanggal_mulai' => $baris['tanggal_mulai'],
                'tanggal_selesai' => $baris['tanggal_selesai'],
                'lokasi_pengiriman' => $baris['lokasi_pengiriman'],
                'lokasi_pengembalian' => $baris['lokasi_pengembalian'],
                'status_jadwal' => $baris['status_jadwal'],
                'id_petugas' => $baris['id_petugas']
            );
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// UPDATE - mengupdate data jadwal_sewa
function update() {
    global $koneksi;
    $id = isset($_GET['id_jadwal']) ? $_GET['id_jadwal'] : '';
    $id_sewa = isset($_POST['id_sewa']) ? mysqli_real_escape_string($koneksi, $_POST['id_sewa']) : null;
    $tanggal_mulai = isset($_POST['tanggal_mulai']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']) : null;
    $tanggal_selesai = isset($_POST['tanggal_selesai']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_selesai']) : null;
    $lokasi_pengiriman = isset($_POST['lokasi_pengiriman']) ? mysqli_real_escape_string($koneksi, $_POST['lokasi_pengiriman']) : null;
    $lokasi_pengembalian = isset($_POST['lokasi_pengembalian']) ? mysqli_real_escape_string($koneksi, $_POST['lokasi_pengembalian']) : null;
    $status_jadwal = isset($_POST['status_jadwal']) ? mysqli_real_escape_string($koneksi, $_POST['status_jadwal']) : null;
    $id_petugas = isset($_POST['id_petugas']) ? mysqli_real_escape_string($koneksi, $_POST['id_petugas']) : null;
    $res = "Gagal update data.";

    if ($id) {
        $set_sql = "";
        $updates = 0;
        if ($id_sewa !== null) { $set_sql .= "id_sewa='$id_sewa', "; $updates++; }
        if ($tanggal_mulai !== null) { $set_sql .= "tanggal_mulai='$tanggal_mulai', "; $updates++; }
        if ($tanggal_selesai !== null) { $set_sql .= "tanggal_selesai='$tanggal_selesai', "; $updates++; }
        if ($lokasi_pengiriman !== null) { $set_sql .= "lokasi_pengiriman='$lokasi_pengiriman', "; $updates++; }
        if ($lokasi_pengembalian !== null) { $set_sql .= "lokasi_pengembalian='$lokasi_pengembalian', "; $updates++; }
        if ($status_jadwal !== null) { $set_sql .= "status_jadwal='$status_jadwal', "; $updates++; }
        if ($id_petugas !== null) { $set_sql .= "id_petugas='$id_petugas', "; $updates++; }
        if ($updates > 0) {
            $set_sql = rtrim($set_sql, ", ");
            $sql = "UPDATE jadwal_sewa SET $set_sql WHERE id_jadwal='$id'";
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

// DELETE - menghapus data jadwal_sewa
function delete() {
    global $koneksi;
    $id = isset($_GET['id_jadwal']) ? $_GET['id_jadwal'] : '';
    $res = "Gagal hapus data.";

    if ($id) {
        $sql = "DELETE FROM jadwal_sewa WHERE id_jadwal='$id'";
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