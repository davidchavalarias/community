<?php
echo '<meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>';
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include("parametres.php");
include("../common/library/fonctions_php.php");

/* Creation des tables */

$dbname='community';
$mytable ="scholars";
$base= new SQLiteDatabase($dbname, 0666, $err);
if ($err)  exit($err);

/* on efface la table et on la recrée*/
$query = "DROP TABLE $mytable";
$results = $base->queryexec($query);

$output= fopen($fichier."_out.csv", "w","UTF-8");

global $data,$la;


$row = 1;

pt("opening ".$fichier);

if (($handle = fopen($fichier, "r","UTF-8")) !== FALSE) {
    
    /* On crée les entrée de la table avec la première ligne */
    $query = "CREATE TABLE ".$mytable." (";
    $subquery=""; /* partie de la requete pour alimenter la base plus bas */
    $la=array();
    $data = fgetcsv($handle, 1000, ",");
    print_r($data);
    
    
    $num = count($data);
    pt("number of columns: ".$num);
        for ($c=0; $c < $num; $c++) {
            $temp=split('--',$data[$c]);
            $label=str_replace(' ', '_',trim($temp[0]));
            $label=str_replace(':', '',$label);
            $label=str_replace("'", '',$label);
            $label=str_replace("?", '',$label);
                        
            $query =$query.$label.' text,';
            $subquery=$subquery.$label.',';
            $la[$label]=$c;
            pt($label);
        }
        
        
    /*    
    $query = $query . ')';
    $subquery = substr($subquery, 0, -1);
    pt("sous requelte" . $subquery);
    pt("Creating table with : " . $query);
    $results = $base->queryexec($query);
    */

    $heading[]='corp_id';
    $heading[]='doc_id';
    $heading[]='title';
    $heading[]='doc_acrnm';
    $heading[]='abstract';
    $heading[]='keywords';
    fputcsv($output,$heading,$file_sep,'"');
    
    while (($data = fgetcsv($handle, 1000, $file_sep)) !== FALSE) {
        $num = count($data);        
        $row++;
        $profile=array();
        $corp_id=str_replace(' ','_',$data[$la['Country']]);
        $doc_id=$data[$la['itemId']];
        $title=$data[$la['First_Name']].' '.$data[$la['Last_Name']];
        $doc_acrnm=$title;
        
        
        $abstract=$data[$la['Country']].'<br/>'.
        section('Affiliation','Lab,Institutional_affiliations_of_your_lab').
        section('Second Affiliation','Second_lab,Second_institutional_affiliation').
        section('Keywords','Keywords');
        $keywords=merge('Personal_Interest');
        
                
        $profile[]=$corp_id;
        $profile[]=$doc_id;
        $profile[]=$title;
        $profile[]=$doc_acrnm;
        $profile[]=$abstract;
        $profile[]=$keywords;
        
        
        if ($title!=null){
            fputcsv($output,$profile,',','"');
            pt($title);            
        }
            
        /*
        for ($c = 0; $c < $num; $c++) {
        $query = "INSERT INTO " . $mytable . "(";
            $number = 1;
            $title = "Mon dernier billet";
            $content = "Le texte de mon article...";
            $date = strftime("%b %d %Y %H:%M", time());
            $author = 1;
            $url = "http://www.scriptol.fr/sql/tutoriel-sqlite.php";

            $query = "INSERT INTO $mytable(ID, post_title, post_content, post_author, post_date, guid) 
                VALUES ('$number', '$title', '$content', '$author', '$date', '$url')";
            $results = $base->queryexec($query);
            pt($data[$c]);
            }    
         */
        
    }
    fclose($handle);
}

pt($row.' scholars processed');

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
            $string='<b>'.$name.': </b>'.$temp.'<br/>';
        }    
    return $string;   
}
        
?>
