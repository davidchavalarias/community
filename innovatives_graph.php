<?php
echo '<meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>';
/*
 * Génère le gexf bipartite des noeud1 et 2 à partir de la base sqlite
 */

include("csv2generic_param.php");
include("../common/library/fonctions_php.php");

$gexf = '<?xml version="1.0" encoding="UTF-8"?>';

$base = new PDO("sqlite:" . $dbname);

$termsMatrix = array(); // liste des termes présents chez les scholars avec leurs cooc avec les autres termes
$scholarsMatrix = array(); // liste des scholars avec leurs cooc avec les autres termes
$scholarsIncluded=0;

// on récupère les paramètres
//$scholar_filter='';
//if(isset( $_POST['labs'])){
//        $labs=$_POST['labs'];    
//        $scholar_filter.='(';        
//        foreach($labs as $labname){
//            $scholar_filter.='lab='.$value.' OR';            
//        }
//        if (count($labs)>1){
//            $scholar_filter=substr($scholar_filter,1,-2);            
//        }
//        $scholar_filter.=')';
//    } 
//
//    

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
$gexf.=' <attribute id="5" title="stand" type="string">   </attribute>' . "\n";
$gexf.='</attributes>' . "\n";
$gexf.='<attributes class="edge" type="float">' . "\n";
$gexf.=' <attribute id="5" title="cooc" type="float"> </attribute>' . "\n";
$gexf.=' <attribute id="6" title="type" type="string"> </attribute>' . "\n";
$gexf.="</attributes>" . "\n";
$gexf.="<nodes>" . "\n";

// liste des chercheurs
$sql = "SELECT * FROM $nodes1 ";
pt($sql);


