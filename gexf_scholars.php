<?php

/*
 * Génère le gexf des scholars à partir de la base sqlite
 */
include("parametres.php");
include("../common/library/fonctions_php.php");

$gexf = '<?xml version="1.0" encoding="UTF-8"?>';

$base = new PDO("sqlite:" . $dbname);

$termsMatrix = array(); // liste des termes présents chez les scholars avec leurs cooc avec les autres termes
$scholarsMatrix = array(); // liste des scholars avec leurs cooc avec les autres termes
// Ecriture de l'entête du gexf

$gexf.='<gexf xmlns="http://www.gexf.net/1.1draft" xmlns:viz="http://www.gephi.org/gexf/viz" version="1.1"> ';
$gexf.= ' <meta lastmodifieddate="' . date('Y-m-d') . '"> "\n"';
$gexf.=' </meta> "\n"';
$gexf.='<graph type="static">' . "\n";
$gexf.='<attributes class="node" type="static">' . "\n";
$gexf.=' <attribute id="0" title="category" type="string">  </attribute>' . "\n";
$gexf.=' <attribute id="1" title="occurrences" type="float">    </attribute>' . "\n";
$gexf.=' <attribute id="2" title="content" type="string">    </attribute>' . "\n";
$gexf.=' <attribute id="3" title="keywords" type="string">   </attribute>' . "\n";
$gexf.=' <attribute id="4" title="weight" type="float">   </attribute>' . "\n";
$gexf.='</attributes>' . "\n";
$gexf.='<attributes class="edge" type="float">' . "\n";
$gexf.=' <attribute id="5" title="cooc" type="float"> </attribute>' . "\n";
$gexf.=' <attribute id="6" title="type" type="string"> </attribute>' . "\n";
$gexf.="</attributes>" . "\n";
$gexf.="<nodes>" . "\n";

// liste des chercheurs
$sql = "SELECT unique_id,first_name,last_name,initials FROM scholars";
pt($query);
$scholars = array();
//$query = "SELECT * FROM scholars";
foreach ($base->query($sql) as $row) {
    $info = array();
    $info['unique_id'] = $row['unique_id'];
    $info['first_name'] = $row['first_name'];
    $info['initials'] = $row['initials'];
    $info['last_name'] = $row['last_name'];
    $scholars[] = $info;
}

foreach ($scholars as $scholar) {
    // on en profite pour charger le profil sémantique du gars
    $query = "SELECT term_id FROM scholars2terms where scholar='" . $scholar['unique_id'] . "'";
    $scholar_keywords = array();
    foreach ($base->query($query) as $row) {
        $scholar_keywords[] = $row['term_id'];
    }

    // on en profite pour construire le réseau des termes par cooccurrence chez les scholars
    for ($k = 0; $k < count($scholar_keywords); $k++) {
        if ($termsMatrix[$scholar_keywords[$k]] != null) {
            $termsMatrix[$scholar_keywords[$k]][occ] = $termsMatrix[$scholar_keywords[$k]][occ] + 1;
            for ($l = 0; $l < count($scholar_keywords); $l++) {
                if ($termsMatrix[$scholar_keywords[$k]][cooc][$scholar_keywords[$l]] != null) {
                    $termsMatrix[$scholar_keywords[$k]][cooc][$scholar_keywords[$l]]+=1;
                } else {
                    $termsMatrix[$scholar_keywords[$k]][cooc][$scholar_keywords[$l]] = 1;
                }
            }
        } else {
            $termsMatrix[$scholar_keywords[$k]][occ] = 1;
            for ($l = 0; $l < count($scholar_keywords); $l++) {
                if ($termsMatrix[$scholar_keywords[$k]][cooc][$scholar_keywords[$l]] != null) {
                    $termsMatrix[$scholar_keywords[$k]][cooc][$scholar_keywords[$l]]+=1;
                } else {
                    $termsMatrix[$scholar_keywords[$k]][cooc][$scholar_keywords[$l]] = 1;
                }
            }
        }

        $nodeId = 'D::' . $scholar['unique_id'];
        $nodeLabel = $scholar['first_name'] . ' ' . $scholar['initials'] . ' ' . $scholar['last_name'];
        $nodePositionY = rand(0, 100) / 100;
        $gexf.='<node id="' . $nodeId . '" label="' . $nodeLabel . '">' . "\n";
        $gexf.='<viz:color b="255" g="0"  r="0"/>' . "\n";
        $gexf.='<viz:position x="' . (rand(0, 100) / 100) . '"    y="' . $nodePositionY . '"  z="0" />' . "\n";
        $gexf.='<attvalues> <attvalue for="0" value="Document"/>' . "\n";
        $gexf.='<attvalue for="1" value="10"/>' . "\n";
        $gexf.='<attvalue for="4" value="10"/>' . "\n";
        $gexf.='</attvalues></node>' . "\n";
    }
}
// liste des termes
$sql = "SELECT term,id,occurrences FROM terms";
pt($query);
$term_array = array();
//$query = "SELECT * FROM scholars";
foreach ($base->query($sql) as $row) {
    $info = array();
    $info['id'] = $row['id'];
    $info['occurrences'] = $row['occurrences'];
    $info['term'] = $row['term'];
    $terms_array[] = $info;
}

