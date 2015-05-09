<?php
class TagHelper {
    protected static function label($model, $field) {
        return '<dt><label for="<?php echo '.$field.'; ?>"><?php echo '.$model.'->label('.$field.'); ?></label></dt>' . "\n";
    }

    protected static function input($model, $field, $type) {
        $model = self::tokenize($model);
        $field = self::tokenize($field);

        return self::label($model, $field) .
               '<dd><input type="'.$type.'" name="<?php echo '.$field.'; ?>" value="<?php echo '.$model.'->get('.$field.'); ?>" /></dd>';
    }
    
    public static function text(array $node) {
        $attr = $node['@attrib'];
        return self::input($attr["model"], $attr["field"], "text");
    }
    
    public static function password(array $node) {
        $attr = $node['@attrib'];
        return self::input($attr["model"], $attr["field"], "password");
    }
    
    public static function select(array $node) {
        $attr = $node['@attrib'];
        $model = self::tokenize($attr['model']);
        $field = self::tokenize($attr['field']);
        $items = self::tokenize($attr['items']);
        
        $label = self::label($model, $field);
        $html = '<dd><select name="<?php echo '.$field.'; ?>"><?php foreach('.$items.' as $val=>$txt): ?>' .
                '   <option value="<?php echo '.self::escape('$val').'; ?>"<?php if($val=='.$model.'->get('.$field.')) echo " selected"; ?>><?php echo $txt; ?></option>'.
                '<?php endforeach; ?></select></dd>';
        
        return $label . $html;
    }
    
    protected static function escape($str) {
        return "htmlspecialchars($str, ENT_COMPAT, 'ISO-8859-1')";
    }
    
    protected static function tokenize($var) {
        $token = Template::instance()->token($var);
        return $token[0] === '$'
            ? $token
            : "'$token'";
    }
};
?>