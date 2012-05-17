<?php
///////////////////////////////////////////
/////////// Analyse des organisations //////
///////////////////////////////////////////

    
    pt("opening " . $orga_csv . ' delimiter should be set to ; and " ');
    if (($handle = fopen($orga_csv, "r", "UTF-8")) !== FALSE) {

        $la = array(); // liste des noms de colonne du csv
        $data = fgetcsv($handle, 1000, $file_sep);
        $count = 0;
        $label_list = array();
        $orga_terms_array = array(); // tableau pour remplir la table terms
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


            $subquery = $subquery . $label . ',';
            $la[$label] = $c;
            if ($label_list[$label] == 1) { // si le label existe déjà on lui colle un post_fix pour éviter les doublon de fields
                $label.='_2';
            }
            $label_list[$label] = 1;
            $query = $query . $label . ' text,';
            //pt($label);
        }

        // on analyse le csv
        $ngram_id = array();
        while (($data = fgetcsv($handle, 1000, $file_sep)) !== FALSE) {

            //pt($data[$la['Legal_name']]);
            if ($data[$la['Legal_name']] != NULL) {
                // analyse des mots clefs
                $count+=1;
                $orga= trim($data[$la['Legal_name']]);
                $lab_ngrams = '';
                $orga_ngrams_ids = '';
                $orga_ngrams_count = 0;
                $keywords = $data[$la['Key_words']];
                $keywords = str_replace(".", ', ', $keywords);
                $keywords = str_replace("-", ' ', $keywords);
                $ngrams = split('(,|;)', $keywords);

                foreach ($ngrams as $ngram) {
                    $ngram = str_replace("'", " ", trim($ngram));
                    if ((strlen($ngram) < 50) && (strlen($ngram) > 0)) {
                        $gram_array = split(' ', $ngram);
                        $ngram_stemmed = '';
                        if (count($gram_array) == 2) {
                            natsort($gram_array);
                        }
                        foreach ($gram_array as $gram) {
                            $ngram_stemmed.=stemword(trim(strtolower($gram)), $language, 'UTF_8') . ' ';
                        }
                        $ngram_stemmed = trim($ngram_stemmed);
                        if (array_key_exists($ngram_stemmed, $orga_terms_array)) {// si la forme stemmed du ngram a déjà été rencontrée
                            if (array_key_exists($ngram, $orga_terms_array[$ngram_stemmed])) {// si la forme pleine a déjà été rencontrée
                                $orga_terms_array[$ngram_stemmed][$ngram]+=1;
                            } else {
                                $orga_terms_array[$ngram_stemmed][$ngram] = 1;
                            }
                        } else {
                            $orga_terms_array[$ngram_stemmed][$ngram] = 1;
                            $ngram_id[$ngram_stemmed] = count($ngram_id) + 1;
                        }
                        $lab_ngrams.=$ngram . ',';
                        $orga_ngrams_ids.=$ngram_id[$ngram_stemmed] . ',';
                        $orga_ngrams_count+=1;
                        $query = "INSERT INTO orga2terms (orga,term_id) VALUES ('" . $orga. "'," . $ngram_id[$ngram_stemmed] . ")";
                        //pt($query);
                        $results = $base->query($query);
                    }
                }

                //$query = "CREATE TABLE labs (name text,acronym text,homepage text,
//    keywords text,country text,address text,organization text,object text,frameworks, text director
//    admin text, phone text,fax text,login text)";
               
                $object = '';
                if ($data[$la['Main_Fields']] != null) {
                    $fields.=$data[$la['Main_Fields']] . ', ';
                }
               
                $director = '';
                if ($data[$la['First_Name']] != null) {
                    $director.=$data[$la['Title']] . ' ' . $data[$la['First_Name']] . ' ' . $data[$la['Last_Name']];
                }

        
                //////////
                
                $orga_ngrams = str_replace("'", " ", $orga_ngrams); //       
                $query = 'INSERT INTO organizations (id, name,acronym,homepage, keywords,country,
                street,state,postal_code,city,fields,admin,login,phone,fax) VALUES 
                (' . $count
                        . ',"' . str_replace('"',"''",$orga)
                        . '","' . str_replace('"',"''",$data[$la["Acronym"]])
                        . '","' . $data[$la["Homepage"]]
                        . '","' . str_replace('"',"''",$keywords)
                        . '","' . $data[$la["Country"]]                                
                        . '","' . str_replace('"',"''",$data[$la["Street"]])                   
                        . '","' . $data[$la["State"]]
                        . '","' . $data[$la["Postal_code"]]
                        . '","' . $data[$la["City"]]
                        . '","' . $data[$la["Main_Fields"]]
                        . '","' . str_replace('"',"''",$data[$la["Name_of_administrative_contact"]])                        
                        . '","' . $data[$la["Contributor"]]
                        . '","' . $data[$la["Phone"]]
                        . '","' . $data[$la["Fax"]]
                        . '")';
                 $orga_array[]=$data[$la['Acronym']];                 
                //pt($query);
                $results = $base->query($query);
            }
        }
    }
    pt(count($orga_array).' organizations');
    ?>
