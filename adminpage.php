<?php

include "headeradmin.php";
include "footer.php";

?>

<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
.button {
 background-color: #008EC1; /* Green */
 border: none;
 color: white;
 padding: 15px 20px;
 text-align: center;
 text-decoration: none;
 display: inline-block;
 font-size: 16px;
 cursor: pointer;
 width: 200px;
 border-radius: 10px; /* Rounded corners */
 margin: 5px;}

.table{
  border-collapse: collapse;
  width: 100%;
}

.table td {
    padding: 10px;
}

.table th{
  padding: 0px;
}

.button {
    width: 200px;  /* Or whatever width you prefer */
    margin: 5px;
}
</style>
</head>

<div class="container h-100">
    <div class="row h-100 align-items-center justify-content-center">
        <!-- Logo -->
        <div class="text-center mb-4">
            <img src="img/kadalogo.jpg" alt="Logo" class="logo">
        </div>

        <br><br>
    <body>
        <div class="container">
            <table class="table table-borderless">
                <tr>
                    <td class="text-center">
                        <a class="button" href="ahlisemasa.php">Ahli Semasa</a>
                    </td>
                    <td class="text-center">
                        <a class="button" href="pinjamandilulus.php">Pinjaman diluluskan</a>
                    </td>
                </tr>
                <tr>
                    <td class="text-center">
                        <a class="button" href="senaraiPermohonanAhli.php">Senarai Permohonan Ahli</a>
                    </td>
                    <td class="text-center">
                        <a class="button" href="senaraiPermohonanPinjaman.php">Senarai Permohonan Pinjaman</a>
                    </td>
                </tr>
                <tr>
                    <td class="text-center" colspan="2">
                        <a class="button" href="#">Hasilkan report</a>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</div>