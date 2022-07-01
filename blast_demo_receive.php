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
    if (!stripos($query_seq, '>', stripos($query_seq, '>')+1)===false) {
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
    //turn species to A_a format
    for($i=0,$num=count($species);$i<$num;$i++){
        $species[$i]=str_replace(" ","_",$species[$i]);
    }
    echo "<br>";
    print_r($species);
    
    //decide blast n or p
    $query="";
    if ($blast_type == "n") {
        $query = "blastn -db ".implode(" ",$species)." -query $query_seq -out text -outfmt 6";
    } else if ($blast_type == "p") {
        $query = "blastp -db ".implode(" ",$species)." -query $query_seq -out text -outfmt 6";
    }
    echo "<br>".$query."<br>";
    $id=time();
    if(!$fp=fopen("../blastinput/$id","w")){
        echo "fail to open blastinput/".$id."<br>";
    }
    if(!$fp2=fopen("../blastoutput/$id","w")){
        echo "fail to open blastoutput/".$id."<br>";
    };

    // if(!system($query)){
        //     echo "blast fail.";
        // }
        // $link = new mysqli('140.116.56.177', 'onlineDB', 'bidlab711', 'plantpan4') or die("Unable to connect the database."); //連接資料庫
        ?>
</body>

</html>