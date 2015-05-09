<?php
require_once(__DIR__ . "/framework/db/sql.php");

// Extension of the Fat-Free (F3) SQL wrapper.
class SQLext extends SQL {
    public function __construct($f3,$dsn,$user=NULL,$pw=NULL,array $options=NULL) {
        parent::__construct($f3,$dsn,$user,$pw,$options);
    }

    public function exec($cmds,$args=NULL,$ttl=0,$log=TRUE) {
        if (self::is_numeric_arr($args)) {
            // Fix PDO's stupid 1-based indexing for prepared statements containing '?'
            array_unshift($args, NULL);
            unset($args[0]);
        }
        return parent::exec($cmds,$args,$ttl,$log);
    }
    
    public function execSingle($cmds,$args=NULL,$ttl=0,$log=TRUE) {
        $result = $this->exec($cmds,$args,$ttl,$log);
        if (is_array($result))
            return count($result) > 0 ? $result[0] : NULL;
        return $result;
    }

    private static function is_numeric_arr($arr) {
        if (!is_array($arr))
            return false;
            
        $i = -1;
        foreach($arr as $key=>$v) {
            if ($key !== ++$i)
                return false;
        }
        
        return $i === (count($arr) - 1);
    }
}
?>