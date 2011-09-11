<?php

/*
 * Génère le gexf des scholars à partir de la base sqlite
 */


echo '<?xml version="1.0" encoding="UTF-8"?>';

$base = new PDO("sqlite:".$dbname);    


// Ecriture de l'entête du gexf

fputs($gexfFile,'<?xml version="1.0" encoding="UTF-8"?>');
fputs($gexfFile,'<gexf xmlns="http://www.gexf.net/1.1draft" xmlns:viz="http://www.gephi.org/gexf/viz" version="1.1"> ');
fputs($gexfFile, ' <meta lastmodifieddate="'.date('Y-m-d').'"> "\n"');
fputs($gexfFile,' </meta> "\n"');
fputs($gexfFile,'<graph type="static">'."\n");
fputs($gexfFile,'<attributes class="node" type="static">'."\n");
fputs($gexfFile,' <attribute id="0" title="category" type="string">  </attribute>'."\n");
fputs($gexfFile,' <attribute id="1" title="occurrences" type="float">    </attribute>'."\n");
fputs($gexfFile,' <attribute id="2" title="content" type="string">    </attribute>'."\n");
fputs($gexfFile,' <attribute id="3" title="keywords" type="string">   </attribute>'."\n");
fputs($gexfFile,' <attribute id="4" title="weight" type="float">   </attribute>'."\n");
fputs($gexfFile,'</attributes>'."\n");
fputs($gexfFile,'<attributes class="edge" type="float">'."\n");
fputs($gexfFile,' <attribute id="5" title="cooc" type="float"> </attribute>'."\n");
fputs($gexfFile,' <attribute id="6" title="type" type="string"> </attribute>'."\n");
fputs($gexfFile,"</attributes>"."\n");
fputs($gexfFile,"<nodes>"."\n");

// liste des chercheurs
$query = "SELECT * FROM scholars";
$scholars = sqlite_array_query($base,$query, SQLITE_ASSOC);


foreach ($scholars as $scholar) {
    // on en profite pour charger le profil sémantique du gars
    $query = "SELECT * FROM bipartite where scholar=".$scholar['unique_id'];
    $scholar_keywords = sqlite_array_query($base,$query, SQLITE_ASSOC);
    
    //ICI  Question : comment sont formatés les tableaux par sqlite_array_query
    //$scholars[$scholar]['keywords']=$scholar_keywords

    
    $nodeId='D::'.$scholar['unique_id'];    
    $nodeLabel=$scholar['first_name'].' '.$scholar['initials'].' '.$scholars['last_name'];
    $nodePositionY=  rand(0,100)/100;
    fputs($gexfFile,'<node id="'.$nodeId.'" label="'.$nodeLabel.'">'."\n");
    fputs($gexfFile,'<viz:color b="255" g="0"  r="0"/>'."\n");
    fputs($gexfFile,'<viz:position x="'.(rand(0,100)/100).'"    y="'.$nodePositionY.'"  z="0" />'."\n");
    fputs($gexfFile,'<attvalues> <attvalue for="0" value="Document"/>'."\n");
    fputs($gexfFile,'<attvalue for="1" value="10"/>'."\n");
    fputs($gexfFile,'<attvalue for="4" value="10"/>'."\n");
    fputs($gexfFile,'</attvalues></node>'."\n");
}

// liste des termes
$query = "SELECT * FROM terms";
$terms_list = sqlite_array_query($base,$query, SQLITE_ASSOC);


foreach ($terms_list as $term) {
    $nodeId=str_replace(' ','_', $term['term']);
    
    fputs($gexfFile,'<node id="N::'.$nodeId.'" label="'.trim($term['term']).'">'."\n");
    fputs($gexfFile,'<viz:color b="0" g="0"  r="255"/>'."\n");
    fputs($gexfFile,'<viz:position x="'.rand(0,3).'"    y="'.rand(0,3).'"  z="0" />'."\n");
    fputs($gexfFile,'<attvalues> <attvalue for="0" value="NGram"/>'."\n");
    fputs($gexfFile,'<attvalue for="1" value="'.$term['occurrences'].'"/>'."\n");
    fputs($gexfFile,'<attvalue for="4" value="'.$term['occurrences'].'"/>'."\n");
    fputs($gexfFile,'</attvalues></node>'."\n");
}

