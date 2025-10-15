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

// GET ALL - menampilkan semua data konfigurasi
function normal() {
    global $koneksi;
    $sql = "SELECT * FROM konfigurasi ORDER BY id_konfigurasi DESC";
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
            'id_konfigurasi' => $baris['id_konfigurasi'],
            'nama_setting' => $baris['nama_setting'],
            'nilai_setting' => $baris['nilai_setting'],
            'keterangan' => $baris['keterangan']
        );
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// CREATE - menambah data konfigurasi baru
function create() {
    global $koneksi;
    $nama_setting = isset($_POST['nama_setting']) ? mysqli_real_escape_string($koneksi, $_POST['nama_setting']) : '';
    $nilai_setting = isset($_POST['nilai_setting']) ? mysqli_real_escape_string($koneksi, $_POST['nilai_setting']) : '';
    $keterangan = isset($_POST['keterangan']) ? mysqli_real_escape_string($koneksi, $_POST['keterangan']) : '';
    $res = "Input data gagal.";

    if ($nama_setting && $nilai_setting) {
        // Cek duplikat nama_setting
        $cek = mysqli_query($koneksi, "SELECT * FROM konfigurasi WHERE nama_setting='$nama_setting'");
        if (mysqli_num_rows($cek) > 0) {
            $res = "Nama setting sudah ada!";
        } else {
            $sql = "INSERT INTO konfigurasi(nama_setting, nilai_setting, keterangan) VALUES('$nama_setting', '$nilai_setting', '$keterangan')";
            if (mysqli_query($koneksi, $sql)) {
                $res = "Input data berhasil.";
            } else {
                $res = "Query INSERT gagal: " . mysqli_error($koneksi);
            }
        }
    } else {
        $res = "Nama setting dan nilai setting wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DETAIL - mengambil data konfigurasi berdasarkan id
function detail() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $res = array();

    if ($id) {
        $query = mysqli_query($koneksi, "SELECT * FROM konfigurasi WHERE id_konfigurasi='$id'");
        
        if (!$query) {
            $error = mysqli_error($koneksi);
            $data['data']['error'] = "Query gagal: " . $error;
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            return;
        }
        
        while ($baris = mysqli_fetch_assoc($query)) {
            $res[] = array(
                'id_konfigurasi' => $baris['id_konfigurasi'],
                'nama_setting' => $baris['nama_setting'],
                'nilai_setting' => $baris['nilai_setting'],
                'keterangan' => $baris['keterangan']
            );
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// UPDATE - mengupdate data konfigurasi
function update() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $nama_setting = isset($_POST['nama_setting']) ? mysqli_real_escape_string($koneksi, $_POST['nama_setting']) : null;
    $nilai_setting = isset($_POST['nilai_setting']) ? mysqli_real_escape_string($koneksi, $_POST['nilai_setting']) : null;
    $keterangan = isset($_POST['keterangan']) ? mysqli_real_escape_string($koneksi, $_POST['keterangan']) : null;
    $res = "Gagal update data.";

    if ($id) {
        $set_sql = "";
        $updates = 0;
        if ($nama_setting !== null) { $set_sql .= "nama_setting='$nama_setting', "; $updates++; }
        if ($nilai_setting !== null) { $set_sql .= "nilai_setting='$nilai_setting', "; $updates++; }
        if ($keterangan !== null) { $set_sql .= "keterangan='$keterangan', "; $updates++; }
        if ($updates > 0) {
            $set_sql = rtrim($set_sql, ", ");
            $sql = "UPDATE konfigurasi SET $set_sql WHERE id_konfigurasi='$id'";
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

// DELETE - menghapus data konfigurasi
function delete() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $res = "Gagal hapus data.";

    if ($id) {
        $sql = "DELETE FROM konfigurasi WHERE id_konfigurasi='$id'";
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