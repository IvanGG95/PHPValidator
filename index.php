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
	//Si el fichero de configuración existe y se tinene permisos de lectura
	if (is_readable($confFile)) {
		//Se introduce el contenido en el array $files
		$files = file($confFile);
		//Si está vacío
		if (empty($files)) { return 0; }

		return 1;

	} else { return -1; } //Si no existe o no se tienen permisos de lectura

}

/*
 * Función que envía un mensaje si existe algún error en los ficheros de configuración.
 */
function validateConfMessage($pathdirectories, $pathfiles) {
	//Si la función validateConf devuelve 0 (está vacío)
	if (validateConf($pathdirectories) == 0) {
		return '</br><p2 class="errorMessage">ERROR: El fichero Directories.conf está vacío.</p2></br></br>';
	} else if (validateConf($pathdirectories) == -1) { //Si la función validateConf devuelve -1 (no tiene permisos de lectura o no existe)
		return '</br><p2 class="errorMessage">ERROR: El fichero Directories.conf no existe o no tiene permisos de lectura.</p2></br></br>';
	}

	if (validateConf($pathfiles) == 0) { //Si la función validateConf devuelve 0 (está vacío)
		return '</br><p2 class="errorMessage">ERROR: El fichero Files.conf está vacío.</p2></br></br>';
	} else if (validateConf($pathfiles) == -1) { //Si la función validateConf devuelve -1 (no tiene permisos de lectura o no existe)
		return '</br><p2 class="errorMessage">ERROR: El fichero Files.conf no existe o no tiene permisos de lectura.</p2></br></br>';
	}

}

/*
 * Función que valida que el directorio CodigoAExaminar se pueda leer
 * no esté vacío y que exista.
 */
function validateCode($pathcode) {
	//Si el PATH existe y se tinene permisos de lectura
	if (is_readable($pathcode)) {
		//Se introducen todos los directorios en el array $files sin los dirs '..' '.'
		$files = array_diff(scandir($pathcode), array('.', '..'));
		//Si está vacío
		if (empty($files)) { return -1; }

		return 1;

	} else { return -1; }; //Si no existe o no se tienen permisos de lectura

}

/*
	Escanea recursivamente todos los ficheros de $pathdirectories y los devuelve.
*/
function escaneoRecursivoFicheros($pathdirectories)
{
	//Declaramos la variable como array que va a guardar el escaneo
	$result = array();
	//Por cada directorio/fichero en el codigo a validar
	foreach(scandir($pathdirectories) as $line)
	{
		//Si el $line es el directorio actual o el anterior pasamos al siguiente elemento
		if($line === '.' || $line === '..')
		{
			continue;
		}
		$files = $pathdirectories . '/' . $line; 
		//Si el $files es un directorio, entramos en el y escaneamos de nuevo
		if(is_readable($files) && is_dir($files))
		{
			//Por cada uno de los elementos de lo devuelto en el escaneoRecursivo se mete en el array
			foreach(escaneoRecursivoFicheros($files) as $childFiles)
			{
				//Metemos al final del array el $childFiles
				$result[] = $childFiles;
			}
		}
		//Si el $files NO es un directorio
		else
		{
			//Metemos al final del array el PATH completo del fichero
			$result[] = $pathdirectories . '/' . $line;
		}
	}
	return $result;
}

/*
 * Existen los directorios especificados en el fichero Directories.conf
 * y no hay ningún fichero mas en el directorio principal que el
 * index.php
 */
