#!/bin/php

<?php

define("NS_SERVICE","Application\\Service");
define("NS_MAPPER", "Application\\Mapper");
define("NS_MODEL",  "Application\\Model");


function deleteDirRec($path)
{
    foreach (glob($path . "/*") as $filename) {
        if (!is_dir($filename)) {
                    unlink($filename);
            } else {
            deleteDirRec($filename);
        }
    }
    if (is_dir($path)) {
        rmdir($path);
    }
}

function toNoCamelCase($name)
{
    $replaceCamel = array('_a','_b','_c','_d','_e','_f','_g','_h','_i','_j','_k','_l','_m','_n','_o','_p','_q','_r','_s','_t','_u','_v','_w','_x','_y','_z');
    $replaceAlpha = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

    return str_replace($replaceAlpha, $replaceCamel,$name);
}

function toCamelCase($name)
{
    $replaceCamel = array('_a','_b','_c','_d','_e','_f','_g','_h','_i','_j','_k','_l','_m','_n','_o','_p','_q','_r','_s','_t','_u','_v','_w','_x','_y','_z');
    $replaceAlpha = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

    return str_replace($replaceCamel,$replaceAlpha, $name);
}

function createServiceRpc($nameTable,$path,$pathConflink,$write,$verbose)
{
    $moduleConflink = file_get_contents($pathConflink);
    $rightW = true;
    $tableNameCamel = toCamelCase($nameTable);
    $pageService ="<?php\n\nnamespace JsonRpcServer\Service;\n\nclass " . ucfirst($tableNameCamel) . " extends AbstractService\n{\n\tprotected \$dal_service = 'dal_service_" . $nameTable . "';\n}";

    if (strpos($moduleConflink,"'JsonRpcServer\\Service\\" . ucfirst($tableNameCamel) . "'")===false) {
        $confServicelink = "'" . strtolower($tableNameCamel) . "'\t\t\t\t\t\t=> 'JsonRpcServer\\Service\\" . ucfirst($tableNameCamel) . "',\n\t\t\t\t//REPLACE-CONFIGURE-SERVICE-JSON-RPC//";
        $moduleConflink = str_replace("//REPLACE-CONFIGURE-SERVICE-JSON-RPC//",$confServicelink,$moduleConflink);

        if($verbose) echo "Service rpc Conf : \033[42;30mwrite\033[0m - " . $nameTable . " \n";
        if ($write) {
            file_put_contents($pathConflink, $moduleConflink);
        }

    } else {
    $rightW = false;
        if($verbose) echo "Service rpc Conf Class : \033[41;30mexist\033[0m - " . $nameTable . "\n";
    }

    if($verbose && $rightW) echo "Service rpc Conf Class : \033[42;30mwrite\033[0m - " . $nameTable . " \n";
    if ($write && $rightW) {
        file_put_contents($path . "Service/" . ucfirst($tableNameCamel) . ".php", $pageService);
    }

}

function removeModel($nameTable, $path, $write, $verbose)
{
    $tableNameCamel = toCamelCase($nameTable);
    if (file_exists($path . "Model/" . ucfirst($tableNameCamel) . ".php")) {
    if($write)
    unlink($path . "Model/" . ucfirst($tableNameCamel) . ".php");
        if($verbose) echo "REMOVE MODEL Class : \033[42;30mremove\033[0m - " . $nameTable . " \n";

    }

    if (file_exists($path . "Model/" . ucfirst($tableNameCamel) . "/")) {
        if($write)
        deleteDirRec($path . "Model/" . ucfirst($tableNameCamel) . ".php");
        if($verbose) echo "REMOVE DIRECTORY MODEL Class : \033[42;30mremove\033[0m - " . $nameTable . " \n";
    }

}

