<?php
/**
 * Digital Languages (digital-language)
 * @var $this LanguageController
 * @var $data DigitalLanguage
 * version: 0.0.1
 *
 * @author Putra Sudaryanto <putra@sudaryanto.id>
 * @copyright Copyright (c) 2016 Ommu Platform (ommu.co)
 * @created date 20 October 2016, 10:13 WIB
 * @link http://company.ommu.co
 * @contect (+62)856-299-4114
 *
 */
?>

<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('language_id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->language_id), array('view', 'id'=>$data->language_id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('language_name')); ?>:</b>
	<?php echo CHtml::encode($data->language_name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('language_desc')); ?>:</b>
	<?php echo CHtml::encode($data->language_desc); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('creation_date')); ?>:</b>
	<?php echo CHtml::encode($data->creation_date); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('creation_id')); ?>:</b>
	<?php echo CHtml::encode($data->creation_id); ?>
	<br />


</div>