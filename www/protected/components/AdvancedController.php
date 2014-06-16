<?php
Yii::import('application.components.ParamCondition');

class AdvancedController extends Controller
{
	protected function getVar($varName, $conditions = array(), $conditionsData = array())
	{
		$value = (isset($_POST[$varName]) ? $_POST[$varName] : (isset($_GET[$varName]) ? $_GET[$varName] : null));
		
		while (count($conditionsData) < count($conditions))
		{
			$conditionsData[] = null;
		}
		
		foreach ($conditions as $i => $condition)
		{
			$this->checkParamCondition($varName, $value, $condition, $conditionsData[$i]);
		}
		
		return $value;
	}
	
	private function checkParamCondition($varName, $value, $condition, $conditionData)
	{
		switch ($condition)
		{
			case ParamCondition::NOT_NULL:
			{
				if (!isset($value)) throw new Exception(Yii::t('general', 'Undefined parameter').': '.$varName);
				break;
			}
			case ParamCondition::NOT_EMPTY:
			{
				$this->checkParamCondition($varName, $value, ParamCondition::NOT_NULL, null);
				
				if (empty($value)) throw new Exception(Yii::t('general', 'Empty parameter').': '.$varName);
				break;
			}
			case ParamCondition::IS_ARRAY:
			{
				$this->checkParamCondition($varName, $value, ParamCondition::NOT_NULL, null);
				
				if (!is_array($value)) throw new Exception(Yii::t('general', 'Parameter is not an array').': '.$varName);
				break;
			}
			case ParamCondition::ARRAY_MIN_LENGTH:
			{
				$this->checkParamCondition($varName, $value, ParamCondition::IS_ARRAY, null);
				
				if (count($value) < $conditionData) throw new Exception(Yii::t('general', 'Unexpected array length').': '.$varName.'['.count($value).']');
				
				break;
			}
		}
	}
}