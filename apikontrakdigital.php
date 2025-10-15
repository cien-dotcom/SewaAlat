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

// GET ALL - menampilkan semua data kontrak_digital
function normal() {
    global $koneksi;
    $sql = "SELECT * FROM kontrak_digital ORDER BY id_kontrak DESC";
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
            'id_kontrak' => $baris['id_kontrak'],
            'id_sewa' => $baris['id_sewa'],
            'file_kontrak' => $baris['file_kontrak'],
            'tanggal_tanda_tangan' => $baris['tanggal_tanda_tangan'],
            'status_kontrak' => $baris['status_kontrak']
        );
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// CREATE - menambah data kontrak_digital baru
function create() {
    global $koneksi;
    $id_sewa = isset($_POST['id_sewa']) ? mysqli_real_escape_string($koneksi, $_POST['id_sewa']) : '';
    $file_kontrak = isset($_POST['file_kontrak']) ? mysqli_real_escape_string($koneksi, $_POST['file_kontrak']) : '';
    $tanggal_tanda_tangan = isset($_POST['tanggal_tanda_tangan']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_tanda_tangan']) : '';
    $status_kontrak = isset($_POST['status_kontrak']) ? mysqli_real_escape_string($koneksi, $_POST['status_kontrak']) : 'Draft';
    $res = "Input data gagal.";

    if ($id_sewa && $file_kontrak) {
        $sql = "INSERT INTO kontrak_digital(id_sewa, file_kontrak, tanggal_tanda_tangan, status_kontrak) 
                VALUES('$id_sewa', '$file_kontrak', '$tanggal_tanda_tangan', '$status_kontrak')";
        if (mysqli_query($koneksi, $sql)) {
            $res = "Input data berhasil.";
        } else {
            $res = "Query INSERT gagal: " . mysqli_error($koneksi);
        }
    } else {
        $res = "ID sewa dan file kontrak wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DETAIL - mengambil data kontrak_digital berdasarkan id
function detail() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $res = array();

    if ($id) {
        $query = mysqli_query($koneksi, "SELECT * FROM kontrak_digital WHERE id_kontrak='$id'");
        
        if (!$query) {
            $error = mysqli_error($koneksi);
            $data['data']['error'] = "Query gagal: " . $error;
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            return;
        }
        
        while ($baris = mysqli_fetch_assoc($query)) {
            $res[] = array(
                'id_kontrak' => $baris['id_kontrak'],
                'id_sewa' => $baris['id_sewa'],
                'file_kontrak' => $baris['file_kontrak'],
                'tanggal_tanda_tangan' => $baris['tanggal_tanda_tangan'],
                'status_kontrak' => $baris['status_kontrak']
            );
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// UPDATE - mengupdate data kontrak_digital
function update() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $id_sewa = isset($_POST['id_sewa']) ? mysqli_real_escape_string($koneksi, $_POST['id_sewa']) : null;
    $file_kontrak = isset($_POST['file_kontrak']) ? mysqli_real_escape_string($koneksi, $_POST['file_kontrak']) : null;
    $tanggal_tanda_tangan = isset($_POST['tanggal_tanda_tangan']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_tanda_tangan']) : null;
    $status_kontrak = isset($_POST['status_kontrak']) ? mysqli_real_escape_string($koneksi, $_POST['status_kontrak']) : null;
    $res = "Gagal update data.";

    if ($id) {
        $set_sql = "";
        $updates = 0;
        if ($id_sewa !== null) { $set_sql .= "id_sewa='$id_sewa', "; $updates++; }
        if ($file_kontrak !== null) { $set_sql .= "file_kontrak='$file_kontrak', "; $updates++; }
        if ($tanggal_tanda_tangan !== null) { $set_sql .= "tanggal_tanda_tangan='$tanggal_tanda_tangan', "; $updates++; }
        if ($status_kontrak !== null) { $set_sql .= "status_kontrak='$status_kontrak', "; $updates++; }
        if ($updates > 0) {
            $set_sql = rtrim($set_sql, ", ");
            $sql = "UPDATE kontrak_digital SET $set_sql WHERE id_kontrak='$id'";
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

// DELETE - menghapus data kontrak_digital
function delete() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $res = "Gagal hapus data.";

    if ($id) {
        $sql = "DELETE FROM kontrak_digital WHERE id_kontrak='$id'";
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