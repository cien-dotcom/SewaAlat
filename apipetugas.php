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

// GET ALL - menampilkan semua data petugas
function normal() {
    global $koneksi;
    $sql = "SELECT * FROM petugas ORDER BY id_petugas DESC";
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
            'id_petugas' => $baris['id_petugas'],
            'nama_petugas' => $baris['nama_petugas'],
            'no_telp' => $baris['no_telp'],
            'role' => $baris['role'],
            'status' => $baris['status']
        );
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// CREATE - menambah data petugas baru
function create() {
    global $koneksi;
    $nama_petugas = isset($_POST['nama_petugas']) ? mysqli_real_escape_string($koneksi, $_POST['nama_petugas']) : '';
    $no_telp = isset($_POST['no_telp']) ? mysqli_real_escape_string($koneksi, $_POST['no_telp']) : '';
    $role = isset($_POST['role']) ? mysqli_real_escape_string($koneksi, $_POST['role']) : 'Kurir';
    $status = isset($_POST['status']) ? mysqli_real_escape_string($koneksi, $_POST['status']) : 'Aktif';
    $res = "Input data gagal.";

    if ($nama_petugas) {
        // Cek duplikat nama_petugas
        $cek = mysqli_query($koneksi, "SELECT * FROM petugas WHERE nama_petugas='$nama_petugas'");
        if (mysqli_num_rows($cek) > 0) {
            $res = "Nama petugas sudah ada!";
        } else {
            $sql = "INSERT INTO petugas(nama_petugas, no_telp, role, status) VALUES('$nama_petugas', '$no_telp', '$role', '$status')";
            if (mysqli_query($koneksi, $sql)) {
                $res = "Input data berhasil.";
            } else {
                $res = "Query INSERT gagal: " . mysqli_error($koneksi);
            }
        }
    } else {
        $res = "Nama petugas wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DETAIL - mengambil data petugas berdasarkan id
function detail() {
    global $koneksi;
    $id = isset($_GET['id_petugas']) ? $_GET['id_petugas'] : '';
    $res = array();

    if ($id) {
        $query = mysqli_query($koneksi, "SELECT * FROM petugas WHERE id_petugas='$id'");
        
        if (!$query) {
            $error = mysqli_error($koneksi);
            $data['data']['error'] = "Query gagal: " . $error;
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            return;
        }
        
        while ($baris = mysqli_fetch_assoc($query)) {
            $res[] = array(
                'id_petugas' => $baris['id_petugas'],
                'nama_petugas' => $baris['nama_petugas'],
                'no_telp' => $baris['no_telp'],
                'role' => $baris['role'],
                'status' => $baris['status']
            );
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// UPDATE - mengupdate data petugas
function update() {
    global $koneksi;
    $id = isset($_GET['id_petugas']) ? $_GET['id_petugas'] : '';
    $nama_petugas = isset($_POST['nama_petugas']) ? mysqli_real_escape_string($koneksi, $_POST['nama_petugas']) : null;
    $no_telp = isset($_POST['no_telp']) ? mysqli_real_escape_string($koneksi, $_POST['no_telp']) : null;
    $role = isset($_POST['role']) ? mysqli_real_escape_string($koneksi, $_POST['role']) : null;
    $status = isset($_POST['status']) ? mysqli_real_escape_string($koneksi, $_POST['status']) : null;
    $res = "Gagal update data.";

    if ($id) {
        $set_sql = "";
        $updates = 0;
        if ($nama_petugas !== null) { $set_sql .= "nama_petugas='$nama_petugas', "; $updates++; }
        if ($no_telp !== null) { $set_sql .= "no_telp='$no_telp', "; $updates++; }
        if ($role !== null) { $set_sql .= "role='$role', "; $updates++; }
        if ($status !== null) { $set_sql .= "status='$status', "; $updates++; }
        if ($updates > 0) {
            $set_sql = rtrim($set_sql, ", ");
            $sql = "UPDATE petugas SET $set_sql WHERE id_petugas='$id'";
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

// DELETE - menghapus data petugas
function delete() {
    global $koneksi;
    $id = isset($_GET['id_petugas']) ? $_GET['id_petugas'] : '';
    $res = "Gagal hapus data.";

    if ($id) {
        $sql = "DELETE FROM petugas WHERE id_petugas='$id'";
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