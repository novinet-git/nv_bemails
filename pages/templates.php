<?php

$func = rex_request('func', 'string');

if ($func == 'delete') {
    $id = (rex_request('id', 'int'));
    $sql = rex_sql::factory();

    $sql->setTable(rex::getTablePrefix() . 'nv_bemails_templates');
    $sql->setWhere('id = ' . $id);

    if ($sql->delete()) {
        echo '<div class="alert alert-success">' . $this->i18n('nv_bemails_templates_deleted') . '</div>';
    }

    $func = '';
}

if ($func == '') {
    $list = rex_list::factory("SELECT id,title,subject FROM " . rex::getTablePrefix() . "nv_bemails_templates ORDER BY subject ASC");
    $list->addTableAttribute('class', 'table-striped');
    $list->setNoRowsMessage('<div class="alert alert-info" role="alert">' . $this->i18n('nv_bemails_templates_no_template') . '</div>');

    // icon column
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '" title="' . $this->i18n('nv_bemails_templates_add') . '"><i class="rex-icon rex-icon-add-action"></i></a>';
    $tdIcon = '<i class="rex-icon fa-file-text-o"></i>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);

    $list->setColumnLabel('title', $this->i18n('nv_bemails_templates_col_title'));
    $list->setColumnParams('title', ['func' => 'edit', 'id' => '###id###']);

    $list->setColumnLabel('subject', $this->i18n('nv_bemails_templates_col_subject'));

    $delete = 'deleteCol';
    $list->addColumn($delete, '<i class="rex-icon rex-icon-delete"></i> ' . $this->i18n('nv_bemails_templates_delete'), -1, ['<th></th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams($delete, ['id' => '###id###', 'func' => 'delete']);

    $list->addLinkAttribute($delete, 'data-confirm', rex_i18n::msg('delete') . ' ?');
    $list->removeColumn('id');
    $content = '<div id="nv_bemails">' . $list->get() . '</div>';
    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('nv_bemails_templates'));
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');
    echo $content;
} elseif ($func == 'edit' || $func == 'add') {
    $fieldset = $func == 'edit' ? $this->i18n('nv_bemails_templates_edit') : $this->i18n('nv_bemails_templates_add');
    $id = rex_request('id', 'int');
    $form = rex_form::factory(rex::getTablePrefix() . 'nv_bemails_templates', '', 'id=' . $id);
    $field = $form->addTextField('title');
    $field->setLabel($this->i18n('nv_bemails_templates_col_title'));
    $field->getValidator()->add('notEmpty', $this->i18n('nv_bemails_templates_col_title_validate_empty'));

    $field = $form->addTextField('subject');
    $field->setLabel($this->i18n('nv_bemails_templates_col_subject'));
    $field->getValidator()->add('notEmpty', $this->i18n('nv_bemails_templates_col_subject_validate_empty'));
    
    $field = $form->addInputField('email','sender_email',null,["class" => "form-control"]);
    $field->setLabel($this->i18n('nv_bemails_templates_col_sender_email'));
    $field->getValidator()->add('notEmpty', $this->i18n('nv_bemails_templates_col_sender_email_validate_empty'));
    $field->getValidator()->add('email', $this->i18n('nv_bemails_templates_col_sender_email_validate_email'));

    $field = $form->addTextField('sender_name');
    $field->setLabel($this->i18n('nv_bemails_templates_col_sender_name'));
    $field->getValidator()->add('notEmpty', $this->i18n('nv_bemails_templates_col_sender_name_validate_empty'));

    $field = $form->addInputField('email','replyto_email',null,["class" => "form-control"]);
    $field->setLabel($this->i18n('nv_bemails_templates_col_replyto_email'));
    $field->getValidator()->add('email', $this->i18n('nv_bemails_templates_col_replyto_email_validate_email'));

    $field = $form->addTextAreaField('body_html',null,['class' => 'form-control cke5-editor', 'data-profile' => 'default']);
    $field->setLabel($this->i18n('nv_bemails_templates_col_body_html'));
    #$field->getValidator()->add('notEmpty', $this->i18n('nv_bemails_templates_col_body_html_validate_empty'));
    $field->setNotice("<strong>Mögliche Platzhalter</strong><br />###salutation###<br>###gender###<br>###firstname###<br>###lastname###<br>###email###");

    if ($func == 'edit') {
        $form->addParam('id', $id);
    }

    $content = $form->get();
    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit', false);
    $fragment->setVar('title', "$fieldset");
    $fragment->setVar('body', $content, false);
    $content = '<div id="nv_bemails">' . $fragment->parse('core/page/section.php') . '</div>';
    echo $content;
}
?>