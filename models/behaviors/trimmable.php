<?php
/**
 * Tr.im Behavior
 * 
 * Automatically saves a short url to the current record
 *
 * @package app
 * @author Jose Diaz-Gonzalez
 * @version 0.1
 * @copyright 2009 Jose Diaz-Gonzalez
 **/
class TrimBehavior extends ModelBehavior {

	var $__settings = array();

	function setup(&$model, $settings = array()) {
		$default = array(
			'action' => 'view',
			'api' => 'tinyurl',
			'mode' => 'create',
			'field' => 'shorturl'
		);

		if (!isset($this->__settings[$model->alias])) {
			$this->__settings[$model->alias] = $default;
		}

		$this->__settings[$model->alias] = array_merge(
			$this->__settings[$model->alias],
			ife(
				is_array($settings),
				$settings,
				array()
			)
		);
		
	}

	public function beforeSave(&$model) {
		$return = parent::beforeSave($model);

		// What is the field that should hold this?
		if(isset($model->data[$model->alias][$model->primaryKey]) && !empty($model->data[$model->alias][$model->primaryKey])) {
			if (($this->__settings[$model->alias]['mode'] == 'update') || ($this->__settings[$model->alias]['mode'] == 'auto')) {
				$field = $this->__settings[$model->alias]['field'];
				if (!$model->hasField($field)) {
				} else {
					//Build the URL...
					$controllerName = Inflector::tableize($model->alias);
					$controllerName = Inflector::variable($controllerName);
					$url = Router::url(array('controller' => $controllerName, 'action' => $this->__settings[$model->alias]['action'], $model->data[$model->alias][$model->primaryKey]));
					$model->data[$model->alias][$field] = $this->trimURL($url, $this->__settings[$model->alias]['api']);
				}
			}
		}
		return $return;
	}

	public function afterSave(&$model, $created) {
		$return = parent::afterSave($model, $created);

		if(isset($model->id) && !empty($model->id)) {
			if ($this->__settings[$model->alias]['mode'] == 'create') {
				$field = $this->__settings[$model->alias]['field'];
				if (!$model->hasField($field)) {
				} else {
					//Build the URL...
					$controllerName = Inflector::tableize($model->alias);
					$controllerName = Inflector::variable($controllerName);
					$url = Router::url(array('controller' => $controllerName, 'action' => $this->__settings[$model->alias]['action'], $model->data[$model->alias][$model->primaryKey]));
					$trimmedURL = $this->trimURL($url, $this->__settings[$model->alias]['api']);
					$fieldToSaveTo = $model->alias . $field;
					$model->saveField($fieldToSaveTo, $trimmedURL, true);
				}
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
} // End of TrimBehavior
?>