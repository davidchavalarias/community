<?php

/*
list de parametres.
 */
$fichier = "csv/cnrs/cartographieSalonTerritoires.csv";
$fichier = "csv/cnrs/test.csv";
//$fichier = "cartographieSalonTerritoires.csv";



$dbname='innovatives.sqlite';// nom de la base sqlite utilisée par les scripts
//$dbname='scholar_test_data.db';
$language='french';
$nodes1='projets'; // type de noeuds1
$nodes1_name='Intitulé_du_projet';
$nodes1_acronym='acronym';
$nodes1_id='id';
$node1_content="Descriptif";

$nodes2='keywords'; // type de noeuds2
$nodes2_name='mots_clé'; // nom du champs contenant les noeuds2

$descriptif='html';//'Descriptif';// //ce qui est affiché dans l'info div




$all=true;// dit s'il faut tenir compte du who's who approval
if ($all){
    echo 'WARNING ALL IS ON';
}

$min_num_friends=0;// nombre minimal de voisin que doit avoir un scholar pour être affiché
//$fichier = "Scholars13Sept2011.csv";
//$fichier = "test2.csv";
$drop_tables=true; // dit s'il faut réinitialiser les tables
$file_sep=',';

// filtres pour filter les scholars inclus dans le gexf
//$scholar_filter=" where country='France' AND status='o'";
//$scholar_filter=" where country='France' AND want_whoswho='Yes' AND css_member='Yes'";
//$scholar_filter="where css_member='Yes' AND want_whoswho='Yes'";
$scholar_filter="";
//$scholar_filter="where css_member='Yes'";
//$scholar_filter="";
//$scholar_filter=" where country='France'";

//$compress='No';

?>
