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

// GET ALL - menampilkan semua data sessions_pelanggan
function normal() {
    global $koneksi;
    $sql = "SELECT * FROM sessions_pelanggan ORDER BY id_session DESC";
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
            'id_session' => $baris['id_session'],
            'id_pelanggan' => $baris['id_pelanggan'],
            'token' => $baris['token'],
            'expires_at' => $baris['expires_at'],
            'created_at' => $baris['created_at'],
            'updated_at' => $baris['updated_at'],
            'last_activity' => $baris['last_activity'],
            'device_type' => $baris['device_type'],
            'fcm_token' => $baris['fcm_token']
        );
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// CREATE - menambah data sessions_pelanggan baru
function create() {
    global $koneksi;
    $id_pelanggan = isset($_POST['id_pelanggan']) ? mysqli_real_escape_string($koneksi, $_POST['id_pelanggan']) : '';
    $token = isset($_POST['token']) ? mysqli_real_escape_string($koneksi, $_POST['token']) : '';
    $expires_at = isset($_POST['expires_at']) ? mysqli_real_escape_string($koneksi, $_POST['expires_at']) : '';
    $device_type = isset($_POST['device_type']) ? mysqli_real_escape_string($koneksi, $_POST['device_type']) : 'Mobile';
    $fcm_token = isset($_POST['fcm_token']) ? mysqli_real_escape_string($koneksi, $_POST['fcm_token']) : '';
    $res = "Input data gagal.";

    if ($token && $expires_at) {
        $sql = "INSERT INTO sessions_pelanggan(id_pelanggan, token, expires_at, device_type, fcm_token) 
                VALUES('$id_pelanggan', '$token', '$expires_at', '$device_type', '$fcm_token')";
        if (mysqli_query($koneksi, $sql)) {
            $res = "Input data berhasil.";
        } else {
            $res = "Query INSERT gagal: " . mysqli_error($koneksi);
        }
    } else {
        $res = "Token dan expires_at wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DETAIL - mengambil data sessions_pelanggan berdasarkan id
function detail() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $res = array();

    if ($id) {
        $query = mysqli_query($koneksi, "SELECT * FROM sessions_pelanggan WHERE id_session='$id'");
        
        if (!$query) {
            $error = mysqli_error($koneksi);
            $data['data']['error'] = "Query gagal: " . $error;
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            return;
        }
        
        while ($baris = mysqli_fetch_assoc($query)) {
            $res[] = array(
                'id_session' => $baris['id_session'],
                'id_pelanggan' => $baris['id_pelanggan'],
                'token' => $baris['token'],
                'expires_at' => $baris['expires_at'],
                'created_at' => $baris['created_at'],
                'updated_at' => $baris['updated_at'],
                'last_activity' => $baris['last_activity'],
                'device_type' => $baris['device_type'],
                'fcm_token' => $baris['fcm_token']
            );
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// UPDATE - mengupdate data sessions_pelanggan
function update() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $id_pelanggan = isset($_POST['id_pelanggan']) ? mysqli_real_escape_string($koneksi, $_POST['id_pelanggan']) : null;
    $token = isset($_POST['token']) ? mysqli_real_escape_string($koneksi, $_POST['token']) : null;
    $expires_at = isset($_POST['expires_at']) ? mysqli_real_escape_string($koneksi, $_POST['expires_at']) : null;
    $device_type = isset($_POST['device_type']) ? mysqli_real_escape_string($koneksi, $_POST['device_type']) : null;
    $fcm_token = isset($_POST['fcm_token']) ? mysqli_real_escape_string($koneksi, $_POST['fcm_token']) : null;
    $res = "Gagal update data.";

    if ($id) {
        $set_sql = "";
        $updates = 0;
        if ($id_pelanggan !== null) { $set_sql .= "id_pelanggan='$id_pelanggan', "; $updates++; }
        if ($token !== null) { $set_sql .= "token='$token', "; $updates++; }
        if ($expires_at !== null) { $set_sql .= "expires_at='$expires_at', "; $updates++; }
        if ($device_type !== null) { $set_sql .= "device_type='$device_type', "; $updates++; }
        if ($fcm_token !== null) { $set_sql .= "fcm_token='$fcm_token', "; $updates++; }
        if ($updates > 0) {
            $set_sql = rtrim($set_sql, ", ");
            $sql = "UPDATE sessions_pelanggan SET $set_sql WHERE id_session='$id'";
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

// DELETE - menghapus data sessions_pelanggan
function delete() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $res = "Gagal hapus data.";

    if ($id) {
        $sql = "DELETE FROM sessions_pelanggan WHERE id_session='$id'";
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