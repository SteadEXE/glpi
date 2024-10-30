<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2024 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

use Glpi\Exception\AuthenticationFailedException;

/**
 * @since 0.85
 */

/**
 * @var array $CFG_GLPI
 */
global $CFG_GLPI;

if (!isset($_SESSION["glpicookietest"]) || ($_SESSION["glpicookietest"] != 'testcookie')) {
    if (!Session::canWriteSessionFiles()) {
        Html::redirect($CFG_GLPI['root_doc'] . "/index.php?error=2");
    } else {
        Html::redirect($CFG_GLPI['root_doc'] . "/index.php?error=1");
    }
}

if (isset($_POST['totp_code']) && is_array($_POST['totp_code'])) {
    $_POST['totp_code'] = implode('', $_POST['totp_code']);
}

//Do login and checks
//$user_present = 1;
if (isset($_SESSION['namfield']) && isset($_POST[$_SESSION['namfield']])) {
    $login = $_POST[$_SESSION['namfield']];
} else {
    $login = '';
}
if (isset($_SESSION['pwdfield']) && isset($_POST[$_SESSION['pwdfield']])) {
    $password = $_POST[$_SESSION['pwdfield']];
} else {
    $password = '';
}
// Manage the selection of the auth source (local, LDAP id, MAIL id)
if (isset($_POST['auth'])) {
    $login_auth = $_POST['auth'];
} else {
    $login_auth = '';
}

$remember = isset($_SESSION['rmbfield']) && isset($_POST[$_SESSION['rmbfield']]) && $CFG_GLPI["login_remember_time"];

$auth = new Auth();


// now we can continue with the process...
if (isset($_REQUEST['totp_cancel'])) {
    session_destroy();
    Html::redirect($CFG_GLPI['root_doc'] . '/index.php');
}
$mfa_params = [];
if (!empty($_POST['totp_code'])) {
    $mfa_params['totp_code'] = $_POST['totp_code'];
} else if (!empty($_POST['backup_code'])) {
    $mfa_params['backup_code'] = $_POST['backup_code'];
}
if ($auth->login($login, $password, (isset($_REQUEST["noAUTO"]) ? $_REQUEST["noAUTO"] : false), $remember, $login_auth, $mfa_params)) {
    Auth::redirectIfAuthenticated();
} else {
    throw new AuthenticationFailedException(authentication_errors: $auth->getErrors());
}
