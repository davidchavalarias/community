<?php
echo 'toto';
echo '<meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>';
/*
 * prend un liste csv avec autres meta donnée en csv et ajouter un tag 
 lorque le champs  défini correspond à un éléments de la white list
 */

include("parametres.php");
include("../common/library/fonctions_php.php");



$output= fopen('csv/chercheurs_idfJune2013_merged.csv', "w","UTF-8");

// var :
$fichier='csv/idfOnlyJune2013.csv';
$whitelist_file='csv/ChercheursContactJanv2013IdF.csv';
$white_first_name=array();
$white_last_name=array();
$white_csv=array(  );
pt("opening ".$fichier);
$data=array();
$emails=array();
$names=array(); // liste des noms de famille pour compater


$second_line=array(); // pour repérer le champ contenant les mails
$stopped='';
$ok='';
$last_name_fields='Last Name -- 166';///
$first_name_fields='First Name -- 165';

// on detecte les colonnes cibles dans le csv whitelist, celles les noms et prénoms
$count=0;
if (($handle = fopen($whitelist_file, "r","UTF-8")) !== FALSE) {    
    while ((($line= fgetcsv($handle, 4096)) !== false)&($count<1)) {
        if ($count<1){
            $white_first_line=$line;
        } 
        $count+=1;
    }        
}

$last_white_name_column=array_search($last_name_fields,$white_first_line);
$first_white_name_column=array_search($first_name_fields,$white_first_line);
$white_email=array_search('Adresse électronique -- 391',$white_first_line);



if (($handleStop = fopen($whitelist_file, "r","UTF-8")) !== FALSE) {    
    while (($data= fgetcsv($handleStop, 4096)) !== false) {
        $white_first_name[]=trim($data[$first_white_name_column]);
        $white_last_name[]=trim($data[$last_white_name_column]);  
        $white_csv[]=$data;
    }        
}

//pta($white_words);
pt('');

$count=0;
if (($handle = fopen($fichier, "r","UTF-8")) !== FALSE) {    
    while ((($line= fgetcsv($handle, 4096)) !== false)&($count<2)) {
        if ($count<1){
            $first_line=$line;
        } 
        $count+=1;
        $second_line=$line;    
    }        
}
//pta($first_line);
$last_name_column=array_search($last_name_fields,$first_line);
$first_name_column=array_search($first_name_fields,$first_line);
$check_column=array_search('itemId',$first_line);

pt('last name in col:'.$last_name_column);// colonne servant à détecter les pre-taggés
pt('check in col:'.$check_column);// colonne servant à détecter les pre-taggés



$email_field=0;
foreach ($second_line as $key => $value) {   
    $pos=strpos($value, "@");    
    if (($pos>=0)&&($pos!=false)){
        $email_field=$key; // donne le champ contenant les mails
        continue;
    }
}
fclose($handle);

pt('email field:'.$email_field);

if (($handle = fopen($fichier, "r","UTF-8")) !== FALSE) {    
    while (($line= fgetcsv($handle, 4096)) !== false) {
        $data[]=$line;        
        $emails[]=trim($line[$email_field]);
        $first_names[]=trim($line[$first_name_column]);
        $last_names[]=trim($line[$last_name_column]);        
    }        
}

$emails=array_unique($emails);
pt(count($emails)." unique mails");
$count=0;
foreach ($last_names as $key => $value) { 
    if (in_array($value,$white_last_name)){
//        pt($data[$key]);
        $rank_in_white_csv=array_search($value,$white_last_name);
        if(strcmp(trim($first_names[$key]),trim($white_first_name[$rank_in_white_csv]))==0){
        $white_csv[$rank_in_white_csv][0]='x';

        $data[$key][$check_column]='idf';        
        fputcsv($output, $data[$key]);
        $count+=1;    
        }else{
        fputcsv($output, $data[$key]);
    }
        
    }else{
        fputcsv($output, $data[$key]);
    }    
    
}

// on ajoute ceux de la white liste qui ne sont pas dans la liste initiale
$template=$data[$key];
foreach ($template as $key => $value) {
    $template[$key]='';
}
pt($count.' terms in OK LIST');

$count=0;
foreach ($white_csv as $key => $value) {     
    if (strcmp($value[0],'x')!=0){
        $line=$template;
        $line[array_search($last_name_fields,$first_line)]= $value[2];
        $line[array_search($first_name_fields,$first_line)]= $value[3];
        $line[array_search('Institutional affiliation: -- 667',$first_line)]= $value[5];
        $line[array_search('Lab: -- 880',$first_line)]= $value[6];        
        $line[$check_column]='idf'; 
        fputcsv($output,$line);
        $count+=1;
    }
}
pt($count.' new scholars added');
fclose($handle);
fclose($output);
pt($ok);


