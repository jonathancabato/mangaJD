<?php 
function admin_page_menu(){
    // add_menu_page( 'Custom Plugin', 'API Gateway', 'manage_options', 'custom-gateway');
    add_menu_page("Custom Plugin", "BTC API Gateway","manage_options", "blockonomics-gateway", "custom_gateway");
    // add_submenu_page("myplugin","All Entries", "All entries","manage_options", "allentries", "displayList");
    // add_submenu_page("myplugin","Add new Entry", "Add new Entry","manage_options", "addnewentry", "addEntry");
    }

?>
   