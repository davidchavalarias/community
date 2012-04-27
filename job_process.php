<?php
///////////////////////////////////////////
/////////// Analyse des organisations //////
///////////////////////////////////////////

    
    pt("opening " . $jobs_csv . ' delimiter should be set to ; and " ');
    pt('second ngram');
    if (($handle = fopen($jobs_csv, "r", "UTF-8")) !== FALSE) {

        $la = array(); // liste des noms de colonne du csv
        $data = fgetcsv($handle, 1000, $file_sep);
        $count = 0;
        $label_list = array();
        $job_terms_array = array(); // tableau pour remplir la table terms
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
            pt($label);
        }

        // on analyse le csv
        while (($data = fgetcsv($handle, 1000, $file_sep)) !== FALSE) {

            pt($data[$la['Title']]);
            if ($data[$la['Title']] != NULL) {
                // analyse des mots clefs
                $count+=1;
                $job= trim($data[$la['Title']]);
                $job_ngrams = '';
                $job_ngrams_ids = '';
                $job_ngrams_count = 0;
                $keywords = $data[$la['Keywords']];
                pt($keywords );
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
                    if (array_key_exists($ngram_stemmed, $ngram_id)) {// si la forme stemmed du ngram a déjà été rencontrée
                        pt($ngram_stemmed);
                        $job_ngrams.=$ngram . ',';
                        $job_ngrams_ids.=$ngram_id[$ngram_stemmed] . ',';
                        $job_ngrams_count+=1;
                        $query = "INSERT INTO jobs2terms (job_id,term_id)  VALUES ('" . $data[$la['itemId']] . "'," . $ngram_id[$ngram_stemmed] . ")";                        
                        pt($query);
                        $results = $base->query($query);
                    }
                }
            }

            //$query = "CREATE TABLE labs (name text,acronym text,homepage text,
//    keywords text,country text,address text,organization text,object text,frameworks, text director
//    admin text, phone text,fax text,login text)";
                             
        
                //////////
                
                $job_ngrams = str_replace("'", " ", $job_ngrams); //       
                $query = 'INSERT INTO jobs (id, title,position,lab,organization,keywords,country,
                start_date,deadline,url,login) VALUES 
                (' . $data[$la["itemId"]] 
                        . ',"' . str_replace('"',"''",$job)
                        . '","' . $data[$la["Position"]]
                        . '","' . str_replace('"',"''",$data[$la["Lab"]])
                        . '","' . str_replace('"',"''",$data[$la["Organization"]])
                        . '","' . str_replace('"',"''",$keywords)
                        . '","' . $data[$la["Country"]]                    
                        . '","' . $data[$la["Start_date"]]
                        . '","' . $data[$la["Deadline"]]
                        . '","' . $data[$la["URL"]]
                        . '","' . str_replace("@", "[at]", $data[$la["Login"]])
                        . '")';

                 $job_array[]=$job;                 
                pt($query);
                $results = $base->query($query);
            }
        }
    }
    ?>
