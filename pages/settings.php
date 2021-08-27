<?php
if ($_GET["message"] == "sent") {
    echo rex_view::success($this->i18n('nv_bemails_sent_success'));
}
/*

$form = new rex_form($oBeMails->addon->name);

$field = $form->addSelectField('template',$value = null,['class'=>'form-control selectpicker']);
$field->setLabel($this->i18n('nv_bemails_template'));
$select = $field->getSelect();
$oSql = rex_sql::factory();
$oSql->setQuery('select * from ' . rex::getTablePrefix() . 'yform_email_template WHERE name LIKE "nv_bemails_%" ORDER BY name ASC');
for($i=0; $i<$oSql->getRows(); $i++)
{
    $select->addOption($oSql->getValue(name)." (Betreff: ".$oSql->getValue(subject).")",$oSql->getValue("key"));

	$oSql->next();
}

$field = $form->addInputField('email', 'email', null, ["required" => "required","class" => "form-control"]);
$field->setLabel($this->i18n('nv_bemails_email'));

$field = $form->addSelectField('style',$value = null,['class'=>'form-control selectpicker']);
$field->setLabel($this->i18n('nv_bemails_gender'));
$select = $field->getSelect();
$select->addOption("Bitte wählen","");
$select->addOption("Herr","Herr");
$select->addOption("Frau","Frau");

$field = $form->addInputField('text', 'firstname', null, ["class" => "form-control"]);
$field->setLabel($this->i18n('nv_bemails_firstname'));

$field = $form->addInputField('text', 'lastname', null, ["class" => "form-control"]);
$field->setLabel($this->i18n('nv_bemails_lastname'));


$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $this->i18n('nv_bemails_settings'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');


if (rex_post($form->getName() . '_save')) {
    dump($_POST);
}

return;
*/
$sContent = '';

$sFunction = rex_request('function', 'string');

if ($sFunction == "send") {

    $aVars = array("email", "template", "text");
    $aValues = array();
    foreach ($aVars as $sVar) {
        if (rex_post($sVar) != "") {
            $aValues[$sVar] = rex_post($sVar);
        }
    }


    $sql = rex_sql::factory();
    $sql->setQuery('SELECT * FROM ' . rex::getTablePrefix() . 'nv_bemails_templates WHERE id = :id', ['id' => $aValues['template']]);
    $sText = $aValues["text"];
    $sBody = strip_tags(nl2br($aValues["text"]));

    $mail = new rex_mailer();
    $mail->AddAddress($aValues["email"]);
    $mail->SetFrom($sql->getValue("sender_email"), $sql->getValue("sender_name"));

    if ('' != $sql->getValue("replyto_email")) {
        $mail->AddReplyTo($sql->getValue("replyto_email"), $sql->getValue("replyto_name"));
    }

    $mail->Subject = $sql->getValue("subject");
    $mail->Body = $sBody;

    $mail->MsgHTML($sText);
    if ('' != $sBody) {
        $mail->AltBody = $sBody;
    }
    /*

    if (is_array($template['attachments'])) {
        foreach ($template['attachments'] as $f) {
            $mail->AddAttachment($f['path'], $f['name']);
        }
    }
*/




    if (!$mail->Send()) {
        echo rex_view::error($this->i18n('nv_bemails_sent_error'));
    } else {
        $sPage = $this->name . "/settings";
        $sUrl = rex_url::backendPage($sPage);
        $sUrl .= "&message=sent";
        header("Location: " . $sUrl);
        exit;
    }

    return;
}

