<?php

    $this->addJS('templates/default/js/content.js');

    $page_title =   $do=='add' ?
                    sprintf(LANG_CONTENT_ADD_ITEM, $ctype['labels']['create']) :
                    $item['title'];

    $this->setPageTitle($page_title);

    if ($ctype['options']['list_on'] && !$parent){
        $this->addBreadcrumb($ctype['title'], href_to($ctype['name']));
    }

    if ($parent){

        if ($parent['ctype']['options']['list_on']){
            $this->addBreadcrumb($parent['ctype']['title'], href_to($parent['ctype']['name']));
        }

        $this->addBreadcrumb($parent['item']['title'], href_to($parent['ctype']['name'], $parent['item']['slug'].'.html'));

    }

    $back_url = $this->controller->request->get('back');

    $this->addToolButton(array(
        'class' => 'save',
        'title' => LANG_SAVE,
        'href'  => "javascript:icms.forms.submit()"
    ));

    if ($ctype['options']['list_on']){
        $this->addToolButton(array(
            'class' => 'cancel',
            'title' => LANG_CANCEL,
            'href'  => $back_url ? $back_url : href_to($ctype['name'])
        ));
    }
	
	$is_multi_cats = !empty($ctype['options']['is_cats_multi']);

    $this->addBreadcrumb($page_title);
	
?>

<h1><?php echo html($page_title) ?></h1>

<?php
    $this->renderForm($form, $item, array(
        'action' => '',
        'method' => 'post',
        'toolbar' => false,
        'hook' => array(
            'event' => "content_{$ctype['name']}_form_html",
            'param' => array(
                'do' => $do,
                'id' => $do=='edit' ? $item['id'] : null
            )
        ),
    ), $errors);
?>

<?php if ($is_premoderation && !$is_moderator) { ?>
    <div class="content_moderation_notice icon-info">
        <?php echo LANG_MODERATION_NOTICE; ?>
    </div>
<?php } ?>

<?php if ($is_multi_cats) { ?>
	<div class="content_multi_cats_form">
		<div class="list"></div>
		<div class="add_button">
			<a href="javascript:" class="ajaxlink"><?php echo LANG_ADD; ?></a>
		</div>
	</div>
<?php } ?>

<?php if ($props || $is_multi_cats){ ?>
    <script>        
		<?php if ($is_multi_cats){ ?>
			<?php echo $this->getLangJS('LANG_DELETE'); ?>
			var add_cats = [];
			<?php if (!empty($add_cats)) { ?>
				<?php foreach($add_cats as $cat_id){ ?>
					add_cats.push(<?php echo $cat_id; ?>);
				<?php } ?>
			<?php } ?>
		<?php } ?>
		<?php if ($props){ ?>
			<?php echo $this->getLangJS('LANG_LOADING'); ?>
			icms.content.initProps('<?php echo href_to($ctype['name'], 'props'); ?>'<?php if($do=='edit'){ ?>, <?php echo $item['id']; ?><?php } ?>);
			<?php if ($is_load_props){ ?>
				icms.content.loadProps();
			<?php } ?>
		<?php } ?>
    </script>
<?php } ?>
