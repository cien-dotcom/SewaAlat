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

// GET ALL - menampilkan semua data admin
function normal() {
    global $koneksi;
    $sql = "SELECT * FROM admin ORDER BY id_admin DESC";
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
            'id_admin' => $baris['id_admin'],
            'username' => $baris['username'],
            'password' => $baris['password'],
            'nama_admin' => $baris['nama_admin'],
            'level' => $baris['level']
        );
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// CREATE - menambah data admin baru
function create() {
    global $koneksi;
    $username = isset($_POST['username']) ? mysqli_real_escape_string($koneksi, $_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $nama_admin = isset($_POST['nama_admin']) ? mysqli_real_escape_string($koneksi, $_POST['nama_admin']) : '';
    $level = isset($_POST['level']) ? mysqli_real_escape_string($koneksi, $_POST['level']) : 'Operator';
    $res = "Input data gagal.";

    if ($username && $password) {
        // Cek username sudah ada
        $cek = mysqli_query($koneksi, "SELECT * FROM admin WHERE username='$username'");
        if (mysqli_num_rows($cek) > 0) {
            $res = "Username sudah digunakan!";
        } else {
            $sql = "INSERT INTO admin(username, password, nama_admin, level) VALUES('$username', '$password', '$nama_admin', '$level')";
            if (mysqli_query($koneksi, $sql)) {
                $res = "Input data berhasil.";
            } else {
                $res = "Query INSERT gagal: " . mysqli_error($koneksi);
            }
        }
    } else {
        $res = "Username dan password wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DETAIL - mengambil data admin berdasarkan id
function detail() {
    global $koneksi;
    $id = isset($_GET['id_admin']) ? $_GET['id_admin'] : '';
    $res = array();

    if ($id) {
        $query = mysqli_query($koneksi, "SELECT * FROM admin WHERE id_admin='$id'");
        
        if (!$query) {
            $error = mysqli_error($koneksi);
            $data['data']['error'] = "Query gagal: " . $error;
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            return;
        }
        
        while ($baris = mysqli_fetch_assoc($query)) {
            $res[] = array(
                'id_admin' => $baris['id_admin'],
                'username' => $baris['username'],
                'password' => $baris['password'],
                'nama_admin' => $baris['nama_admin'],
                'level' => $baris['level']
            );
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// UPDATE - mengupdate data admin
function update() {
    global $koneksi;
    $id = isset($_GET['id_admin']) ? $_GET['id_admin'] : '';
    $username = isset($_POST['username']) ? mysqli_real_escape_string($koneksi, $_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : null;
    $nama_admin = isset($_POST['nama_admin']) ? mysqli_real_escape_string($koneksi, $_POST['nama_admin']) : '';
    $level = isset($_POST['level']) ? mysqli_real_escape_string($koneksi, $_POST['level']) : '';
    $res = "Gagal update data.";

    if ($id && $username) {
        $set_sql = "username='$username'";
        if ($password) $set_sql .= ", password='$password'";
        if ($nama_admin !== '') $set_sql .= ", nama_admin='$nama_admin'";
        if ($level !== '') $set_sql .= ", level='$level'";
        $sql = "UPDATE admin SET $set_sql WHERE id_admin='$id'";
        if (mysqli_query($koneksi, $sql)) {
            $res = "Data berhasil update.";
        } else {
            $res = "Query UPDATE gagal: " . mysqli_error($koneksi);
        }
    } else {
        $res = "ID dan username wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DELETE - menghapus data admin
function delete() {
    global $koneksi;
    $id = isset($_GET['id_admin']) ? $_GET['id_admin'] : '';
    $res = "Gagal hapus data.";

    if ($id) {
        $sql = "DELETE FROM admin WHERE id_admin='$id'";
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