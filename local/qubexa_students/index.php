<?php
require_once(__DIR__ . '/../../config.php');
require_login();
redirect(\local_qubexa\workspace::page_url('students'));
