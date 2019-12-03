<?php

require 'elasticfind/elasticfind.php';

/* Load libraries for PHP composer */ 
require (__DIR__.'/../vendor/autoload.php'); 

/* Connect to Elasticsearch */
try {
    $client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build(); 
    //print("<pre>".print_r($client,true)."</pre>");
    $indexParams['index']  = $index;   
    $testIndex = $client->indices()->exists($indexParams);
} catch (Exception $e) {    
    $error_connection_message = '<div class="alert alert-danger" role="alert">Elasticsearch não foi encontrado. Favor executar o arquivo elasticsearch.lnk.</div>';
}

if (isset($testIndex) && $testIndex == false) {
    Elasticsearch::createIndex($index, $client);

    $mappingsParams = [
        'index' => $index,
        'body' => [
            'properties' => [
                'name' => [
                    'type' => 'text',
                    'analyzer' => 'portuguese',
                    'fields' => [
                        'keyword' => [
                            'type' => 'keyword',
                            'ignore_above' => 256
                        ]
                    ]
                ],
                'alternateName' => [
                    'type' => 'text',
                    'analyzer' => 'portuguese',
                    'fields' => [
                        'keyword' => [
                            'type' => 'keyword',
                            'ignore_above' => 256
                        ]
                    ]
                ],
                'nameOfpart' => [
                    'type' => 'text',
                    'analyzer' => 'portuguese',
                    'fields' => [
                        'keyword' => [
                            'type' => 'keyword',
                            'ignore_above' => 256
                        ]
                    ]
                ],
                'about' => [
                    'type' => 'text',
                    'analyzer' => 'portuguese',
                    'fields' => [
                        'keyword' => [
                            'type' => 'keyword',
                            'ignore_above' => 256
                        ]
                    ]
                ], 
                'genero_e_forma' => [
                    'type' => 'text',
                    'analyzer' => 'portuguese',
                    'fields' => [
                        'keyword' => [
                            'type' => 'keyword',
                            'ignore_above' => 256
                        ]
                    ]
                ],                                                       
                'datePublished' => [
                    'type' => 'integer'
                ]                                         
            ]
        ]
    ];

    Elasticsearch::mappingsIndex($index, $client, $mappingsParams);
}

    /* Definição de idioma */

    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        if (empty($_SESSION['localeToUse'])) {
            $_SESSION['localeToUse'] = Locale::acceptFromHttp($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }
    }
    else {
        if (empty($_SESSION['localeToUse'])) {
            $_SESSION['localeToUse'] = Locale::getDefault();
        }
    }

    if (!empty($_GET['locale'])) {
        $_SESSION['localeToUse'] = $_GET["locale"];
    } 
    
    
    use Gettext\Translator;

    //Create the translator instance
    $t = new Translator();
    
    if ($_SESSION['localeToUse'] == 'pt_BR') {
        $t->loadTranslations(__DIR__.'/../Locale/pt_BR/LC_MESSAGES/pt_BR.php');
    } else {
        $t->loadTranslations(__DIR__.'/../Locale/en_US/LC_MESSAGES/en.php');
    }



/**
 * Classe de funções da página inicial
 */
class paginaInicial {
    
    static function facet_inicio($field) 
    {
        global $type;
        $query = '{
            "aggs": {
                "group_by_state": {
                    "terms": {
                        "field": "'.$field.'.keyword",                    
                        "size" : 10
                    }
                }
            }
        }';
        $response = Elasticsearch::search(null, 0, $query);
        foreach ($response["aggregations"]["group_by_state"]["buckets"] as $facets) {
            echo '<li><a href="result.php?filter[]='.$field.':&quot;'.$facets['key'].'&quot;">'.$facets['key'].' ('.number_format($facets['doc_count'], 0, ',', '.').')</a></li>';
        }   

    }    
    
    static function ultimos_registros() 
    {
        global $index;
        $params = [];
        $params["index"] = $index;
        $params["size"] = 0;
        $query["query"]["bool"]["must"]["query_string"]["query"] = "*";
        $query["sort"]["_uid"]["unmapped_type"] = "long";
        $query["sort"]["_uid"]["missing"] = "_last";
        $query["sort"]["_uid"]["order"] = "desc";
        $query["sort"]["_uid"]["mode"] = "max";         
        $params["body"] = $query;   
        $response = Elasticsearch::search(null, 10, $query);

        foreach ($response["hits"]["hits"] as $r){
            echo '<article class="uk-comment">
            <header class="uk-comment-header uk-grid-medium uk-flex-middle" uk-grid>';    
            if (!empty($r["_source"]['unidadeUSP'])) {
                $file = 'inc/images/logosusp/'.$r["_source"]['unidadeUSP'][0].'.jpg';
            } else {
                $file = "";
            }
            if (file_exists($file)) {
            echo '<div class="uk-width-auto"><img class="uk-comment-avatar" src="'.$file.'" width="80" height="80" alt=""></div>';
            } else {

            };
            echo '<div class="uk-width-expand">';
            if (!empty($r["_source"]['name'])) {
                echo '<a href="http://dedalus.usp.br/F/?func=direct&doc_number='.$r['_id'].'" target="_blank"><h4 class="uk-comment-title uk-margin-remove">'.$r["_source"]['name'].'';
                if (!empty($r["_source"]['datePublished'])) {
                    echo ' ('.$r["_source"]['datePublished'].')';
                }         
                echo '</h4></a>';
            };
            echo '<ul class="uk-comment-meta uk-subnav uk-subnav-divider uk-margin-small">';
            if (!empty($r["_source"]['author'])) { 
                foreach ($r["_source"]['author'] as $autores) {
                    echo '<li><a href="result.php?filter[]=author.person.name:&quot;'.$autores["person"]["name"].'&quot;">'.$autores["person"]["name"].'</a></li>';
                }
                echo '</ul></div>';     
            };
            echo '</header>';
            echo '</article>';
        }
    }
}