function validateDirectories($pathdirectories, $pathcode) {

	//Array que guardará la salida
	$array = array();

	//Si se valida (ver condiciones en las funciones) la carpeta contenedora y el fichero de configuración
	if (validateConf($pathdirectories) == 1 && validateCode($pathcode) == 1) {

		//Variable que vuelca el contenido del fichero de configuración
		$path = file($pathdirectories);
		//Variable que vuelca todo el contenido que cuelga directamente de la carpeta contenedora
		$dir = glob($pathcode . '/*');
		//Variable que guarda el número de ficheros + directorios (se eliminan '.' '..')
		$dir_num = count($dir) - 1;
		//Variable para la comprobación de index.php
		$index_bool = 1;

		//Por cada línea del fichero de configuración
		foreach ($path as $num_line => $line) {//1ro
			//Por cada fichero o directorio de la carpeta contedora
			foreach ($dir as $num_line2 => $line2) { //2do

				//Si coinciden el nombre en los ficheros de configuración y el de la carpeta contenedora
				if (strcmp(trim($line),trim($line2)) === 0) {

					echo '<p2>' . trim($line) . ' ---------- OK</p2><br/>';
					array_push($array, 'OK');
					array_splice($dir, $num_line2, 1);
					$dir_num--;
					break; //Pasamos a la siguiente iteracción foreach 1ro
				}
				//Si no hemos llegado al final de la lista de ficheros + directorios
				else if($dir_num <= $num_line2)
				{
					echo '<p2 class="errorMessage">' . trim($line) . ' ---------- ERROR: El directorio no existe' . '</p2><br/>';
					array_push($array, 'ERROR');
				}
			}
		}

		//Por cada fichero + directorios de la carpeta contenedora
		foreach ($dir as $num_line => $line) {//3ro
			//Si coincide con index.php
			if(strcmp($line, $pathcode . '/index.php') === 0)
			{
				echo '<p2>' . trim($line) . ' ---------- OK' . '<br/>';
				array_push($array, 'OK');
				$index_bool = 0;
			}
			//Si no coincide con index.php
			else {
				echo '<p2 class="errorMessage">' . trim($line) . ' ---------- ERROR: El directorio no existe en Directories.conf' . '</p2><br/>';
				array_push($array, 'ERROR');
			}
		}

		//Si no se ha validado el index.php
		if($index_bool == 1) {			
			echo '<p2 class="errorMessage">' . $pathcode . '/index.php' . ' ---------- ERROR: El fichero index.php no existe' . '</p2><br/>';
			array_push($array, 'ERROR');
		}
	}
	
	//
	//TODO: llamar a la función SUMMARY
	//
}

/*
 * Existen los ficheros especificados en el fichero Files.conf y
 * tienen el nombre especificado
 */