$scholars = array();
foreach ($base->query($sql) as $row) {

    $info = array();
    foreach ($row as $key => $value) {
        $info[$key] = $value;
    }
    //pta($row);
    // on fait la liste des noeud2 liés
    $nodes1_id_list='';
    $sql2="SELECT * FROM ".$nodes1."2".$nodes2." Where $nodes1=".$info[$nodes1_id]; //'SELECT '.$nodes2.'_id FROM projets2keywords'.$nodes1.'2'.$nodes2;//.' WHERE '.$nodes1.'='.$row[$nodes1_id];
    pt($sql2);
    foreach ($base->query($sql2) as $line) {
        $nodes1_id_list.=$line[$nodes2.'_id'].', ';        
    }
    $info['keywords_ids']=explode(',',substr(trim($nodes1_id_list), 0, -1)); // liste des indices des noeuds 2 associés

    $scholars[$info[$nodes1_id]] = $info;

}
foreach ($scholars as $scholar) {
    // on en profite pour charger le profil sémantique du gars
    $scholar_keywords = $scholar['keywords_ids'];        
    pta($scholar_keywords);
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
pt('terms cooc');
pta($termsMatrix);
// liste des termes
$sql = "SELECT term,id,occurrences FROM ".$nodes2;
$term_array = array();
//$query = "SELECT * FROM scholars";
foreach ($base->query($sql) as $row) {
  //  if ($termsMatrix[$row['id']]!= null){ // on prend que les termes sont mentionnés par les chercheurs filtrés
    $info = array();
    $info['id'] = $row['id'];
    $info['occurrences'] = $row['occurrences'];
    $info['term'] = $row['term'];
    $terms_array[$row['id']] = $info;
//}
}
//pt('terms array');
//pta($terms_array);

$count = 1;

foreach ($terms_array as $term) {
        $count+=1;
        // on en profite pour charger le profil scholar du term
        $query = "SELECT ".$nodes1." FROM ".$nodes1."2".$nodes2." where ".$nodes2."_id='" . $term['id'] . "'";
        //pt($query);
        $term_scholars = array();
        foreach ($base->query($query) as $row) {
            $term_scholars[] = $row[$nodes1]; // ensemble des scholars partageant ce term
        }
        // on en profite pour construire le réseau des scholars partageant les mêmes termes 
    for ($k = 0; $k < count($term_scholars); $k++) {
        if ($scholarsMatrix[$term_scholars[$k]] != null) {
            $scholarsMatrix[$term_scholars[$k]]['occ'] = $scholarsMatrix[$term_scholars[$k]]['occ'] + 1;
            for ($l = 0; $l < count($term_scholars); $l++) {
                if (array_key_exists($term_scholars[$l], $scholars)) {
                    if ($scholarsMatrix[$term_scholars[$k]]['cooc'][$term_scholars[$l]] != null) {
                        $scholarsMatrix[$term_scholars[$k]]['cooc'][$term_scholars[$l]] += 1;
                    } else {
                        $scholarsMatrix[$term_scholars[$k]]['cooc'][$term_scholars[$l]] =1;
                    }
                }
            }
        } else {
            $scholarsMatrix[$term_scholars[$k]]['occ'] = 1;
            for ($l = 0; $l < count($term_scholars); $l++) {
                if (array_key_exists($term_scholars[$l], $scholars)) {
                    if ($scholarsMatrix[$term_scholars[$k]]['cooc'][$term_scholars[$l]] != null) {
						$scholarsMatrix[$term_scholars[$k]]['cooc'][$term_scholars[$l]] += 1;
					} else {
						$scholarsMatrix[$term_scholars[$k]]['cooc'][$term_scholars[$l]] = 1;
					}
				}
			}
		}
	}

    
        
        
    
}


foreach ($scholars as $scholar) {
        //pt($scholar['unique_id']. '-'.count($scholarsMatrix[$scholar['unique_id']]['cooc']));
        if (count($scholarsMatrix[$scholar[$nodes1_id]]['cooc'])>$min_num_friends){// > car chacun est son propre ami
            $scholarsIncluded+=1;
        $nodeId = 'D::' . $scholar[$nodes1_id];
        pt($scholar[$nodes1_acronym]);
        pt(strlen($scholar[$nodes1_acronym]));
        if (strlen($scholar[$nodes1_acronym])>12){
            $nodeLabel = substr($scholar[$nodes1_acronym], 0,12)."...";
        }else{
            $nodeLabel = $scholar[$nodes1_acronym];
        }
        pt($nodeLabel);
        $nodePositionY = rand(0, 100) / 100;
        $content=$scholar[$descriptif];
        
        
        
   
        //pt($scholar['last_name'].','.$scholar['css_voter'].','.$scholar['css_member']);
        //pt($color);        
        //pt($content);
        if(is_utf8($nodeLabel)){
        $gexf.='<node id="' . $nodeId . '" label="' . $nodeLabel . '">' . "\n";
        $gexf.='<viz:color b="200" g="0"  r="0"/>' . "\n";
        $gexf.='<viz:position x="' . (rand(0, 100) / 100) . '"    y="' . $nodePositionY . '"  z="0" />' . "\n";
        $gexf.='<attvalues> <attvalue for="0" value="Document"/>' . "\n";
        if (true){
        $gexf.='<attvalue for="1" value="12"/>' . "\n";
        $gexf.='<attvalue for="4" value="12"/>' . "\n";
            
        }else{
        $gexf.='<attvalue for="1" value="10"/>' . "\n";
        $gexf.='<attvalue for="4" value="10"/>' . "\n";
            
        }
        if(is_utf8($content)){
            $gexf.='<attvalue for="2" value="'.htmlspecialchars($content).'"/>' . "\n";
        }
        $gexf.='</attvalues></node>' . "\n";
        }        
        }
        
}


$gexf.='</nodes><edges>' . "\n";
// écritude des liens
$edgeid = 0;

// ecriture des liens bipartite


// ecriture des liens entre scholars
//print_r($terms);
foreach ($scholars as $scholar){
    $nodeId1=$scholar[$nodes1_id];
    $neighbors=$scholarsMatrix[$nodeId1]['cooc'];   
    foreach ($neighbors as $neigh_id => $cooc) {        
        if ($neigh_id>$nodeId1) {
            $weight=jaccard($scholarsMatrix[$nodeId1]['occ'],$scholarsMatrix[$neigh_id]['occ'],$cooc);
            $edgeid+=1;
            $gexf.='<edge id="'.$edgeid.'"'.' source="D::'.$nodeId1.'" '.
                    ' target="D::'.$neigh_id.'" weight="'.$weight.'">'."\n";
            $gexf.='<attvalues> <attvalue for="5" value="'.$weight.'"'.
                    '/><attvalue for="6" value="nodes2"/></attvalues>'."\n".'</edge>'."\n";

        }
    }
}


$gexf.='</edges></graph></gexf>';

$gexfFile = fopen('innovatives.gexf', 'w');
fputs($gexfFile, $gexf);

fclose($gexfFile);

pt(count($scholarsMatrix).' scholars');
pt($scholarsIncluded.' scholars included');
pt(count($termsMatrix).' terms');

function jaccard($occ1,$occ2,$cooc){   
    if (($occ1==0)||($occ2==0)){
        return 0;        
    }else{
        return ($cooc*$cooc/($occ1*$occ2));        
    }
}

function scholarlink($term_occurrences,$scholars1_nb_keywords,$scholars2nb_keywords){
    pt('terms='.$term_occurrences);
    pt('schol1='.$scholars1_nb_keywords);
    pt('schol2='.$scholars2_nb_keywords);
    if (($term_occurrences==0)||($scholars1_nb_keywords==0)||($scholars2_nb_keywords==0)){
        pt('link=0');
        return 0;        
    }else {
        pt('link='.(1/log($term_occurrences)*1/log($scholars1_nb_keywords)*1/$scholars2_nb_keywords));
        return 1/log($term_occurrences)*1/log($scholars1_nb_keywords)*1/$scholars2_nb_keywords;    
    }
    
    }     
?>