if ($sFunction == "prepare") {

    $aVars = array("gender", "firstname", "lastname", "email", "template");
    $aValues = array();
    foreach ($aVars as $sVar) {
        $aValues[$sVar] = rex_post($sVar);
    }

    $sSalutation = "";
    $sName = "";
    $aNameParts = [];

    if ($aValues["firstname"]) {
        array_push($aNameParts, $aValues["firstname"]);
    }

    if ($aValues["lastname"]) {
        array_push($aNameParts, $aValues["lastname"]);
    }
    $sName = implode(" ", $aNameParts);

    if ($aValues["gender"] && $sName) {
        switch ($aValues["gender"]) {
            case "Herr":
                $sSalutation = "Sehr geehrter Herr " . $sName;
                break;

            case "Frau":
                $sSalutation = "Sehr geehrte Frau " . $sName;
                break;

            default:

                break;
        }
    }

    if (!$sSalutation) {
        $sSalutation = "Sehr geehrte Damen und Herren";
    }


    $aValues["salutation"] = $sSalutation;
    $sql = rex_sql::factory();
    $sql->setQuery('SELECT body_html FROM ' . rex::getTablePrefix() . 'nv_bemails_templates WHERE id = :id', ['id' => $aValues['template']]);
    $sText = $sql->getValue("body_html");
    foreach ($aValues as $sKey => $sValue) {
        $sText = str_replace("###" . $sKey . "###", $sValue, $sText);
    }

    $formElements = [];

    $n = [];
    $n['label'] = '<label for="text">' . $this->i18n('nv_bemails_email') . '</label>';
    $n['field'] = $aValues["email"];
    $n['field'] .= '<input type="hidden" name="email" value="' . $aValues["email"] . '">';
    $formElements[] = $n;

    $n = [];
    $n['label'] = '<label for="text">' . $this->i18n('nv_bemails_text') . '</label>';
    $n['field'] = '<textarea name="text" class="form-control cke5-editor" data-profile="default" id="text">' . $sText . '</textarea>';
    $n['field'] .= '<input type="hidden" name="template" value="' . $aValues["template"] . '">';
    $formElements[] = $n;


    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $sContent .= $fragment->parse('core/form/form.php');


    /* buttons */

    $formElements = [];
    $n = [];
    $n['field'] = '<button class="btn btn-apply rex-form-aligned" type="submit" name="send" value="1"' . rex::getAccesskey(rex_i18n::msg('update'), 'apply') . '>' . $this->i18n('nv_bemails_send') . '</button>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $buttons = $fragment->parse('core/form/submit.php');



    /* generate page */

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit');
    $fragment->setVar('title', $this->i18n('nv_bemails_send'));
    $fragment->setVar('body', $sContent, false);
    $fragment->setVar('buttons', $buttons, false);
    $sContent = $fragment->parse('core/page/section.php');

    $sContent = '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        <input type="hidden" name="function" value="send">
        ' . $sContent . '
    </form>';

    echo $sContent;


    return;
}









/* form */

$formElements = [];

$n = [];
$n['label'] = '<label for="template">' . $this->i18n('nv_bemails_template') . '</label>';
$n['field'] = '<select required="required" class="form-control selectpicker" id="template" name="template">';
$n['field'] .= '<option value="">Bitte wählen</option>';
$oSql = rex_sql::factory();
$oSql->setQuery('select * from ' . rex::getTablePrefix() . 'nv_bemails_templates ORDER BY title ASC');
for ($i = 0; $i < $oSql->getRows(); $i++) {
    $n['field'] .= '<option value="' . $oSql->getValue("id") . '">Betreff: ' . $oSql->getValue('subject') . ' (Titel intern: '.$oSql->getValue('title').')</option>';
    $oSql->next();
}

$n['field'] .= '</select>';
$formElements[] = $n;


$n = [];
$n['label'] = '<label for="email">' . $this->i18n('nv_bemails_email') . '</label>';
$n['field'] = '<input type="email" required="required" class="form-control" id="email" name="email" value="">';
$formElements[] = $n;

$n['label'] = '<label for="gender">' . $this->i18n('nv_bemails_gender') . '</label>';
$n['field'] = '<select class="form-control selectpicker" id="gender" name="gender">';
$n['field'] .= '<option value="">Bitte wählen</option>';
$n['field'] .= '<option value="Herr">Herr</option>';
$n['field'] .= '<option value="Frau">Frau</option>';
$n['field'] .= '</select>';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="firstname">' . $this->i18n('nv_bemails_firstname') . '</label>';
$n['field'] = '<input type="text" class="form-control" id="firstname" name="firstname" value="">';
$formElements[] = $n;

$n = [];
$n['label'] = '<label for="lastname">' . $this->i18n('nv_bemails_lastname') . '</label>';
$n['field'] = '<input type="text" class="form-control" id="lastname" name="lastname" value="">';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$sContent .= $fragment->parse('core/form/form.php');




/* buttons */

$formElements = [];
$n = [];
$n['field'] = '<a class="btn btn-abort" href="' . rex_url::currentBackendPage() . '">' . rex_i18n::msg('form_abort') . '</a>';
$formElements[] = $n;

$n = [];
$n['field'] = '<button class="btn btn-apply rex-form-aligned" type="submit" name="send" value="1"' . rex::getAccesskey(rex_i18n::msg('update'), 'apply') . '>' . $this->i18n('nv_bemails_prepare') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');



/* generate page */

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $this->i18n('nv_bemails_prepare'));
$fragment->setVar('body', $sContent, false);
$fragment->setVar('buttons', $buttons, false);
$sContent = $fragment->parse('core/page/section.php');

$sContent = '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        <input type="hidden" name="function" value="prepare">
        ' . $sContent . '
    </form>';

echo $sContent;
