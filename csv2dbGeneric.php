    <?php

    echo '<meta http-equiv="Content-type" content="text/html; charset=UTF-8"/>';
    /*
     * Importe un fichier csv et le mets en base sous forme de graphe
     */

   
    $logs = fopen('logs.txt', "w", "UTF-8");
    
    $scriptpath = dirname(__FILE__);

    include($scriptpath . "/csv2generic_param.php");
    echo $scriptpath."/../common/library/fonctions_php.php";
    include($scriptpath."/../common/library/fonctions_php.php");

    pt('custom stop word');
    $stop_word=explode(',','recherche,valorisation,développement, logique,analyse,logiciel,diffusion,espace,acteurs,international,informatique,dispositif,acquisition');

    
    pt('processing '.$nodes1.' and '.$nodes2.' in '.$language);

$regex = <<<'END'
/
  (
    (?: [\x00-\x7F]                 # single-byte sequences   0xxxxxxx
    |   [\xC0-\xDF][\x80-\xBF]      # double-byte sequences   110xxxxx 10xxxxxx
    |   [\xE0-\xEF][\x80-\xBF]{2}   # triple-byte sequences   1110xxxx 10xxxxxx * 2
    |   [\xF0-\xF7][\x80-\xBF]{3}   # quadruple-byte sequence 11110xxx 10xxxxxx * 3 
    ){1,100}                        # ...one or more times
  )
