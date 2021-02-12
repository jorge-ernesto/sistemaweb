<?php
/*
 * this classes encapsulates the building forms version 2
 * Trosky B. Callo [ctrosky_boris@hotmail.com] Multiples ideas de www.phpclasses 
 *  @Acosa May-2006
 */
//define('FORM_METHOD_GET','get');
//define('FORM_METHOD_POST','post');
//define('FORM_GROUP_MAIN',"%%main%%");
//define('FORM_GROUP_HIDDEN',"%%hidden%%");

class form2_group {
  var $nameid;
  var $title;
  var $attributes;
  var $parameters;

  function __construct ($nameid, $title, $attributes=array(), $parameters=array()) {
    $this->nameid = $nameid;
    $this->title = $title;
    $this->attributes = $attributes;
    $this->parameters = $parameters;
  }
}

class form2_element {
  var $label;
  var $nameid;
  var $value;
  var $separator;
  var $attributes;
  var $parameters;
  var $src;
  
  function __construct ($nameid, $label, $value, $separator, $attributes=array(), $parameters=array(), $src='') {
    $this->label = $label;
    $this->nameid = $nameid;
    $this->value = $value;
    $this->separator = $separator;
    if (!is_array($attributes))
      $attributes = [];
    $this->attributes = $attributes;
    $this->parameters = $parameters;
    $this->src = $src;
  }
}

class f2element_hidden extends form2_element {
  function __construct ($nameid, $value) {
    parent::__construct($nameid, '', $value, ' ');
  }
  function getTag () {
      return '<input type="hidden" name="'.$this->nameid.'" value="'.$this->value.'">';
  }
}

class f2element_text extends form2_element {
  var $class = "form_input";

  function __construct ($nameid, $label, $value, $separator, $size, $maxlength, $attributes=array(), $parameters=array()){
    parent::__construct($nameid, $label, $value, $separator, $attributes, $parameters);
    if (!isset($this->attributes["class"])) $this->attributes["class"] = $this->class;
    $this->attributes["size"] = $size;
    $this->attributes["maxlength"] = $maxlength;
  }

  function getTag () {
    return '<input type="text" name="'.$this->nameid.'" id="'.$this->nameid.'" value="'.$this->value.'" '.getAttributes($this->attributes).' '.getParameters($this->parameters).'>'.$this->separator."\n";
  }
}

class f2element_password extends form2_element {
  var $class = "form_input";

  function f2element_text ($nameid, $label, $value, $separator, $size, $maxlength, $attributes=array(), $parameters=array()){
    parent::__construct($nameid, $label, $value, $separator, $attributes, $parameters);
    if (!isset($this->attributes["class"])) $this->attributes["class"] = $this->class;
    $this->attributes["size"] = $size;
    $this->attributes["maxlength"] = $maxlength;
  }

  function getTag () {
    return '<input type="password" name="'.$this->nameid.'" value="'.$this->value.'" '.getAttributes($this->attributes).' '.getParameters($this->parameters).'>'.$this->separator."\n";
  }
}


class f2element_textarea extends form2_element {
  var $class = "form_textarea";

  function __construct ($nameid, $label, $value, $separator, $cols, $rows, $attributes=array(), $parameters=array()) {
    parent::__construct($nameid, $label, $value, $separator, $attributes, $parameters);
    if (!isset($this->attributes["class"])) $this->attributes["class"] = $this->class;
    $this->attributes["cols"] = $cols;
    $this->attributes["rows"] = $rows;
  }

  function getTag() {
      return '<textarea name="'.$this->nameid.'" '.getAttributes($this->attributes).' '.getParameters($this->parameters).'>' .$this->value.'</textarea>'.$this->separator."\n";
  }
}

class f2element_combo extends form2_element {
  var $class = "form_combo";
  var $size;
  var $values;
  var $attributes_eltos;

  function __construct ($nameid, $label, $value, $values, $separator='', $attributes=array(), $parameters=array(), $attributes_opt=array()) {
     parent::__construct($nameid, $label, $value, $separator, $attributes, $parameters);
    if (!isset($this->attributes["class"])) $this->attributes["class"] = $this->class;
    $this->values = $values;
    $this->attributes_opt = $attributes_opt;
  }

  function getTag () {
    $r = '';
    $r .= '<select name="'.$this->nameid.'" id="'.$this->nameid.'" '.getAttributes($this->attributes).' '.getParameters($this->parameters).'>';
    //$r .= '<select name="'.$this->nameid.'" onChange="cambiarCombo()">';
    foreach($this->values as $value => $label){
      $att = count($this->attributes_opt)>0?getAttributes($this->attributes_opt[$value]):'';
      $r .= '<option value="'.$value.'"'.($value == $this->value?' selected ':'').$att.'>'.$label.'</option>';
    }
    $r .= '</select>'.$this->separator;
    return $r;
  }
}

