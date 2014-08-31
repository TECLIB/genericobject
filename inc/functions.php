<?php

/*
 * return the name of a dropdown type
 * This shared by the following classes :
 *    - PluginGenericobjectCommonDropdown
 *    - PluginGenericobjectCommonTreeDropdown
 */
function dropdown_getTypeName($class,$nb=0) {
      global $GO_FIELDS;
      $fk = getForeignKeyFieldForTable(getTableForItemType($class));
      $instance = new $class();
      $options = PluginGenericobjectField::getFieldOptions($fk, $instance->linked_itemtype);
      Toolbox::logDebug($fk, "\n", $options);
      $dropdown_type = isset($options['dropdown_type'])
         ? $options['dropdown_type']
         : null;
      $label = $options['name'];
      if (!is_null($dropdown_type) and $dropdown_type==='isolated') {
         $linked_itemtype_object = new $instance->linked_itemtype();
         $label .= " (" . __($linked_itemtype_object::getTypeName(), 'genericobject') . ")";
      }
      if($label != '') {
         return $label;
      } else {
         return $class;
      }
}
global $LOG_FILTER;
$LOG_FILTER = array();
/*
 * a simple logger function
 * You can disable logging by using the global $LOG_FILTER
 * in setup.php after including this file
 */
function _log() {
   global $LOG_FILTER;
   $trace = debug_backtrace();
   $callee = array_shift($trace);
   if (count($trace)>0) {
      $caller = $trace[0];
   } else {
      $caller = null;
   }

   if (
      !is_null($caller)
      and isset($caller['class'])
      and in_array($caller['class'], $LOG_FILTER)
   ) {
      $msg = _format_trace($trace, func_get_args());
      call_user_func_array("Toolbox::logInFile", array('generic-object', $msg, true));
   }
}

function _format_trace($bt, $args) {
   static $tps = 0;
   $msg = "";
   $msg = '  From ';
   if (count($bt) > 0) {
      if (isset($bt[0]['class'])) {
         $msg .= $bt[0]['class'].'::';
      }
      $msg .= $bt[0]['function'].'() in ';
   }
   $msg .= $bt[0]['file'] . ' line ' . $bt[0]['line'];

   if ($tps && function_exists('memory_get_usage')) {
      $msg .= ' ('.number_format(microtime(true)-$tps,3).'", '.
         number_format(memory_get_usage()/1024/1024,2).'Mio)';
   }
   $msg .= "\n  ";
   foreach ($args as $arg) {
      if (is_array($arg) || is_object($arg)) {
         $msg .= str_replace("\n", "\n  ",print_r($arg, true));
      } else if (is_null($arg)) {
         $msg .= 'NULL ';
      } else if (is_bool($arg)) {
         $msg .= ($arg ? 'true' : 'false').' ';
      } else {
         $msg .= $arg . ' ';
      }
   }
   return $msg;
}