function validateFiles($pathfiles, $pathcode) {
	//Array que guardará la salida
	$array = array();
	//Caracter separador de los archivos
	$simbolo = "_";
	//Simbolo que se utiliza para substituir a todos los caracteres
	$caracter_alfabetico = '%';
	//Si se valida (ver condiciones en las funciones) la carpeta contenedora y el fichero de configuración
	if (validateConf($pathfiles) == 1 && validateCode($pathcode) == 1) {
		$files = escaneoRecursivoFicheros($pathcode);
		$path = file($pathfiles);
		foreach ($path as $num_line => $line) { //1ro
			//Dividimos por el caracter separdor
			$dividirTodo = explode(('/'), trim($line));
			//Creamos el array para guardar el PATH incompleto
			$path_incompleto = array();
			//Contamos el número de elementos divididos
			$num_elem = count($dividirTodo) - 1;
			//Recorremos el bucle for num_elem de veces
			for($i = 0; $i < $num_elem; $i++)
			{
				//Guardamos todo menos el nombre del archivo
				$path_incompleto[] = $dividirTodo[$i];
			}
			//Formamos el PATH completo
			$path_completo = implode('/', $path_incompleto);
			//Array para guardar cada una de las partes al dividir por el punto
			$dividirPunto = array();
			//Dividimos a partir del punto
			$dividirPunto = explode('.', $dividirTodo[$num_elem], 2);
			//Cogemos el tipo de fichero
			$tipoFichero = $dividirPunto[1];
			//Si lleva '%' - es decir, que valide los que NO tengan un nombre concreto
			if(strpos($line, $caracter_alfabetico) !== false)
			{
				echo $line . '<br/>';
				//Dividimos ahora cada una de las partes del %
				$dividirSimbolo = explode(trim($simbolo), $dividirPunto[0]);
				//Contamos el tamaño de $dividirSimbolo
				$size_dividirSimbolo = count($dividirSimbolo)-1;
				//Seleccionamos la cadena a validar
				$cadenaValidacion = $dividirSimbolo[$size_dividirSimbolo];
				//Contamos el número de '%'
				$numeroPorcentajes = substr_count($dividirTodo[$num_elem], $caracter_alfabetico);
				if(is_dir($path_completo) && is_readable($path_completo))
				{
					$allPaths = scandir($path_completo);
					//Por cada uno de los elementos del directorio $path_completo como $valor
					foreach($allPaths as $num_valor => $valor) {//2do
			        	//Quitamos los que son directorios y los . y ..
			        	if($valor === '.' || $valor === '..' || is_dir(trim($path_completo) . '/' . trim($valor)))
						{
							continue; //Si es directorio . o .. pasamos a la siguiente iteración del bucle 
						}
						//Si hay más de 1 '%'
						if($size_dividirSimbolo > 0)
						{
							//Si coincide con la expresión regular con +1 '%' con $valor
							if(preg_match("/\\" . '/\b' . "([a-z]*" . $simbolo . "){" . $numeroPorcentajes . "}" . trim($cadenaValidacion) . "\." . trim($tipoFichero) . "\b/i", trim('/' . $valor), $array_pregmatch))
						    {
						    	$resultado = str_replace($array_pregmatch[0],'', ('/' . $valor));
						    	if(!empty($resultado)){
						    		echo '<p2 class="errorMessage">' . trim($path_completo) . '/' . trim($valor) . ' ---------- ERROR: Formato incorrecto' . '</p2></br>';
									array_push($array, 'ERROR');
						    	}
						    	else{
							    	echo '<p2>' . trim($path_completo) . '/' . trim($valor) . ' ---------- OK' . '</p2></br>';
									array_push($array, 'OK');
						    	}
						    }
						    else
						    {	
						    	echo '<p2 class="errorMessage">' . trim($path_completo) . '/' . trim($valor) . ' ---------- ERROR: Formato incorrecto' . '</p2></br>';
									array_push($array, 'ERROR');
						    }
						}
						//Si hay menos de  1 '%'
						else
						{
							//Si coincide con la expresión regular con menos de 1 '%' con $valor
							if(preg_match("/\\" . '/' . "([a-z]*)\." . trim($tipoFichero) . "/i", trim('/' . $valor)))
						    {
						    	echo '<p2>' . trim($path_completo) . '/' . trim($valor) . ' ---------- OK' . '</p2></br>';
						    	array_push($array, 'OK');
						    }
						    else
						    {
						    	echo '<p2 class="errorMessage">' . $line . ' ---------- ERROR: No existe el archivo' . '</p2></br>';
									array_push($array, 'ERROR');
						    }
						}
					}
				}
				else {
					echo '<p2 class="errorMessage">' . $line .' ---------- ERROR: No existe el directorio base' . '</p2></br>';
					array_push($array, 'ERROR');
				}
			}
			//Si no lleva '%' - es decir, que valide los que tengan un nombre concreto
			else
			{
				if(is_dir($path_completo) &&  is_readable($path_completo))
				{
					$allPaths = scandir($path_completo);
					$total_valor = count($allPaths) - 1;
					//Por cada uno de los elementos del directorio $path_completo como $valor
					foreach($allPaths as $num_valor => $valor)
					{
					    //Quitamos los que son directorios y los . y ..
					    if($valor === '.' || $valor === '..' || is_dir($valor))
						{
							continue;
						}
						//Validamos que el nombre concreto conincide
						if(strcasecmp(trim($path_completo . '/' . $valor),trim($path_completo . '/' . $dividirTodo[$num_elem])) === 0)
						{
						  echo '<p2>' . trim($path_completo) . '/' . trim($valor) . ' ---------- OK' . '</p2><br/>';
						  array_push($array, 'OK');
						  break;
						}
						elseif($total_valor <= $num_valor) {
							echo '<p2 class="errorMessage">' . $line . ' ---------- ERROR: No existe el archivo' . '</p2><br/>';
						  array_push($array, 'ERROR');
						}
					}
				}
				else
				{
					echo '<p2 class="errorMessage">' . trim($path_completo) .' ---------- ERROR: No existe el directorio base' . '</p2><br/>';
					array_push($array, 'ERROR');
				}
			}
		}
	}

	//
	//TODO: llamar a la función SUMMARY
	//
}

//-------------------------------------------------------------------------------------------------------------------