class f2element_button extends form2_element {
  var $class = "form_button";

  function __construct ($nameid, $value, $separator, $attributes=array(), $parameters=array()) {
    parent::__construct($nameid, '', $value, $separator, $attributes, $parameters);
    if (!isset($this->attributes["class"])) $this->attributes["class"] = $this->class;
  }

  function getTag () {
     return '<button type="button" name="'.$this->nameid.'" '.getAttributes($this->attributes).' '.getParameters($this->parameters) .' >'.$this->value.'</button>'.$this->separator;
  }
}

class f2element_reset extends form2_element {
  var $class = "form_button";

  function __construct ($nameid, $value, $separator, $attributes=array(), $parameters=array()) {
    parent::__construct($nameid, '', $value, $separator, $attributes, $parameters);
    if (!isset($this->attributes["class"])) $this->attributes["class"] = $this->class;
  }

  function getTag () {
     return '<button type="button" name="'.$this->nameid.'" '.getAttributes($this->attributes).' '.getParameters($this->parameters) .' >'.$this->value.'</button>'.$this->separator;
  }
}

class f2element_file extends form2_element 
{
var $class = "form_input";

  function __construct ($nameid, $label, $value, $separador, $size, $attributes=array(), $parameters=array()) 
  {
    parent::__construct($nameid, $label, $value, $separador, $attributes, $parameters);
    if (isset($this->attributes["class"])) $this->attributes["class"] = $this->class;
    $this->attributes["size"] = $size;
    $this->attributes["maxlength"] = $maxlegth;
  }

  function getTag ()
  {
    return '<input type="file" name="'.$this->nameid.'" value="'.$this->value.'" '.getAttributes($this->attributes).' '.getParameters($this->parameters).' > '.$this->separador;
  }
}

class f2element_image extends form2_element 
{
var $class = "form_input";

  function __construct ($name, $src, $value, $separator, $attributes=array(), $parameters=array()) 
  {
    parent::__construct($name, '', $value, $separator, $attributes, $parameters, $src);
    if (isset($this->attributes["class"])) $this->attributes["class"] = $this->class;
    /*$this->attributes["size"] = $size;
    $this->attributes["maxlength"] = $maxlegth;*/
  }

  function getTag ()
  {
    return '<input type="image" name="'.$this->nameid.'" value="'.$this->value.'" src="'.$this->src.'" '.getAttributes($this->attributes).' '.getParameters($this->parameters).' > '.$this->separator;
  }
}

class f2element_obj_image extends form2_element 
{

  function __construct ($src, $separator, $attributes=array(), $parameters=array()) 
  {
    parent::__construct('', '', '', $separator, $attributes, $parameters, $src);
  }

  function getTag ()
  {
    return '<img src="'.$this->src.'" '.getAttributes($this->attributes).' '.getParameters($this->parameters).' >'.$this->separator;
  }
}

class f2element_radio extends form2_element {
  var $class = "form_input";

  function __construct ($nameid, $label, $value, $separator, $attributes=array(), $parameters=array()){
    parent::__construct($nameid, $label, $value, $separator, $attributes, $parameters);
    if (!isset($this->attributes["class"])) $this->attributes["class"] = $this->class;
    //$this->attributes["size"] = $size;
    //$this->attributes["maxlength"] = $maxlength;
  }
  function getTag () {
    return '<input type="radio" name="'.$this->nameid.'" value="'.$this->value.'" '.getAttributes($this->attributes).' '.getParameters($this->parameters).'>'.$this->separator."\n";
  }
}

class f2element_radio2 extends form2_element { // added
  var $style = "form_input";
  var $size;
  var $values;
  function __construct ($name, $title, $value, $separator, $style, $size, $values, $events) {
    parent::__construct($name, $title, $value, $separator);
    if ($style != '') $this->style = $style;
    $this->size = $size;
    $this->values = $values;
    $this->events = $events;
  }
  function getTag () {
    $r = '';
    $r .= '<span class="'.$this->style.'">';
    while (list ($value, $title) = each ($this->values))
      $r .= '<input class="'.$this->style.'" type="radio" name="'.$this->name.'" value="' . $value . '"'.($value == $this->value ? ' checked':'').' ' .$this->events . '>'.$title;
    $r .= '</span>'.$this->separator;
    return $r;
  }
}// added



class f2element_checkbox extends form2_element {
  var $class = "form_input";

