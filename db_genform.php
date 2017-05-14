<?php 


	class db_genform() {

		var $table = '';
		var $id = 0;
		var $fields = array();
		var $updated = True; // update or insert


		function table($name) {
			$this->table = $name;
			return $this;
		}


		function id($value){
			$this->id = $value;
			return $this;
		}


		function fields($fields = array()) {
			$this->fields = $fields;
			return $this;
		}


		function init () {
			
			
			foreach ($this->fields as $name=>$item) {
				
				/**
				* проверка записи на уникальность
				*/
				if (isset($item['unique']) {
					if (kORM::Table($this->table)->where($name, $value)->searched())
						$this->currform->adderror($name, 'record already exists');

				}
			}	
		}


	}