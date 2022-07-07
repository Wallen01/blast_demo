<html>

<head>
    <title>
        blast
    </title>
</head>

<body>
    <?php
    $id = time();
    $target_file = "";
    $output_file = "";
    if ($_POST["query_seq"] != "") {
        //將輸入的序列儲存為文件
        $target_file = "./blastinput/$id";
        $output_file = "./blastoutput/$id";
        if (!$fp1 = fopen($target_file, "w")) {
            echo "fail to open blastinput/" . $id . "<br>";
        } else {
            fwrite($fp1, $_POST["query_seq"]);
            fclose($fp1);
            //調整權限
            chmod($target_file, 0777);
        }
    } else if ($_FILES["uploadfile"]["tmp_name"]) {
        //資料夾+ID+檔名.副檔名
        $target_file = "./blastinput/" . $id . basename($_FILES["uploadfile"]["name"]);
        $output_file = "./blastoutput/" . $id . basename($_FILES["uploadfile"]["name"]);
        //副檔名
        $FileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $uploadOk = 1;

        // Check if file already exists
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.<br>";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["uploadfile"]["size"] > 500000) {
            echo "Sorry, your file is too large.<br>";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (
            $FileType != "fasta" && $FileType != "fas" && $FileType != "txt"
        ) {
            echo "Sorry, only FASTA ,FAS & TXT files are allowed.<br>";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.<br>";
            // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["uploadfile"]["tmp_name"], $target_file)) {
                chmod($target_file, 0777);
                echo "The file " . htmlspecialchars(basename($_FILES["uploadfile"]["name"])) . " has been uploaded.<br>";
            } else {
                echo "Sorry, there was an error uploading your file.<br>";
            }
        }
    } else {
        echo "You haven't insert your input.<br>";
        exit();
    }

    echo $target_file . "<br>";
    //check if fasta format
    $query_seq = file_get_contents($target_file);
    $pos;
    //檔案為空
    if ($query_seq == "") {
        echo "Your input is empty.<br>";
    }
    //不含有">"
    else if ($pos = stripos($query_seq, ">") === false) {
        echo "Your query isn't a fasta format.<br>";
    }
    //有兩個query
    else if (stripos($query_seq, ">", $pos + 1) !== false) {
        echo "Please only insert one query.<br>";
    }

    //獲得表單參數
    $species = array();
    $evalue = $_POST["evalue"];
    $blast_type = $_POST["blast_type"];
    $species;
    if (!$_POST["species"]) {

        echo "nothing<br>";
        $link = new mysqli('140.116.56.177', 'onlineDB', 'bidlab711', 'plantpan4') or die("Unable to connect the database."); //連接資料庫
        mysqli_query($link, "SET NAMES 'utf8'"); //設定語系
        mysqli_select_db($link, 'plantpan4');
        $result = mysqli_query($link, "select species_info from species_info order by species_info;");
        while ($name_row = mysqli_fetch_row($result)) {
            array_push($species, str_replace(" ", "_", $name_row[0]));
            echo str_replace(" ", "_", $name_row[0]) . "<br>";
        }
        mysqli_close($link);
    } else {
        $species = $_POST["species"];
    }

    //顯示表單參數
    echo "
    query_seq=<br><textarea>$query_seq</textarea><br>
    evalue=$evalue<br>
    blast_type=$blast_type<br>
    species=$species<br>
    ";
    print_r($species);




    //將物種轉為固定格式
    print_r($species);
    for ($i = 0, $num = count($species); $i < $num; $i++) {

        $species[$i] = str_replace(" ", "_", $species[$i]);
        if ($species[$i] == "Aegilops_tauschii") {
            unset($species[$i]);
        }
    }
    echo "<br>";
    print_r($species);

    //BLAST N OR P
    $query = "";
    if ($blast_type == "n") {
        $query = "/home/C54076275/ncbi-blast-2.13.0+/bin/blastn -db \"" . implode(" ", $species) . "\" -query  $target_file -out $output_file -outfmt '6 qseqid sseqid pident evalue bitscore' -evalue $evalue";
    } else if ($blast_type == "p") {
        $query = "/home/C54076275/ncbi-blast-2.13.0+/bin/blastn -db \"" . implode(" ", $species) . "\" -query  $target_file -out $output_file -outfmt '6 qseqid sseqid pident evalue bitscore' -evalue $evalue";
    }
    echo "<br>$query<br>";
    echo "system<br>";
    system($query, $return_var);
    chmod($output_file, 0777);
    echo "<br>return_var:";
    print_r($return_var);
    echo "<br>";
    if ($return_var == 0) {
        echo "BLAST RESULT<br>";
        echo "<textarea>";
        $blast_result = explode(PHP_EOL, file_get_contents($output_file)); //在文件結尾有換行符號，因此會多出一個空白arr
        foreach ($blast_result as $k => $v) {
            $blast_result[$k] = explode(chr(9), $v);
        }
        print_r($blast_result);
        echo "</textarea><br>";
    } else {
        echo "blast fail :(<br>";
    }

    $result_num = sizeof($blast_result);
    if ($result_num >= 2) {
        $result_num--;
        echo "<table>";
        echo "<tr>";
        echo "<td>Source sequence id</td>";
        echo "<td>Species</td>";
        echo "<td>Target sqquence id</td>";
        echo "<td>Percentage of identical matches</td>";
        echo "<td>Expect value</td>";
        echo "<td>Bit score</td>";
        echo "</tr>";
        for ($i = 0; $i < $result_num; $i++) {
            echo "<tr>";
            foreach ($blast_result[$i] as $k => $v) {

                if ($k == 1) {
                    $blast_result[$i][1] = explode('-', $v, 2);
                    foreach ($blast_result[$i][1] as $x) {
                        echo "<td>";
                        echo $x;
                        echo "</td>";
                    }
                } else {
                    echo "<td>";
                    echo $v;
                    echo "</td>";
                }
            }
            echo "</tr>";
        }
        echo "</table>";
    }
    echo "<textarea>";
    print_r($blast_result);
    echo "</textarea>";
    // system("rm $target_file");
    // system("rm $output_file");
    ?>
</body>

</html>