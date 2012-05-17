<?php

///////////////////////////////////////////
/////////// Analyse des laboratoires //////
///////////////////////////////////////////
    
    pt("opening " . $lab_csv . ' delimiter should be set to ; and " ');
    if (($handle = fopen($lab_csv, "r", "UTF-8")) !== FALSE) {

        $la = array(); // liste des noms de colonne du csv
        $data = fgetcsv($handle, 1000, $file_sep);
        $count = 0;
        $label_list = array();
        $lab_terms_array = array(); // tableau pour remplir la table terms
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

            //pt($data[$la['legal_name']]);
            if ($data[$la['legal_name']] != NULL) {
                // analyse des mots clefs
                $count+=1;
                $lab = trim($data[$la['legal_name']]);
                $lab_ngrams = '';
                $lab_ngrams_ids = '';
                $lab_ngrams_count = 0;
                $keywords = $data[$la['Keywords']];
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
                        if (array_key_exists($ngram_stemmed, $lab_terms_array)) {// si la forme stemmed du ngram a déjà été rencontrée
                            if (array_key_exists($ngram, $lab_terms_array[$ngram_stemmed])) {// si la forme pleine a déjà été rencontrée
                                $lab_terms_array[$ngram_stemmed][$ngram]+=1;
                            } else {
                                $lab_terms_array[$ngram_stemmed][$ngram] = 1;
                            }
                        } else {
                            $lab_terms_array[$ngram_stemmed][$ngram] = 1;
                            $ngram_id[$ngram_stemmed] = count($ngram_id) + 1;
                        }
                        $lab_ngrams.=$ngram . ',';
                        $lab_ngrams_ids.=$ngram_id[$ngram_stemmed] . ',';
                        $lab_ngrams_count+=1;
                        $query = "INSERT INTO labs2terms (labs,term_id) VALUES ('" . $lab . "'," . $ngram_id[$ngram_stemmed] . ")";
                        //pt($query);
                        $results = $base->query($query);
                    }
                }

                //$query = "CREATE TABLE labs (name text,acronym text,homepage text,
//    keywords text,country text,address text,organization text,object text,frameworks, text director
//    admin text, phone text,fax text,login text)";
                $org_name = '';
                if ($data[$la['Organization_this_lab_belong_to']] != null) {
                    $org_name.=$data[$la['Organization_this_lab_belong_to']];
                } elseif ($data[$la['Organizations_name_if_not_found_in_previous_list']] != null) {
                    $org_name.=$data[$la['Organizations_name_if_not_found_in_previous_list']];
                }

                $object = '';
                if ($data[$la['Objects_of_research']] != null) {
                    $object.=$data[$la['Objects_of_research']] . ', ';
                }
                if ($data[$la['Objects_of_research__free_response']] != null) {
                    $object.=$data[$la['Objects_of_research__free_response']];
                }

                $methods = '';
                if ($data[$la['Theoretical_framework']] != null) {
                    $methods.=$data[$la['Theoretical_framework']] . ', ';
                }
                if ($data[$la['Theoretical_framework__free_response']] != null) {
                    $methods.=$data[$la['Theoretical_framework__free_response']];
                }

                $director = '';
                if ($data[$la['First_Name']] != null) {
                    $director.=$data[$la['Title']] . ' ' . $data[$la['First_Name']] . ' ' . $data[$la['Last_Name']];
                }


                $lab_ngrams = str_replace("'", " ", $lab_ngrams); //       
                $query = 'INSERT INTO labs (id, name,acronym,homepage, keywords,country,
                address,organization,organization2,object,methods,director,admin, phone,fax,login) VALUES 
                (' . $count
                        . ',"' . str_replace('"',"''",$lab)
                        . '","' . str_replace('"',"''",$data[$la["Short_name"]])
                        . '","' . $data[$la["Homepage"]]
                        . '","' . $keywords
                        . '","' . $data[$la["Country"]]
                        . '","' . $data[$la["Address"]]
                        . '","' . $org_name
                        . '","' . $data[$la["Second_affiliation"]]
                        . '","' . str_replace('"',"''",str_replace("- Other,", " ", str_replace(".", ", ", str_replace("  ", " ", str_replace(",", ", ", $object)))))
                        . '","' . str_replace('"',"''",str_replace("- Other,", " ", str_replace(".", ", ", str_replace("  ", " ", str_replace(",", ", ", $methods)))))
                        . '","' . $data[$la["$director"]]
                        . '","' . $data[$la["Administrative_contact_first_and_last_name"]]
                        . '","' . $data[$la["phone"]]
                        . '","' . $data[$la["Fax"]]
                        . '","' . $data[$la["login_of_the_contributor"]]
                        . '")';
                $labs_array[]=$data[$la["Short_name"]];
                //pt($query);
                $results = $base->query($query);
                if (strcmp($data[$la['Short_name']], 'LIP6')==0){
                        pt($query);
                    }
                
            }
        }
    }
pt(count($labs_array).' labs');
?>
