<?php

function normalize_position($value) {
   $value = strtolower($value);

   // global normalization
   $value=str_replace('.','', $value);

   // phd
   $value=str_replace('professor', 'prof', $value);
   $value=str_replace('profesor', 'prof', $value);
   $value=str_replace('proffesor', 'prof', $value);
   
   $value=str_replace('laboratory', ' lab', $value);

   // final normalization
   $value=str_replace('phd student', 'phd', $value);
   
   $value=str_replace('doctor', 'dr', $value);

   // phd
   $value=str_replace('prof', 'professor', $value);
   
   $value=str_replace('head of lab', 'head of laboratory', $value);

   $value=str_replace('research officer', 'research assistant', $value);
   $value=str_replace('assistant researcher', 'research assistant', $value);

   // final normalization
   $value=str_replace('phd', 'phd student', $value);

   $value=str_replace('dr', 'doctor', $value);
   
   $value = preg_replace('/\b(\w)/e', 'strtoupper("$1")', $value);
   
   return $value;
}


?>
