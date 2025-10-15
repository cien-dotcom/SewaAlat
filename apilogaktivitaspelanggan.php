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

// GET ALL - menampilkan semua data log_aktivitas_pelanggan
function normal() {
    global $koneksi;
    $sql = "SELECT * FROM log_aktivitas_pelanggan ORDER BY id_log DESC";
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
            'id_log' => $baris['id_log'],
            'id_pelanggan' => $baris['id_pelanggan'],
            'aktivitas' => $baris['aktivitas'],
            'deskripsi' => $baris['deskripsi'],
            'id_referensi' => $baris['id_referensi'],
            'waktu' => $baris['waktu'],
            'ip_address' => $baris['ip_address'],
            'user_agent' => $baris['user_agent']
        );
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// CREATE - menambah data log_aktivitas_pelanggan baru
function create() {
    global $koneksi;
    $id_pelanggan = isset($_POST['id_pelanggan']) ? mysqli_real_escape_string($koneksi, $_POST['id_pelanggan']) : '';
    $aktivitas = isset($_POST['aktivitas']) ? mysqli_real_escape_string($koneksi, $_POST['aktivitas']) : '';
    $deskripsi = isset($_POST['deskripsi']) ? mysqli_real_escape_string($koneksi, $_POST['deskripsi']) : '';
    $id_referensi = isset($_POST['id_referensi']) ? mysqli_real_escape_string($koneksi, $_POST['id_referensi']) : '';
    $ip_address = isset($_POST['ip_address']) ? mysqli_real_escape_string($koneksi, $_POST['ip_address']) : '';
    $user_agent = isset($_POST['user_agent']) ? mysqli_real_escape_string($koneksi, $_POST['user_agent']) : '';
    $res = "Input data gagal.";

    if ($aktivitas) {
        $sql = "INSERT INTO log_aktivitas_pelanggan(id_pelanggan, aktivitas, deskripsi, id_referensi, ip_address, user_agent) 
                VALUES('$id_pelanggan', '$aktivitas', '$deskripsi', '$id_referensi', '$ip_address', '$user_agent')";
        if (mysqli_query($koneksi, $sql)) {
            $res = "Input data berhasil.";
        } else {
            $res = "Query INSERT gagal: " . mysqli_error($koneksi);
        }
    } else {
        $res = "Aktivitas wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DETAIL - mengambil data log_aktivitas_pelanggan berdasarkan id
function detail() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $res = array();

    if ($id) {
        $query = mysqli_query($koneksi, "SELECT * FROM log_aktivitas_pelanggan WHERE id_log='$id'");
        
        if (!$query) {
            $error = mysqli_error($koneksi);
            $data['data']['error'] = "Query gagal: " . $error;
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            return;
        }
        
        while ($baris = mysqli_fetch_assoc($query)) {
            $res[] = array(
                'id_log' => $baris['id_log'],
                'id_pelanggan' => $baris['id_pelanggan'],
                'aktivitas' => $baris['aktivitas'],
                'deskripsi' => $baris['deskripsi'],
                'id_referensi' => $baris['id_referensi'],
                'waktu' => $baris['waktu'],
                'ip_address' => $baris['ip_address'],
                'user_agent' => $baris['user_agent']
            );
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// UPDATE - mengupdate data log_aktivitas_pelanggan
function update() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $id_pelanggan = isset($_POST['id_pelanggan']) ? mysqli_real_escape_string($koneksi, $_POST['id_pelanggan']) : null;
    $aktivitas = isset($_POST['aktivitas']) ? mysqli_real_escape_string($koneksi, $_POST['aktivitas']) : null;
    $deskripsi = isset($_POST['deskripsi']) ? mysqli_real_escape_string($koneksi, $_POST['deskripsi']) : null;
    $id_referensi = isset($_POST['id_referensi']) ? mysqli_real_escape_string($koneksi, $_POST['id_referensi']) : null;
    $ip_address = isset($_POST['ip_address']) ? mysqli_real_escape_string($koneksi, $_POST['ip_address']) : null;
    $user_agent = isset($_POST['user_agent']) ? mysqli_real_escape_string($koneksi, $_POST['user_agent']) : null;
    $res = "Gagal update data.";

    if ($id) {
        $set_sql = "";
        $updates = 0;
        if ($id_pelanggan !== null) { $set_sql .= "id_pelanggan='$id_pelanggan', "; $updates++; }
        if ($aktivitas !== null) { $set_sql .= "aktivitas='$aktivitas', "; $updates++; }
        if ($deskripsi !== null) { $set_sql .= "deskripsi='$deskripsi', "; $updates++; }
        if ($id_referensi !== null) { $set_sql .= "id_referensi='$id_referensi', "; $updates++; }
        if ($ip_address !== null) { $set_sql .= "ip_address='$ip_address', "; $updates++; }
        if ($user_agent !== null) { $set_sql .= "user_agent='$user_agent', "; $updates++; }
        if ($updates > 0) {
            $set_sql = rtrim($set_sql, ", ");
            $sql = "UPDATE log_aktivitas_pelanggan SET $set_sql WHERE id_log='$id'";
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

// DELETE - menghapus data log_aktivitas_pelanggan
function delete() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $res = "Gagal hapus data.";

    if ($id) {
        $sql = "DELETE FROM log_aktivitas_pelanggan WHERE id_log='$id'";
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