/* Recupera os exemplares do DEDALUS */
function load_itens_single ($sysno) {
    $xml = simplexml_load_file('http://dedalus.usp.br/X?op=item-data&base=USP01&doc_number='.$sysno.'');
    if ($xml->error == "No associated items"){
    } else {
        echo "<h4>Exemplares físicos disponíveis nas Bibliotecas</h4>";
        echo "<table class=\"uk-table uk-table-hover uk-table-striped uk-table-condensed\">
                    <thead>
                      <tr>
                        <th>Biblioteca</th>                        
                        <th>Status</th>
                        <th>Número de chamada</th>";
                        if ($xml->item->{'loan-status'} == "A"){
                        echo "<th>Status</th>
                        <th>Data provável de devolução</th>";
                      } else {
                        echo "<th>Status</th>";
                      }
                      echo "</tr>
                    </thead>
                  <tbody>";
          foreach ($xml->item as $item) {
            echo '<tr>';
            echo '<td>'.$item->{'sub-library'}.'</td>';
            echo '<td>'.$item->{'item-status'}.'</td>';
            echo '<td>'.$item->{'call-no-1'}.'</td>';
            if ($item->{'loan-status'} == "A"){
            echo '<td>Emprestado</td>';
            echo '<td>'.$item->{'loan-due-date'}.'</td>';
          } else {
            echo '<td>Disponível</td>';
          }
            echo '</tr>';
          }
          echo "</tbody></table>";
          echo '<hr>';
          }
          flush();
  }

function gera_consulta_citacao($citacao) {
    $type = get_type($citacao["type"]);
    $author_array = array();
    foreach ($citacao["authors"] as $autor_citation){
        $array_authors = explode(',', $autor_citation);
        $author_array[] = '{"family":"'.$array_authors[0].'","given":"'.$array_authors[1].'"}';
    };
    $authors = implode(",",$author_array);
    if (!empty($citacao["ispartof"])) {
        $container = '"container-title": "'.$citacao["ispartof"].'",';
    } else {
        $container = "";
    };
    if (!empty($citacao["doi"])) {
        $doi = '"DOI": "'.$citacao["doi"][0].'",';
    } else {
        $doi = "";
    };
    if (!empty($citacao["url"])) {
        $url = '"URL": "'.$citacao["url"][0].'",';
    } else {
        $url = "";
    };
    if (!empty($citacao["publisher"])) {
        $publisher = '"publisher": "'.$citacao["publisher"].'",';
    } else {
        $publisher = "";
    };
    if (!empty($citacao["publisher_place"])) {
        $publisher_place = '"publisher-place": "'.$citacao["publisher_place"].'",';
    } else {
        $publisher_place = "";
    };
    $volume = "";
    $issue = "";
    $page_ispartof = "";
    if (!empty($citacao["ispartof_data"])) {
        foreach ($citacao["ispartof_data"] as $ispartof_data) {
            if (strpos($ispartof_data, 'v.') !== false) {
                $volume = '"volume": "'.str_replace("v.","",$ispartof_data).'",';
            } elseif (strpos($ispartof_data, 'n.') !== false) {
                $issue = '"issue": "'.str_replace("n.","",$ispartof_data).'",';
            } elseif (strpos($ispartof_data, 'p.') !== false) {
                $page_ispartof = '"page": "'.str_replace("p.","",$ispartof_data).'",';
            }
        }
    }
    $data = json_decode('{
    "title": "'.$citacao["title"].'",
    "type": "'.$type.'",
    '.$container.'
    '.$doi.'
    '.$url.'
    '.$publisher.'
    '.$publisher_place.'
    '.$volume.'
    '.$issue.'
    '.$page_ispartof.'
    "issued": {
    "date-parts": [
    [
    "'.$citacao["year"].'"
    ]
    ]
    },
    "author": [
    '.$authors.'
    ]
    }');
    
    return $data;    
    
}

/* Pegar o tipo de material */
function get_type($material_type){
  switch ($material_type) {
  case "ARTIGO DE JORNAL":
      return "article-newspaper";
      break;
  case "ARTIGO DE PERIODICO":
      return "article-journal";
      break;
  case "PARTE DE MONOGRAFIA/LIVRO":
      return "chapter";
      break;
  case "APRESENTACAO SONORA/CENICA/ENTREVISTA":
      return "interview";
      break;
  case "TRABALHO DE EVENTO-RESUMO":
      return "paper-conference";
      break;
  case "TRABALHO DE EVENTO":
      return "paper-conference";
      break;     
  case "TESE":
      return "thesis";
      break;          
  case "TEXTO NA WEB":
      return "post-weblog";
      break;
  }
}

?>
