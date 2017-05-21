<?php
/**
 * Digital Publishers (digital-publisher)
 * @var $this PublisherController
 * @var $model DigitalPublisher
 * @var $form CActiveForm
 * version: 0.0.1
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @copyright Copyright (c) 2016 Ommu Platform (opensource.ommu.co)
 * @created date 20 October 2016, 10:13 WIB
 * @link https://github.com/ommu/mod-digital-archive
 * @contact (+62)856-299-4114
 *
 */

	$this->breadcrumbs=array(
		'Digital Publishers'=>array('manage'),
		'Create',
	);
?>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>