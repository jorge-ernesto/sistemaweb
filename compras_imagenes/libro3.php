
1. upload.php -- for upload file....
<html>
<head>
<title>Daftar File</title>
</head>
<body>
<center>
<p><h3>Daftar File Di Dalam Tabel UPLOAD</h3></p>
<font size=5>
<?php
    $konek = "host=server.amq.org dbname=latihan user=postgres password=postgres";
    $dbh = pg_connect($konek);
    if (!$dbh) {
        echo "Koneksi ke Database PostgreSQL tidak berhasil";
	exit;
    }
    $sql = "SELECT id, name, picoid, adddate FROM pic";
    $hasil = pg_exec($dbh, $sql);
    $row = pg_numrows($hasil);
    for  ($i=0; $i<$row; $i++)
    {
    $data = pg_fetch_row($hasil);
    echo $data[0].'   -   <a href="detail.php?id='.$data[0].'" target="_blank"'.">  $data[1] </a><br>";
    }
 ?>

<br><hr>
<h3>Upload File</h3>
<form id="data" method="post" action="input_file.php"
      enctype="multipart/form-data">
<p><font size=3><b>Pilih File yang ingin di Upload</b></font></p>
<input name="testfile" type="file" size="50" maxlength="100000"><br><br>
<input name="submit" type="submit" value="KIRIM">
</form> </font> </center> </body> </html>


2. input_file.php  

<?php
    $konek = "host=server.amq.org dbname=latihan user=postgres password=postgres";
    $dbh = pg_connect($konek);
    if (!$dbh) {
        echo "Koneksi ke Database PostgreSQL tidak berhasil";
    }
// check file upload

    if ($testfile)
    {
       if (is_uploaded_file ($testfile))
        {
           chmod ($testfile, 0777);

        //query for upload file in to database
        $sql = "INSERT INTO pic (name, picoid) VALUES";
        $sql .= "('$testfile_name', lo_import('$testfile'))";

        $hasil = pg_exec($dbh, $sql);
        if (!$hasil)
        {
          echo "File yang dimaksud tidak berhasil di UPLOAD<br><br><br>";
          exit;
    }
        else
        {
          echo "<h1>File <b>$testfile_name</b> BERHASIL di UPLOAD</h1><br> ";
        }
      }
      else
      {
         echo "Tidak ada File yang telah di UPLOAD";
      }
   }
        pg_close($dbh);
?>


3. detail.php  -- for display the image.

<?php
    $konek = "host=server.amq.org dbname=latihan user=postgres password=postgres";
    $dbh = pg_connect($konek);
    if (!$dbh) {
        echo "Koneksi ke Database PostgreSQL tidak berhasil";
	exit;
    }
    $sql = "SELECT id, name, picoid, adddate FROM pic WHERE id=$id";
    $hasil = pg_exec($dbh, $sql);
    $data = pg_fetch_row($hasil, 0);
    if    (!$data)
    {
	echo "Tidak ditemukan file yang dimaksud";
    }
    else
    {
	Header ("Content-type: image/png");
	pg_exec($dbh, "BEGIN");
	$ofp = pg_loopen($data[2], "r");
	if  (!$ofp)
	{
	    echo "File Tersebut Tidak Dapat Diakses";
	}
	$img = pg_loreadall($ofp);
	print $img;
	pg_loclose($ofp);
	pg_exec($dbh, "END");
	}
  ?>

