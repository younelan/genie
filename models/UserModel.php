<?php
#please put in auth before using it anywhere that's not localhost and locked down from the outside
class UserModel
{
    public static function getCurrentUserId()
    {
        return $_SESSION['user_id'] ?? 1;
    }
}
