<?php
echo 'toto';
echo '<meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>';
/*
 * prend un liste csv avec autres meta donnée en csv et ajouter un tag 
 lorque le champs  défini correspond à un éléments de la white list
 */

include("parametres.php");
include("../common/library/fonctions_php.php");



$output= fopen('chercheurs_idf_merged.csv', "w","UTF-8");

// var :
$fichier='csv/iscpifJune2013.csv';
$whitelisst_file='ChercheursContactJanv2013IdF.csv';
$white_words=array();
pt("opening ".$fichier);
$data=array();
$emails=array();
$names=array(); // liste des noms de famille pour compater


$second_line=array(); // pour repérer le champ contenant les mails
$stopped='';
$ok='';

$last_name_fields_contact='Nom -- 389';///
$last_mail_fields_scholars='Last Name -- 166';

echo 'toto';

if (($handleStop = fopen($whitelisst_file, "r","UTF-8")) !== FALSE) {    
    while (($data= fgets($handleStop, 4096)) !== false) {
        $white_words[]=trim($data);
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
pta($first_line);
$last_name_column=array_search($last_mail_fields_scholars,$first_line);
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
        $names[]=trim($line[$last_name_column]);
    }        
}



$emails=array_unique($emails);

pt(count($emails)." unique mails");
$count=0;
foreach ($names as $key => $value) { 
    if (in_array($value,$white_words)){
//        pt($data[$key]);
        $data[$key][$check_column]='idf';        
        fputcsv($output, $data[$key]);
        $count+=1;
    }else{
        fputcsv($output, $data[$key]);
    }    
    
}



fclose($handle);
fclose($output);
pt($count.' terms in OK LIST');
pt($ok);

function merge($fieldlist, $sep=', ') {
    global $data,$la;
    /* merge les champs dans $fieldlist séparés par des virgules en vérifiant qu'ils sont non vides */
    $string = '';
    $fields = split(',', $fieldlist);
    $data[$la[trim($fields[0])]];
    for ($i = 0; $i < count($fields); $i++) {
        if ($data[$la[trim($fields[$i])]] != null) {
            $string = $string . $data[$la[trim($fields[$i])]] . $sep;
        }
    }

    if (count($string) > count($sep)) {
        if (strcmp($sep, substr($string(-count($sep), -1))) == 0) {
            $string = substr($string, 0, -count($sep) - 1);
        }
    }
    return $string;
}

function section($name,$content,$sep=', '){
    /* merge les champs dans $fieldlist séparés par des virgules en vérifiant qu'ils sont non vides*/
    global $data,$la;
    
    $string='';        
    $temp=merge($content,$sep);
    if ($temp!=null){
            $string=''.$name.': '.$temp.'';
        }    
    return $string;   
}
        
