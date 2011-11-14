<?php

/*
 * Génère le gexf des scholars à partir de la base sqlite
 */
include("parametres.php");
//include("../common/library/fonctions_php.php");
include("normalize.php");


$base = new PDO("sqlite:" . $dbname);

$search =  trim(strtolower($_GET['term']));
$q = "%".sanitize_input($_GET['term'])."%";
$limit = 10;

function filter_word($value) {
  $filtered = array (
  "yes", "1", "0", "nvgfpmeilym", "no", "mr", "ms", "", " ", "   "
  );
  return ! in_array(strtolower($value),$filtered); 
}


$search_query = 'SELECT * FROM scholars WHERE '
.'country LIKE \''.$q.'\' OR '
.'unique_id LIKE \''.$q.'\' OR '
.'keywords LIKE \''.$q.'\' OR '
.'title LIKE \''.$q.'\' OR '
.'lab LIKE \''.$q.'\' OR '
.'affiliation LIKE \''.$q.'\' OR '
.'lab2 like \''.$q.'\'';

function contains($r,$s,$k) {
return (strlen(strstr(
      strtolower(trim($r[$k])),
      strtolower(trim($s))
      ))>0); 
}

$results = array();
$i = 0;
foreach ($base->query($search_query) as $row) {
 $value = $row["first_name"] ." ". $row["last_name"];
 $uid = $row["unique_id"];

 if (contains($row, $search, "unique_id")) {
       array_push($results, array(
        'id' => $uid,
        'label' => $value,
       // 'value' => $value,
        'category' => 'Scholars:'
      ));
    }
}

echo json_encode(array_slice($results,0,$limit));
?>
