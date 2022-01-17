<?php

namespace Aldemeery\Shieldify\Contracts;

interface ResetsUserPasswords
{
    /**
     * Validate and reset the user's forgotten password.
     *
     * @param mixed $user
     * @param array $input
     *
     * @return void
     */
    public function reset($user, array $input);
}