fputs($gexfFile,'</nodes><edges>'."\n");

$edgeid=1;
// ecriture des liens de filiation
for ($i=0;$i<count($fieldsList);$i++) {
    $nodeId1='D::'.str_replace(" ", "#", $fieldsList[$i][periode]).'_'.$fieldsList[$i][id];
    echo $nodeId1.'<br/>';
    // récupération des fils
    $sql='SELECT id_cluster_2_univ,strength FROM phylo WHERE id_cluster_1_univ='.$fieldsList[$i][id_cluster_univ];
    $resultat=mysql_query($sql) or die ("<b>Requête non exécutée (récupération des fils)</b>.");
    while ($ligne=mysql_fetch_array($resultat)) {
        $id_cluster_2_univ=$ligne[id_cluster_2_univ];
        $sql_cluster="select * FROM cluster WHERE id_cluster_univ=".$id_cluster_2_univ." GROUP BY id_cluster_univ";
        $clusterQuery=mysql_query($sql_cluster) or die ("<b>Requête non exécutée (récupération de clusters d'une partition)</b>.");
        while ($clusterLigne=mysql_fetch_array($clusterQuery)) {
            $nodeId2='D::'.str_replace(" ", "#", $clusterLigne[periode]).'_'.$clusterLigne[id_cluster];

             fputs($gexfFile,'<edge id="'.$edgeid.'"'.' source="'.$nodeId1.'" '.
                     ' target="'.$nodeId2.'" weight="'.$ligne[strength].'">'."\n");
             fputs($gexfFile,'<attvalues> <attvalue for="5" value="'.$ligne[strength].
                     '"/><attvalue for="6" value="node1"/></attvalues>'."\n".'</edge>'."\n");
             $edgeid+=1;
        }
    }
}

// ecriture des liens bipartite
echo 'Fields list'.count($fieldsList).'<br/>';
for ($i=0;$i<count($fieldsList);$i++) {
    $nodeId1='D::'.str_replace(" ", "#", $fieldsList[$i][periode]).'_'.$fieldsList[$i][id];
    echo 'bipart'.$nodeId1.'<br/>';
    // récupération des concepts
    $sql='SELECT concept FROM cluster WHERE id_cluster='.$fieldsList[$i][id].' AND periode="'.$fieldsList[$i][periode].'"';
    echo $sql;
    $resultat=mysql_query($sql) or die ("<b>Requête non exécutée (récupération des bipart)</b>.");
    while ($ligne=mysql_fetch_array($resultat)) {
        $id_concept=$ligne[concept];
        fputs($gexfFile,'<edge id="'.$edgeid.'"'.' source="'.$nodeId1.'" '.
                ' target="N::'.$id_concept.'" weight="1">'."\n");
        fputs($gexfFile,'<attvalues> <attvalue for="5" value="1"'.
                '/><attvalue for="6" value="bipartite"/></attvalues>'."\n".'</edge>'."\n");
        $edgeid+=1;
    }
}

// ecriture des liens semantiques
$terms=array_keys($termsList);
//print_r($terms);
for ($i=0;$i<count($terms);$i++) {
    $nodeId1=$terms[$i];
    echo 'node2 '.$nodeId1.'<br/>';
    $neighbors=$termsList[$nodeId1][cooc];
    $neighborsIds=array_keys($neighbors);
    for ($j=0;$j<count($neighborsIds);$j++) {
        if ($neighborsIds[$j]!=$nodeId1) {
            fputs($gexfFile,'<edge id="'.$edgeid.'"'.' source="N::'.$nodeId1.'" '.
                    ' target="N::'.$neighborsIds[$j].'" weight="'.$neighbors[$neighborsIds[$j]].'">'."\n");
            fputs($gexfFile,'<attvalues> <attvalue for="5" value="1"'.
                    '/><attvalue for="6" value="nodes2"/></attvalues>'."\n".'</edge>'."\n");
            $edgeid+=1;
        }
    }
}

fputs($gexfFile,'</edges> '."\n");
fputs($gexfFile,'</graph></gexf> '."\n");
 
fclose($gexfFile);

?>
