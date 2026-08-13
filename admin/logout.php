<?php
require_once dirname(__DIR__) . '/bootstrap.php'; Auth::logout(); Support::redirect('/admin/login.php');
