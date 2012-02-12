<?php

/*
list de parametres.
 */

$dbname='community.db';// nom de la base sqlite utilisée par les scripts
//$dbname='scholar_test_data.db';
$scholars_db ="raw_scholars";
$lab_db ="raw_lab";
$orga_db ="raw_orga";
$job_db ="raw_job";

$fichier = "csv/CSSscholars20Oct2011.csv";//fichier utilisé pour importer les scholars en base
$fichier = "csv/CSSscholars2Oct2011.csv";
$fichier = "csv/CSSscholars24Oct2011.csv";
$fichier = "csv/CSSscholars30Nov2011.csv";
$fichier = "csv/CSSScholars5janvier2012.csv";
$fichier = "csv/CSSScholars12Fevrier2012.csv";

$target_scholar='davidchavalarias';
//$fichier = "csv/CNRS.csv";
//$fichier = "debug.csv";

$labs="labs22Nov11";
$orga="org22Nov11";
$jobs="jobs22Nov11";

$min_num_friends=0;// nombre minimal de voisin que doit avoir un scholar pour être affiché
//$fichier = "Scholars13Sept2011.csv";
//$fichier = "test2.csv";
$drop_tables=true; // dit s'il faut réinitialiser les tables
$language='english';
$file_sep=',';

// filtres pour filter les scholars inclus dans le gexf
//$scholar_filter=" where country='France' AND status='o'";
//$scholar_filter=" where country='France' AND want_whoswho='Yes' AND css_member='Yes'";
//$scholar_filter="where css_member='Yes' AND want_whoswho='Yes'";
//$scholar_filter="where want_whoswho='Yes'";
//$scholar_filter="where css_member='Yes'";
//$scholar_filter="";
$scholar_filter=" where country='France' AND want_whoswho='Yes'";
//$scholar_filter=" where country='France'";

//$compress='No';

?>
