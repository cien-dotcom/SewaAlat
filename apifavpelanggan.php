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

// GET ALL - menampilkan semua data favorit_pelanggan
function normal() {
    global $koneksi;
    $sql = "SELECT * FROM favorit_pelanggan ORDER BY id_favorit DESC";
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
            'id_favorit' => $baris['id_favorit'],
            'id_pelanggan' => $baris['id_pelanggan'],
            'id_alat' => $baris['id_alat'],
            'created_at' => $baris['created_at']
        );
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// CREATE - menambah data favorit_pelanggan baru
function create() {
    global $koneksi;
    $id_pelanggan = isset($_POST['id_pelanggan']) ? mysqli_real_escape_string($koneksi, $_POST['id_pelanggan']) : '';
    $id_alat = isset($_POST['id_alat']) ? mysqli_real_escape_string($koneksi, $_POST['id_alat']) : '';
    $res = "Input data gagal.";

    if ($id_pelanggan && $id_alat) {
        // Cek duplikat (pelanggan sudah favorit alat ini)
        $cek = mysqli_query($koneksi, "SELECT * FROM favorit_pelanggan WHERE id_pelanggan='$id_pelanggan' AND id_alat='$id_alat'");
        if (mysqli_num_rows($cek) > 0) {
            $res = "Favorit sudah ada!";
        } else {
            $sql = "INSERT INTO favorit_pelanggan(id_pelanggan, id_alat) VALUES('$id_pelanggan', '$id_alat')";
            if (mysqli_query($koneksi, $sql)) {
                $res = "Input data berhasil.";
            } else {
                $res = "Query INSERT gagal: " . mysqli_error($koneksi);
            }
        }
    } else {
        $res = "ID pelanggan dan ID alat wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DETAIL - mengambil data favorit_pelanggan berdasarkan id
function detail() {
    global $koneksi;
    $id = isset($_GET['id_favorit']) ? $_GET['id_favorit'] : '';
    $res = array();

    if ($id) {
        $query = mysqli_query($koneksi, "SELECT * FROM favorit_pelanggan WHERE id_favorit='$id'");

        if (!$query) {
            $error = mysqli_error($koneksi);
            $data['data']['error'] = "Query gagal: " . $error;
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            return;
        }
        
        while ($baris = mysqli_fetch_assoc($query)) {
            $res[] = array(
                'id_favorit' => $baris['id_favorit'],
                'id_pelanggan' => $baris['id_pelanggan'],
                'id_alat' => $baris['id_alat'],
                'created_at' => $baris['created_at']
            );
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// UPDATE - mengupdate data favorit_pelanggan
function update() {
    global $koneksi;
    $id = isset($_GET['id_favorit']) ? $_GET['id_favorit'] : '';
    $id_pelanggan = isset($_POST['id_pelanggan']) ? mysqli_real_escape_string($koneksi, $_POST['id_pelanggan']) : null;
    $id_alat = isset($_POST['id_alat']) ? mysqli_real_escape_string($koneksi, $_POST['id_alat']) : null;
    $res = "Gagal update data.";

    if ($id) {
        $set_sql = "";
        $updates = 0;
        if ($id_pelanggan !== null) {
            $set_sql .= "id_pelanggan='$id_pelanggan', ";
            $updates++;
        }
        if ($id_alat !== null) {
            $set_sql .= "id_alat='$id_alat', ";
            $updates++;
        }
        if ($updates > 0) {
            $set_sql = rtrim($set_sql, ", ");
            $sql = "UPDATE favorit_pelanggan SET $set_sql WHERE id_favorit='$id'";
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

// DELETE - menghapus data favorit_pelanggan
function delete() {
    global $koneksi;
    $id = isset($_GET['id_favorit']) ? $_GET['id_favorit'] : '';
    $res = "Gagal hapus data.";

    if ($id) {
        $sql = "DELETE FROM favorit_pelanggan WHERE id_favorit='$id'";
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