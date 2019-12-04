<!DOCTYPE html>
<?php

require 'inc/config.php'; 
require 'inc/functions.php';

if (isset($fields)) {
    $_GET["fields"] = $fields;
}
$result_get = Requests::getParser($_GET);
$limit = $result_get['limit'];
$page = $result_get['page'];
$params = [];
$params["index"] = $index;
$params["body"] = $result_get['query'];
$cursorTotal = $client->count($params);
$total = $cursorTotal["count"];
if (isset($_GET["sort"])) {
    $result_get['query']["sort"][$_GET["sort"]]["unmapped_type"] = "long";
    $result_get['query']["sort"][$_GET["sort"]]["missing"] = "_last";
    $result_get['query']["sort"][$_GET["sort"]]["order"] = "desc";
    $result_get['query']["sort"][$_GET["sort"]]["mode"] = "max";
} else {
    // $result_get['query']['sort']['datePublished']['order'] = "desc";
    // $result_get['query']["sort"]["_uid"]["unmapped_type"] = "long";
    // $result_get['query']["sort"]["_uid"]["missing"] = "_last";
    // $result_get['query']["sort"]["_uid"]["order"] = "desc";
    // $result_get['query']["sort"]["_uid"]["mode"] = "max";
}
$params["body"] = $result_get['query'];
$params["size"] = $limit;
$params["from"] = $result_get['skip'];
$cursor = $client->search($params);

