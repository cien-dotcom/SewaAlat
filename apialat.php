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

// GET ALL - menampilkan semua data alat
function normal() {
    global $koneksi;
    $sql = "SELECT * FROM alat_berat ORDER BY id_alat DESC";
    $query = mysqli_query($koneksi, $sql);

    // Tambahkan pengecekan error query
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
            'id_alat' => $baris['id_alat'],
            'nama_alat' => $baris['nama_alat'],
            'jenis' => $baris['jenis'],
            'kapasitas' => $baris['kapasitas'],
            'harga_sewa_per_hari' => $baris['harga_sewa_per_hari'],
            'status' => $baris['status'],
            'deskripsi' => $baris['deskripsi'],
            'foto' => $baris['foto']
        );
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// CREATE - menambah data alat baru
function create() {
    global $koneksi;
    $nama_alat = isset($_POST['nama_alat']) ? mysqli_real_escape_string($koneksi, $_POST['nama_alat']) : '';
    $jenis = isset($_POST['jenis']) ? mysqli_real_escape_string($koneksi, $_POST['jenis']) : '';
    $kapasitas = isset($_POST['kapasitas']) ? mysqli_real_escape_string($koneksi, $_POST['kapasitas']) : '';
    $harga_sewa_per_hari = isset($_POST['harga_sewa_per_hari']) ? $_POST['harga_sewa_per_hari'] : '';
    $status = isset($_POST['status']) ? mysqli_real_escape_string($koneksi, $_POST['status']) : 'Tersedia';
    $deskripsi = isset($_POST['deskripsi']) ? mysqli_real_escape_string($koneksi, $_POST['deskripsi']) : '';
    $foto = isset($_POST['foto']) ? mysqli_real_escape_string($koneksi, $_POST['foto']) : '';
    $res = "Input data gagal.";

    if ($nama_alat && $harga_sewa_per_hari) {
        $sql = "INSERT INTO alat_berat(nama_alat, jenis, kapasitas, harga_sewa_per_hari, status, deskripsi, foto) 
                VALUES('$nama_alat', '$jenis', '$kapasitas', '$harga_sewa_per_hari', '$status', '$deskripsi', '$foto')";
        if (mysqli_query($koneksi, $sql)) {
            $res = "Input data berhasil.";
        } else {
            $res = "Query INSERT gagal: " . mysqli_error($koneksi);
        }
    } else {
        $res = "Nama alat dan harga sewa per hari wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DETAIL - mengambil data alat berdasarkan id
function detail() {
    global $koneksi;
    $id = isset($_GET['id_alat']) ? $_GET['id_alat'] : '';
    $res = array();

    if ($id) {
        $query = mysqli_query($koneksi, "SELECT * FROM alat_berat WHERE id_alat='$id'");
        
        // Tambahkan pengecekan error query
        if (!$query) {
            $error = mysqli_error($koneksi);
            $data['data']['error'] = "Query gagal: " . $error;
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            return;
        }
        
        while ($baris = mysqli_fetch_assoc($query)) {
            $res[] = array(
                'id_alat' => $baris['id_alat'],
                'nama_alat' => $baris['nama_alat'],
                'jenis' => $baris['jenis'],
                'kapasitas' => $baris['kapasitas'],
                'harga_sewa_per_hari' => $baris['harga_sewa_per_hari'],
                'status' => $baris['status'],
                'deskripsi' => $baris['deskripsi'],
                'foto' => $baris['foto']
            );
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// UPDATE - mengupdate data alat
function update() {
    global $koneksi;
    $id = isset($_GET['id_alat']) ? $_GET['id_alat'] : '';
    $nama_alat = isset($_POST['nama_alat']) ? mysqli_real_escape_string($koneksi, $_POST['nama_alat']) : '';
    $jenis = isset($_POST['jenis']) ? mysqli_real_escape_string($koneksi, $_POST['jenis']) : '';
    $kapasitas = isset($_POST['kapasitas']) ? mysqli_real_escape_string($koneksi, $_POST['kapasitas']) : '';
    $harga_sewa_per_hari = isset($_POST['harga_sewa_per_hari']) ? $_POST['harga_sewa_per_hari'] : '';
    $status = isset($_POST['status']) ? mysqli_real_escape_string($koneksi, $_POST['status']) : '';
    $deskripsi = isset($_POST['deskripsi']) ? mysqli_real_escape_string($koneksi, $_POST['deskripsi']) : '';
    $foto = isset($_POST['foto']) ? mysqli_real_escape_string($koneksi, $_POST['foto']) : '';
    $res = "Gagal update data.";

    if ($id && $nama_alat && $harga_sewa_per_hari) {
        $sql = "UPDATE alat_berat SET nama_alat='$nama_alat', jenis='$jenis', kapasitas='$kapasitas', 
                harga_sewa_per_hari='$harga_sewa_per_hari', status='$status', deskripsi='$deskripsi', foto='$foto' 
                WHERE id_alat='$id'";
        if (mysqli_query($koneksi, $sql)) {
            $res = "Data berhasil update.";
        } else {
            $res = "Query UPDATE gagal: " . mysqli_error($koneksi);
        }
    } else {
        $res = "ID, nama alat, dan harga sewa per hari wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DELETE - menghapus data alat
function delete() {
    global $koneksi;
    $id = isset($_GET['id_alat']) ? $_GET['id_alat'] : '';
    $res = "Gagal hapus data.";

    if ($id) {
        $sql = "DELETE FROM alat_berat WHERE id_alat='$id'";
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