$count=1;

foreach ($terms_array as $term) {
if ($count<1000){
    $count+=1;
    // on en profite pour charger le profil scholar du term
    $query = "SELECT scholar FROM scholars2terms where term_id='" . $term['id'] . "'";
    
    $term_scholars = array();
    foreach ($base->query($query) as $row) {
        $term_scholars[] = $row['scholar']; // ensemble des scholars partageant ce term
    }
        // on en profite pour construire le réseau des scholars partageant les mêmes termes
        for ($k = 0; $k < count($term_scholars); $k++) {
            if ($scholarsMatrix[$term_scholars[$k]] != null) {
                $scholarsMatrix[$term_scholars[$k]][occ] = $scholarsMatrix[$term_scholars[$k]][occ] + 1;
                for ($l = 0; $l < count($term_scholars); $l++) {
                    if ($scholarsMatrix[$term_scholars[$k]][cooc][$term_scholars[$l]] != null) {
                        $scholarsMatrix[$term_scholars[$k]][cooc][$term_scholars[$l]]+=1;
                    } else {
                        $scholarsMatrix[$term_scholars[$k]][cooc][$term_scholars[$l]] = 1/(log($term['occurrences']));
                    }
                }
            } else {
                $scholarsMatrix[$term_scholars[$k]][occ] = 1;
                for ($l = 0; $l < count($term_scholars); $l++) {
                    if ($scholarsMatrix[$term_scholars[$k]][cooc][$term_scholars[$l]] != null) {
                        $scholarsMatrix[$term_scholars[$k]][cooc][$term_scholars[$l]]+=1/log($term['occurrences']);;
                    } else {
                        $scholarsMatrix[$term_scholars[$k]][cooc][$term_scholars[$l]] = 1/log($term['occurrences']);;
                    }
                }
            }
        }
    

  
  
        
        $nodeId = 'N::' . $terms_array['unique_id'];
        $nodeLabel = $terms_array['term'];
        $nodePositionY = rand(0, 100) / 100;
        $gexf.='<node id="' . $nodeId . '" label="' . $nodeLabel . '">' . "\n";
        $gexf.='<viz:color b="0" g="255"  r="0"/>' . "\n";
        $gexf.='<viz:position x="' . (rand(0, 100) / 100) . '"    y="' . $nodePositionY . '"  z="0" />' . "\n";
        $gexf.='<attvalues> <attvalue for="0" value="NGram"/>' . "\n";
        $gexf.='<attvalue for="1" value="10"/>' . "\n";
        $gexf.='<attvalue for="4" value="10"/>' . "\n";
        $gexf.='</attvalues></node>' . "\n";    
}
}

$gexf.='</nodes>' . "\n";

$gexf.='</graph></gexf>';

$gexfFile = fopen('output.gexf', 'w');
fputs($gexfFile, $gexf);

fclose($gexfFile);
?>
