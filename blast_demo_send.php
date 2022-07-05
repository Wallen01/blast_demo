<html>

<head>
    <title>blast</title>
    <!-- css -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="css/style.css" rel="stylesheet">
    <link href="color/default.css" rel="stylesheet">
    <link href="css/magicsuggest.css" rel="stylesheet">
</head>

<body>
    <form action="blast_demo_receive.php" method="post" enctype="multipart/form-data">
        Input a sequence: <br>
        <textarea type="text" name="query_seq"></textarea><br>
        <input type="file" name="uploadfile">
        E-value: <input type="text" name="evalue" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');"><br>
        Blast type<br>
        <label><input type="radio" name="blast_type" value="n" checked>N to N</label><br>
        <label><input type="radio" name="blast_type" value="p">P to P</label><br>
        which database: <input id="dbselect" name="species" required><br>
        <input type="submit">
    </form>
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