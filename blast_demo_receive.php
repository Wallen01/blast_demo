<!DOCTYPE html>
<html>

<head>
    <title>
        Blast
    </title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>PlantPAN 3.0</title>

    <!-- css -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="css/style.css" rel="stylesheet">
    <link href="color/default.css" rel="stylesheet">
    <link href="css/magicsuggest.css" rel="stylesheet">
</head>

<body>
    <?php
    $id = time();
    $target_file = "";
    $output_file = "";
    $query_or_file = true;
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
        $query_or_file = false;
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
    if (!$_POST["species"]) {
        $link = new mysqli('140.116.56.177', 'onlineDB', 'bidlab711', 'plantpan4') or die("Unable to connect the database."); //連接資料庫
        mysqli_query($link, "SET NAMES 'utf8'"); //設定語系
        $result = mysqli_query($link, "select species_info from species_info order by species_info;");
        while ($name_row = mysqli_fetch_row($result)) {
            array_push($species, str_replace(" ", "_", $name_row[0]));
        }
    } else {
        $species = $_POST["species"];
    }

    //顯示表單參數
    //The Seq
    if ($query_or_file) {
        echo "Your input sequence is: <br>
        <textarea><strong>" . $_POST['query_seq'] . "</strong></textarea>";
    } else {
        echo "Your input filename is: <strong>" . basename($_FILES["uploadfile"]["name"]) . "</strong><br>";
    }
    //E-value
    echo "E-value: <strong>$evalue</strong><br>";
    //Blast type
    if ($blast_type = "n") {
        echo "The blast type is <strong>Nucleotide to Nucleotide</strong><br>";
    } else if ($blast_type = "p") {
        echo "The blast type is <strong>Protein to Protein</strong><br>";
    }
    //select Species
    echo "The Compared DataBase is <strong>" . implode(", ", $species) . "</strong><br>";

    //將物種轉為固定格式
    for ($i = 0, $num = count($species); $i < $num; $i++) {

        $species[$i] = str_replace(" ", "_", $species[$i]);
        if ($species[$i] == "Aegilops_tauschii") {
            unset($species[$i]);
        }
    }
    echo "<br>";

    //BLAST N OR P
    $query = "";
    if ($blast_type == "n") {
        $query = "/home/C54076275/ncbi-blast-2.13.0+/bin/blastn -db \"" . implode(" ", $species) . "\" -query  $target_file -out $output_file -outfmt '6 qseqid sseqid pident evalue bitscore' -evalue $evalue";
    } else if ($blast_type == "p") {
        $query = "/home/C54076275/ncbi-blast-2.13.0+/bin/blastn -db \"" . implode(" ", $species) . "\" -query  $target_file -out $output_file -outfmt '6 qseqid sseqid pident evalue bitscore' -evalue $evalue";
    }
    system($query, $return_var);
    chmod($output_file, 0777);

    if ($return_var == 0) {
        echo "BLAST RESULT<br>";
        $blast_result = explode(PHP_EOL, file_get_contents($output_file)); //在文件結尾有換行符號，因此會多出一個空白arr
        foreach ($blast_result as $k => $v) {
            $blast_result[$k] = explode(chr(9), $v);
        }
    } else {
        echo "Blast fail :(<br>";
    }

    $result_num = sizeof($blast_result);
    if ($result_num >= 2) {
        $result_num--;
        echo "<font size='5'><table width='1140px' align='center' border='1' style='border-collapse:collapse; word-break:break-all' borderColor='black'><tr align='center'>";
        echo "<tr>";
        echo "<td>See alignment</td>";
        echo "<td>Source sequence id</td>";
        echo "<td>Species</td>";
        echo "<td>Target sqquence id</td>";
        echo "<td>Percentage of identical matches</td>";
        echo "<td>Expect value</td>";
        echo "<td>Bit score</td>";
        echo "</tr>";
        for ($i = 0; $i < $result_num; $i++) {
            echo "<tr>";
            echo "<td>";
            echo "<a href=\"#".$blast_result[$i][1]."\">".$blast_result[$i][1]."</a>";
            echo "</td>";
            foreach ($blast_result[$i] as $k => $v) {
                if ($k == 1) {
                    $blast_result[$i][1] = explode('-', $v, 2);
                    $blast_result[$i][1][0] = str_replace("_", " ", $blast_result[$i][1][0]);
                    echo "<td>";
                    echo $blast_result[$i][1][0];
                    echo "</td>";
                    echo "<td>";
                    echo "<a href='./gene_info.php?species=" . $blast_result[$i][1][0] . "&NotGraphic_GeneID=" . $blast_result[$i][1][1] . "'>" . $blast_result[$i][1][1] . "</a>";
                    echo "</td>";
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
    mysqli_close($link);
    // system("rm $target_file");
    // system("rm $output_file");

    $query = "/home/C54076275/ncbi-blast-2.13.0+/bin/blastn -db \"" . implode(" ", $species) . "\" -query  $target_file -out $output_file -outfmt 0 -evalue $evalue";
    system($query, $return_var);
    $alignment_result = explode('>', file_get_contents($output_file));
    unset($alignment_result[0]);
    array_values($alignment_result);
    $alignment_result[(count($alignment_result)-1)] = substr($alignment_result[(count($alignment_result)-1)], 0, strpos($alignment_result[(count($alignment_result)-1)], "\n\n\n", 0) + 3);
    foreach ($alignment_result as $v) {
        $result_species_id=substr($v,0,strpos($v, "\n", 0));
        echo "<div id='".$result_species_id."'>";
        $result_species_id=explode("-",$result_species_id,2);
        echo "Species: ".$result_species_id[0]."\tGeneID: ".$result_species_id[1]." <br>";
        echo "<textarea>" . $v . "</textarea><br>";
        echo "</div>";
    }
?>
</body>

</html>