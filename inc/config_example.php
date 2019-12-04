<?php

    /* Configurações iniciais */
    
    $branch = "Partituras da Escola de Comunicações e Artes da USP";
    $branch_abrev = "ACORDE";

    /* Exibir erros */ 
    ini_set('display_errors', 1); 
    ini_set('display_startup_errors', 1); 
    error_reporting(E_ALL); 

    /* Endereço do server, sem http:// */ 
    $hosts = ['localhost']; 

    /* Variáveis de configuração */

    $index = "acorde";

    /* Load libraries for PHP composer */ 
    require (__DIR__.'/../vendor/autoload.php'); 
    /* Load Elasticsearch Client */ 
    $client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build(); 

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


?>