| .                                 # anything else
/x
END;


    if (true) {
        /* Creation des tables */

        $base = new PDO("sqlite:" . $dbname);
        echo 'creating tables<br/>';

        if ($drop_tables) {
            /* on efface les tables et on la recrée */

            $query = "DROP TABLE data;";
            $results = $base->query($query);

            $query = "DROP TABLE ".$nodes2.";";
            $results = $base->query($query);

            $query = "DROP TABLE ".$nodes1.";";
            $results = $base->query($query);

            $query = "DROP TABLE ".$nodes1."2".$nodes2.";";
            $results = $base->query($query);
            
            $query = "CREATE TABLE data (name text,content text)";
            $results = $base->query($query);

            $query = "CREATE TABLE ".$nodes2." (id integer,stemmed_key text,term text,variations text,occurrences integer)";
            $results = $base->query($query);

            $query = "CREATE TABLE ".$nodes1."2".$nodes2." (".$nodes1." text,".$nodes2."_id interger)";
            $results = $base->query($query);
        }

        global $data, $la;


        $row = 1;
        // on analyse le csv
        $ngram_id = array(); // ngram rencontrés tous types de données confondues
        $node1_count = 0;


        pt("opening " . $fichier . ' delimiter should be set to ' . $file_sep . ' and " ');
        if (($handle = fopen($fichier, "r", "UTF-8")) !== FALSE) {        


            /* On crée les entrées de la table avec la première ligne */
            $query = "CREATE TABLE " . $nodes1 . " (";
            $subquery = ""; /* partie de la requete pour alimenter la base plus bas */
            $la = array(); // liste des noms de colonne du csv
            $data = fgetcsv($handle, 1000, $file_sep);
            $count = 0;
            $label_list = array();
            $terms_array = array(); // tableau pour remplir la table terms
            // on prépare les colonnes pour la table de données brutes
            $num = count($data);
            pt("number of columns: " . $num);
            for ($c = 0; $c < $num; $c++) {
                $temp = split('--', $data[$c]);
                $label = str_replace(' ', '_', trim($temp[0]));
                $label = str_replace(':', '', $label);
                $label = str_replace("'", '', $label);
                $label = str_replace("?", '', $label);
                $label = str_replace("-", '_', $label);
                $label = str_replace(",", '_', $label);
                $label = str_replace("(", '', $label);
                $label = str_replace(")", '', $label);


                if (trim($label)!=null){
                $subquery = $subquery . $label . ',';
                $la[$label] = $c;
                if ($label_list[$label] == 1) { // si le label existe déjà on lui colle un post_fix pour éviter les doublon de fields
                    $label.='_2';
                }
                $label_list[$label] = 1;
                $query = $query . $label . ' text,';
    
                }
                
                //pt($label);
            }
            pt('processing '.$data[$la[$nodes1]]);
            $query = substr(trim($query), 0, -1);
            $query = $query . ')';

            $subquery = substr(trim($subquery), 0, -1);
            pt("sous requete: " . $subquery);
            pt("Creating table with : " . $query);
            $results = $base->query($query);   
            // white list

            $white_list = array();
            
            while (($data = fgetcsv($handle, 1000, $file_sep)) !== FALSE) {                
                if (true){
                // analyse des mots clefs
                $node1_count+=1;
                $node1 = $data[$la[$nodes1_id]];
                
                $node1_ngrams = '';
                $node1_ngrams_ids = '';
                $node1_ngrams_count = 0;

                $keywords = $data[$la[$nodes2_name]];
                $keywords = str_replace(".", '', $keywords);
                //$keywords = str_replace("-", ' ', $keywords);
                //$ngrams = split('(,|;|/|\|)', $keywords);
                $ngrams = split('(,|;)', $keywords);                
              
                if (strlen($keywords)>4){
                    $score+=1;
                    //pt('score for keywords');
                }

                foreach ($ngrams as $ngram) {
                    $ngram = str_replace("'", "\'", trim($ngram));
                    $normalized_ngram=str_replace("-", ' ', $ngram);
                    $normalized_ngram2=str_replace("-", '', $ngram); // pour regrouper les termes du type co-evolution/coevolution

                    if (!in_array(strtolower(trim($ngram)),$stop_word)){
                    if ((strlen($ngram) < 50) && (strlen($ngram) > 0)) {
                        
                        
                        //
                        $gram_array = split(' ', $normalized_ngram);
                        $ngram_stemmed = '';
                        if (count($gram_array) >= 2) {
                            natsort($gram_array);
                        }
                        foreach ($gram_array as $gram) {
                            $ngram_stemmed.=stemword(trim(strtolower($gram)), $language, 'UTF_8') . ' ';
                        }
                        
                        $ngram_stemmed = trim($ngram_stemmed);
                        //
                        $gram_array2 = split(' ', $normalized_ngram2);
                        $ngram_stemmed2 = '';
                        if (count($gram_array2) >= 2) {
                            natsort($gram_array2);
                        }
                        foreach ($gram_array2 as $gram) {
                            $ngram_stemmed2.=stemword(trim(strtolower($gram)), $language, 'UTF_8') . ' ';
                        }
                        $ngram_stemmed2 = trim($ngram_stemmed2);
                        //                    
                                
                        if ((array_key_exists($ngram_stemmed, $terms_array)) || (array_key_exists($ngram_stemmed2, $terms_array))) {// si la forme stemmed du ngram a déjà été rencontrée                        
                                if (array_key_exists($ngram_stemmed2, $terms_array)) {
                                    //pt($ngram_stemmed2 . ' already exist');
                                    if (array_key_exists($ngram, $terms_array[$ngram_stemmed2])) {// si la forme pleine a déjà été rencontrée
                                        $terms_array[$ngram_stemmed2][$ngram]+=1;
                                    } else {
                                        $terms_array[$ngram_stemmed2][$ngram] = 1;
                                    }
                                } else {
                                    //pt($ngram_stemmed . ' already exist');
                                    if (array_key_exists($ngram, $terms_array[$ngram_stemmed])) {// si la forme pleine a déjà été rencontrée
                                        $terms_array[$ngram_stemmed][$ngram]+=1;
                                    } else {
                                        $terms_array[$ngram_stemmed][$ngram] = 1;
                                    }
                                }
                            } else {                        
                            $terms_array[$ngram_stemmed2][$ngram] = 1;
                            $ngram_id[$ngram_stemmed2] = count($ngram_id) + 1;
                        }
                        $node1_ngrams.=$ngram . ',';
                        $node1_ngrams_ids.=$ngram_id[$ngram_stemmed] . ',';
                        $node1_ngrams_count+=1;
                        $query = "INSERT INTO ".$nodes1."2".$nodes2." (".$nodes1.",".$nodes2."_id) VALUES ('" . $node1 . "'," . $ngram_id[$ngram_stemmed] . ")";

                        //pt($query);                        
                        $results = $base->query($query);                        
                    }    
                    }
                    
                }                
            $node1_ngrams = str_replace("'", " ", $node1_ngrams); //
                    
                $num = count($data);
                $row++;
      

                $values = '"' . $data[0] . '"';
                for ($c = 1; $c < $num; $c++) {
                    if (($c==$la[html])){
                        $values = $values . ',"' . htmlentities(preg_replace($regex, '$1', $data[$c]), ENT_QUOTES) . '"';
                    }else{
                        $values = $values . ',"' . str_replace('"', '', preg_replace($regex, '$1', $data[$c])) . '"';    
                    }
                    
                }



                $query = 'INSERT INTO ' . $nodes1 . '(' . $subquery . ') VALUES (' . $values . ')';
                db($query);
                fputs($logs,$query.PHP_EOL);
                /* $query = "INSERT INTO $node1s_db(ID, post_title, post_content, post_author, post_date, guid) 
                  VALUES ('$number', '$title', '$content', '$author', '$date', '$url')"; */
                $results = $base->query($query);                       
            }
            }
        }


        pt($node1_count .' '.$nodes1. ' processed');
 
        $id = 0;
        //pt('inserting terms ' . count($terms_array));
        $stemmed_ngram_list = array_keys($terms_array);
        for ($i = 0; $i < count($stemmed_ngram_list); $i++) {
        //foreach ($terms_array as $stemmed_ngram -> $ngram_forms){
            $stemmed_ngram = $stemmed_ngram_list[$i];
            $ngram_forms = $terms_array[$stemmed_ngram];
            $ngram_forms_list = array_keys($ngram_forms);
            //pt($id);
            $most_common_form = array_search(max($ngram_forms), $ngram_forms);
            //pt($most_common_form);
            $most_common_form = str_replace('"', '', $most_common_form);
            $variantes = array_keys($ngram_forms);
            $variations = implode('***', $variantes);
            //$query = "INSERT INTO terms (id,stemmed_key,term,variations,occurrences) VALUES ('".$id."','".$stemmed_ngram."','".$most_common_form."','".$variations."','".sum($ngram_forms).")";        

            $query = "INSERT INTO ".$nodes2." (id,stemmed_key,term,variations,occurrences) VALUES ('" . $ngram_id[$stemmed_ngram] . "','" . $stemmed_ngram . "','" . $most_common_form . "','" . $variations . "'," . array_sum($ngram_forms) . ")";
            //pt($query);
            $results = $base->query($query);

            //on ralonge la white liste
            foreach ($ngram_forms_list as $form) {
                $white_list[$form] = array('stemmed' => $stemmed_ngram, 'main_form' => $most_common_form);
            }
        }



        $query = "INSERT INTO data (name,content) VALUES ('whitelist','" . $white_list_serialized . "')";
        $results = $base->query($query);


    //////////////////////
    }
    fclose($handle);

    function merge($fieldlist, $sep = ', ') {
        global $data, $la;
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

    function section($name, $content, $sep = ', ') {
        /* merge les champs dans $fieldlist séparés par des virgules en vérifiant qu'ils sont non vides */
        global $data, $la;

        $string = '';
        $temp = merge($content, $sep);
        if ($temp != null) {
            $string = '' . $name . ': ' . $temp . '';
        }
        return $string;
    }

    function db($text) {
    
        return pt($text);
    }
    

    
    ?>

