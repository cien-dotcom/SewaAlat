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

// GET ALL - menampilkan semua data pelanggan
function normal() {
    global $koneksi;
    $sql = "SELECT * FROM pelanggan ORDER BY id_pelanggan DESC";
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
            'id_pelanggan' => $baris['id_pelanggan'],
            'nama_pelanggan' => $baris['nama_pelanggan'],
            'no_ktp' => $baris['no_ktp'],
            'alamat' => $baris['alamat'],
            'no_telp' => $baris['no_telp'],
            'email' => $baris['email']
        );
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// CREATE - menambah data pelanggan baru
function create() {
    global $koneksi;
    $nama_pelanggan = isset($_POST['nama_pelanggan']) ? mysqli_real_escape_string($koneksi, $_POST['nama_pelanggan']) : '';
    $no_ktp = isset($_POST['no_ktp']) ? mysqli_real_escape_string($koneksi, $_POST['no_ktp']) : '';
    $alamat = isset($_POST['alamat']) ? mysqli_real_escape_string($koneksi, $_POST['alamat']) : '';
    $no_telp = isset($_POST['no_telp']) ? mysqli_real_escape_string($koneksi, $_POST['no_telp']) : '';
    $email = isset($_POST['email']) ? mysqli_real_escape_string($koneksi, $_POST['email']) : '';
    $res = "Input data gagal.";

    if ($nama_pelanggan) {
        // Cek duplikat email jika diisi
        if ($email) {
            $cek = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE email='$email'");
            if (mysqli_num_rows($cek) > 0) {
                $res = "Email sudah digunakan!";
                $data['data']['result'] = $res;
                header('Content-Type: application/json');
                echo json_encode($data, JSON_PRETTY_PRINT);
                return;
            }
        }
        // Cek duplikat no_ktp jika diisi
        if ($no_ktp) {
            $cek = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE no_ktp='$no_ktp'");
            if (mysqli_num_rows($cek) > 0) {
                $res = "No KTP sudah digunakan!";
                $data['data']['result'] = $res;
                header('Content-Type: application/json');
                echo json_encode($data, JSON_PRETTY_PRINT);
                return;
            }
        }
        $sql = "INSERT INTO pelanggan(nama_pelanggan, no_ktp, alamat, no_telp, email) 
                VALUES('$nama_pelanggan', '$no_ktp', '$alamat', '$no_telp', '$email')";
        if (mysqli_query($koneksi, $sql)) {
            $res = "Input data berhasil.";
        } else {
            $res = "Query INSERT gagal: " . mysqli_error($koneksi);
        }
    } else {
        $res = "Nama pelanggan wajib diisi!";
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// DETAIL - mengambil data pelanggan berdasarkan id
function detail() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $res = array();

    if ($id) {
        $query = mysqli_query($koneksi, "SELECT * FROM pelanggan WHERE id_pelanggan='$id'");
        
        if (!$query) {
            $error = mysqli_error($koneksi);
            $data['data']['error'] = "Query gagal: " . $error;
            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
            return;
        }
        
        while ($baris = mysqli_fetch_assoc($query)) {
            $res[] = array(
                'id_pelanggan' => $baris['id_pelanggan'],
                'nama_pelanggan' => $baris['nama_pelanggan'],
                'no_ktp' => $baris['no_ktp'],
                'alamat' => $baris['alamat'],
                'no_telp' => $baris['no_telp'],
                'email' => $baris['email']
            );
        }
    }

    $data['data']['result'] = $res;
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

// UPDATE - mengupdate data pelanggan
function update() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $nama_pelanggan = isset($_POST['nama_pelanggan']) ? mysqli_real_escape_string($koneksi, $_POST['nama_pelanggan']) : null;
    $no_ktp = isset($_POST['no_ktp']) ? mysqli_real_escape_string($koneksi, $_POST['no_ktp']) : null;
    $alamat = isset($_POST['alamat']) ? mysqli_real_escape_string($koneksi, $_POST['alamat']) : null;
    $no_telp = isset($_POST['no_telp']) ? mysqli_real_escape_string($koneksi, $_POST['no_telp']) : null;
    $email = isset($_POST['email']) ? mysqli_real_escape_string($koneksi, $_POST['email']) : null;
    $res = "Gagal update data.";

    if ($id) {
        $set_sql = "";
        $updates = 0;
        if ($nama_pelanggan !== null) { $set_sql .= "nama_pelanggan='$nama_pelanggan', "; $updates++; }
        if ($no_ktp !== null) { $set_sql .= "no_ktp='$no_ktp', "; $updates++; }
        if ($alamat !== null) { $set_sql .= "alamat='$alamat', "; $updates++; }
        if ($no_telp !== null) { $set_sql .= "no_telp='$no_telp', "; $updates++; }
        if ($email !== null) { $set_sql .= "email='$email', "; $updates++; }
        if ($updates > 0) {
            $set_sql = rtrim($set_sql, ", ");
            $sql = "UPDATE pelanggan SET $set_sql WHERE id_pelanggan='$id'";
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

// DELETE - menghapus data pelanggan
function delete() {
    global $koneksi;
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $res = "Gagal hapus data.";

    if ($id) {
        $sql = "DELETE FROM pelanggan WHERE id_pelanggan='$id'";
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