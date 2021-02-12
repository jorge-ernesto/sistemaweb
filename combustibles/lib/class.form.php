<?php
/****************************************************************************************/
/* this classes encapsulates the building forms function.               */
/* Trosky B. Callo [ctrosky_boris@hotmail.com] Multiples ideas de www.phpclasses    */
/****************************************************************************************/
define('FORM_METHOD_GET','get');
define('FORM_METHOD_POST','post');
define('FORM_GROUP_MAIN',"%%main%%");
define('FORM_GROUP_HIDDEN',"%%hidden%%");

class form_group {
  var $name;
  var $title;
  var $display;

  function __construct ($name, $title, $display) {
    $this->name = $name;
    $this->title = $title;
    $this->display = $display;
  }
}

class form_element {
  var $title;
  var $name;
  var $value;
  var $separator;
  var $evento;
  function __construct ($title, $name, $value,$separator,$evento="") {
    $this->title = $title;
    $this->name = $name;
    $this->value = $value;
    $this->separator = $separator;
    $this->evento = $evento;
  }
}

class form_element_text extends form_element {
  var $style = "form_input";
  var $size;
  var $maxlength;
  var $isreadonly = false;

  function __construct ($title, $name, $value, $separator, $style, $size, $maxlength, $isreadonly, $evento="") {
    parent::__construct($title, $name, $value, $separator,$evento);
    if ($style != "") $this->style = $style;
    $this->size = $size;
    $this->maxlength = $maxlength;
    $this->isreadonly = $isreadonly;
  }

  function getTag () {
    return '<input type="text" name="'.$this->name.'" value="'.$this->value.'" class="'.$this->style.'" size="'.$this->size.'" maxlength="'.$this->maxlength.'" '.($this->isreadonly ? ' readonly ' : '').$this->evento.'>'.$this->separator."\n";
  }
}

class form_element_onlytext extends form_element {
  var $style = "form_textonly";
  function __construct ($title, $value, $separator, $style) {
    parent::__construct ($title, "", $value, $separator);
    if ($style != "") $this->style = $style;
  }
  function getTag () {
    return '<span class="'.$this->style.'">'.$this->value.'</span>'.$this->separator;
  }
}

class form_element_anytext extends form_element {
  function __construct ($text) {
    parent::__construct ("", "", $text, "");
  }
  function getTag () {
    return $this->value;
  }
}

class form_element_onlytextarea extends form_element {
  var $style = "form_textonly";
  function __construct ($title, $value, $separator, $style) {
    parent::__construct ($title, "", $value, $separator);
    if ($style != "") $this->style = $style;
  }
  function getTag () {
    return '<p class="'.$this->style.'">'.$this->value.'</p>';
  }
}

class form_element_password extends form_element {
  var $style = "form_input";
  var $size;
  var $maxlength;
  function __construct ($title, $name, $value, $separator, $style, $size, $maxlength,$evento="") {
    parent::__construct ($title, $name, $value, $separator,$evento);
    if ($style != "") $this->style = $style;
    $this->size = $size;
    $this->maxlength = $maxlength;
  }
  function getTag () {
    return '<input type="password" name="'.$this->name.'" value="'.$this->value.'" class="'.$this->style.'" size="'.$this->size.'" maxlength="'.$this->maxlength.'" '.$this->evento.'>'.$this->separator;
  }
}

class form_element_combo extends form_element {
  var $style = "form_combo";
  var $size;
  var $values;
  var $multiple;
  var $disabled;
  var $visible;

  function __construct ($title, $name, $value, $separator, $style, $size, $values, $multiple, $evento="", $disabled=false, $display='inline') {
    parent::__construct ($title, $name, $value, $separator,$evento);
    if ($style != "") $this->style = $style;
    $this->size = $size;
    $this->values = $values;
    $this->multiple = $multiple;
    $this->disabled = $disabled;
    $this->display = $display;
  }

  function getTag () {
    $r = '';
    $r .= '<select name="'.$this->name.'" class="'.$this->style.'"'.( $this->size != 0 ? ' size="'.$this->size.'"':'').($this->multiple ? ' multiple ' : '').($this->disabled ? ' disabled ' : '').' style="display:'.$this->display.'" '.$this->evento.'>';
    foreach($this->values as $value => $title){
      $r .= '<option value="'.$value.'"'.($value == $this->value ? ' selected': '').'>'.$title.'</option>';
    }
    $r .= '</select>'.$this->separator;
    return $r;
  }
}

