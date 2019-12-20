<?php

chdir('../'); 
require 'inc/config.php';
require 'inc/functions.php';
   
while ($record = fgets(STDIN)) {

    $row = explode("\t", $record);

    foreach ($row as $key => $value) {
        if ($value == "000") {
            define("EID", $key);
        }
        if ($value == "130") {
            define("MEIODEEXPRESSAO", $key);            
        }
        if ($value == "280") {
            define("TITLE", $key);            
        }
        if ($value == "290") {
            define("ALTERNATENAME", $key);            
        }        
        if ($value == "295") {
            define("ISPARTOFTITLE", $key);            
        }        
        if ($value == "020") {
            define("AUTHORS", $key);
        }
        if ($value == "340") {
            define("DATEPUBLISHED", $key);
        }
        if ($value == "330") {
            define("PUBLISHER", $key);
        }
        if ($value == "420") {
            define("GENEROEFORMA", $key);            
        }           
                                
    }

    $doc = Record::Build($row);
    if ($doc["doc"]["name"] == "280") {
        $sha256 = null;
    } else {
        $sha256 = hash('sha256', ''.$doc["doc"]["name"].''.$doc["doc"]["source_id"].'');
    }
    //print_r($doc);
    if (!is_null($sha256)) {
        $result = Elasticsearch::update($sha256, $doc);
    }        

}

class Record
{
    public static function build($row)
    {

        $doc["doc"]["source_id"] = $row[EID];

        $doc["doc"]["source"] = "Base Sonora";

        if (empty($row[TITLE])) {
            $doc["doc"]["type"] = "Disco";
            $ispartoftitle = explode("$", $row[ISPARTOFTITLE]);
            $doc["doc"]["name"] = str_replace('"', '', $ispartoftitle[0]);
        } else {
            $doc["doc"]["type"] = "Gravação";
            $title = explode("$", $row[TITLE]);
            $doc["doc"]["name"] = str_replace('"', '', $title[0]);
        }

        if (!empty($row[ALTERNATENAME])) {
            $alternateName = explode("$", $row[ALTERNATENAME]);
            $doc["doc"]["alternateName"] = str_replace('"', '', $alternateName[0]);
        }
        
        if (!empty($row[MEIODEEXPRESSAO])) {
            $doc["doc"]["USP"]["meio_de_expressao"] = explode("|", $row[MEIODEEXPRESSAO]);
        }

        if (!empty($row[GENEROEFORMA])) {
            $doc["doc"]["USP"]["about"]["genero_e_forma"] = explode("|", $row[GENEROEFORMA]);
        }
        
        $doc["doc"]["datePublished"] = $row[DATEPUBLISHED];
       
        $doc["doc"]["publisher"]["organization"]["name"] = explode("|", $row[PUBLISHER]);

        // Autores
        if (!empty($row[AUTHORS])) {
            $authorsArray = explode("|", $row[AUTHORS]);
            $i_aut=0;
            foreach ($authorsArray as $author) {
                $doc["doc"]["author"][$i_aut]["person"]["name"] = $author;
                $i_aut++;
            }
            unset($authorsArray);
        } else {
            unset($doc["doc"]["author"]);
        }
        $doc["doc_as_upsert"] = true;        
        return $doc;
        unset($doc);
        unset($i_aut);



    }
}

?>


