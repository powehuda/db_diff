<?php
    require_once "spyc.php";
     $hostname = 'localhost';
     $port = '3306';
     $username = 'root';
     $password = '';
     $db_master = 'icore_lab';
     $db_slave = 'sinar';
     $url_json = 'http://lab.indonesiacore.net/icore/tools/struktur_lab.json';
     $data_master = array();
     $data_slave = array();
     $komparasi = array();
     $_GET['aksi'] == "index";

     function get_http_response_code($url) {
        $headers = get_headers($url);
        return substr($headers[0], 9, 3);
    }
    
    if($_GET['aksi'] == "index"){
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">

        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

        <!-- Optional theme -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">

        <!-- Latest compiled and minified JavaScript -->
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>

        <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
            crossorigin="anonymous"></script>

        <title>Cek DB</title>
    </head>
    <body>
        <div class="col-md-12">
            <h4>DB Check struktur</h4>
            <form action="" method="get">
                <div class="form-group">
                    <input type="hidden" name="aksi" value="komparasi_json">
                    <label for="">Url Program Master</label>
                    <input type="text" name="url_program_master" id="url_program_master" class="form-control">
                </div>
                <div class="form-group">
                    <label for="">Url Program Slave</label>
                    <input type="text" name="url_program_slave" id="" class="form-control">
                </div>
                <div class="form-group">
                    <label for="">URL Masal</label>
                    <input type="text" name="url_yaml" id="url_yaml" class="form-control">
                </div>
                <div class="form-group">
                    <input type="submit" value="Proses" class="btn btn-primary">
                </div>
                <div class="form-group">
                    <a href="#" id="btn_massal" class="btn btn-warning">Proses URL Massal</a>
                </div>
            </form>
        </div>

        <div class="col-md-12" id="kanvas">
            <!-- <div class="panel panel-default">
                <div class="panel-heading">Panel Heading</div>
                <div class="panel-body">Panel Content</div>
            </div> -->
        </div>

        <script>
            var url      = window.location.href;
            var origin   = window.location.origin; 

            $(document).ready(function () {
                $("#btn_massal").on('click',function(){
                    var lokasi_yaml = $("#url_yaml").val();
                    $.getJSON(url, {'aksi':'parse_yaml','url_yaml':lokasi_yaml},
                        function (data, textStatus, jqXHR) {
                            // console.log(data);
                        }
                    ).done(function(data) {
                        if(data.status){
                            $.each(data.data, function (i, a) { 
                                 
                                 
                                var url_program_master = $("#url_program_master").val();
                                var url_program_slave = "http://"+a.host+"/tools/dbdiff/struktur.php?aksi=struktur";

                                $.getJSON(url, {'aksi':'komparasi_json_massal','url_program_master':url_program_master,'url_program_slave':url_program_slave},
                                    function (data_response, textStatus, jqXHR) {
                                        html = "";
                                        html +='<div class="panel panel-default">';
                                            html +='<div class="panel-heading">'+a.host+'</div>';
                                            html +='<div class="panel-body">'+data_response.sql+'</div>';
                                        html +='</div>';
                                        if(data_response.status){
                                            $("#kanvas").append(html);
                                        }
                                    }
                                );
                                // 
                            });
                        }
                    }); 
                });
               
            });
        </script>
    </body>

    </html>
    <?php
    }

    if($_GET['aksi'] == "komparasi"){

        $conn_master = new mysqli($hostname, $username, $password,$db_master);
        $conn_slave = new mysqli($hostname, $username, $password,$db_slave);


        $q_tabel_master = "show tables";
        $result = mysqli_query($conn_master,$q_tabel_master);

        $t_master = 'Tables_in_'.$db_master;
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            $data_master[$row[$t_master]]['tabel'] = $row[$t_master];
            $data_master[$row[$t_master]]['struktur'] = array();
        }
        if(isset($_GET['json'])){
            $content =  file_get_contents($url_json);
            $data_master = json_decode($content,true);
        }
        $q_tabel_slave = "show tables";
        $result_slave = mysqli_query($conn_slave,$q_tabel_slave);

        $t_slave = 'Tables_in_'.$db_slave;

        while ($row = mysqli_fetch_array($result_slave, MYSQLI_ASSOC)) {
            $data_slave[$row[$t_slave]]['tabel'] = $row[$t_slave];
            $data_slave[$row[$t_slave]]['struktur'] = array();

        }

        if(count($data_master) > 0 ){
            foreach ($data_master as $k => $v) {
                $q_struktur = "DESCRIBE ".$k.";";
                $result_master = mysqli_query($conn_master,$q_struktur);

                while ($row = mysqli_fetch_array($result_master, MYSQLI_ASSOC)) {
                    $data_master[$k]['struktur'][$row['Field']] = $row;
                }
            }
        }

        if(count($data_slave) > 0 ){
            foreach ($data_slave as $k => $v) {
                $q_struktur = "DESCRIBE ".$k.";";
                $result_slave = mysqli_query($conn_slave,$q_struktur);

                while ($row = mysqli_fetch_array($result_slave, MYSQLI_ASSOC)) {
                    $data_slave[$k]['struktur'][$row['Field']] = $row;
                }
            }
        }

        // proses komparasi
        if(count($data_master) > 0 && count($data_slave) > 0){
            foreach ($data_master as $k => $v) {
                if(!array_key_exists($k,$data_slave)){
                    $komparasi[$k]['table'] = $data_master[$k];
                }
                if(array_key_exists($k,$data_slave)){
                    foreach ($v['struktur'] as $ks => $vs) {

                        // bila stuktur tidak ditemukan
                        if(!array_key_exists($ks,$data_slave[$k]['struktur'])){
                            $komparasi[$k]['struktur'][$ks] = $vs;
                        }
                        // bila stuktur tidak ditemukan
                        // bila stuktur ditemukan
                        if(array_key_exists($ks,$data_slave[$k]['struktur'])){
                            
                            $data_field_slave = $data_slave[$k]['struktur'][$ks];
                            // cek field
                            /*foreach ($vs as $kf => $vf) {
                                if($vf != $data_field_slave[$kf]){
                                    $komparasi[$k]['struktur'][$ks] = $vs;
                                }
                            }*/
                            // cek field
                        }
                        // bila stuktur ditemukan
                    }
                    
                }
            }
        }
        // proses komparasi

        // echo '<pre>';
        // print_r($data_master);
        // echo '</pre>';
        // echo json_encode($data_master);

        // echo '<pre>';
        // print_r($data_slave);
        // echo '</pre>';

        // echo '<pre>';
        // print_r($komparasi);
        // echo '</pre>';
        // die();

        if(count($komparasi) > 0){
            /*
            CREATE TABLE `admin_groups` (
                `id` INT(10) NOT NULL AUTO_INCREMENT,
                `nama` VARCHAR(50) NULL DEFAULT NULL,
                `keterangan` TEXT NULL,
                `hak_akses` MEDIUMTEXT NULL,
                `delete_status` TINYINT(4) NULL DEFAULT '0',
                `create` DATETIME NULL DEFAULT NULL,
                `update` DATETIME NULL DEFAULT NULL,
                `delete` DATETIME NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            )
            COLLATE='latin1_swedish_ci'
            ENGINE=MyISAM
            ;
            */
            foreach ($komparasi as $k => $v) {
                // tabel
                if(array_key_exists('table',$v)){
                    $primary = "";
                    $gen_q = "CREATE TABLE `".$v['table']['tabel']."` (";
                    foreach ($v['table']['struktur'] as $ks => $vs) {
                        $field_q = "";
                        if($vs['Key'] == 'PRI'){
                            $primary = $vs['Field'];
                        }
                        $field_q .=" `".$vs['Field']."` ".$vs['Type']." ";
                        if($vs['Null'] == "NO"){
                            $field_q .= " NOT NULL ";
                        }
                        if($vs['Null'] == "YES"){
                            $field_q .= " NULL ";
                        }
                        if($vs['Extra'] == "auto_increment"){
                            $field_q .= " AUTO_INCREMENT, ";
                        }
                        if($vs['Extra'] == ""){
                            if($vs['Default'] == ""){
                                $field_q .= " ,";
                            }
                            if($vs['Default'] != ""){
                                $field_q .= " DEFAULT '".$vs['Default']."',";
                            }

                            
                        }
                        $gen_q .= $field_q;
                    }
                    if($primary !=""){
                        $gen_q .= " PRIMARY KEY (`".$primary."`)";
                    }
                    $gen_q .= " )
                    COLLATE='latin1_swedish_ci'
                    ENGINE=MyISAM
                    ;";
                    echo $gen_q."<br><br>";
                    
                }
                // tabel
                // struktur
                /*
                ALTER TABLE `accounts`
                    ADD COLUMN `detima` VARCHAR(50) NULL AFTER `EditedByIP`;

                    ALTER TABLE `plant_do_partners` ADD `Rate` VARCHAR(20) NULL AFTER `IDCurrency`;
                    ALTER TABLE test ADD COLUMN IF NOT EXISTS column_a VARCHAR(255);
                    ALTER TABLE test MODIFY IF EXISTS column_a VARCHAR(255);
                */
                if(array_key_exists('struktur',$v)){
                    $gen_f = "";
                    foreach ($v['struktur'] as $kf => $vf) {
                        $field_q = "ALTER TABLE `".$k."` ADD COLUMN IF NOT EXISTS `".$vf['Field']."` ".$vf['Type']." ";
                        if($vf['Null'] == "YES"){
                            $field_q .=" Null ";
                        }
                        $field_q .=" ;";
                        $field_q .=" ALTER TABLE `".$k."` MODIFY IF EXISTS `".$vf['Field']."` ".$vf['Type']." ";
                        if($vf['Null'] == "YES"){
                            $field_q .=" Null ";
                        }
                        $field_q .=" ;";


                        echo $field_q."<br><br>";
                    }
                }
                // struktur
            }
        }

        ?>

            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <!-- Latest compiled and minified CSS -->
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

                <!-- Optional theme -->
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">

                <!-- Latest compiled and minified JavaScript -->
                <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
                <title>Komparasi</title>

                <style>
                #myBtn {
                display: none; /* Hidden by default */
                position: fixed; /* Fixed/sticky position */
                bottom: 20px; /* Place the button at the bottom of the page */
                right: 30px; /* Place the button 30px from the right */
                z-index: 99; /* Make sure it does not overlap */
                border: none; /* Remove borders */
                outline: none; /* Remove outline */
                background-color: red; /* Set a background color */
                color: white; /* Text color */
                cursor: pointer; /* Add a mouse pointer on hover */
                padding: 15px; /* Some padding */
                border-radius: 10px; /* Rounded corners */
                font-size: 18px; /* Increase font size */
                }

                #myBtn:hover {
                background-color: #555; /* Add a dark-grey background on hover */
                }
                </style>

                <link rel="dns-prefetch" href="//cdn.jsdelivr.net" />
                <link href="https://cdn.jsdelivr.net/npm/prismjs/themes/prism.min.css" rel="stylesheet" />
                <link href="https://cdn.jsdelivr.net/gh/jablonczay/code-box-copy/code-box-copy/css/code-box-copy.min.css" rel="stylesheet" />
                <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/prismjs/prism.min.js"></script>
                <script src="https://cdn.jsdelivr.net/combine/gh/jablonczay/code-box-copy/clipboard/clipboard.min.js,gh/jablonczay/code-box-copy/code-box-copy/js/code-box-copy.min.js"></script>
                <!-- Cod Box Copy end -->
                <script src="https://cdn.jsdelivr.net/combine/npm/prismjs/prism.min.js,npm/prismjs/plugins/normalize-whitespace/prism-normalize-whitespace.min.js"></script>
                
            </head>
            <body>
                <?php
                    foreach ($komparasi as $kdm => $vdm) {
                ?>
                    <div class="col-md-12">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td class="text-center"><?=$kdm?></td>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if(array_key_exists('struktur',$vdm)){
                                        foreach ($vdm['struktur'] as $ksm => $vsm) {
                                    
                                    ?>
                                        <tr>
                                            <td style="margin-left:20px;"> <a href="#<?=$kdm.'_'.$ksm?>"><?=$ksm?></a> </td>
                                        </tr>
                                <?php  
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php
                    }
                ?>
                <br>
                <br>
                <br>
                <?php
                    foreach ($data_master as $kdm => $vdm) {
                        $class = "";
                        if(array_key_exists($kdm,$komparasi)){
                            $class = 'class="bg-danger"';
                        }
                        ?>
                        <div class="col-md-12">
                            <div class="col-md-6">
                                <table class="table table-bordered table-striped">
                                    <thead <?=$class?>>
                                        <tr>
                                            <td class="text-center"><?=$kdm?></td>
                                        
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            foreach ($vdm['struktur'] as $ksm => $vsm) {
                                                $class_field = "";
                                                if(array_key_exists($kdm,$komparasi)){
                                                    if(array_key_exists($ksm,$komparasi[$kdm]['struktur'])){
                                                        $class_field = 'class="bg-danger"';
            
                                                    }
                                                }
                                                
                                            ?>
                                                <tr id="<?=$kdm.'_'.$ksm?>">
                                                    <td style="margin-left:20px;" <?=$class_field?>><?=$ksm?></td>
                                                </tr>
                                                <?php
                                                    foreach ($vsm as $kfm => $vfm) {
                                                        ?>
                                                        <tr>
                                                            <td style="margin-left:70px!important;"> ===> <?=$kfm?> = <?=$vfm?></td>
                                                        </tr>
                                                <?php
                                                    }
                                                ?>
                                                
                                        <?php  
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <?php
                                    if(array_key_exists($kdm,$data_slave)){
                                ?>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <td class="text-center"><?=$kdm?></td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                foreach ($data_slave[$kdm]['struktur'] as $kss => $vss) {
                                                ?>
                                                    <tr>
                                                        <td style="margin-left:20px;"><?=$kss?></td>
                                                    </tr>
                                                    <?php
                                                        foreach ($vss as $kfs => $vfs) {
                                                            ?>
                                                            <tr>
                                                                <td style="margin-left:70px!important;"> ===> <?=$kfs?> = <?=$vfs?></td>
                                                            </tr>
                                                    <?php
                                                        }
                                                    ?>
                                                    
                                            <?php  
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                <?php
                                    }
                                ?>
                            </div>
                        </div>
                        <button onclick="topFunction()" id="myBtn" title="Go to top">Top</button>
                <?php
                    }
                ?>

                <script>
                //Get the button
                var mybutton = document.getElementById("myBtn");

                // When the user scrolls down 20px from the top of the document, show the button
                window.onscroll = function() {scrollFunction()};

                function scrollFunction() {
                if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                    mybutton.style.display = "block";
                } else {
                    mybutton.style.display = "none";
                }
                }

                // When the user clicks on the button, scroll to the top of the document
                function topFunction() {
                document.body.scrollTop = 0;
                document.documentElement.scrollTop = 0;
                }
                </script>

            </body>
            </html>
<?php
    }
    
    if($_GET['aksi'] == "komparasi_json"){

        $content_master =  file_get_contents($_GET['url_program_master']."&key=".md5("norman_redus"));
        $data_master = json_decode($content_master,true);

        $content_slave =  file_get_contents($_GET['url_program_slave']."&key=".md5("norman_redus"));
        $data_slave = json_decode($content_slave,true);
        
        // proses komparasi
        if(count($data_master) > 0 && count($data_slave) > 0){
            foreach ($data_master as $k => $v) {
                if(!array_key_exists($k,$data_slave)){
                    $komparasi[$k]['table'] = $data_master[$k];
                }
                if(array_key_exists($k,$data_slave)){
                    foreach ($v['struktur'] as $ks => $vs) {

                        // bila stuktur tidak ditemukan
                        if(!array_key_exists($ks,$data_slave[$k]['struktur'])){
                            $komparasi[$k]['struktur'][$ks] = $vs;
                        }
                        // bila stuktur tidak ditemukan
                        // bila stuktur ditemukan
                        if(array_key_exists($ks,$data_slave[$k]['struktur'])){
                            
                            $data_field_slave = $data_slave[$k]['struktur'][$ks];
                            // cek field
                            /*foreach ($vs as $kf => $vf) {
                                if($vf != $data_field_slave[$kf]){
                                    $komparasi[$k]['struktur'][$ks] = $vs;
                                }
                            }*/
                            // cek field
                        }
                        // bila stuktur ditemukan
                    }
                    
                }
            }
        }
        // proses komparasi
        if(count($komparasi) > 0){
            foreach ($komparasi as $k => $v) {
                // tabel
                if(array_key_exists('table',$v)){
                    $primary = "";
                    $gen_q = "CREATE TABLE `".$v['table']['tabel']."` (";
                    foreach ($v['table']['struktur'] as $ks => $vs) {
                        $field_q = "";
                        if($vs['Key'] == 'PRI'){
                            $primary = $vs['Field'];
                        }
                        $field_q .=" `".$vs['Field']."` ".$vs['Type']." ";
                        if($vs['Null'] == "NO"){
                            $field_q .= " NOT NULL ";
                        }
                        if($vs['Null'] == "YES"){
                            $field_q .= " NULL ";
                        }
                        if($vs['Extra'] == "auto_increment"){
                            $field_q .= " AUTO_INCREMENT, ";
                        }
                        if($vs['Extra'] == ""){
                            if($vs['Default'] == ""){
                                $field_q .= " ,";
                            }
                            if($vs['Default'] != ""){
                                $field_q .= " DEFAULT '".$vs['Default']."',";
                            }

                            
                        }
                        $gen_q .= $field_q;
                    }
                    if($primary !=""){
                        $gen_q .= " PRIMARY KEY (`".$primary."`)";
                    }
                    $gen_q .= " )
                    COLLATE='latin1_swedish_ci'
                    ENGINE=MyISAM
                    ;";
                    echo $gen_q."<br><br>";
                    
                }
              
                if(array_key_exists('struktur',$v)){
                    $gen_f = "";
                    foreach ($v['struktur'] as $kf => $vf) {
                        $field_q = "ALTER TABLE `".$k."` ADD COLUMN IF NOT EXISTS `".$vf['Field']."` ".$vf['Type']." ";
                        if($vf['Null'] == "YES"){
                            $field_q .=" Null ";
                        }
                        $field_q .=" ;";
                        $field_q .=" ALTER TABLE `".$k."` MODIFY IF EXISTS `".$vf['Field']."` ".$vf['Type']." ";
                        if($vf['Null'] == "YES"){
                            $field_q .=" Null ";
                        }
                        $field_q .=" ;";


                        echo $field_q."<br><br>";
                    }
                }
            }
        }

        ?>

            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta http-equiv="X-UA-Compatible" content="ie=edge">
                <!-- Latest compiled and minified CSS -->
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css" integrity="sha384-HSMxcRTRxnN+Bdg0JdbxYKrThecOKuH5zCYotlSAcp1+c8xmyTe9GYg1l9a69psu" crossorigin="anonymous">

                <!-- Optional theme -->
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap-theme.min.css" integrity="sha384-6pzBo3FDv/PJ8r2KRkGHifhEocL+1X2rVCTTkUfGk7/0pbek5mMa1upzvWbrUbOZ" crossorigin="anonymous">

                <!-- Latest compiled and minified JavaScript -->
                <script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
                <title>Komparasi</title>

                <style>
                #myBtn {
                display: none; /* Hidden by default */
                position: fixed; /* Fixed/sticky position */
                bottom: 20px; /* Place the button at the bottom of the page */
                right: 30px; /* Place the button 30px from the right */
                z-index: 99; /* Make sure it does not overlap */
                border: none; /* Remove borders */
                outline: none; /* Remove outline */
                background-color: red; /* Set a background color */
                color: white; /* Text color */
                cursor: pointer; /* Add a mouse pointer on hover */
                padding: 15px; /* Some padding */
                border-radius: 10px; /* Rounded corners */
                font-size: 18px; /* Increase font size */
                }

                #myBtn:hover {
                background-color: #555; /* Add a dark-grey background on hover */
                }
                </style>

                <link rel="dns-prefetch" href="//cdn.jsdelivr.net" />
                <link href="https://cdn.jsdelivr.net/npm/prismjs/themes/prism.min.css" rel="stylesheet" />
                <link href="https://cdn.jsdelivr.net/gh/jablonczay/code-box-copy/code-box-copy/css/code-box-copy.min.css" rel="stylesheet" />
                <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/prismjs/prism.min.js"></script>
                <script src="https://cdn.jsdelivr.net/combine/gh/jablonczay/code-box-copy/clipboard/clipboard.min.js,gh/jablonczay/code-box-copy/code-box-copy/js/code-box-copy.min.js"></script>
                <!-- Cod Box Copy end -->
                <script src="https://cdn.jsdelivr.net/combine/npm/prismjs/prism.min.js,npm/prismjs/plugins/normalize-whitespace/prism-normalize-whitespace.min.js"></script>
                
            </head>
            <body>
                <?php
                    foreach ($komparasi as $kdm => $vdm) {
                ?>
                    <div class="col-md-12">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <td class="text-center"><?=$kdm?></td>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if(array_key_exists('struktur',$vdm)){
                                        foreach ($vdm['struktur'] as $ksm => $vsm) {
                                    
                                    ?>
                                        <tr>
                                            <td style="margin-left:20px;"> <a href="#<?=$kdm.'_'.$ksm?>"><?=$ksm?></a> </td>
                                        </tr>
                                <?php  
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php
                    }
                ?>
                <br>
                <br>
                <br>
                <?php
                    foreach ($data_master as $kdm => $vdm) {
                        $class = "";
                        if(array_key_exists($kdm,$komparasi)){
                            $class = 'class="bg-danger"';
                        }
                        ?>
                        <div class="col-md-12">
                            <div class="col-md-6">
                                <table class="table table-bordered table-striped">
                                    <thead <?=$class?>>
                                        <tr>
                                            <td class="text-center"><?=$kdm?></td>
                                        
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            foreach ($vdm['struktur'] as $ksm => $vsm) {
                                                $class_field = "";
                                                if(array_key_exists($kdm,$komparasi)){
                                                    if(array_key_exists($ksm,$komparasi[$kdm]['struktur'])){
                                                        $class_field = 'class="bg-danger"';
            
                                                    }
                                                }
                                                
                                            ?>
                                                <tr id="<?=$kdm.'_'.$ksm?>">
                                                    <td style="margin-left:20px;" <?=$class_field?>><?=$ksm?></td>
                                                </tr>
                                                <?php
                                                    foreach ($vsm as $kfm => $vfm) {
                                                        ?>
                                                        <tr>
                                                            <td style="margin-left:70px!important;"> ===> <?=$kfm?> = <?=$vfm?></td>
                                                        </tr>
                                                <?php
                                                    }
                                                ?>
                                                
                                        <?php  
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <?php
                                    if(array_key_exists($kdm,$data_slave)){
                                ?>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <td class="text-center"><?=$kdm?></td>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                                foreach ($data_slave[$kdm]['struktur'] as $kss => $vss) {
                                                ?>
                                                    <tr>
                                                        <td style="margin-left:20px;"><?=$kss?></td>
                                                    </tr>
                                                    <?php
                                                        foreach ($vss as $kfs => $vfs) {
                                                            ?>
                                                            <tr>
                                                                <td style="margin-left:70px!important;"> ===> <?=$kfs?> = <?=$vfs?></td>
                                                            </tr>
                                                    <?php
                                                        }
                                                    ?>
                                                    
                                            <?php  
                                                }
                                            ?>
                                        </tbody>
                                    </table>
                                <?php
                                    }
                                ?>
                            </div>
                        </div>
                        <button onclick="topFunction()" id="myBtn" title="Go to top">Top</button>
                <?php
                    }
                ?>

                <script>
                //Get the button
                var mybutton = document.getElementById("myBtn");

                // When the user scrolls down 20px from the top of the document, show the button
                window.onscroll = function() {scrollFunction()};

                function scrollFunction() {
                if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                    mybutton.style.display = "block";
                } else {
                    mybutton.style.display = "none";
                }
                }

                // When the user clicks on the button, scroll to the top of the document
                function topFunction() {
                document.body.scrollTop = 0;
                document.documentElement.scrollTop = 0;
                }
                </script>

            </body>
            </html>
<?php
    }
    
    if($_GET['aksi'] == "komparasi_json_massal"){

        $content_master =  file_get_contents($_GET['url_program_master']."&key=".md5("norman_redus"));
        $data_master = json_decode($content_master,true);

        if(get_http_response_code($_GET['url_program_slave']."&key=".md5("norman_redus")) != "200"){
            echo json_encode(array("status"=>true,"pesan"=>"url tidak bisa di akses","url_slave"=>$_GET['url_program_slave'],"sql"=>"<h4 class='text-danger'>file tidak bisa di akses</h4>"));
            die();
        }else{
            $content_slave =  file_get_contents($_GET['url_program_slave']."&key=".md5("norman_redus"));
            $data_slave = json_decode($content_slave,true);
        }
        
        // proses komparasi
        if(count($data_master) > 0 && count($data_slave) > 0){
            foreach ($data_master as $k => $v) {
                if(!array_key_exists($k,$data_slave)){
                    $komparasi[$k]['table'] = $data_master[$k];
                }
                if(array_key_exists($k,$data_slave)){
                    foreach ($v['struktur'] as $ks => $vs) {

                        // bila stuktur tidak ditemukan
                        if(!array_key_exists($ks,$data_slave[$k]['struktur'])){
                            $komparasi[$k]['struktur'][$ks] = $vs;
                        }
                        // bila stuktur tidak ditemukan
                    }
                    
                }
            }
        }
        // proses komparasi
        if(count($komparasi) > 0){
            $field_sql = "";
            $table_sql = "";
            foreach ($komparasi as $k => $v) {
                // tabel
                if(array_key_exists('table',$v)){
                    $primary = "";
                    $gen_q = "CREATE TABLE `".$v['table']['tabel']."` (";
                    foreach ($v['table']['struktur'] as $ks => $vs) {
                        $field_q = "";
                        if($vs['Key'] == 'PRI'){
                            $primary = $vs['Field'];
                        }
                        $field_q .=" `".$vs['Field']."` ".$vs['Type']." ";
                        if($vs['Null'] == "NO"){
                            $field_q .= " NOT NULL ";
                        }
                        if($vs['Null'] == "YES"){
                            $field_q .= " NULL ";
                        }
                        if($vs['Extra'] == "auto_increment"){
                            $field_q .= " AUTO_INCREMENT, ";
                        }
                        if($vs['Extra'] == ""){
                            if($vs['Default'] == ""){
                                $field_q .= " ,";
                            }
                            if($vs['Default'] != ""){
                                $field_q .= " DEFAULT '".$vs['Default']."',";
                            }

                            
                        }
                        $gen_q .= $field_q;

                    }
                    if($primary !=""){
                        $gen_q .= " PRIMARY KEY (`".$primary."`)";
                    }
                    $gen_q .= " )
                    COLLATE='latin1_swedish_ci'
                    ENGINE=MyISAM
                    ;";
                    // echo $gen_q."<br><br>";
                    $table_sql .=$gen_q;

                    
                }
              
                if(array_key_exists('struktur',$v)){
                    $gen_f = "";
                    foreach ($v['struktur'] as $kf => $vf) {
                        $field_q = "ALTER TABLE `".$k."` ADD COLUMN `".$vf['Field']."` ".$vf['Type']." ";
                        if($vf['Null'] == "YES"){
                            $field_q .=" Null ";
                        }
                        $field_q .=" ;";
                        
                        $field_sql .= $field_q;
                        // echo $field_q."<br><br>";
                    }
                }
            }

            echo json_encode(array("status"=>true,"pesan"=>"data berbeda","url_slave"=>$_GET['url_program_slave'],"sql"=>$table_sql.$field_sql));
            die();
        }
        echo json_encode(array("status"=>false,"pesan"=>"data sama","url_slave"=>$_GET['url_program_slave'],"sql"=>""));

       
    }
    
    if($_GET['aksi'] == "parse_yaml"){
        $content_yml =  file_get_contents($_GET['url_yaml']);
        $Data = Spyc::YAMLLoad($content_yml);

        echo json_encode(array('status'=>true,'data'=>$Data));
    }
?>

