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

// GET ALL - menampilkan semua data notifikasi
function normal() {
    global $koneksi;
    $sql = "SELECT * FROM notifikasi ORDER BY id_notifikasi DESC";
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
            'id_notifikasi' => $baris['id_notifikasi'],
            'id_admin' => $baris['id_admin'],
            'judul' => $baris['judul'],
            'pesan' => $baris['pesan'],
            'dibaca' => $baris['dibaca'],
            'created_at' => $baris['created_at']
        );
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// CREATE - menambah data notifikasi baru
function create() {
    global $koneksi;
    $id_admin = isset($_POST['id_admin']) ? mysqli_real_escape_string($koneksi, $_POST['id_admin']) : '';
    $judul = isset($_POST['judul']) ? mysqli_real_escape_string($koneksi, $_POST['judul']) : '';
    $pesan = isset($_POST['pesan']) ? mysqli_real_escape_string($koneksi, $_POST['pesan']) : '';
    $dibaca = isset($_POST['dibaca']) ? mysqli_real_escape_string($koneksi, $_POST['dibaca']) : 'Tidak';
    $res = "Input data gagal.";

    if ($judul && $pesan) {
        $sql = "INSERT INTO notifikasi(id_admin, judul, pesan, dibaca) 
                VALUES('$id_admin', '$judul', '$pesan', '$dibaca')";
        if (mysqli_query($koneksi, $sql)) {
            $res = "Input data berhasil.";
        } else {
            $res = "Query INSERT gagal: " . mysqli_error($koneksi);
        }
    } else {
        $res = "Judul dan pesan wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DETAIL - mengambil data notifikasi berdasarkan id
function detail() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $res = array();

    if ($id) {
        $query = mysqli_query($koneksi, "SELECT * FROM notifikasi WHERE id_notifikasi='$id'");
        
        if (!$query) {
            $error = mysqli_error($koneksi);
            $data['data']['error'] = "Query gagal: " . $error;
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            return;
        }
        
        while ($baris = mysqli_fetch_assoc($query)) {
            $res[] = array(
                'id_notifikasi' => $baris['id_notifikasi'],
                'id_admin' => $baris['id_admin'],
                'judul' => $baris['judul'],
                'pesan' => $baris['pesan'],
                'dibaca' => $baris['dibaca'],
                'created_at' => $baris['created_at']
            );
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// UPDATE - mengupdate data notifikasi
function update() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $id_admin = isset($_POST['id_admin']) ? mysqli_real_escape_string($koneksi, $_POST['id_admin']) : null;
    $judul = isset($_POST['judul']) ? mysqli_real_escape_string($koneksi, $_POST['judul']) : null;
    $pesan = isset($_POST['pesan']) ? mysqli_real_escape_string($koneksi, $_POST['pesan']) : null;
    $dibaca = isset($_POST['dibaca']) ? mysqli_real_escape_string($koneksi, $_POST['dibaca']) : null;
    $res = "Gagal update data.";

    if ($id) {
        $set_sql = "";
        $updates = 0;
        if ($id_admin !== null) { $set_sql .= "id_admin='$id_admin', "; $updates++; }
        if ($judul !== null) { $set_sql .= "judul='$judul', "; $updates++; }
        if ($pesan !== null) { $set_sql .= "pesan='$pesan', "; $updates++; }
        if ($dibaca !== null) { $set_sql .= "dibaca='$dibaca', "; $updates++; }
        if ($updates > 0) {
            $set_sql = rtrim($set_sql, ", ");
            $sql = "UPDATE notifikasi SET $set_sql WHERE id_notifikasi='$id'";
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

// DELETE - menghapus data notifikasi
function delete() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $res = "Gagal hapus data.";

    if ($id) {
        $sql = "DELETE FROM notifikasi WHERE id_notifikasi='$id'";
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