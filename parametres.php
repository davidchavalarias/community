<?php

/*
list de parametres.
 */

$dbname='community.db';// nom de la base sqlite utilisée par les scripts
//$dbname='scholar_test_data.db';
//$scholars_db ="raw_scholars";
$fichier = "CSSscholars20Oct2011.csv";//fichier utilisé pour importer les scholars en base
$fichier = "CSSscholars2Oct2011.csv";
$fichier = "CSSscholars24Oct2011.csv";
$fichier = "CSSscholars16Nov2011.csv";
//$fichier = "debug.csv";

$labs="labs22nov11";
$orga="org22nov11";
$jobs="jobs22nov11";

$min_num_friends=0;// nombre minimal de voisin que doit avoir un scholar pour être affiché
//$fichier = "Scholars13Sept2011.csv";
//$fichier = "test2.csv";
$drop_tables=true; // dit s'il faut réinitialiser les tables
$language='english';
$file_sep=';';

// filtres pour filter les scholars inclus dans le gexf
//$scholar_filter=" where country='France' AND status='o'";
//$scholar_filter=" where country='France' AND want_whoswho='Yes' AND css_member='Yes'";
$scholar_filter=" where css_member='Yes' AND want_whoswho='Yes'";
//$scholar_filter=" where css_member='Yes' OR want_whoswho='Yes'";
//$scholar_filter="where css_member='Yes'";
//$scholar_filter="";
//$scholar_filter=" where country='France' AND want_whoswho='Yes'";
//$scholar_filter=" where country='France'";

//$compress='No';

?>
