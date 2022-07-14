<!DOCTYPE html>
<html>

<head>
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
    <script type="text/javascript" src="css/colorbox/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="css/colorbox/colorbox/jquery.colorbox-min.js"></script>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-45242567-12"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());
        gtag('config', 'UA-45242567-12');
    </script>
</head>

<body id="page-top" data-spy="scroll" data-target=".navbar-custom" style="font-size: 20px;">
    <!-- Navigation -->
    <div id="navigation">
        <nav class="navbar navbar-custom navbar-fixed-top top-nav-collapse" role="navigation">
            <div class="container">
                <div class="navbar-header page-scroll">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
                        <i class="fa fa-bars"></i>
                    </button>
                    <a class="navbar-brand" href="index.html">
                        <h1>PlantPAN 3.0</h1>
                    </a>
                </div>
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse navbar-right navbar-main-collapse">
                    <ul class="nav navbar-nav">
                        <li><a href="index.html">Home</a></li>
                        <li><a href="index.html#link">Link</a></li>
                        <li><a href="index.html#guide">Guide</a></li>
                        <li><a href="index.html#about">About</a></li>
                        <li><a href="http://plantpan2.itps.ncku.edu.tw/" style="text-transform: none;">PlantPAN 2.0</a></li>
                        <li><a href="http://pcbase.itps.ncku.edu.tw/JBrowse_search.php" style="text-transform: none;">Browse in PCBase</a></li>
                    </ul>
                </div>
                <!-- /.navbar-collapse -->
            </div>
            <!-- /.container -->
        </nav>
    </div>
    <!-- /Navigation -->

    <!-- MAIN CONTENT -->
    <section class='home-section color-dark'>
        <div class='container'>
            <div class='row'>
                <div class='col-lg-8 col-lg-offset-2'>
                    <div class='section-heading text-center'>
                        <h2 class='h-bold'>BLAST</h2>
                    </div>
                    <hr class='marginbot-50'>
                </div>
            </div>
        </div>
        <div class='container'>
            <form action="blast_demo_receive.php#BLAST_SETTING" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <div class='col-lg-8'>
                        <label>Input a sequence:</label>
                    </div>
                    <div class='col-lg-4'>
                        <input type="file" name="uploadfile" class="btn btn-lg">
                    </div>
                    <textarea type="text" name="query_seq" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>E-value:</label>
                    <input class="form-control" type="text" name="evalue" value="0.00001" placeholder="0 < E-value <=10" ; oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"><br>
                </div>
                <div class="form-group">
                    <label>Blast type</label><br>
                    <label class="checkbox-inline">
                        <input type="radio" name="blast_type" value="n" checked>N to N
                    </label>
                    <label class="checkbox-inline">
                        <input type="radio" name="blast_type" value="p">P to P
                    </label>
                </div>
                <div class="form-group">
                    Which database:
                    <input class="form-control" id="dbselect" name="species" required>
                </div>
                <button type="submit">Submit</button>
            </form>
        </div>
    </section>
    <div id='BLAST_SETTING'></div>
    <?php
    if (isset($_POST["evalue"])) {
        $id = time();
        $target_file = "";
        $output_file = "";
        $query_or_file = true;
        $something_wrong = false;
        $ERROR_MESSAGE = array();
        /* 可能出錯的部分
         * 1:無法開啟臨時Query存檔->使用者須稍等幾秒?可能ID衝突
         * 2:檔名已存在
         * 3:檔案過大
         * 4:檔名錯誤(fasta/fas/txt)
         * 5:檔案轉移出錯
         * 6:未輸入Query
         * 7:檔案為空
         * 8:不含">"
         * 9:輸入2個Query
         * 10:evalue超出範圍
         */
        if ($_POST["query_seq"] != "") {
            //將輸入的序列儲存為文件
            $target_file = "./blastinput/$id";
            $output_file = "./blastoutput/$id";
            if (!$fp1 = fopen($target_file, "w")) {
                array_push($ERROR_MESSAGE, 1);
                $something_wrong = true;
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
                array_push($ERROR_MESSAGE, 2);
                $something_wrong = true;
                $uploadOk = 0;
            }

            // Check file size
            if ($_FILES["uploadfile"]["size"] > 500000) {
                array_push($ERROR_MESSAGE, 3);
                $something_wrong = true;
                $uploadOk = 0;
            }

            // Allow certain file formats
            if (
                $FileType != "fasta" && $FileType != "fas" && $FileType != "txt"
            ) {
                array_push($ERROR_MESSAGE, 4);
                $something_wrong = true;
                $uploadOk = 0;
            }

            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES["uploadfile"]["tmp_name"], $target_file)) {
                    chmod($target_file, 0777);
                } else {
                    array_push($ERROR_MESSAGE, 5);
                    $something_wrong = true;
                }
            }
        } else {
            array_push($ERROR_MESSAGE, 6);
            $something_wrong = true;
        }

        //check if fasta format
        $query_seq = file_get_contents($target_file);
        $pos;
        //檔案為空
        if ($query_seq == "") {
            array_push($ERROR_MESSAGE, 7);
            $something_wrong = true;
        }
        //不含有">"
        else if ($pos = stripos($query_seq, ">") === false) {
            array_push($ERROR_MESSAGE, 8);
            $something_wrong = true;
        }
        //有兩個query
        else if (stripos($query_seq, ">", $pos + 1) !== false) {
            array_push($ERROR_MESSAGE, 9);
            $something_wrong = true;
        }
        ////獲得表單參數
        $species = array();
        $evalue = $_POST["evalue"];
        //evalue超出範圍
        if (10 < $evalue && $evalue <= 0) {
            array_push($ERROR_MESSAGE, 10);
            $something_wrong = true;
        }
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
        echo "<section class='home-section color-dark'>
            <div class='container'>
                <div class='row'>
                    <div class='col-lg-8 col-lg-offset-2'>
                        <div class='section-heading text-center'>
                            <h2 class='h-bold'>BLAST SETTING</h2>
                        </div>
                        <hr class='marginbot-50'>
                    </div>
                </div>";

        ////輸入沒問題
        if ($something_wrong) {
            echo "Hey, there might be something wrong.<br>";
            $ShowError = array(
                1 => "Please wait a second then try again.<br>", 2 => "Please wait a second then try again.<br>", 3 => "The file is too large.<br>", 4 => "Only accept fasta / fas / txt file.<br>", 5 => "Something strange just happened.<br>", 6 => "I think you forgot inserting your Query.<br>", 7 => "Your file is empty.<br>", 8 => "The fasta format is a little strange.<br>", 9 => "You only can insert one Query.<br>", 10 => "E-value is out of range.(0 < E-value <= 10)<br>"
            );
            foreach ($ERROR_MESSAGE as $v) {
                echo $ShowError[$v];
            }
            echo "</div></section>";
        } else {
            //顯示表單參數
            echo "<div class='container'>";
            //The Seq
            if ($query_or_file) {
                echo "<div class='col-md-3'>";
                echo "Your input sequence is: ";
                echo "</div>";
                echo "<div class='col-md-9'>";
                echo "<textarea><strong>" . $_POST['query_seq'] . "</strong></textarea>";
                echo "</div>";
            } else {
                echo "<div class='col-md-3'>";
                echo "Your input filename is: <strong>";
                echo "</div>";
                echo "<div class='col-md-9'>";
                echo basename($_FILES["uploadfile"]["name"]) . "</strong><br>";
                echo "</div>";
            }
            //E-value
            echo "<div class='col-md-3'>";
            echo "E-value: ";
            echo "</div>";
            echo "<div class='col-md-9'>";
            echo "<strong>$evalue</strong>";
            echo "</div>";
            //Blast type
            echo "<div class='col-md-3'>";
            echo "The blast type is ";
            echo "</div>";
            if ($blast_type = "n") {
                echo "<div class='col-md-9'>";
                echo "<strong>Nucleotide to Nucleotide</strong>";
                echo "</div>";
            } else if ($blast_type = "p") {
                echo "<div class='col-md-9'>";
                echo "<strong>Protein to Protein</strong>";
                echo "</div>";
            }
            //select Species
            echo "<div class='col-md-3'>";
            echo "Selected DataBases ";
            echo "</div>";
            echo "<div class='col-md-9'>";
            echo "<strong>" . implode(", ", $species) . "</strong>";
            echo "</div>";

            ////準備blast
            //將物種轉為固定格式
            for ($i = 0, $num = count($species); $i < $num; $i++) {
                $species[$i] = str_replace(" ", "_", $species[$i]);
            }
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
                $blast_result = explode(PHP_EOL, file_get_contents($output_file)); //在文件結尾有換行符號，因此會多出一個空白arr
                foreach ($blast_result as $k => $v) {
                    $blast_result[$k] = explode(chr(9), $v);
                }
            }
            echo "<div class='col-md-3'>";
            echo "Source sequence id is";
            echo "</div>";
            echo "<div class='col-md-9'>";
            echo "<strong>" . $blast_result[0][0] . "</strong>";
            echo "</div>";
            echo "</div>";
            echo "</section>";

            echo "<section class='home-section color-dark'>";
            echo "
            <div class='container'>
            <div class='row'>
                <div class='col-lg-8 col-lg-offset-2'>
                    <div class='section-heading text-center'>
                        <h2 class='h-bold'>";
            if ($return_var != 0) {
                echo "Blast fail :(<br>
                        </h2>
                    </div>
                <hr class='marginbot-50'>
                </div>
            </div>";
            } else {
                echo "BLAST RESULT";
                echo "      </h2>
                     </div>
                <hr class='marginbot-50'>
                </div>
            </div>";

                $result_num = sizeof($blast_result);
                if ($result_num >= 2) {
                    $result_num--;
                    echo "<div class='table-responsive'>";
                    echo "<table class='table' align='center' width='1240px'>";
                    echo "<tr align='center'><strong>";
                    echo "<td>Alignment</td>";
                    echo "<td>Species</td>";
                    echo "<td>Target sequence id</td>";
                    echo "<td>Identical matches (%)</td>";
                    echo "<td>E-value</td>";
                    echo "<td>Bit score</td>";
                    echo "<td>Download</td>";
                    echo "</strong></tr>";
                    for ($i = 0; $i < $result_num; $i++) {
                        echo "<tr align='center'>";
                        echo "<td>";
                        echo "<a href=\"#" . $blast_result[$i][1] . "\"><img src='./img/icons/left-chevron.png' alt='To Alignment' height=30px align='center' ></a>";
                        echo "</td>";
                        foreach ($blast_result[$i] as $k => $v) {
                            if ($k == 1) {
                                $blast_result[$i][1] = explode('-', $v, 2);
                                $blast_result[$i][1][0] = str_replace("_", " ", $blast_result[$i][1][0]);
                                echo "<td>";
                                echo $blast_result[$i][1][0];
                                echo "</td>";
                                echo "<td>";
                                echo "<a target='_blank' href='./gene_info.php?species=" . $blast_result[$i][1][0] . "&NotGraphic_GeneID=" . $blast_result[$i][1][1] . "'>" . $blast_result[$i][1][1] . "</a>";
                                echo "</td>";
                            } else if ($k > 1) {
                                echo "<td>";
                                echo $v;
                                echo "</td>";
                            }
                        }
                        echo "<td>";
                        if ($blast_type = "n") {
                            echo "<a href='sequence.php?species=" . $blast_result[$i][1][0] . "&type=cDNA&NotGraphic_cdna=" . $blast_result[$i][1][1] . "'>";
                        } else if ($blast_type = "p") {
                            echo "<a target='_blank' href='sequence.php?species=" . $blast_result[$i][1][0] . "&type=Protein&NotGraphic_protein=" . $blast_result[$i][1][1] . "'>";
                        }
                        echo "<img src='./img/icons/down-chevron.png' alt='To Alignment' height=30px align='center' ></a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                    echo "</div>";
                    echo "</div>";
                    echo "</section>";
                }
                mysqli_close($link);
                echo "
            <div class='container'>
            <div class='row'>
                <div class='col-lg-8 col-lg-offset-2'>
                    <div class='section-heading text-center'>
                        <h2 class='h-bold'>SEQUENCE ALIGNMENT</h2>
                    </div>
                    <hr class='marginbot-50'>
                </div>
            </div>
            </div>";

                $query = "/home/C54076275/ncbi-blast-2.13.0+/bin/blastn -db \"" . implode(" ", $species) . "\" -query  $target_file -out $output_file -outfmt 0 -evalue $evalue";
                system($query, $return_var);
                $alignment_result = explode('>', file_get_contents($output_file));
                unset($alignment_result[0]);
                array_values($alignment_result);
                $alignment_result[(count($alignment_result))] = substr($alignment_result[(count($alignment_result))], 0, strpos($alignment_result[(count($alignment_result))], "\n\n\n\n", 0) + 3);
                echo "<div class='container'>";
                foreach ($alignment_result as $v) {
                    $result_species_id = substr($v, 0, strpos($v, "\n", 0));
                    echo "<div id='" . $result_species_id . "'></div>";
                    echo "<br><br><br>";
                    $result_species_id = explode("-", $result_species_id, 2);

                    echo "<div class='col-md-6' style='font-size:150%'>";
                    echo "Species: ";
                    echo "<strong>" . $result_species_id[0] . "</strong>";
                    echo "</div>";

                    echo "<div class='col-md-6' style='font-size:150%'>";
                    echo "GeneID: ";
                    echo "<strong>" . $result_species_id[1] . "</strong>";
                    echo "</div>";

                    echo "<textarea style='width: 1240px; height: 600px'>" . $v . "</textarea><br>";
                }
                echo "</div>";
            }
        }
        if(file_exists($target_file))system("rm $target_file");
        if(file_exists($output_file))system("rm $output_file");
    }
    ?>
</body>
<!--nesscery for scrabble box-->
<script src="css/jquery-2.1.1.min.js"> </script>
<script src="css/magicsuggest.js"></script>
<script>
    $('#dbselect').magicSuggest({
        placeholder: 'Leave Space for Select All',
        data: [
            <?php
            $link = mysqli_connect('140.116.56.177', 'onlineDB', 'bidlab711') or die("Unable to connect the database."); //連接資料庫
            mysqli_query($link, "SET NAMES 'utf8'"); //設定語系
            mysqli_select_db($link, 'plantpan4');
            $result = mysqli_query($link, "select species_info from species_info order by species_info;");
            while ($name_row = mysqli_fetch_row($result)) {
                echo "{'id':'$name_row[0]','name':'$name_row[0]'},";
            }
            ?>
        ],
        allowFreeEntries: false,
        expand: true,
        expandOnFocus: true,
        highlight: false,
        noSuggestionText: 'No spcies are matched.',
        selectFirst: true,
        strictSuggest: true,
    });
</script>

</html>