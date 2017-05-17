<?php

	class genform {

		var $sheme = '';
		var $url = '';
		var $prefix = 'genfrm_';
		var $valid = True;
		var $submitted = False;
		var $fields = array();
		var $errors = array();
		var $method = 'POST';
		var $template = 'formtemplate.phtml';
		var $values = array(); //данные полученные из базы для сравнения
		var $table = null; //таблица
		var $conn = null; // connect к базе
		var $current_id = 0;
		var $increment = '';


				
		function table($table, $conn = ''){
			$this->table = $table;
			$this->conn = $conn;
			return $this;
		}

		function increment($value){
			$this->increment = $value;
			return $this;

		}

		function current_id($value){
			
			$this->current_id = $value;
			
			$result = Table($this->table, $this->connect)->where($this->increment, $value)->one();
			
			if (is_array($result)) 
				$this->values = $result;

			return $this;
		}

		


		function fields($fields = array()){
			$this->fields = $fields;
			$this->init(); // генерация дополнительных полей
			return $this;
		}


		
		function action($url) {
			$this->url = $url;
			return $this;
		}

		function prefix($prefix){
			$this->prefix = $prefix.'_';
			return $this;
		}

		
		/* нестандартный template */
		function template($file){
			$this->template = $file;
			return $this;
		}
		
		function loadsheme($scheme) {
			
			
			$jfile = file_get_contents($this->scheme);
			$items = json_decode($jfile, True);	

			return $items;
		
		}


		/*добавляем ошибку*/
		function adderror($key, $error = 'Error field'){
			$this->fields[$key]['error'] = $error;
			$this->valid = False;
			return $this;
		}

		
		function value($name){

			
			//if (isset($this->fields[$name]))
				
				return $this->fields[$name]['value'];
		
		}	


		//возвращает все значения
		function values() {
			
			foreach ($this->fields as $name=>$field) {
				if ($field['type'] !== 'submit')
					$value[$name] =  $field['value'];
			}


			return $value;

		}





		/* проверяет валидна ли форма */
		function isValid(){
			return $this->valid;	
		}

		
		/* status submit form*/
		function isSubmit(){
			return $this->submitted;
		}

		function save() {
			 
			if ($this->table == '')
			 	return False;

			foreach ($this->fields as $name=>$item) {
				if ($item['type'] !== 'submit' and $item['type'] !== 'confirm_password')
					$add[$name] = $item['value'];
			}


			if ($this->current_id == 0) {
				return Table($this->table, $this->conn)->array2insert($add);
			}
			else {
				return Table($this->table, $this->conn)->where($this->increment, $this->current_id)->update($add);
			}


		}

		
		function valuestable(){
			
			//if ($this->table !== '' and $this->current_id > 0) {
				
			//}

			//return $result;
		}


		function findunique($column, $value){

			$unique = Table($this->table)->where($column, $value)->one();

			if (!is_array($unique))
				return False;

		
			
			if(isset($this->values[$this->increment])){
			  	if ($this->values[$this->increment] == $unique[$this->increment])
					return False;
				else
					return True;
			}
			else
				return True; 
			
		
		}


		function init() {

			$submitname = $this->id('submit');

			if( $_POST )
				$this->submitted = True;
			else
				$this->submitted = False;			
			


			foreach ($this->fields as $name=>$item) {
				
				$id = $this->id($name);

				$this->fields[$name]['id'] =  $id;
				$this->fields[$name]['name'] =  $id;
							

				if (isset($_POST[$id]))
					$value = trim($_POST[$id]);
	
				elseif(isset($this->values[$name]))
					$value = $this->values[$name];
			
				elseif (isset($item['value']))
					$value = $item['value'];
				else 
					$value = '';
				

								
				$currname = mb_strtolower(trim($name));
				
				if (!isset($item['type'])) { //autogeneration type
					
					if ($currname == 'email')
						$type = 'email';
					elseif ($currname == 'password')
						$type = 'password';
					elseif ($currname == 'submit')
						$type = 'submit';
					else
						$type = 'text';

					$this->fields[$name]['type'] = $type;

				}
				else if ($item['type'] == 'save') {
						$type = 'submit';
				}
				else if ($item['type'] == 'confirm_password') {
					$type = 'password';
				}	
				else
					$type = $item['type'];	

				if ($value !== '') {
					if ($type == 'password' or $type == 'confirm_password')
						$value = md5($value);
				}

				$this->fields[$name]['value'] = $value;	

				
				$this->fields[$name]['class'] = $this->prefix.$type; 

								
				/* валидация обязательного поля*/
				if ($this->submitted) {

			

					if ($item['required'] and $value == '') {
						$this->valid = False;
						$this->adderror($name, 'zero field');
					}
					
					elseif ($item['unique']) {
						if ($this->findunique($name, $value)) {
							$this->adderror($name, 'record already exists');
						}
					}

					if ($item['type'] == 'confirm_password') {
						
						if ($parent = $item['parent']) {

							if ($this->fields[$parent]['value'] !== $value) {
								$this->adderror($parent, 'пароли не совпадают');
								$this->adderror($name, 'пароли не совпадают');
							}

						}

					}

				}	

			}



			return;


		}

		/* render template */
		function viewform($render = False) {
			
			if ($render){
				ob_start();
				$this->render();
				$result = ob_get_contents();
				ob_end_clean();
				return $result;
			}
			else
				$this->render();
		}

		

		/* render template */
		function render() {

			$items = $this->fields;
			$action = $this->getUrl();
			$submitted = $this->submitted;
			$method = $this->method;

			include $this->template;
		}


		function id($key) {
			return $this->prefix.$key;
		}


			
		function getUrl() {
  			$url  = @( $_SERVER["HTTPS"] != 'on' ) ? 'http://'.$_SERVER["SERVER_NAME"] :  'https://'.$_SERVER["SERVER_NAME"];
  			$url .= ( $_SERVER["SERVER_PORT"] != 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
  			$url .= $_SERVER["REQUEST_URI"];
  			return $url;
		}




	}
