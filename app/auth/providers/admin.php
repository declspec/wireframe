<?php
class AdminRoleProvider implements IRoleProvider {
    public function fetchRoles($userId) {
        return $userId == 43 ? "admin" : null;
    }
};