<?php
/**
 * Tr.immable Behavior
 * 
 * Automatically saves a short url to the current record
 *
 * @package app
 * @author Jose Diaz-Gonzalez
 * @version 0.2
 * @copyright 2009 Jose Diaz-Gonzalez
 **/
class TrimmableBehavior extends ModelBehavior {

/**
 * Contains configuration settings for use with individual model objects.
 * Individual model settings should be stored as an associative array, 
 * keyed off of the model name.
 *
 * @var array
 * @access public
 * @see Model::$alias
 */
	var $settings = array();

/**
 * Initiate My Behavior
 *
 * @param object $model
 * @param array $config
 * @return void
 * @access public
 */
	function setup(&$model, $config = array()) {
		$default = array(
			'action' => 'view',
			'api' => 'tinyurl',
			'mode' => 'create',
			'field' => 'shorturl',
			'fields' => $model->primaryKey
		);

		if (!isset($this->settings[$model->alias])) {
			$this->settings[$model->alias] = $default;
		}

		$config = (is_array($config)) ? $config : array();
		$this->settings[$model->alias] = array_merge(
			$this->settings[$model->alias], $config
		);
		
	}

/**
 * Before save callback
 *
 * @param object $model Model using this behavior
 * @return boolean True if the operation should continue, false if it should abort
 * @access public
 */
	public function beforeSave(&$model) {
		$return = parent::beforeSave($model);

		// What is the field that should hold this?
		if(in_array($this->settings[$model->alias]['mode'], array('update', 'auto')) 
		and isset($model->data[$model->alias][$model->primaryKey])
		and !empty($model->data[$model->alias][$model->primaryKey])) {
			$field = $this->settings[$model->alias]['field'];
			if ($model->hasField($field)) {
				//Build the URL...
				$controllerName = Inflector::variable(Inflector::tableize($model->alias));
				$params = $this->_getParams(&$model, $this->settings[$model->alias]['fields']);
				$url = Router::url(array('controller' => $controllerName, 'action' => $this->settings[$model->alias]['action'], $params));
				$model->data[$model->alias][$field] = $this->trimURL($url, $this->settings[$model->alias]['api']);
			}
		}
		return $return;
	}

/**
 * After save callback
 *
 * @param object $model Model using this behavior
 * @param boolean $created True if this save created a new record
 * @access public
 * @return boolean True if the operation succeeded, false otherwise
 */
	public function afterSave(&$model, $created) {
		$return = parent::afterSave($model, $created);

		if(($this->settings[$model->alias]['mode'] == 'create') and isset($model->id) and !empty($model->id)) {
			$field = $this->settings[$model->alias]['field'];
			if ($model->hasField($field)) {
				//Build the URL...
				$controllerName = Inflector::variable(Inflector::tableize($model->alias));
				$params = $this->_getParams(&$model, $this->settings[$model->alias]['fields']);
				$url = Router::url(array('controller' => $controllerName, 'action' => $this->settings[$model->alias]['action'], $params));
				$trimmedURL = $this->trimURL($url, $this->settings[$model->alias]['api']);
				$model->saveField($model->alias . $field, $trimmedURL, true);
			}
		}
		return $return;
	}

/**
 * Trims urls
 *
 * @param string $url valid http url
 * @param string $api Optional built-in api or http URL to custom api (defaults to tinyurl)
 * @return mixed On success a shortened URL, false on failure
 * @author savant
 */
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

/**
 * Gets the parameters for URL to be shortened
 *
 * @param Object $model Model using the behavior
 * @param mixed $fields string with field or array(field1, field2=>AssocName, field3)
 * @return string URL parameters
 * @author savant
 */
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