<?php
/**
 * Digital Categories (digital-category)
 * @var $this CategoryController
 * @var $model DigitalCategory
 * @var $form CActiveForm
 *
 * @author Putra Sudaryanto <putra@ommu.co>
 * @contact (+62)856-299-4114
 * @copyright Copyright (c) 2016 Ommu Platform (www.ommu.co)
 * @created date 20 October 2016, 10:13 WIB
 * @link https://github.com/ommu/mod-digital-archive
 *
 */
?>

<?php $form=$this->beginWidget('application.libraries.yii-traits.system.OActiveForm', array(
	'id'=>'digital-category-form',
	'enableAjaxValidation'=>true,
	'htmlOptions' => array(
		'enctype' => 'multipart/form-data',
		'on_post' => true,
	),
)); ?>

<div class="dialog-content">
	<fieldset>

		<?php /*
		//begin.Messages ?>
		<div id="ajax-message">
			<?php echo $form->errorSummary($model); ?>
		</div>
		<?php //begin.Messages 
		*/?>

		<div class="form-group row">
			<?php echo $form->labelEx($model,'cat_title', array('class'=>'col-form-label col-lg-3 col-md-3 col-sm-12')); ?>
			<div class="col-lg-6 col-md-9 col-sm-12">
				<?php echo $form->textField($model,'cat_title', array('maxlength'=>32, 'class'=>'form-control')); ?>
				<?php echo $form->error($model,'cat_title'); ?>
				<?php /*<div class="small-px silent"></div>*/?>
			</div>
		</div>

		<div class="form-group row">
			<?php echo $form->labelEx($model,'cat_desc', array('class'=>'col-form-label col-lg-3 col-md-3 col-sm-12')); ?>
			<div class="col-lg-6 col-md-9 col-sm-12">
				<?php echo $form->textArea($model,'cat_desc', array('rows'=>6, 'cols'=>50, 'class'=>'form-control')); ?>
				<?php echo $form->error($model,'cat_desc'); ?>
				<?php /*<div class="small-px silent"></div>*/?>
			</div>
		</div>

		<div class="form-group row">
			<?php echo $form->labelEx($model,'cat_code', array('class'=>'col-form-label col-lg-3 col-md-3 col-sm-12')); ?>
			<div class="col-lg-6 col-md-9 col-sm-12">
				<?php echo $form->textField($model,'cat_code', array('maxlength'=>6, 'class'=>'form-control')); ?>
				<?php echo $form->error($model,'cat_code'); ?>
				<?php /*<div class="small-px silent"></div>*/?>
			</div>
		</div>
		
		<div class="form-group row">
			<?php echo $form->labelEx($model,'tag_input', array('class'=>'col-form-label col-lg-3 col-md-3 col-sm-12')); ?>
			<div class="col-lg-6 col-md-9 col-sm-12">
				<?php 
				if($model->isNewRecord) {
					echo $form->textArea($model,'tag_input', array('rows'=>6, 'cols'=>50, 'class'=>'form-control'));
					
				} else {
					//echo $form->textField($model,'tag_input', array('maxlength'=>32, 'class'=>'form-control'));
					$url = Yii::app()->controller->createUrl('o/categorytag/add', array('type'=>'digital'));
					$category = $model->cat_id;
					$tagId = 'DigitalCategory_tag_input';
					$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
						'model' => $model,
						'attribute' => 'tag_input',
						'source' => Yii::app()->createUrl('globaltag/suggest'),
						'options' => array(
							//'delay '=> 50,
							'minLength' => 1,
							'showAnim' => 'fold',
							'select' => "js:function(event, ui) {
								$.ajax({
									type: 'post',
									url: '$url',
									data: { cat_id: '$category', tag_id: ui.item.id, tag: ui.item.value },
									dataType: 'json',
									success: function(response) {
										$('form #$tagId').val('');
										$('form #tag-suggest').append(response.data);
									}
								});

							}"
						),
						'htmlOptions' => array(
							'class'	=> 'form-control',
						),
					));
					echo $form->error($model,'tag_input');
				}?>
				<div id="tag-suggest" class="suggest clearfix">
					<?php 
					if(!$model->isNewRecord) {
						$tags = $model->tags;
						if(!empty($tags)) {
							foreach($tags as $key => $val) {?>
							<div><?php echo $val->tag->body;?><a href="<?php echo Yii::app()->controller->createUrl('o/categorytag/delete', array('id'=>$val->id,'type'=>'digital'));?>" title="<?php echo Yii::t('phrase', 'Delete');?>"><?php echo Yii::t('phrase', 'Delete');?></a></div>
						<?php }
						}
					}?>
				</div>
				<?php if($model->isNewRecord) {?><div class="small-px">tambahkan tanda koma (,) jika ingin menambahkan tag lebih dari satu</div><?php }?>
			</div>
		</div>

		<div class="form-group row">
			<?php echo $form->labelEx($model,'cat_icon', array('class'=>'col-form-label col-lg-3 col-md-3 col-sm-12')); ?>
			<div class="col-lg-6 col-md-9 col-sm-12">
				<?php echo $form->textField($model,'cat_icon', array('maxlength'=>32, 'class'=>'form-control')); ?>
				<?php echo $form->error($model,'cat_icon'); ?>
				<?php /*<div class="small-px silent"></div>*/?>
			</div>
		</div>

		<div class="form-group row">
			<?php echo $form->labelEx($model,'cat_file_type', array('class'=>'col-form-label col-lg-3 col-md-3 col-sm-12')); ?>
			<div class="col-lg-6 col-md-9 col-sm-12">
				<?php				
				if(!$model->getErrors()) {
					$cat_file_type = unserialize($model->cat_file_type);
					if(!empty($cat_file_type))
						$model->cat_file_type = Utility::formatFileType($cat_file_type, false);
				}
				echo $form->textField($model,'cat_file_type', array('class'=>'form-control')); ?>
				<?php echo $form->error($model,'cat_file_type'); ?>
				<div class="small-px">pisahkan type file dengan koma (,). example: "mp3, mp4, pdf, doc, docx"</div>
			</div>
		</div>

		<div class="form-group row">
			<?php echo $form->labelEx($model,'cat_icon_image', array('class'=>'col-form-label col-lg-3 col-md-3 col-sm-12')); ?>
			<div class="col-lg-6 col-md-9 col-sm-12">
				<?php 
				if(!$model->isNewRecord) {
					if(!$model->getErrors())
						$model->old_cat_icon_image_input = $model->cat_icon_image;
					echo $form->hiddenField($model,'old_cat_icon_image_input');
					if($model->old_cat_icon_image_input != '') {
						$cat_icon_image = Yii::app()->request->baseUrl.'/public/digital/'.$model->old_cat_icon_image_input;?>
						<img class="mb-15" src="<?php echo Utility::getTimThumb($cat_icon_image, 200, 300, 3);?>" alt="">
				<?php }
				}
				echo $form->fileField($model,'cat_icon_image', array('class'=>'form-control')); ?>
				<?php echo $form->error($model,'cat_icon_image'); ?>
				<?php /*<div class="small-px silent"></div>*/?>
			</div>
		</div>

		<div class="form-group row">
			<?php echo $form->labelEx($model,'cat_cover', array('class'=>'col-form-label col-lg-3 col-md-3 col-sm-12')); ?>
			<div class="col-lg-6 col-md-9 col-sm-12">
				<?php 
				if(!$model->isNewRecord) {
					if(!$model->getErrors())
						$model->old_cat_cover_input = $model->cat_cover;
					echo $form->hiddenField($model,'old_cat_cover_input');
					if($model->old_cat_cover_input != '') {
						$cat_cover = Yii::app()->request->baseUrl.'/public/digital/'.$model->old_cat_cover_input;?>
						<img class="mb-15" src="<?php echo Utility::getTimThumb($cat_cover, 300, 400, 3);?>" alt="">
				<?php }
				}
				echo $form->fileField($model,'cat_cover', array('class'=>'form-control')); ?>
				<?php echo $form->error($model,'cat_cover'); ?>
				<?php /*<div class="small-px silent"></div>*/?>
			</div>
		</div>

		<div class="form-group row publish">
			<?php echo $form->labelEx($model,'publish', array('class'=>'col-form-label col-lg-3 col-md-3 col-sm-12')); ?>
			<div class="col-lg-6 col-md-9 col-sm-12">
				<?php echo $form->checkBox($model,'publish', array('class'=>'form-control')); ?>
				<?php echo $form->labelEx($model,'publish'); ?>
				<?php echo $form->error($model,'publish'); ?>
				<?php /*<div class="small-px silent"></div>*/?>
			</div>
		</div>

	</fieldset>
</div>
<div class="dialog-submit">
	<?php echo CHtml::submitButton($model->isNewRecord ? Yii::t('phrase', 'Create') : Yii::t('phrase', 'Save') , array('onclick' => 'setEnableSave()')); ?>
	<?php echo CHtml::button(Yii::t('phrase', 'Cancel'), array('id'=>'closed')); ?>
</div>
<?php $this->endWidget(); ?>


