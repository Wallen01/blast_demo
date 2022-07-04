<html>

<head>
    <title>
        blast
    </title>
</head>

<body>
    <?php
    //獲得表單參數
    if (!isset($_POST["query_seq"])) {
        echo "No query.";
        exit();
    }
    $query_seq = $_POST["query_seq"];
    if (!stripos($query_seq, '>', stripos($query_seq, '>') + 1) === false) {
        echo "Only One Query.";
        exit();
    }
    if (isset($_POST["evalue"])) {
        $evalue = $_POST["evalue"];
    }
    if (isset($_POST["blast_type"])) {
        $blast_type = $_POST["blast_type"];
    }
    if (isset($_POST["species"])) {
        $species = $_POST["species"];
    }

    //顯示表單參數
    echo "
    query_seq=<textarea>$query_seq</textarea><br>
    evalue=$evalue<br>
    blast_type=$blast_type<br>
    species=$species<br>
    ";

    //將輸入的序列儲存為文件
    $id = time();
    if (!$fp1 = fopen("./blastinput/$id", "w")) {
        echo "fail to open blastinput/" . $id . "<br>";
    } else {
        fwrite($fp1, $query_seq);
        fclose($fp1);
        
        //調整權限
        chmod("./blastinput/$id", 0777);
    }

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
        $query = "/home/C54076275/ncbi-blast-2.13.0+/bin/blastn -db /home/C54076275/blastdb/" . implode(" ", $species) . " -query  /home/C54076275/public_html/playpan/blastinput/" . $id . " -out ./blastoutput/" . $id . " -outfmt 6 -evalue $evalue";
    } else if ($blast_type == "p") {
        $query = "/home/C54076275/ncbi-blast-2.13.0+/bin/blastn -db /home/C54076275/blastdb/" . implode(" ", $species) . " -query  /home/C54076275/public_html/playpan/blastinput/" . $id . " -out /home/C54076275/public_html/playpan/blastoutput/" . $id . " -outfmt 6 -evalue $evalue";
    }

    echo "<br>$query<br>";
    echo "<br>system";
    $last_line = system($query, $return_var);
    echo "<br>return_var:";
    print_r($return_var);
    echo "<br>last_line:";
    print_r($last_line);
    
    if (!$last_line) {
        echo "blast fail.";
    } else {
        echo "Cool";
        echo "<textarea>";
        $blast_result = explode(PHP_EOL, file_get_contents("./blastoutput/$id"));
        // foreach ($blast_result as $k => $v) {
        //     $blast_result[$k] = explode(chr(9), $v);
        // }
        print_r($blast);
        echo "</textarea>";
    }



    // $link = new mysqli('140.116.56.177', 'onlineDB', 'bidlab711', 'plantpan4') or die("Unable to connect the database."); //連接資料庫
    ?>
</body>

</html>