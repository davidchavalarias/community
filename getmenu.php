<?php

/*
 * Génère le gexf des scholars à partir de la base sqlite
 */
include("parametres.php");
include("../common/library/fonctions_php.php");
include("normalize.php");


$base = new PDO("sqlite:" . $dbname);

$filtered = array (
  "yes", "1", 1, "0", 0, "nvgfpmeilym", "no", "mr", "ms", "", " ", "   ", null
  );
  
$categories = array(
  'a' => "Member",
  'b' => "Keywords", 
  'c' => "Institution", 
  'e' => "Country"
);
$req_getPositions = "SELECT position, count(position) AS nb FROM scholars WHERE position IS NOT '' GROUP BY country ORDER BY nb DESC";
$positions = array();
$i = 0;
foreach ($base->query($req_getPositions) as $row) {
    $value = trim(normalize_position($row["position"]));
    if ( ! in_array(strtolower($value),$filtered) ) {
      if (array_key_exists($value, $positions)) {
          $positions[ $value ] += intval($row["nb"]);
      } else {
          $positions[ $value ] = intval($row["nb"]);
      }
    }
}

$req_getCountries = "SELECT country, count(country) AS nb FROM scholars WHERE country IS NOT '' GROUP BY country ORDER BY nb DESC";
$countries = array();
$i = 0;
foreach ($base->query($req_getCountries) as $row) {
    $value = trim($row["country"]);
    if ( ! in_array(strtolower($value),$filtered) ) {
      if (array_key_exists($value, $countries)) {
          $countries[ $value ] += intval($row["nb"]);
      } else {
          $countries[ $value ] = intval($row["nb"]);
      }
    }
    
}

$menu = array(
   'countries' => $countries,
   'positions' => $positions,
   'categories' => $categories
);
echo json_encode($menu);
?>
