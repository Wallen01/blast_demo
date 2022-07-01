<html>

<head>
    <title>
        blast
    </title>
</head>

<body>
    <?php
    if (!isset($_POST["query_seq"])) {
        echo "No query.";
        exit();
    }
    $query_seq = $_POST["query_seq"];
    if (!stripos($query_seq, '>', stripos($query_seq, '>'))===false) {
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
    echo "
    query_seq=$query_seq;<br>
    evalue=$evalue;<br>
    blast_type=$blast_type<br>
    species=$species<br>
    ";
    print_r($species);
    // $link = new mysqli('140.116.56.177', 'onlineDB', 'bidlab711', 'plantpan4') or die("Unable to connect the database."); //連接資料庫
    //decide blast n or p
    if ($_POST["species"] == "n") {
        $query = "blastn -db $species -query $query_seq -out text -outfmt 6";
    } else if ($_POST["species"] == "p") {
        $query = "blastp -db $species -query $query_seq -out text -outfmt 6";
    }
    echo $query;
    // if(!system($query)){
    //     echo "blast fail.";
    // }
    ?>
</body>

</html>