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
$sql = "SELECT * FROM scholars".$scholar_filter;
$scholars = array();
//$query = "SELECT * FROM scholars";
foreach ($base->query($sql) as $row) {
    $info = array();
    $info['unique_id'] = $row['unique_id'];
    $info['first_name'] = $row['first_name'];
    $info['initials'] = $row['initials'];
    $info['last_name'] = $row['last_name'];
    $info['nb_keywords'] = $row['nb_keywords'];
    $info['css_voter'] = $row['css_voter'];
    $info['css_member'] = $row['css_member'];
    $info['keywords_ids'] = explode(',',$row['keywords_ids']);    
    $info['status'] =  $row['status']; 
    $info['country'] =  $row['country']; 
    $info['homepage'] =  $row['homepage']; 
    $info['lab'] =  $row['lab']; 
    $info['affiliation'] =  $row['affiliation']; 
    $info['lab2'] =  $row['lab2']; 
    $info['affiliation2'] =  $row['affiliation2']; 
    $info['homepage'] =  $row['homepage']; 
    $info['title'] =  $row['title']; 
    $info['position'] =  $row['position']; 
    
    $scholars[$row['unique_id']] = $info;
}


foreach ($scholars as $scholar) {
    // on en profite pour charger le profil sémantique du gars
    $scholar_keywords = $scholar['keywords_ids'];    
    // on en profite pour construire le réseau des termes par cooccurrence chez les scholars
    for ($k = 0; $k < count($scholar_keywords); $k++) {
        if($scholar_keywords[$k]!=null){
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
        }
    }
            
}



// liste des termes
$sql = "SELECT term,id,occurrences FROM terms";
pt($query);
$term_array = array();
//$query = "SELECT * FROM scholars";
foreach ($base->query($sql) as $row) {
    if ($termsMatrix[$row['id']]!= null){ // on prend que les termes sont mentionnés par les chercheurs filtrés
    $info = array();
    $info['id'] = $row['id'];
    $info['occurrences'] = $row['occurrences'];
    $info['term'] = $row['term'];
    $terms_array[$row['id']] = $info;
}
}

$count = 1;

foreach ($terms_array as $term) {
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
                if (array_key_exists($term_scholars[$l], $scholars)) {
                    if ($scholarsMatrix[$term_scholars[$k]][cooc][$term_scholars[$l]] != null) {
                        $scholarsMatrix[$term_scholars[$k]][cooc][$term_scholars[$l]]+=1 / (log($term['occurrences']) * log($scholars[$term_scholars[$k]]['nb_keywords']));
                    } else {
                        $scholarsMatrix[$term_scholars[$k]][cooc][$term_scholars[$l]] = 1 / (log($term['occurrences']) * log($scholars[$term_scholars[$k]]['nb_keywords']));
                    }
                }
            }
            } else {
                $scholarsMatrix[$term_scholars[$k]][occ] = 1;
                for ($l = 0; $l < count($term_scholars); $l++) {
                if (array_key_exists($term_scholars[$l], $scholars)) {
                    if ($scholarsMatrix[$term_scholars[$k]][cooc][$term_scholars[$l]] != null) {
                        $scholarsMatrix[$term_scholars[$k]][cooc][$term_scholars[$l]]+=1 / (log($term['occurrences']) * log($scholars[$term_scholars[$k]]['nb_keywords']));
                        ;
                    } else {
                        $scholarsMatrix[$term_scholars[$k]][cooc][$term_scholars[$l]] = 1 / (log($term['occurrences']) * log($scholars[$term_scholars[$k]]['nb_keywords']));
                        ;
                    }
                }
            }
        }
    }
        $nodeId = 'N::' . $term['id'];
        $nodeLabel = str_replace('&',' and ',$terms_array[$term['id']]['term']);
        $nodePositionY = rand(0, 100) / 100;
        $gexf.='<node id="' . $nodeId . '" label="' . $nodeLabel . '">' . "\n";
        $gexf.='<viz:color b="0" g="255"  r="0"/>' . "\n";
        $gexf.='<viz:position x="' . (rand(0, 100) / 100) . '"    y="' . $nodePositionY . '"  z="0" />' . "\n";
        $gexf.='<attvalues> <attvalue for="0" value="NGram"/>' . "\n";
        $gexf.='<attvalue for="1" value="'.$terms_array[$term['id']]['occurrences'].'"/>' . "\n";
        $gexf.='<attvalue for="4" value="'.$terms_array[$term['id']]['occurrences'].'"/>' . "\n";
        $gexf.='</attvalues></node>' . "\n";
        
    
}

