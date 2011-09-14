<?php

/*
list de parametres.
 */

$dbname='community.db';
$scholars_db ="raw_scholars";
$fichier = "Scholars13Sept2011.csv";
//$fichier = "Scholars13Sept2011.csv";
//$fichier = "test2.csv";
$drop_tables=true; // on efface les tables
$language='english';
$file_sep=',';
$scholar_filter=" where country='France' AND status='o'";
$scholar_filter=" where country='France'";
//$scholar_filter=" where css_member='Yes'";

?>
