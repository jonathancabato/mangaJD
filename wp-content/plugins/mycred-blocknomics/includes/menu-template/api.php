<?php
// $api_key = 'INSERT_API_KEY_HERE';
// $url = 'https://www.blockonomics.co/api/new_address';

// $ch = curl_init();

// curl_setopt($ch, CURLOPT_URL, $url);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

// $header = "Authorization: Bearer " . $api_key;
// $headers = array();
// $headers[] = $header;
// curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

// $contents = curl_exec($ch);
// if (curl_errno($ch)) {
//   echo "Error:" . curl_error($ch);
// }

// $responseObj = json_decode($contents);
// $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// curl_close ($ch);

// if ($status == 200) {
//     echo $responseObj->address;
// } else {
//     echo "ERROR: " . $status . ' ' . $responseObj->message;
// }
                    

// global $wpdb;
// $tablename = $wpdb->prefix."customplugin";

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


?>

<form method="POST">
<div class="container">
    <h4>Add your BTC Button here.</h4>
</div>
<div class="container" style="margin-top:20px">
    <div class="form-group">
        <label for="">BTC Button:</label>
        <textarea name="btc_btn" class="form-control col-lg-6 col-md-6" cols="50" value=""><?=  wp_get_block_api() ?>
        </textarea>
    </div> 
    <div class="">
        <h5>Select a post type to show the donation metabox (Pick one)</h5>
    <?php 
      $args = array(
        'public'   => true,
        '_builtin' => false,
     );
     ?>
        <div class="container">
            <div class="form-group">
                <input type="checkbox" class="form-control get-cpt-blck" name="checkedBox[]" value="post" <?= wp_get_block_checked() === 'post' ? 'checked' : ''  ?>>
                <label for="">Posts</label>
            </div>
            <div class="form-group">
                <input type="checkbox" class="form-control get-cpt-blck" name="checkedBox[]" value="page" <?= wp_get_block_checked() === 'page' ? 'checked' : ''  ?>>
                <label for="">Pages</label>
            </div>
        <?php
        $types = get_post_types( $args, 'name', 'and' );
        foreach ( $types as $type ) {
            ?>
            <div class="form-group">
                <input type="checkbox" class="form-control get-cpt-blck" name="checkedBox[]" value="<?= $type->name ?>" <?= wp_get_block_checked() === $type->name ? 'checked' : ''  ?>>
                <label for=""><?= $type->label ?></label>
            </div>
            <?php
        }
        ?>
        </div>

    </div>
    <div class="form-group">
      <button type="submit"  class="btn btn-primary">Save Changes</button>
    </div>   
</div>
</form>


<?php 
function insert_api(){
global $wpdb;

$table_name = $wpdb->prefix . 'block_api';
$btn = $_POST['btc_btn'];
$checked = $_POST['checkedBox'];

if(isset($_POST['btc_btn'])){
$count = $wpdb->get_results("SELECT COUNT(*) as num_rows FROM wp_block_api ");
if($count[0]->num_rows > 0){
$update = $wpdb->update( 
    $table_name, 
    array( 
       'blockonomics_btn' => $btn,
       'post_type_to_show' => $checked[0]
    ) ,
    array(
        'api_id'    => 1
    ),
    array( "%s" )
);
}else{
$update = $wpdb->insert( 
    $table_name, 
    array( 
        'blockonomics_btn' => $btn,
        'post_type_to_show' => $checked[0]
    ) ,
    array( "%s" )
); 
}
if(!$update){
    $result = array(
        'status'    => 'error',
        'text'      => 'An error occured please try again'
    );
}else{
    $result = array(
        'status'    => 'success',
        'text'      => 'Your api key is saved.'
    );
}
    return $result;
}
}