foreach ($scholars as $scholar) {
        if (count($scholarsMatrix[$scholar['unique_id']]['cooc'])>1){
        $nodeId = 'D::' . $scholar['unique_id'];
        $nodeLabel = $scholar['title']. ' ' . $scholar['first_name'] . ' ' . $scholar['initials'] . ' ' . $scholar['last_name'];
        $nodePositionY = rand(0, 100) / 100;
        $content='';
        
        $content.='<b>Country: </b>'.$scholar['country'].'</br>';
        
        if ($scholar['position']!=null){        
            $content.='<b>Position: </b>'.str_replace('&', ' and ',$scholar['position']).'</br>';
        }
        $affiliation='';
        if ($scholar['lab']!=null){
            $affiliation.=$scholar['lab'].',';
        }
        if ($scholar['affiliation']!=null){
            $affiliation.=$scholar['affiliation'];
        }
        if(($scholar['affiliation']!=null)|($scholar['lab']!=null)){
        //$content.='<b>Affiliation: </b>'.str_replace('&', ' and ',$affiliation).'</br>';            
        }
        
        if (strlen($scholar['homepage'])>2){
            $content.='[ <a href='.str_replace('&', ' and ',$scholar['homepage']).' target=blank > View homepage </a >]';
        }
        pt($content);
        $gexf.='<node id="' . $nodeId . '" label="' . $nodeLabel . '">' . "\n";
        $gexf.='<viz:color b="255" g="0"  r="0"/>' . "\n";
        $gexf.='<viz:position x="' . (rand(0, 100) / 100) . '"    y="' . $nodePositionY . '"  z="0" />' . "\n";
        $gexf.='<attvalues> <attvalue for="0" value="Document"/>' . "\n";
        $gexf.='<attvalue for="1" value="10"/>' . "\n";
        $gexf.='<attvalue for="4" value="10"/>' . "\n";
        $gexf.='<attvalue for="2" value="'.htmlentities($content).'"/>' . "\n";
        $gexf.='</attvalues></node>' . "\n";
        }
}


$gexf.='</nodes><edges>' . "\n";
// écritude des liens
$edgeid = 0;

// ecriture des liens bipartite
foreach ($scholars as $scholar) {
    if (count($scholarsMatrix[$scholar['unique_id']]['cooc'])>1){        
    foreach ($scholar['keywords_ids'] as $keywords)     
    if ($keywords!=null){    
    $edgeid+=1;
    $gexf.='<edge id="' . $edgeid . '"' . ' source="D::' . $scholar['unique_id'] . '" ' .
            ' target="N::' . $keywords . '" weight="1">' . "\n";
    $gexf.='<attvalues> <attvalue for="5" value="1"' .
            '/><attvalue for="6" value="bipartite"/></attvalues>' . "\n" . '</edge>' . "\n";
}
}}


// ecriture des liens semantiques
//print_r($terms);
foreach ($terms_array as $term){
    $nodeId1=$term['id'];
    $neighbors=$termsMatrix[$nodeId1][cooc];       
    foreach ($neighbors as $neigh_id => $occ) {        
        if ($neigh_id!=$nodeId1) {
            $edgeid+=1;
            $gexf.='<edge id="'.$edgeid.'"'.' source="N::'.$nodeId1.'" '.
                    ' target="N::'.$neigh_id.'" weight="'.($occ/$term['occurrences']).'">'."\n";
            $gexf.='<attvalues> <attvalue for="5" value="'.($occ/$term['occurrences']).'"'.
                    '/><attvalue for="6" value="nodes2"/></attvalues>'."\n".'</edge>'."\n";
            
        }
    }
}

// ecriture des liens entre scholars
//print_r($terms);
foreach ($scholars as $scholar){
    $nodeId1=$scholar['unique_id'];
    $neighbors=$scholarsMatrix[$nodeId1][cooc];   
    foreach ($neighbors as $neigh_id => $occ) {        
        if ($neigh_id!=$nodeId1) {
            $edgeid+=1;
            $gexf.='<edge id="'.$edgeid.'"'.' source="D::'.$nodeId1.'" '.
                    ' target="D::'.$neigh_id.'" weight="'.$occ.'">'."\n";
            $gexf.='<attvalues> <attvalue for="5" value="'.$occ.'"'.
                    '/><attvalue for="6" value="nodes2"/></attvalues>'."\n".'</edge>'."\n";

        }
    }
}


$gexf.='</edges></graph></gexf>';

$gexfFile = fopen('../tinasoft.desktop/static/tinaweb/output.gexf', 'w');
fputs($gexfFile, $gexf);

fclose($gexfFile);
?>
