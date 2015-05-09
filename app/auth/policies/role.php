<?php
class RolePolicy extends Policy {
    const ROLES_ALL = 1;
    const ROLES_ANY = 2;

    private $_roles;
    private $_type;
    private $_negate;

    public function __construct($roles, $type=self::ROLES_ANY, $negate=false) {
        $this->_roles = $roles;
        $this->_type = $type;
        $this->_negate = !!$negate;
    }

    /**
     * @param $app
     * @param $params
     * @return bool
     */
    public function run($app, $params) {
        $hasRoles = is_string($this->_roles)
            ? $app->user()->hasRole($this->_roles)
            : ($this->_type === self::ROLES_ALL
                ? $app->user()->hasAllRoles($this->_roles)
                : $app->user()->hasAnyRoles($this->_roles));

        if (($hasRoles && $this->_negate) || (!$hasRoles && !$this->_negate)) {
            $app->error(403);
            return false;
        }

        return true;
    }
};