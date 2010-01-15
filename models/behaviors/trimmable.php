<?php
/**
 * Tr.immable Behavior
 * 
 * Automatically saves a short url to the current record
 *
 * @package app
 * @author Jose Diaz-Gonzalez
 * @version 0.1
 * @copyright 2009 Jose Diaz-Gonzalez
 **/
class TrimmableBehavior extends ModelBehavior {

	var $__settings = array();

	function setup(&$model, $settings = array()) {
		$default = array(
			'action' => 'view',
			'api' => 'tinyurl',
			'mode' => 'create',
			'field' => 'shorturl'
			'fields' => $model->primaryKey
		);

		if (!isset($this->__settings[$model->alias])) {
			$this->__settings[$model->alias] = $default;
		}

		$settings = (is_array($settings)) ? $settings : array();
		$this->__settings[$model->alias] = array_merge(
			$this->__settings[$model->alias], $settings
		);
		
	}

	public function beforeSave(&$model) {
		$return = parent::beforeSave($model);

		// What is the field that should hold this?
		if(in_array($this->__settings[$model->alias]['mode'], array('update', 'auto')) 
		and isset($model->data[$model->alias][$model->primaryKey])
		and !empty($model->data[$model->alias][$model->primaryKey])) {
			$field = $this->__settings[$model->alias]['field'];
			if ($model->hasField($field)) {
				//Build the URL...
				$controllerName = Inflector::variable(Inflector::tableize($model->alias));
				$params = $this->_getParams(&$model, $this->__settings[$model->alias]['fields']);
				$url = Router::url(array('controller' => $controllerName, 'action' => $this->__settings[$model->alias]['action'], $params));
				$model->data[$model->alias][$field] = $this->trimURL($url, $this->__settings[$model->alias]['api']);
			}
		}
		return $return;
	}

	public function afterSave(&$model, $created) {
		$return = parent::afterSave($model, $created);

		if(($this->__settings[$model->alias]['mode'] == 'create') and isset($model->id) and !empty($model->id)) {
			$field = $this->__settings[$model->alias]['field'];
			if ($model->hasField($field)) {
				//Build the URL...
				$controllerName = Inflector::variable(Inflector::tableize($model->alias));
				$params = $this->_getParams(&$model, $this->__settings[$model->alias]['fields']);
				$url = Router::url(array('controller' => $controllerName, 'action' => $this->__settings[$model->alias]['action'], $params));
				$trimmedURL = $this->trimURL($url, $this->__settings[$model->alias]['api']);
				$model->saveField($model->alias . $field, $trimmedURL, true);
			}
		}
		return $return;
	}

	public function trimURL($url = null, $api = 'tinyurl') {
		if (isset($url) && !empty($url)){
			switch ($api) {
				case 'tinyurl' :
					$apiUrl = "http://tinyurl.com/api-create.php?url=";
					break;
				case 'trim' :
					$apiUrl = "http://api.tr.im/api/trim_simple?url=";
					break;
				case 'agd' :
					$apiUrl = "http://a.gd/?module=ShortURL&file=Add&mode=API&url=";
					break;
				default :
					// Assume the user has set their own api url
					$apiURL = $api;
					break;
			}
			return file_get_contents($apiUrl . $url);
		}
		return false;
	}

	public function _getParams(&$model, $fields = array()) {
		if (isset($fields) and !is_array($fields)) {
			$fields = array($fields);
		}
		
		$ret = '';
		$fieldsCount = count($fields) - 1;
		foreach ($fields as $key => &$field) {
			if ($model->hasField($field)) {
				$ret += $model->data[$model->alias][$field];
				if ($key < $fieldsCount) {
					$ret += '/';
				}
			}
		}

		if ($ret == '') {
			return $model->data[$model->alias][$model->primaryKey];
		}
		return $ret;
	}
} // End of TrimBehavior
?>