function createModel($nameTable,$column,$path,$write=true,$verbose=false)
{
    $tableNameCamel = toCamelCase($nameTable);

    if (file_exists($path . "Model/Base/" . ucfirst($tableNameCamel) . ".php")) {
        $tagGetSet = array();
        $matches=array();
        $fileGetSet=array();
        $majForce=false;

        $modelExist = file_get_contents($path . "Model/Base/" . ucfirst($tableNameCamel) . ".php");
        preg_match_all("/public function (?<methode>.*)\(/", $modelExist, $matches, PREG_OFFSET_CAPTURE);

        foreach ($column as $key => $attr) {
            $tagGetSet[] = "get" . ucfirst($attr);
            $tagGetSet[] = "set" . ucfirst($attr);
        }

        foreach ($matches['methode'] as $attr) {
            $fileGetSet[] = $attr[0];
        }

        $plus = array_diff($tagGetSet,$fileGetSet);
        $moins = array_diff($fileGetSet,$tagGetSet);

        if (count($plus) > 0) {
            $majForce=true;
            foreach ($plus as $t) {
                echo "Model      Class : " . $nameTable . " \033[1;31m+ " . $t . "\033[0m\n";
            }
        }

        if (count($moins) > 0 ) {
            $majForce=true;
            foreach ($moins as $t) {
                echo "Model      Class : " . $nameTable . " \033[1;31m - " . $t . "\033[0m\n";
            }
        }
    }

    if (!file_exists($path . "Model/Base/" . ucfirst($tableNameCamel) . ".php") || $majForce===true) {
        $attrs = "";
        $setGet = "";
        foreach ($column as $key => $attr) {
            $attrs .= "\tprotected $" . $key . ";\n";
            $setGet .= "\tpublic function get" . ucfirst($attr) . "()\n\t{\n\t\treturn \$this->" . $key . ";\n\t}\n\n\tpublic function set" . ucfirst($attr) . "($" . $key . ")\n\t{\n\t\t\$this->" . $key . " = $" . $key . ";\n\n\t\treturn \$this;\n\t}\n\n";
        }
        $pageModel ="<?php\n\nnamespace " . NS_MODEL . "\\Base;\n\nuse Dal\\Model\\AbstractModel;\n\nclass " . ucfirst($tableNameCamel) . " extends AbstractModel\n{\n " . $attrs . "\n\tprotected \$prefix = '" . $nameTable . "';\n\n" . $setGet . "}";

        if($verbose) echo "Model Base Class : \033[42;30mwrite\033[0m - " . $nameTable . " \n";
        if ($write) file_put_contents($path . "Model/Base/" . ucfirst($tableNameCamel) . ".php", $pageModel);
    } else {
     if($verbose) echo "Model Base Class : \033[41;30mexist\033[0m - " . $nameTable . " \n";
    }

    if (!file_exists($path . "Model/" . ucfirst($tableNameCamel) . ".php")) {
	    $pageModelR ="<?php\n\nnamespace " . NS_MODEL . ";\n\nuse Application\\Model\\Base\\" . ucfirst($tableNameCamel) . " as Base" . ucfirst($tableNameCamel) . ";\n\nclass " . ucfirst($tableNameCamel) . " extends Base" . ucfirst($tableNameCamel) . "\n{\n}";
		if($verbose) echo "Model      Class : \033[42;30mwrite\033[0m - " . $nameTable . " \n";
        if ($write) file_put_contents($path . "Model/" . ucfirst($tableNameCamel) . ".php", $pageModelR);
    } else {
     if($verbose) echo "Model      Class : \033[41;30mexist\033[0m - " . $nameTable . " \n";
    }
    
    echo "\n";
}

function createMapper($nameTable,$path,$write,$verbose)
{
    $class_name = ucfirst(toCamelCase($nameTable));

    if (!file_exists($path . "Mapper/" . $class_name . ".php")) {
	if ($verbose) {
    	    echo "Mapper  Class : \033[42;30mwrite\033[0m - " . $class_name . " \n";
	}
	if ($write) {
	    file_put_contents($path . "Mapper/" . $class_name . ".php", "<?php\n\nnamespace " . NS_MAPPER . ";\n\nuse Dal\Mapper\AbstractMapper;\n\nclass " . $class_name . " extends AbstractMapper\n{\n}");
	}
    }else{
	if($verbose) echo "Mapper  Class : \033[41;30mexist\033[0m - " . $class_name . " \n";
    }
}

function createService($nameTable,$path,$write,$verbose)
{
    $class_name = ucfirst(toCamelCase($nameTable));

    if (!file_exists($path . "Service/" . $class_name . ".php")) {
        if($verbose) {
	    echo "Service Class : \033[42;30mwrite\033[0m - " . $class_name . " \n";
        }
        if ($write) {
            file_put_contents($path . "Service/" . $class_name . ".php", "<?php\n\nnamespace " . NS_SERVICE . ";\n\nuse Dal\Service\AbstractService;\n\nclass " . $class_name . " extends AbstractService\n{\n}");
        }
    }else{
	if($verbose) echo "Service Class : \033[41;30mexist\033[0m - " . $class_name . " \n";
    }
}