  function __construct ($nameid, $label, $value, $separator, $attributes=array(), $parameters=array()){
    parent::__construct($nameid, $label, $value, $separator, $attributes, $parameters);
    if (!isset($this->attributes["class"])) $this->attributes["class"] = $this->class;
    //$this->attributes["size"] = $size;
    //$this->attributes["maxlength"] = $maxlength;
  }

  function getTag () {
    return '<input type="checkbox" name="'.$this->nameid.'" value="'.$this->value.'" '.getAttributes($this->attributes).' '.getParameters($this->parameters).'>'.$this->separator."\n";
  }
}

class f2element_submit extends form2_element 
  {
  var $class = "form_button";

  function __construct ($nameid, $value, $separator, $attributes=array(), $parameters=array()) {
    parent::__construct($nameid, '', $value, $separator, $attributes, $parameters);
    if (!isset($this->attributes["class"])) $this->attributes["class"] = $this->class;
  }

  function getTag () {
    return '<input type="submit" name="'.$this->nameid.'" value="'.$this->value.'" '.getAttributes($this->attributes).' '.getParameters($this->parameters).'>'.$this->separator;
  }
}

class f2element_freeTags extends form2_element {
  function __construct ($tags) {
    parent::__construct('', '', $tags, '');
  }

  function getTag () {
    return $this->value;
  }
}

class f2element_freeTagsLinkJs extends form2_element
{
  //@ Clase para Agregar un texto con enlace
  var $style = 'float:right;';
  function __construct ($tags, $separator, $attributes=array())
  {
    parent::__construct('', '', $tags, $separator, $attributes);
    if (!isset($this->attributes["style"])) $this->attributes["style"] = $this->style;
  }
  
  function getTag()
  {
   return '</div>&nbsp;<a '.getAttributes($this->attributes).'>'.$this->value.'</a>'.$this->separator;
  }
}

class form2 {
  var $title;
  var $name;
  var $method;
  var $action;
  var $enctype;
  var $target;
  var $elements;
  var $groups;
  var $validar;

  /* static styles */
  var $element_separator = "&nbsp;";

  function __construct ($title, $name, $method, $action, $enctype='', $target='',$validar='') {
    $this->title = $title;
    $this->name = $name;
    $this->method = $method;
    $this->action = $action;
    $this->enctype = $enctype;
    $this->target = $target;
    $this->validar = $validar;
    $this->groups = array();
    $this->addGroup (FORM_GROUP_HIDDEN, "");
    $this->addGroup (FORM_GROUP_MAIN, "");
  }

  function addGroup ($nameid, $title, $attributes=array(), $parameters=array()){
    $this->groups[] = new form2_group($nameid, $title, $attributes, $parameters);
  }

  function addElement ($group, $element) {
    $this->elements[$group][] = $element;
  }

  function getForm () {
    $this->enctype = (($this->enctype<>'') ? ' enctype="'.$this->enctype.'"' : '');
    $r = '<div class="form" align="center">';
    $r .= "\n".'<form method="'.$this->method.'" name="'.$this->name.'" '.$this->enctype.' action="'.$this->action.'" '.$this->validar.' target="'.$this->target.'">';
    $r .= '<table class=form_body cellpadding=5 cellspacing=1 >';
    $r .= $this->title!=''?'<caption class=form_title >'.$this->title.'</caption>':'';
    $r .= '<tr><td colspan=1 class=form_group>';
    foreach($this->groups as $group_i => $group){
      if ($group->nameid != FORM_GROUP_MAIN && $group->nameid != FORM_GROUP_HIDDEN)
        $r .= '<fieldset class="form_group" id="'.$group->nameid.'" '.getAttributes($group->attributes).' '.getParameters($group->parameters).'><legend class=form_group_title>'.$group->title.'</legend>';
      foreach($this->elements[$group->nameid] as $element){
        switch ($group->nameid) {
        case FORM_GROUP_HIDDEN:
          $r .= $element->getTag()."\n";
          break;
        default:
          $r .= ($element->label!=''?' <span class=form_label >'.$element->label.'</span>'.$this->element_separator : ''). $element->getTag ();
          break;
        }
      }
      if ($group->nameid != FORM_GROUP_MAIN && $group->nameid != FORM_GROUP_HIDDEN)
        $r .= '</fieldset><br>'."\n";
    }
    $r .= '</td></tr>';
    $r .= '</table>';
    $r .= '</form></div>'."\n";
    return $r;
  }
} // end class form2

function getAttributes($attributes, $par='"'){
  $result = '';
  foreach($attributes as $name=>$value){
    $result .= $name.'='.$par.$value.$par.' ';
  }
  return $result;
}

function getParameters($parameters){
  return implode(' ',$parameters); 
}