class form_element_radio extends form_element {
  var $style = "form_radio";
  var $size;
  var $values;
  function __construct ($title, $name, $value, $separator, $style, $size, $values) {
    parent::__construct($title, $name, $value, $separator);
    if ($style != '') $this->style = $style;
    $this->size = $size;
    $this->values = $values;
  }
  function getTag () {
    $r = '';
    $r .= '<span class="'.$this->style.'">';
    while (list ($value, $title) = each ($this->values))
      $r .= '<input class="'.$this->style.'" type=radio name="'.$this->name.'" value="' . $value . '"'.($value == $this->value ? ' checked':'').'>'.$title;
    $r .= '</span>'.$this->separator;
    return $r;
  }
}

class form_element_checkbox extends form_element {
  var $style = "form_checkbox";
  var $size;
  var $values;
  function __construct ($title, $name, $value, $separator, $style) {
    parent::__construct($title, $name, $value, $separator);
    if ($style != '') $this->style = $style;
    $this->size = $size;
    $this->values = $values;
  }
  function getTag () {
    $r = '';
    $r .= '<span class="'.$this->style.'">';
    $r .= '<input type=checkbox name="'.$this->name.'" value="1"'.($this->value? ' checked':'').'>'.$title.'<br>';
    $r .= '</span>';
    return $r.$this->separator;
  }
}

class form_element_textarea extends form_element {
  var $style = "form_textarea";
  var $cols;
  var $rows;
  function __construct ($title, $name, $value, $separator, $style, $cols, $rows) {
    parent::__construct ($title, $name, $value, $separator);
    if ($style != "") $this->style  = $style;
    $this->cols = $cols;
    $this->rows = $rows;
  }

  function getTag () {
      return '<br><textarea cols='.$this->cols.' rows='.$this->rows.' name="'.$this->name.'" class="'.$this->style.'">'.$this->value.'</textarea>'.$this->separator;
  }
}

class form_element_submit extends form_element {
  var $style = "form_button";
  var $size;
  var $disabled;
  function __construct ($name, $value, $separator, $style, $size, $disabled=false) {
    parent::__construct('', $name, $value, $separator);
    if ($style != '') $this->style = $style;
    $this->size = $size;
    $this->disabled = $disabled;
  }
  function getTag () {
    $this->disabled;
    return '<input type="submit" name="'.$this->name.'" value="'.$this->value.'" class="'.$this->style.'" size="'.$this->size.'" '.($this->disabled == true?'DISABLED ':'').'>'.$this->separator;
  }
}

class form_element_button extends form_element {
  var $style = "form_button";
  var $size;
  var $disabled;
  var $display;
  function __construct($name, $value, $separator, $style, $size, $evento, $disabled=false, $display='inline') {
    parent::__construct("", $name, $value, $separator,$evento);
    if ($style != "") $this->style = $style;
    $this->size = $size;
    $this->disabled = $disabled;
    $this->display = $display;
  }
  function getTag () {
     return '<BUTTON TYPE=button id='.$this->name.' class="'.$this->style.'" size="'.$this->size.'" '.$this->evento.' '.($this->disabled?'disabled ':'').' style="display:'.$this->display.';">'.$this->value.'</BUTTON>'.$this->separator;
  }
}

class form_element_file extends form_element{
  var $style = "form_file";
  var $size;
  var $maxlength;
  var $isreadonly = false;

  function __construct ($title, $name, $value, $separator, $style, $size, $maxlength, $isreadonly, $evento="") {
    parent::__construct($title, $name, $value, $separator,$evento);
    if ($style != "") $this->style = $style;
    $this->size = $size;
    $this->maxlength = $maxlength;
    $this->isreadonly = $isreadonly;
  }
  function getTag () {
    return '<input type="file" name="'.$this->name.'" value="'.$this->value.'" class="'.$this->style.'" size="'.$this->size.'" maxlength="'.$this->maxlength.'" '.($this->isreadonly ? ' readonly ' : '').$this->evento.'>'.$this->separator."\n";
  }
}

