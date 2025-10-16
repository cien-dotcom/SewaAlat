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

// GET ALL - menampilkan semua data perawatan_alat
function normal() {
    global $koneksi;
    $sql = "SELECT * FROM perawatan_alat ORDER BY id_perawatan DESC";
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
            'id_perawatan' => $baris['id_perawatan'],
            'id_alat' => $baris['id_alat'],
            'tanggal_perawatan' => $baris['tanggal_perawatan'],
            'keterangan' => $baris['keterangan'],
            'biaya_perawatan' => $baris['biaya_perawatan'],
            'status' => $baris['status']
        );
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// CREATE - menambah data perawatan_alat baru
function create() {
    global $koneksi;
    $id_alat = isset($_POST['id_alat']) ? mysqli_real_escape_string($koneksi, $_POST['id_alat']) : '';
    $tanggal_perawatan = isset($_POST['tanggal_perawatan']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_perawatan']) : '';
    $keterangan = isset($_POST['keterangan']) ? mysqli_real_escape_string($koneksi, $_POST['keterangan']) : '';
    $biaya_perawatan = isset($_POST['biaya_perawatan']) ? floatval($_POST['biaya_perawatan']) : 0;
    $status = isset($_POST['status']) ? mysqli_real_escape_string($koneksi, $_POST['status']) : 'Dijadwalkan';
    $res = "Input data gagal.";

    if ($id_alat && $tanggal_perawatan) {
        $sql = "INSERT INTO perawatan_alat(id_alat, tanggal_perawatan, keterangan, biaya_perawatan, status) 
                VALUES('$id_alat', '$tanggal_perawatan', '$keterangan', $biaya_perawatan, '$status')";
        if (mysqli_query($koneksi, $sql)) {
            $res = "Input data berhasil.";
        } else {
            $res = "Query INSERT gagal: " . mysqli_error($koneksi);
        }
    } else {
        $res = "ID alat dan tanggal perawatan wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DETAIL - mengambil data perawatan_alat berdasarkan id
function detail() {
    global $koneksi;
    $id = isset($_GET['id_perawatan']) ? $_GET['id_perawatan'] : '';
    $res = array();

    if ($id) {
        $query = mysqli_query($koneksi, "SELECT * FROM perawatan_alat WHERE id_perawatan='$id'");
        
        if (!$query) {
            $error = mysqli_error($koneksi);
            $data['data']['error'] = "Query gagal: " . $error;
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            return;
        }
        
        while ($baris = mysqli_fetch_assoc($query)) {
            $res[] = array(
                'id_perawatan' => $baris['id_perawatan'],
                'id_alat' => $baris['id_alat'],
                'tanggal_perawatan' => $baris['tanggal_perawatan'],
                'keterangan' => $baris['keterangan'],
                'biaya_perawatan' => $baris['biaya_perawatan'],
                'status' => $baris['status']
            );
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// UPDATE - mengupdate data perawatan_alat
function update() {
    global $koneksi;
    $id = isset($_GET['id_perawatan']) ? $_GET['id_perawatan'] : '';
    $id_alat = isset($_POST['id_alat']) ? mysqli_real_escape_string($koneksi, $_POST['id_alat']) : null;
    $tanggal_perawatan = isset($_POST['tanggal_perawatan']) ? mysqli_real_escape_string($koneksi, $_POST['tanggal_perawatan']) : null;
    $keterangan = isset($_POST['keterangan']) ? mysqli_real_escape_string($koneksi, $_POST['keterangan']) : null;
    $biaya_perawatan = isset($_POST['biaya_perawatan']) ? floatval($_POST['biaya_perawatan']) : null;
    $status = isset($_POST['status']) ? mysqli_real_escape_string($koneksi, $_POST['status']) : null;
    $res = "Gagal update data.";

    if ($id) {
        $set_sql = "";
        $updates = 0;
        if ($id_alat !== null) { $set_sql .= "id_alat='$id_alat', "; $updates++; }
        if ($tanggal_perawatan !== null) { $set_sql .= "tanggal_perawatan='$tanggal_perawatan', "; $updates++; }
        if ($keterangan !== null) { $set_sql .= "keterangan='$keterangan', "; $updates++; }
        if ($biaya_perawatan !== null) { $set_sql .= "biaya_perawatan=$biaya_perawatan, "; $updates++; }
        if ($status !== null) { $set_sql .= "status='$status', "; $updates++; }
        if ($updates > 0) {
            $set_sql = rtrim($set_sql, ", ");
            $sql = "UPDATE perawatan_alat SET $set_sql WHERE id_perawatan='$id'";
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

// DELETE - menghapus data perawatan_alat
function delete() {
    global $koneksi;
    $id = isset($_GET['id_perawatan']) ? $_GET['id_perawatan'] : '';
    $res = "Gagal hapus data.";

    if ($id) {
        $sql = "DELETE FROM perawatan_alat WHERE id_perawatan='$id'";
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