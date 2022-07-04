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
        echo "There is no query.<br>";
        exit();
    }
    $query_seq = $_POST["query_seq"];
    if ($query_seq[0] != ">") {
        echo "Not a fastafile";
    }
    if (!stripos($query_seq, '>', stripos($query_seq, '>') + 1) === false) {
        echo "Only One Query  IS Allowed.";
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
        $query = "/home/C54076275/ncbi-blast-2.13.0+/bin/blastn -db " . implode(" ", $species) . " -query  ./blastinput/" . $id . " -out ./blastoutput/" . $id . " -outfmt '6 qseqid sseqid pident evalue bitscore' -evalue $evalue";
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