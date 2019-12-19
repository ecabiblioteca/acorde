<?php

chdir('../'); 
require 'inc/config.php';
require 'inc/functions.php';
   
while ($record = fgets(STDIN)) {

    //print_r($record);
    $row = explode("\t", $record);

    foreach ($row as $key => $value) {
        if ($value == "000") {
            $rowNum["EID"] = $key;
        }
    //     if ($value == "DOI") {
    //         $rowNum["DOI"] = $key;
    //     }
        if ($value == "280") {
            $rowNum["title"] = $key;            
        }
        if ($value == "020") {
            $rowNum["Authors"] = $key;
        }        
    //     if ($value == "Source") {
    //         $rowNum["sourceTitle"] = $key;
    //     }
    //     if ($value == "Research Area") {
    //         $rowNum["about"] = $key;
    //     }         
    //     if ($value == "Volume") {
    //         $rowNum["Volume"] = $key;
    //     }
    //     if ($value == "Issue") {
    //         $rowNum["Issue"] = $key;
    //     }        
    //     if ($value == "Pages") {
    //         $pages = explode("-", $key);
    //         $rowNum["PageStart"] = $pages[0];
    //         if (isset($pages[1])) {
    //             $rowNum["PageEnd"] = $pages[1];
    //         } else {
    //             $rowNum["PageEnd"] = "N/D";
    //         }            
    //     }
    //     if ($value == "Publication Date") {
    //         $rowNum["year"] = $key;
    //     }
    //     if ($value == "Times Cited") {
    //         $rowNum["citations"] = $key;
    //     }
       

    //     // if ($value == "Language of Original Document") {
    //     //     $rowNum["language"] = $key;
    //     // }

    //     // if ($value == "ISSN") {
    //     //     $rowNum["ISSN"] = $key;
    //     // }
    //     // if ($value == "Publisher") {
    //     //     $rowNum["Publisher"] = $key;
    //     // }
    //     // if ($value == "Abstract") {
    //     //     $rowNum["Abstract"] = $key;
    //     // }
    //     // if ($value == "Funding Details") {
    //     //     $rowNum["FundingDetails"] = $key;
    //     // } 

    //     // if ($value == "References") {
    //     //     $rowNum["References"] = $key;
    //     // }
    //     // if ($value == "Author Keywords") {
    //     //     $rowNum["AuthorKeywords"] = $key;
    //     // }
    //     // if ($value == "Index Keywords") {
    //     //     $rowNum["IndexKeywords"] = $key;
    //     // }
    //     // if ($value == "Authors with affiliations") {
    //     //     $rowNum["AuthorsWithAffiliations"] = $key;
    //     // }
    //     // if ($value == "Affiliations") {
    //     //     $rowNum["Affiliations"] = $key;
    //     // }
    //     unset($pages);                                 
    }
    //print_r($rowNum);


    //while (($row = fgetcsv($record, 108192, "\t")) !== false) {
        $doc = Record::Build($row, $rowNum);   
        $sha256 = hash('sha256', ''.$doc["doc"]["name"].''.$doc["doc"]["source_id"].'');
        print_r($doc);
        if (!is_null($sha256)) {
            $result = Elasticsearch::update($sha256, $doc);
        }        
    //     print_r($result);
    //     //print_r($doc["doc"]["source_id"]);
    //     echo "<br/><br/><br/>";
    //     flush();        

    //}
}

//sleep(5);
//echo '<script>window.location = \'result.php?filter[]=type:"Work"&filter[]=tag:"'.$_POST["tag"].'"\'</script>';

class Record
{
    public static function build($row, $rowNum)
    {

        $doc["doc"]["source_id"] = $row[$rowNum["EID"]];

        $doc["doc"]["type"] = "Gravação";
        $doc["doc"]["source"] = "Base Sonora";
        $doc["doc"]["name"] = str_replace('"', '', $row[$rowNum["title"]]);
        
        
        
        // $doc["doc"]["datePublished"] = $row[$rowNum["year"]];
       
        // $doc["doc"]["tag"][] = $tag;
        // if ($row[$rowNum["DOI"]] != "n/a") {        
        //     $doc["doc"]["doi"] = $row[$rowNum["DOI"]];
        // }
        // if (isset($rowNum["language"])) {
        //     $doc["doc"]["language"] = $row[$rowNum["language"]];
        // }        
        // //$doc["doc"]["description"] = $row[$rowNum["Abstract"]];


        // $doc["doc"]["tipo"] = "Article";

        // $doc["doc"]["isPartOf"]["name"] = $row[$rowNum["sourceTitle"]];
        // $doc["doc"]["isPartOf"]["volume"] = $row[$rowNum["Volume"]];
        // $doc["doc"]["isPartOf"]["fasciculo"] = $row[$rowNum["Issue"]];
        // $doc["doc"]["pageStart"] = $row[$rowNum["PageStart"]];
        // if ($rowNum["PageEnd"] != "N/D") {
        //     $doc["doc"]["pageEnd"] = $row[$rowNum["PageEnd"]];
        // }        
        // //$doc["doc"]["isPartOf"]["issn"] = $row[$rowNum["ISSN"]];
        // //$doc["doc"]["publisher"]["organization"]["name"] = $row[$rowNum["Publisher"]];
        // $doc["doc"]["metrics"]["source"] = "Web of Science";
        // $doc["doc"]["metrics"]["citations"] = $row[$rowNum["citations"]];
        // $aboutArray = explode(",", $row[$rowNum["about"]]);
        // foreach ($aboutArray as $about) {
        //     $doc["doc"]["about"][] = strtoupper($about);
        // }              
        //$doc["doc"]["scopus"]["references"] = $row[$rowNum["References"]];        
        

        // Agência de fomento
        // $agencia_de_fomento_array = explode(";", $row[$rowNum["FundingDetails"]]);
        // $i_funder = 0;
        // foreach ($agencia_de_fomento_array as $funder) {
        //     $funderArray = explode(",", $funder);
        //     if (count($funderArray) > 2) {
        //         $doc["doc"]["funder"][$i_funder]["projectNumber"] = $funderArray[0];
        //         $doc["doc"]["funder"][$i_funder]["name"] = ''.$funderArray[2].' ('.$funderArray[1].')';
        //     } elseif (count($funderArray) > 1) {
        //         $doc["doc"]["funder"][$i_funder]["name"] = ''.$funderArray[1].' ('.$funderArray[0].')';
        //     } else {
        //         $doc["doc"]["funder"][$i_funder]["name"] = $funderArray[0];
        //     }            
        //     $i_funder++;
        // }

        // Palavras chave
        // $palavras_chave_authors = explode(";", $row[$rowNum["AuthorKeywords"]]);
        // $palavras_chave_scopus = explode(";", $row[$rowNum["IndexKeywords"]]);
        // $doc["doc"]["palavras_chave"] = array_merge($palavras_chave_authors, $palavras_chave_scopus);

        // Autores
        $authorsArray = explode("|", $row[$rowNum["Authors"]]);
        $i_aut=0;
        foreach ($authorsArray as $author) {
            $doc["doc"]["author"][$i_aut]["person"]["name"] = $author;
            $i_aut++;
        }
        $doc["doc_as_upsert"] = true;
        return $doc;



    }
}

?>


