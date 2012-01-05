<?php
App::uses('CakeSchema', 'Model');
App::uses('Folder', 'Utility');
class SchemaDiffShell extends AppShell {

	protected $_schemaPath;

	public function main() {
		/* @todo
		 * apply schemashell arguments
		 */

		// set schemaPath
		$this->_schemaPath = APP . 'Config' . DS . 'Schema' . DS;

		// check for schema.php
		$latestSchema = $this->_getLatestSchema();
		if(!$latestSchema){
			$this->_generate();
			$this->_stop();
		}

		// create CakeSchemas and check if current schema changed
		$cakeSchema = new CakeSchema();
		$cakeSchemaOld = new CakeSchema(array('file' => $latestSchema));
		$cakeSchemaTablesNew = $cakeSchema->read();
		$cakeSchemaTablesOld = $cakeSchemaOld->load();
		$diff = $cakeSchema->compare($cakeSchemaTablesOld, $cakeSchemaTablesNew);
		if (!empty($diff)) {
			$this->_generate(true);
		}
		else{
			$this->out('nothing changed');
		}
	}

	protected function _getLatestSchema(){
		$folder = new Folder($this->_schemaPath);
		$result = $folder->read();
		$files = $result[1];
		$count = 1;
		if(!in_array('schema.php', $files)){
			return false;
		}
		else{
			while(in_array('schema_'.$count.'.php', $files)){
				$count++;
			}
			$latestSchema = $count > 1 ? 'schema_'.($count-1).'.php' : 'schema.php';
			return $latestSchema;
		}
	}

	protected function _generate($snapshot = false){
		$command = 'schema generate';
		if($snapshot){
			$command .= ' snapshot';
		}
		$this->dispatchShell($command);
	}
}