//Detecta si existe un comentario al incio del codigo que contenga las palabras autor fecha funcion
function validateComentInit($fichero){
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


function validateComentStruct($string){
	$control=0;
	$contEstruc=0;
	$contEstrucMal=0;
	$archivo = file($string); //se guardan los datos del archivo en un array
	foreach ($archivo as $num_línea => $lin) {//se recorre el array 
		if(preg_match("/else\s*\{|if\s*\(|for|foreach|while|\<WHILE\>|\<IF\>|\<ELSE\>|do\s*\{|switch|case/",$lin)){//comprueba que algo coincida con la expr
			$contEstruc++;
			$selecccion=preg_split("/else\s*\{|if\s*\(|for|foreach|while|\<WHILE\>|\<IF\>|\<ELSE\>|do\s*\{|switch|case/", $lin, 1, PREG_SPLIT_DELIM_CAPTURE);
			if(preg_match("/else|ELSE/",$selecccion[0])){
				$salida="else";
			}
			if(preg_match("/if|IF/",$selecccion[0])){
				$salida="if";
			}
			if(preg_match("/for/",$selecccion[0])){
				$salida="for";
			}
			if(preg_match("/foreach/",$selecccion[0])){
				$salida="foreach";
			}
			if(preg_match("/while|WHILE/",$selecccion[0])){
				$salida="while";
			}
			if(preg_match("/switch/",$selecccion[0])){
				$salida="switch";
			}
			if(preg_match("/case/",$selecccion[0])){
				$salida="case";
			}
			if(preg_match("/do\s*\{/",$selecccion[0])){
				$salida="do";
			}
			$numliR=$num_línea;//guarda el numero de la linea actual que se esta evaluando 
			$numliR++;//le suma uno al numero de linea actual que se esta evaluando 
			for($i=$num_línea;$i>=0;$i--){//recorre hacia atras el array apartir de la posicion en la que encontro algun if else while...
				if(preg_match("/\/\/|\*\/|\-\->|\/\*|<\!\-\-|^\-\-|^\s*\#/", $archivo[$i])){//si encuentra algun modelo de comentario sale del bucle y sigue con la siguiente linea es decir se deja de recorrer hacia atras 
					break ;
				}
				if(!($i==$numliR-1)){//evita detectar casos que den que no hay comentarios en la primera linea dado que tanto las expr como los; señalan el fin del espacio valido para poner un comentario
					if(preg_match("/\;|\}|\<php|$\s*END|\{/",$archivo[$i])||$i==0){//se buscan ; } inicios de script END si los encuentra es que no hay comentario y se imprime lo  siguiente y ademas se sale y continua con la siguiente linea es decir se deja de recorrer hacia atras 
					$contEstrucMal++;
						echo "La estructura de control: \"$salida\" de la linea $numliR no tiene comentario"."<br />\n";
						$control=1;
						break ;
					}
				}
			}
			
		}	
	}

	if(!($control==1)){
		echo "OK"."<br />\n";
	}
	if($contEstrucMal!=0){
	echo "  Estructuras  $contEstruc/ Estructuras mal $contEstrucMal <br>";
	}
	echo "<br>";
}


//detecta si existe un comentario antes de una funcion 
 function validateComentFunction($string){
 	$control=0;
 	$contfunciones=0;
 	$contfuncionesMal=0;
	$archivo = file($string);//guarda el contenido del archivo cuya direcion es el string en la variable archivo
	foreach ($archivo as $num_línea => $lin) {// se recorre el array 
		if(preg_match("/\s*function\s+\w+\s*\(.*\)|\s*CREATE\s+FUNCTION\s+\w+\s*\(.*\)/",$lin)){//comprueba si se cumple alguno de esos patrones 
			$selecccion=preg_split("/\s*function\s+[a-zA-Z0-9]+\s*|\s*CREATE\s+FUNCTION\s+\w+\s*\(.*\)/", $lin, 1, PREG_SPLIT_DELIM_CAPTURE);
			$selecccion2=explode(" ", $selecccion[0]);
			$salida="";
			$cont=0;
			$contfunciones++;
			foreach($selecccion2 as $i){
				$cont++;
			}
				$aux=explode("(", $selecccion2[1]);
				$salida=$aux[0];
			$numliR=$num_línea;//se guarda la linea actual y se le suma uno acontinuacion
			$numliR++;
			for($i=$num_línea;$i>=0;$i--){//se recorre el archivo en desde la posicion actual
				if(preg_match("/\/\/|\*\/|\-\->|\-\-|\/\*|<\!\-\-|^\s*\#/",$archivo[$i])){//si detecta alguno de estos patrones es que esta comentada
					break;//en caso de que este comentada se sale del bucle se deja de contar y se continua con la siguiente linea (el break no sale del if si no del for)
				}
				if(preg_match("/\;|\}|\<\?php|$\s*END/",$archivo[$i])||$i==0){//se comprueba si alguna de exp se cumple y en caso de que si se muestra el mensaje y se sale del for
					$control=1;
					$contfuncionesMal++;
					echo "No hay comentario en la funcion: \"$salida\"  en la linea $numliR"."<br />\n";
					break;

				}
			}

		}	
	}
	if($control!=1){
		echo "OK"."<br />\n";
	}
	if($contfuncionesMal!=0){
	echo "  Funciones  $contfunciones/ Funciones mal $contfuncionesMal<br>";
	}
	echo "<br />\n";
}



?>

<html>
<head>
	<title>PHPValidator</title>
	<link rel="stylesheet" type="text/css" href="stylesheet.css">
</head>
<body>
	<div class="header">
		<h1><a href="index.php">PHPValidator</a></h1>
	</div>
	<button id="b1" onClick='location.href="?button1=1"'>RESUMEN</button>
  <button id="b2" onClick='location.href="?button2=1"'>DETALLE</button>

  <div class="content">
	  <?php

	  if (validateConf($PATH_directories) == 1 && validateConf($PATH_files) == 1) {
	  	echo '<p>Todos los archivos de configuración se han cargado de forma correcta.</p>';
	  } else { echo validateConfMessage($PATH_directories, $PATH_files); }

	  if (isset($_GET['button1'])) {
	  	echo '<h3>--- RESUMEN ---</h3>';

	  	echo '<h4>1. Existen los directorios especificados en el fichero Directories.conf y no hay ningún fichero mas en el directorio principal que el index.php</h4>';

	  	echo '<h4>2. Los ficheros de vista, controlador y modelo tienen el nombre indicado en la especificación en el fichero Files.conf</h4>';

	  	echo '<h4>3. Los ficheros del directorio CodigoAExaminar tiene todos al principio del fichero comentada su función, autor y fecha<br>(para todos los ficheros que no son propietarios de tipo .pdf, .jpg, etc)</h4>';

	  	echo '<h4>4. Las funciones y métodos en el código del directorio CodigoAExaminar tienen comentarios con una descripción antes de su comienzo</h4>';

	  	echo '<h4>5. En el código están todas las variables definidas antes de su uso y tienen un comentario en la línea anterior o en la misma línea</h4>';

	  	echo '<h4>6. En el código están comentadas todas las estructuras de control en la línea anterior a su uso o en la misma línea</h4>';

	  	echo '<h4>7. Todos los ficheros dentro del directorio Model son definiciones de clases</h4>';

	  	echo '<h4>8. Todos los ficheros dentro del directorio Controller son scripts php</h4>';

	  	echo '<h4>9. Todos los ficheros dentro del directorio View son definiciones de clases</h4>';
	  }

	  if (isset($_GET['button2'])) {
	  	echo '<h3>--- DETALLE ---</h3>';

	  	echo '<h4>1. Existen los directorios especificados en el fichero Directories.conf y no hay ningún fichero mas en el directorio principal que el index.php</h4>';

	  	validateDirectories($PATH_directories, $PATH_code);

	  	echo '<h4>2. Los ficheros de vista, controlador y modelo tienen el nombre indicado en la especificación en el fichero Files.conf</h4>';

	  	validateFiles($PATH_files, $PATH_code);

	  	echo '<h4>3. Los ficheros del directorio CodigoAExaminar tiene todos al principio del fichero comentada su función, autor y fecha<br>(para todos los ficheros que no son propietarios de tipo .pdf, .jpg, etc)</h4>';

	  	echo '<h4>4. Las funciones y métodos en el código del directorio CodigoAExaminar tienen comentarios con una descripción antes de su comienzo</h4>';

	  	echo '<h4>5. En el código están todas las variables definidas antes de su uso y tienen un comentario en la línea anterior o en la misma línea</h4>';

	  	echo '<h4>6. En el código están comentadas todas las estructuras de control en la línea anterior a su uso o en la misma línea</h4>';

	  	echo '<h4>7. Todos los ficheros dentro del directorio Model son definiciones de clases</h4>';

	  	echo '<h4>8. Todos los ficheros dentro del directorio Controller son scripts php</h4>';

	  	echo '<h4>9. Todos los ficheros dentro del directorio View son definiciones de clases</h4>';
	  }
		?>
	</div>
</body>
</html>
