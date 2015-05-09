<?php
require_once("auth.php");

class AuthenticatedPolicy extends AuthPolicy {
    public function __construct() {
        parent::__construct("auth", null);
    }

    public function run($app, $params) {
        return !$app->user()->isAuthenticated()
            ? $this->unauthorized($app, "Your session has expired or is invalid, please login to continue")
            : true;
    }
};