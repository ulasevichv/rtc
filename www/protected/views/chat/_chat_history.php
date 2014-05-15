<?php
$historyElementsDataProvider = new CArrayDataProvider($historyElements, array('pagination'=>false));

$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'admin_wrapper',
    'dataProvider' => $historyElementsDataProvider,
    'template' => '{items}',
    'columns' => array(
        'conversationID',
        'sentDate'=>array('name'=>'Date', 'value'=>'Yii::app()->dateFormatter->format("MMMM dd, y hh:mm:ss a", intval($data["sentDate"]/1000))'),
        'fromJID',
        'toJID',
        'body',
    ),
    'itemsCssClass' => 'table table-striped',
));