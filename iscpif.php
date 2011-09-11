<?php

//prend un fichier, colle toute les lignesn découpe par des virgules et renvoie la liste des 
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
include("parametres.php");
include("../common/library/fonctions_php.php");
$handle = @fopen("iscpif.csv", "r","UTF8");
if ($handle) {
    $ligne='';
    while (($buffer = fgets($handle, 4096)) !== false) {
    
    $ligne.=$buffer;    
        
    }
    fclose($handle);
}

$output=array();
$tocken=split('[,()]', $ligne);
foreach ($tocken as $value){
    $term=$value;
    $value=trim($value);
    if (strlen($value)>5){
        $value=strtolower($value);        
    }
    
    $value=str_replace('université', 'Univ.', $value);
    $value=str_replace('university', 'Univ.', $value);
    $value=str_replace('universidad', 'Univ.', $value);
    $value=str_replace('universiteit', 'Univ.', $value);
    $value=str_replace('universitaet', 'Univ.', $value);
    $value=str_replace('universite', 'Univ.', $value);
    $value=str_replace('universita', 'Univ.', $value);
    $value=str_replace('centre national de la recherche scientifique', 'CNRS', $value);        
    if (strlen($value)>5){
    $value=ucwords($value);
    }
    
    if (array_key_exists($value,$output)){
        $output[$value].='***'.$term;
    }else{
        $output[$value]=$term;
    }
}
pt("status","label","ngram forms");
$labels=  array_keys($output);
foreach ($labels as $key){
    pt('"w","'.$key.'","'.$output[$key].'"');
}

?>