function parseMwb($file,$verbose)
{
    $dest = 'tmp';
    deleteDirRec($dest);
    mkdir($dest,0777,true);
    $zip = new ZipArchive() ;
    if ($zip->open($file) !== true) {
        echo "Impossible d'ouvrir l'archive : " . $file . "\n";

        return 1;
    }
    $zip->extractTo($dest);
    $zip->close();
    $z = new XMLReader;
    $z->open($dest . '/document.mwb.xml');
    $result=$TableView=array();
    while ($z->read()) {
    ///////////////////////////////////// VIEW
    if ($z->getAttribute("struct-name")=="db.mysql.View" && $z->nodeType == XMLReader::ELEMENT) {
        $deptView = $z->depth;
        $z->read();
        while (!($z->getAttribute("struct-name")=="db.mysql.View" && $deptView==$z->depth && $z->nodeType==XMLReader::END_ELEMENT) && $z->read()) {
            if ($z->getAttribute("key")=="name" && $z->nodeType == XMLReader::ELEMENT && ($deptView+1)==$z->depth && $z->read()) {
                    $tableV =  $z->value;
                }
        }
        $result[$tableV]['primary_key'] = array();
        $result[$tableV]['column'] = array();
        $result[$tableV]['type'] = 'view';
    }

    if ($z->getAttribute("struct-name")=="db.mysql.Table" && $z->nodeType == XMLReader::ELEMENT) {
        $table = "";
        $colum = array();
        $dTable = $z->depth;
        $refColum = array();
             while (!($z->getAttribute("struct-name")=="db.mysql.Table" && $z->nodeType==XMLReader::END_ELEMENT && $dTable==$z->depth) && $z->read()) {

        /////////////////////////////////////// COLUM
                if ($z->getAttribute("struct-name")=="db.mysql.Column" && $z->nodeType == XMLReader::ELEMENT) {
                    $idColum = $z->getAttribute("id");
                    $dTableColum = $z->depth;
                    $z->read();
                    $type = array();
                    $columName = "";
                    while (!($z->getAttribute("struct-name")=="db.mysql.Column" && $z->nodeType==XMLReader::END_ELEMENT && $dTableColum==$z->depth) && $z->read()) {
                            if ($z->getAttribute("key")=="name" && $z->nodeType == XMLReader::ELEMENT && ($dTableColum+1)==$z->depth && $z->read()) {
                                $columName = $z->value;
                            }
                            if (!empty($z->getAttribute("key")) && $z->nodeType == XMLReader::ELEMENT && ($dTableColum+1)==$z->depth) {
                                $key = $z->getAttribute("key");
                                $z->read();
                                $type[$key] = $z->value;
                            }

                    }
            if($columName !="index_update_date" )
                $colum[$idColum] = array('name' => $columName,'option' => $type);
                }
                ////////////////////////////////////// PRIMARY KEY
               if ($z->getAttribute("struct-name")=="db.mysql.Index" && $z->nodeType == XMLReader::ELEMENT) {
                        $deptIndex = $z->depth;
                    $isPrimaryKey = false;
                    $primColum = "";
                        while (!($z->getAttribute("struct-name")=="db.mysql.Index" && $deptIndex==$z->depth && $z->nodeType == XMLReader::END_ELEMENT) && $z->read()) {
                            if ($z->getAttribute("key")=="isPrimary" && $z->nodeType == XMLReader::ELEMENT && $z->read()) {
                                $isPrimaryKey = $z->value;
                            }
                            if ($z->getAttribute("key")=="referencedColumn" && $z->nodeType == XMLReader::ELEMENT && $z->read()) {
                                            $primColum = $z->value;
                            }

                        }
                    if ($isPrimaryKey) {
                            $refColum[] = $primColum;
                            if($verbose) print_r($refColum);
                    }
                }
                ////////////////////////////////////// TABLE
                if ($z->getAttribute("key")=="name" && $z->nodeType == XMLReader::ELEMENT && $z->depth==($dTable+1) && $z->read()) {
            $table = $z->value;
        }
        }

        $result[$table]['primary_key'] = $refColum;
        $result[$table]['column'] = $colum;
        $result[$table]['type'] = 'table';
        }
    }

    $result[$table]['column'] = $colum;

    $z->close();
    deleteDirRec($dest);

return $result;
}