class form_element_hidden extends form_element {
  function __construct ($name, $value) {
    parent::__construct("", $name, $value," ");
  }
  function getTag () {
      return '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'">';
  }
}

class form {
  var $title;
  var $name;
  var $method;
  var $action;
  var $enctype;
  var $target;
  var $elements;

  /* static styles */
  var $element_separator = "&nbsp;";

  function __construct ($title, $name, $method, $action, $enctype='', $target='', $width='') {
    $this->title = $title;
    $this->name = $name;
    $this->method = $method;
    $this->action = $action;
    $this->enctype = $enctype;
    $this->target = $target;
    $this->addGroup (FORM_GROUP_HIDDEN, "");
    $this->addGroup (FORM_GROUP_MAIN, "");
    $this->width = $width;
    
  }
  function addGroup ($name, $title, $display='inline'){
    $this->groups[] = new form_group ($name, $title, $display);
  }
  function addElement ($group, $element) {
    $this->elements[$group][] = $element;
  }

  function getForm () {
    $this->enctype = (($this->enctype<>'') ? ' enctype="'.$this->enctype.'"' : '');
    $r = '<div class="form" align="center">';
    $r .= "\n".'<form method="'.$this->method.'" name="'.$this->name.'" '.$this->enctype.' action="'.$this->action.'" target="'.$this->target.'">';
    $r .= '<table class=form_body cellpadding=5 cellspacing=1 '.($this->width!=''?'width="'.$this->width.'"':'').' >';
    if ($this->title!='')
      $r .= '<caption class=form_title >'.$this->title.'</caption>';
    $r .= '<tr><td colspan=1 class=form_group>';
    foreach($this->groups as $group_i => $group){
      if ($group->name != FORM_GROUP_MAIN && $group->name != FORM_GROUP_HIDDEN)
        $r .= '<fieldset class="form_group" id="'.$group->name.'" style="display:'.$group->display.';" ><legend class=form_group_title>'.$group->title.'</legend>';
      $color = 0;
      for ($element_i=0; $element_i<sizeof ($this->elements[$this->groups[$group_i]->name]); $element_i++)
        switch ($group->name) {
        case FORM_GROUP_HIDDEN:
          $r .= $this->elements[$this->groups[$group_i]->name][$element_i]->getTag ()."\n";
          break;
        default:
          $element = $this->elements[$this->groups[$group_i]->name][$element_i];
          $r .= ($element->title!=''?' <span class=formlabel >'.$element->title.'</span>'.$this->element_separator : ''). $element->getTag();
          break;
        }
      if ($group->name != FORM_GROUP_MAIN && $group->name != FORM_GROUP_HIDDEN)
        $r .= '</fieldset><br>'."\n";
    }
    $r .= '</td></tr>';
    $r .= '</table>';
    $r .= '</form></div>'."\n";
    return $r;
  }
} // end class's form

function espacios($n=0)
{
  return str_repeat("&nbsp;",$n);
}

function errorMessage($errorMessage){
  $form = new form('Error', "form_error", FORM_METHOD_POST, '','','');
  $form->addGroup("buttons","");
  //$form->addElement(FORM_GROUP_HIDDEN, new form_element_hidden("JobAd[Postedby]", $JobAd['Postedby']));
  $form->addElement(FORM_GROUP_MAIN,new form_element_onlytext('<hr>',$errorMessage,'<hr>','error_message'));
  $form->addElement('buttons',new form_element_submit("action",'Back','','',20));
  return $form->getform();  
}

function Message($Message){
  $form = new form('', "form_msg", FORM_METHOD_POST, '','','');
  $form->addElement(FORM_GROUP_MAIN,new form_element_onlytext('',$Message,'','form_textview ',''));
  return $form->getform();  
}

function resaltar($ov_color='',$ou_color='')
{
    return 'onmouseover="this.style.backgroundColor=\''.$ov_color.'\'" onmouseout="this.style.backgroundColor=\''.$ou_color.'\'" bgColor="'.$ou_color.'"';
}
 
function Status_Bar($Mensaje ="")
{
     return "onMouseMove=\"window.status='$Mensaje';return true\" onmouseout=\"window.status=window.defaultStatus;return true\"";
}

?>