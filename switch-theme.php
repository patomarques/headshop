<?php
require_once __DIR__ . "/wp-load.php";
$action = isset($argv[1]) ? $argv[1] : "storefront";
if ($action === "storefront") {
  update_option("template","storefront");
  update_option("stylesheet","storefront");
  echo "Switched to parent Storefront\n";
} elseif ($action === "child") {
  update_option("template","storefront");
  update_option("stylesheet","storefront-child");
  echo "Switched to Storefront Child\n";
} else {
  echo "Usage: php switch-theme.php [storefront|child]\n";
}