function main()
{
    $arguments = getopt("f:wvche:t:",array("vv","rpc::","service","model","mapper"));
    $file = (isset($arguments['f'])) ? $arguments['f'] : false;

    if (!$file) {
        echo "\033[41;30mERROR Option file -f \033[0m\n";

        return;
    }

    $help = (isset($arguments['h'])) ? true : false;
    $Sservice = (isset($arguments['service'])) ? true : false;
    $Smodel = (isset($arguments['model'])) ? true : false;
    $Smapper = (isset($arguments['mapper'])) ? true : false;
    $Sall = $Sservice + $Smodel + $Smapper;
    $Sall = ($Sall==3 || $Sall==0) ? true : false;
    $write = (isset($arguments['w'])) ? true : false;
    $clean = (isset($arguments['c'])) ? true : false;
    $exclus = (isset($arguments['e'])) ? $arguments['e'] : array();
    $Otable = (isset($arguments['t'])) ? $arguments['t'] : array();
    if (!is_array($exclus) ||( is_array($exclus) &&  count($exclus)==1 ) ) {
        $exclus = array($exclus);
    }
    if (!is_array($Otable) ||( is_array($Otable) &&  count($Otable)==1 ) ) {
    $Otable = array($Otable);
    }
    $verbose = (isset($arguments['v'])) ? true : false;
    $vverbose = (isset($arguments['vv'])) ? true : false;
    $rpc = (isset($arguments['rpc'])) ? (is_bool($arguments['rpc']) ? true : $arguments['rpc']) : false;

    if ($help) {
        echo "-w                  : write\n";
        echo "-e [NameTable]      : exclus table\n";
        echo "-t [NameTable]      : filtre la table\n";
        echo "--service           : génerer service\n";
        echo "--mapper            : génerer mapper\n";
        echo "--model             : génerer model\n";
        echo "-v                  : verbose\n";
        echo "--vv                : verbose++\n";
        echo "-f                  : file *.mwb (file mysql workbench)\n";
        echo "--rpc[=NameTable]   : ATTENTION! si NameTable n'est pas present tous les JsonRpc des tables seront créés, sinon seule la table JSON-RPC NameTable sera créé \n";

        return 0;
    }
    $pathDal = "../module/Application/src/Application/";
    $pathJsonRpcServer = "../module/JsonRpcServer/src/JsonRpcServer/";
    $pathConf = "../module/Dal/Module.php";
    $pathConfRpc = "../module/JsonRpcServer/config/module.config.php";
    $result = parseMwb($file,$vverbose);
    $handle = opendir($pathDal . '/Model');
    $TableExistante = array();
    while (false !== ($entry = readdir($handle))) {
        if ($entry != "." && $entry != ".." && strpos($entry, 'Abstract')!==0 && strpos($entry,'.php')!==false && $entry!="ModelInterface.php")
            $TableExistante[] =  substr(toNoCamelCase(substr($entry,0,-4)),1);
    }
    foreach ($result as $nameTable => $body) {
    	if (!empty($Otable) && in_array($nameTable, $Otable) || empty($Otable)) {
       		$columCamel = array();
        	foreach ($body['column'] as $colum => $opt) {
            		$columCamel[$opt['name']] = toCamelCase($opt['name']);
        	}
    		if (in_array($nameTable,$TableExistante)) {
        		$val = array_search($nameTable,$TableExistante);
        		if($val!==false) unset($TableExistante[$val]);
    		}
    		if (!in_array($nameTable,$exclus) && $nameTable!="" && $body['type']=="table") {
        		if($Sall || $Smodel)   createModel($nameTable,$columCamel,$pathDal,$write,$verbose);
        		if($Sall || $Smapper)  createMapper($nameTable,$pathDal,$write,$verbose);
        		if($Sall || $Sservice) createService($nameTable,$pathDal,$write,$verbose);
    		}	
    
    		if ( $rpc && !is_string($rpc) || $rpc===$nameTable) {
            	createServiceRpc($nameTable,$pathJsonRpcServer,$pathConfRpc,$write,$verbose);
    		}
    	}
    }

   foreach ($TableExistante as $nameTable) {
   	if ($Otable && $Otable==$nameTable || !$Otable) {
    		removeModel($nameTable, $pathDal, $write, $verbose);
        }
   }
}

main();

?>