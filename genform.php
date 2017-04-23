<?php

	class genform {

		var $sheme = '';
		var $url = '';
		var $prefix = 'genfrm_';
		var $valid = True;
		var $submitted = False;
		var $fields = array();
		var $method = 'POST';
		var $template = 'formtemplate.phtml';

				
		function table($table){
			$this->table = $table;
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
			return $this;
		}

		function value($key){
			
			$id = $this->id($key);
			
			if (isset($_POST[$id])) {
				return $_POST[$id];
			}
		
			return null;

		}	


		/* проверяет валидна ли форма */
		function isValid(){
			return $this->valid;	
		}

		
		/* status submit form*/
		function isSubmit(){
			return $this->submitted;
		}

		function init() {

			$submitname = $this->id('submit');

			if (isset($_POST[$submitname])) 
				$this->submitted = True;
			else
				$this->submitted = False;


			foreach ($this->fields as $name=>$item) {

				
				$id = $this->id($name);

				$this->fields[$name]['id'] =  $id;
				$this->fields[$name]['name'] =  $id;
							

				if (isset($_POST[$id])) {
					$value = trim($_POST[$id]);
					$this->fields[$name]['value'] = $value;
				}
				elseif (isset($item['value']))
					$value = $item['value'];


				$currname = mb_strtolower(trim($name));
				
				if (!isset($item['type'])) {
					
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
				else
					$type = $item['type'];	

				$this->fields[$name]['class'] = $this->prefix.$type; 

								
				/* валидация обязательного поля*/
				if ($this->submitted and $item['required']) {

					if ($value == ''){
						$this->valid = False;
						$this->fields[$name]['error'] = 'zero field';
					}

				}
				
			}


			return;


		}

		
		/* render template */
		function viewform() {
			
			/* vars to template */
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