/*pagination - start*/
$get_data = $_GET;    
/*pagination - end*/      

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <?php
            include('inc/meta-header-new.php'); 
        ?>        
        <title>Lattes USP - Resultado da busca por trabalhos</title>
        
    </head>
    <body>

        <!-- NAV -->
        <?php require 'inc/navbar-new.php'; ?>
        <!-- /NAV -->
        <br/><br/><br/><br/>

        <main role="main">
            <div class="container">

            <div class="row">
                <div class="col-8">    

                    <!-- Navegador de resultados - Início -->
                    <?php ui::pagination($page, $total, $limit); ?>
                    <!-- Navegador de resultados - Fim -->   

                    <?php foreach ($cursor["hits"]["hits"] as $r) : ?>

                        <?php //print_r($r); ?>
                        <?php if (empty($r["_source"]['datePublished'])) {
                            $r["_source"]['datePublished'] = "";
                        }
                        ?>

                        <div class="card">
                            <div class="card-body">

                                <h6 class="card-subtitle mb-2 text-muted"><?php echo $r["_source"]['type'];?></h6>
                                <h5 class="card-title text-dark"><a href="http://dedalus.usp.br/F/?func=direct&doc_number=<?php echo  $r['_id'];?>" target="_blank"><?php echo $r["_source"]['name']; ?> (<?php echo $r["_source"]['datePublished'];?>)</a></h5>
                                <?php if (!empty($r["_source"]['alternateName'])) : ?>
                                    <p class="text-muted"><b>Título original:</b> <?php echo $r["_source"]['alternateName'];?></p>
                                <?php endif; ?>
                                <?php if (!empty($r["_source"]['nameOfpart'])) : ?>
                                    <p class="text-muted"><b>Título específico:</b> <?php echo implode(" ", $r["_source"]['nameOfpart']);?></p>
                                <?php endif; ?>


                                <?php if (!empty($r["_source"]['author'])) : ?>
                                    <p class="text-muted"> 
                                    <?php foreach ($r["_source"]['author'] as $authors) {
                                        if (!empty($authors["person"]["date"])) {
                                            $author_date = ' - ' . $authors["person"]["date"];
                                        } else {
                                            $author_date = "";
                                        }

                                        if (!empty($authors["person"]["potentialAction"])) {
                                            $authors_array[]='<a href="result.php?search[]=author.person.name.keyword:&quot;'.$authors["person"]["name"].'&quot;">'.$authors["person"]["name"].$author_date.' ('.$authors["person"]["potentialAction"].')</a>';
                                        } else {
                                            $authors_array[]='<a href="result.php?search[]=author.person.name.keyword:&quot;'.$authors["person"]["name"].'&quot;">'.$authors["person"]["name"].$author_date.'</a>';
                                        }
                                    } 
                                    $array_aut = implode("; ",$authors_array);
                                    unset($authors_array);
                                    echo '<b>Compositore e autores/funções:</b> '.$array_aut.'';
                                    ?>
                                    </p>        
                                <?php endif; ?> 
                                        <?php if (!empty($r["_source"]['isPartOf'])) : ?>
                                            <p class="uk-text-small uk-margin-remove">In: <a href="result.php?search[]=isPartOf.name.keyword:&quot;<?php echo $r["_source"]['isPartOf']['name'];?>&quot;"><?php echo $r["_source"]['isPartOf']['name'];?></a>
                                            </p>
                                        <?php endif; ?> 
                                        <p class="text-muted">
                                            <?php if (!empty($r["_source"]['about'])) : ?>
                                                <b>Assuntos:</b>
                                                <?php foreach ($r["_source"]['about'] as $assunto) : ?>
                                                    <a href="result.php?search[]=about.keyword:&quot;<?php echo $assunto;?>&quot;"><?php echo $assunto;?></a>
                                                <?php endforeach;?>
                                            <?php endif; ?>
                                            <?php if (!empty($r["_source"]['genero_e_forma'])) : ?>
                                                <b>Gênero e forma:</b>
                                                <?php foreach ($r["_source"]['genero_e_forma'] as $genero_e_forma) : ?>
                                                    <a href="result.php?search[]=genero_e_forma.keyword:&quot;<?php echo $genero_e_forma;?>&quot;"><?php echo $genero_e_forma;?></a>
                                                <?php endforeach;?>
                                            <?php endif; ?>
                                        </p>
                                         <p class="text-muted">   
                                            <?php if (!empty($r["_source"]["USP"]['notes'])) : ?>
                                                <b>Notas:</b>
                                                <?php foreach ($r["_source"]["USP"]['notes'] as $notas) : ?>
                                                    <a href="result.php?search[]=USP.notes.keyword:&quot;<?php echo $notas;?>&quot;"><?php echo $notas;?></a>
                                                <?php endforeach;?>
                                            <?php endif; ?>   
                                        </p>
                                        <p class="text-muted">
                                            <?php if (!empty($r["_source"]["USP"]['meio_de_expressao'])) : ?>
                                                <b>Meio de expressão:</b>
                                                <?php foreach ($r["_source"]["USP"]['meio_de_expressao'] as $meio_de_expressao) : ?>
                                                    <a href="result.php?search[]=USP.meio_de_expressao.keyword:&quot;<?php echo $meio_de_expressao;?>&quot;"><?php echo $meio_de_expressao;?></a>
                                                <?php endforeach;?>
                                            <?php endif; ?> 
                                        </p>


                                        <?php if (!empty($r["_source"]["publisher"]["organization"]["name"])) : ?>
                                            <p class="text-muted"><b>Casa publicadora:</b> <a href="result.php?search[]=publisher.organization.name:&quot;<?php echo $r["_source"]["publisher"]["organization"]["name"];?>&quot;"><?php echo $r["_source"]["publisher"]["organization"]["name"];?></a></p>
                                        <?php endif; ?>
                                        <?php if (!empty($r["_source"]["publisher"]["organization"]["location"])) : ?>
                                            <p class="text-muted"><b>Local:</b> <a href="result.php?search[]=publisher.organization.location:&quot;<?php echo $r["_source"]["publisher"]["organization"]["location"];?>&quot;"><?php echo $r["_source"]["publisher"]["organization"]["location"];?></a></p>
                                        <?php endif; ?>
                                        <?php if (!empty($r["_source"]["datePublished"])) : ?>
                                            <p class="text-muted"><b>Ano de publicação:</b> <a href="result.php?search[]=datePublished:&quot;<?php echo $r["_source"]["datePublished"];?>&quot;"><?php echo $r["_source"]["datePublished"];?></a></p>
                                        <?php endif; ?> 

                                        <?php if (!empty($r["_source"]['url'])) : ?>
                                            <?php foreach ($r["_source"]['url'] as $url) : ?>
                                                <?php if ($url != '') : ?>
                                                    <a class="btn btn-primary" href="<?php echo $url;?>" target="_blank">Visualize a primeira página</a>
                                                <?php endif; ?>
                                            <?php endforeach;?>
                                        <?php endif; ?> 
                            </div>
                        </div>
                        <?php endforeach;?>


                        <!-- Navegador de resultados - Início -->
                        <?php ui::pagination($page, $total, $limit); ?>
                        <!-- Navegador de resultados - Fim -->  

                </div>
                <div class="col-4">
                
                <hr>
                <h3>Refinar meus resultados</h3>    
                <hr>
                <?php
                    $facets = new facets();
                    $facets->query = $result_get['query'];

                    if (!isset($_GET)) {
                        $_GET = null;                                    
                    }
                    

                    $facets->facet("author.person.name", 120, "Compositores", null, "_term", $_GET);
                    $facets->facet("author.person.USP.autor_funcao", 120, "Autor/Função", null, "_term", $_GET);
                    $facets->facet("USP.meio_de_expressao", 200, "Meio de expressão", null, "_term", $_GET);
                    $facets->facet("datePublished", 120, "Ano de publicação", "desc", "_term", $_GET);
                    $facets->facet("USP.about.genero_e_forma", 100, "Gênero e forma", null, "_term", $_GET);
                    $facets->facet("about", 100, "Assuntos", null, "_term", $_GET);
                    $facets->facet("publisher.organization.name", 100, "Casa publicadora", null, "_term", $_GET);

                ?>
                </ul>
                <!-- Limitar por data - Início -->
                <form action="result.php?" method="GET">
                    <h5 class="mt-3">Filtrar por ano de publicação</h5>
                    <?php 
                        parse_str($_SERVER["QUERY_STRING"], $parsedQuery);
                        foreach ($parsedQuery as $k => $v) {
                            if (is_array($v)) {
                                foreach ($v as $v_unit) {
                                    echo '<input type="hidden" name="'.$k.'[]" value="'.htmlentities($v_unit).'">';
                                }
                            } else {
                                if ($k == "initialYear") {
                                    $initialYearValue = $v;
                                } elseif ($k == "finalYear") {
                                    $finalYearValue = $v;
                                } else {
                                    echo '<input type="hidden" name="'.$k.'" value="'.htmlentities($v).'">';
                                }                                    
                            }
                        }

                        if (!isset($initialYearValue)) {
                            $initialYearValue = "";
                        }                            
                        if (!isset($finalYearValue)) {
                            $finalYearValue = "";
                        }

                    ?>
                    <div class="form-group">
                        <label for="initialYear">Ano inicial</label>
                        <input type="text" class="form-control" id="initialYear" name="initialYear" pattern="\d{4}" placeholder="Ex. 2010" value="<?php echo $initialYearValue; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="finalYear">Ano final</label>
                        <input type="text" class="form-control" id="finalYear" name="finalYear" pattern="\d{4}" placeholder="Ex. 2020" value="<?php echo $finalYearValue; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </form>   
                <!-- Limitar por data - Fim -->
                <hr>     
                        
            </div>
        </div>
                

        <?php include('inc/footer.php'); ?>

        </div>

        <script>
            function copyToClipboard(element) {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($(element).text()).select();
            document.execCommand("copy");
            $temp.remove();
            }
        </script>
        
    </body>
</html>