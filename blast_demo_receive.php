<html>

<head>
    <title>
        blast
    </title>
</head>

<body>
    <?php
    //獲得表單參數

    if (isset($_POST["evalue"])) {
        $evalue = $_POST["evalue"];
    }
    if (isset($_POST["blast_type"])) {
        $blast_type = $_POST["blast_type"];
    }
    if (isset($_POST["species"])) {
        $species = $_POST["species"];
    }

    $id = time();
    $target_file="";
    if ($_POST["query_seq"]!="") {
        //將輸入的序列儲存為文件
        $target_file="./blastinput/$id";
        if (!$fp1 = fopen($target_file, "w")) {
            echo "fail to open blastinput/" . $id . "<br>";
        } else {
            fwrite($fp1, $_POST["query_seq"]);
            fclose($fp1);
            //調整權限
            chmod($target_file, 0777);
        }
    } else if ($_FILES["uploadfile"]["tmp_name"]) {
        //儲存的資料夾
        $target_dir = "./blastinput/";
        //資料夾+ID+檔名.副檔名
        $target_file = $target_dir . $id . basename($_FILES["uploadfile"]["name"]);
        //副檔名
        $FileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $uploadOk = 1;

        // Check if file already exists
        if (file_exists($target_file)) {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Check file size
        if ($_FILES["uploadfile"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (
            $FileType != "fasta" && $FileType != "fas" && $FileType != "txt"
        ) {
            echo "Sorry, only FASTA ,FAS & TXT files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["uploadfile"]["tmp_name"], $target_file)) {
                chmod($target_file, 0777);
                echo "The file " . htmlspecialchars(basename($_FILES["uploadfile"]["name"])) . " has been uploaded.";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }else{
        echo "You haven't insert your input.";
        exit();
    }

    echo $target_file."<br>";
    //check if fasta format
    $query_seq = file_get_contents($target_file);
    $pos;
    //檔案為空
    if ($query_seq == "") {
        echo "Your input is empty.";
    }
    //不含有">"
    else if ($pos = stripos($query_seq, ">") === false) {
        echo "Your query isn't a fasta format.";
    }
    //有兩個query
    else if (stripos($query_seq, ">", $pos + 1) !== false) {
        echo "Please only insert one query.";
    }

    //顯示表單參數
    echo "
    query_seq=<br><textarea>$query_seq</textarea><br>
    evalue=$evalue<br>
    blast_type=$blast_type<br>
    species=$species<br>
    ";



    //將物種轉為固定格式
    print_r($species);
    for ($i = 0, $num = count($species); $i < $num; $i++) {
        $species[$i] = str_replace(" ", "_", $species[$i]);
    }
    echo "<br>";
    print_r($species);

    //BLAST N OR P
    echo "<br>" . $query . "<br>";
    $query = "";
    if ($blast_type == "n") {
        $query = "/home/C54076275/ncbi-blast-2.13.0+/bin/blastn -db " . implode(" ", $species) . " -query  $target_file -out ./blastoutput/" . $id . " -outfmt '6 qseqid sseqid pident evalue bitscore' -evalue $evalue";
    } else if ($blast_type == "p") {
        $query = "/home/C54076275/ncbi-blast-2.13.0+/bin/blastn -db " . implode(" ", $species) . " -query  ./blastinput/" . $id . " -out ./blastoutput/" . $id . " -outfmt '6 qseqid sseqid pident evalue bitscore' -evalue $evalue";
    }

    echo "<br>$query<br>";
    echo "<br>system";
    system($query, $return_var);
    echo "<br>return_var:";
    print_r($return_var);

    if ($return_var == 0) {
        echo "BLAST RESULT<br>";
        echo "<textarea>";
        $blast_result = explode(PHP_EOL, file_get_contents("./blastoutput/$id")); //在文件結尾有換行符號，因此會多出一個空白arr
        foreach ($blast_result as $k => $v) {
            $blast_result[$k] = explode(chr(9), $v);
        }
        print_r($blast_result);
        echo "</textarea><br>";
        echo sizeof($blast_result);
        echo "<br>";
        echo sizeof($blast_result, 1);
        echo "<br>";
    } else {
        echo "blast fail :(";
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
                    $blast_result[$i][1] = explode('-', $v);
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


    // $link = new mysqli('140.116.56.177', 'onlineDB', 'bidlab711', 'plantpan4') or die("Unable to connect the database."); //連接資料庫
    ?>
</body>

</html>