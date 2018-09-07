<!--Funcion: Codigo de la aplicacion PHPValidator
Autor: Atenea Fernández Outeda, Iván González González, Alfonso Álvarez Pérez
Fecha: -->

<?php
//Variable que guarda el PATH del fichero de configuración de los directorios
$PATH_directories = "Directories.conf";
//Variable que guarda el PATH del fichero de configuración de los ficheros
$PATH_files = "Files.conf";
//Variable que guarda el PATH del CodigoAExaminar
$PATH_code = "CodigoAExaminar";

/*
 * Función que valida que los ficheros de configuración se puedan leer
 * no estén vacíos y que existan.
 */
function validateConf($confFile) {

	if (is_readable($confFile)) {
		$files = file($confFile);

		if (empty($files)) { return -1; }

		return 1;

	} else { return -1; };

}

/*
 * Función que valida que el directorio CodigoAExaminar se pueda leer
 * no esté vacío y que exista.
 */
function validateCode($pathcode) {

	if (is_readable($pathcode)) {
		$files = array_diff(scandir($pathcode), array('.', '..'));

		if (empty($files)) { return -1; }

		return 1;

	} else { return -1; };

}

/*
 * Existen los directorios especificados en el fichero Directories.conf
 * y no hay ningún fichero mas en el directorio principal que el
 * index.php
 */
function validateDirectories($pathdirectories, $pathcode) {

	//Array que guardará la salida
	$array = array();

	if (validateConf($pathdirectories) == 1 && validateCode($pathcode) == 1) {

		$path = file($pathdirectories);
		$dir = glob($pathcode . '/*');
		$dir_num = count($dir) - 1;
		$index_bool = 1;

		foreach ($path as $num_line => $line) {
			echo trim($line);
			foreach ($dir as $num_line2 => $line2) {	
				if (strcmp(trim($line),trim($line2)) === 0) {
					echo ' ---------- OK' . '<br/>';
					array_push($array, 'OK');
					array_splice($dir, $num_line2, 1);
					$dir_num--;
					break;
				}
				elseif($dir_num > $num_line2)
				{
					continue;
				}
				else {
					echo ' ---------- ERROR: El directorio no existe' . '<br/>';
					array_push($array, 'ERROR');
				}
			}
		}
		foreach ($dir as $num_line => $line) {
			if(strcmp($line, $pathcode . '/index.php') === 0)
			{
				echo trim($line) . ' ---------- OK' . '<br/>';
				array_push($array, 'OK');
				$index_bool = 0;
			}
			else {
				echo trim($line) . ' ---------- ERROR: El directorio no existe en Directories.conf' . '<br/>';
				array_push($array, 'ERROR');
			}
		}
		if($index_bool == 1)
		{
			echo $pathcode . '/index.php' . ' ---------- ERROR: El fichero index.php no existe' . '<br/>';
			array_push($array, 'ERROR');
		}
	}
	return $array;
}
//Detecta si existe un comentario al incio del codigo que contenga las palabras autor fecha funcion
function validateComentarioInit($fichero){
	$archivo = file($fichero);//guarda el fichero del que se quiere comprobar si tiene comentarios al pricipio
	$expr=0;//Variabe de control para saber si se encontro alguno de los tipos de comentario
	$autor=0;//Variable de control si vale mas de 0 significa que el comentario tiene la palabra autor
	$fecha=0;//Variable de control si vale mas de 0 significa que el comentario tiene la palabra fecha
	$funcion=0;//Variable de control si vale mas de 0 significa que el comentario tiene la palabra funcion
	foreach ($archivo as $num_línea => $lin) {//for que recorre el fichero 
		if(preg_match("/^\s*\/\/|^\s*\-\-/",$lin)){//Este if comprueba si existen comentarios de unica linea es decir // o --
			$expr++;
			if(preg_match("/autor|Autor|AUTOR/",$lin)){//En caso de que exista en la linea la palabra autor se suma uno a la variable
				$autor++;
			}
			if(preg_match("/fecha|Fecha|FECHA/",$lin)){//En caso de que exista en la linea la palabra fecha se suma uno a la variable
				$fecha++;
			}
			if(preg_match("/funcion|Funcion|FUNCION|función|Función|FUNCIÓN/",$lin)){//En caso de que exista en la linea la palabra funcion se suma uno a la variable
				$funcion++;
			}
		}else{//Si no se compruba si hay algun comentario multilinea es decir /* */ o <!-- --> si es asi lo da por bueno y finaliza 
			if(preg_match("/^\/\*|^<\!\-\-/",$lin)){
				foreach ($archivo as $num_línea2 => $lin2) {//Este for se usa para encontrar el fin de comentario es decir */ o -->
					if(preg_match("/autor|Autor|AUTOR/",$lin2)){//En caso de que exista en la linea la palabra autor se suma uno a la variable
						$autor++;
					}
					if(preg_match("/fecha|Fecha|FECHA/",$lin2)){//En caso de que exista en la linea la palabra fecha se suma uno a la variable
						$fecha++;
					}
					if(preg_match("/funcion|Funcion|FUNCION|función|Función|FUNCIÓN/",$lin2)){//En caso de que exista en la linea la palabra funcion se suma uno a la variable
						$funcion++;
					}
					if(preg_match("/\*\/|\-\-\>/",$lin2)){//En caso de que exista en la linea el fin de comentario se suma uno a la variable expr
						$expr++;
					}
				}
			}
			if(!preg_match("/^\s*$/",$lin)){//Si detecta algo que no sean espacios  el bucle acaba
					break;
			}
		}
	}
	if((!$expr==0)&&(!$autor==0)&&(!$fecha==0)&&(!$funcion==0)){//Para que sea correcta tiene que tener comentario y las palabras autor fecha y funcion
		echo "Hay comentarios al inicio."."<br />\n";
	}else{//Si no se da la situacon anterior algo no esta correcto 
		echo "No hay comentarios al inicio."."<br />\n";
	}
	echo "<br>";
}


?>

<html>
<head>
	<title>PHPValidator</title>
</head>
<body>
	<h1><a href="index.php" style="text-decoration:none;">PHPValidator</a></h1>
	<?php validateDirectories($PATH_directories, $PATH_code) ?>
</body>
</html>
