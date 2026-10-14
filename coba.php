<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

    <?php

// 1
    $namaBarang = "Laptop";
    $harga = 5000000;

    echo "Harga $namaBarang adalah $harga<br>";

// 2
    $nilai=90;
    if($nilai>=75){
        echo "Selamat Anda Lulus<br>";
    }else{
        echo "Maaf, Anda Tidak Lulus<br>";
    }

// 3
    for($i = 1; $i <= 5; $i++){
        echo "belajar php itu menyenangkan!<br>";
    }
// 4
    $hari = array("Senin", "Selasa", "Rabu", "Kamis", "Jumat");
    echo $hari[2];

// 5
    sapaPengunjung("Nadine");

    function sapaPengunjung($nama){
        echo "Halo, $nama! Selamat datang di website kami.<br>";
    }


echo "Diskon : Rp " . 
     number_format($diskon, 0, ',', '.') . "<br>";

echo "<b>Total Bayar : Rp " . 
     number_format($totalBayar, 0, ',', '.') . "</b>";

    ?>

</body>
</html>