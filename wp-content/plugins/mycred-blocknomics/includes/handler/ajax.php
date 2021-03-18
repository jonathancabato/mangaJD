<?php 

function appendhtml_ajax_handler(){
    global $wpdb;
    $table = $wpdb->prefix . 'pay_to_unlock';
    $chapter_id = $_POST['chapter_id'];
    $result = $wpdb->get_results("SELECT * FROM $table where chapter_id = $chapter_id LIMIT 1 ");
    $input_val = '';
    if(count($result) > 0){

        $input_val = $result[0]->payment_to_unlock;

    }
?>
    <div class="wp-manga-modal-addons block"> 
            <input type="checkbox" name="payment_to_unlock" class="community-pay-checkbox" <?= $input_val != '' ? 'checked' : '' ?>>
            <strong for="">Set to Community Unlock</strong>
        </div>
        <div class="wp-manga-modal-addons-input block" data-chapter-id="<?= $chapter_id ?>" <?= $input_val != '' ? 'style="display:block!important"' : 'style="display:none!important"' ?>> 
            <span class="alert alert-message" style="display:none">Please set a goal before saving for community unlock</span>
            <strong>Donation Goal (Points)</strong>
            <input type="number" name="donation-goal" class="donation-value" value="<?= $input_val != '' ? $input_val : '' ?>">
        </div>
<?php
    die;
}

function savetodb_ajax_handler(){

    global $wpdb;
    $table_name = $wpdb->prefix . 'pay_to_unlock';
    $manga_chapters = $wpdb->prefix . 'manga_chapters';
    $chapter_id = $_POST['chapter_id'];
    $goal = $_POST['donation_goal'];
    $date = date('Y-m-d H:i:s');
    $get_result = $wpdb->get_results("SELECT post_id FROM $manga_chapters where chapter_id = $chapter_id LIMIT 1");
    $post_id = $get_result[0]->post_id;
    if($_POST['query'] === 'insert'){
        $check = $wpdb->get_results("SELECT * FROM $table_name where chapter_id = $chapter_id");
        if(count($check) > 0){
            $result = $wpdb->update(
                $table_name, 
                array(
                    'payment_to_unlock' => $goal,
                    'time'  => $date
                ),
                array(
                    'chapter_id'    => $chapter_id
                )
            );
        }else{
            $result = $wpdb->insert(
                $table_name, 
                array(
                    'chapter_id'    => $chapter_id ,
                    'post_id'       => $post_id,
                    'payment_to_unlock' => $goal,
                    'time'  => $date
                )
            );
        }
    }else if($_POST['query'] === 'delete'){
        $result = $wpdb->delete(
            $table_name,
            array(
                'chapter_id' => $chapter_id
            )
        );
    }
    if($result){
        echo 1;
    }else{
        echo 2;
    }
    die;
}
function modal_ajax_handler(){

    global $wpdb;
    global $current_user;
    if(is_user_logged_in(  )){
        $username =  $current_user->user_login;
    }else{
        $username = '';
    }
    if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-nonce' ) ) {
        die;
    }

    $post_id = $_POST['post_id'];
    
    if($_POST['query'] === 'get'){
        $table_name = $wpdb->prefix . 'pay_to_unlock';
        $result = $wpdb->get_results("SELECT chapter_id, payment_to_unlock, total_payment, is_manga_unlocked FROM $table_name where post_id = $post_id ");
        $r = $result;
        $arr = [];
        if(is_user_logged_in()){
            $logged_in = 'true';
        }else{
            $logged_in = 'false';
        }
        if(count($result) > 0){

            for($i = 0 ; $i < count($result); $i++){
                $data = array(
                    'chapter_id'    => $result[$i]->chapter_id,
                    'goal' => $result[$i]->payment_to_unlock,
                    'total_payment' => $result[$i]->total_payment  !== null ? $result[$i]->total_payment : 0,
                    'is_unlocked'   => $result[$i]->is_manga_unlocked
                ) ;
                array_push($arr,$data);
            }

            $result = array(
                'username'  => $username,
                'status'    => 'success',
                'user'      => $logged_in,
                'data'  => $arr,
                'button' => '<a href="javascript:void(0)"class="button button-primary button-large modal-donation-btn"><i class="fas fa-coins"></i>Donate</a>',
                'modal' => '<div class="modal fade" id="myDonateModal" role="dialog">
                            <div class="modal-dialog crypto-dialog">
                                <div class="modal-content crypto-modal">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        <h4 class="modal-title"><span class="current-amount"></span> out of <span
                                                class="total-amount"></span> Points </h4>
                                
                                        <p class="response-message"></p>
                                      
                                        <div class="progress">
                                            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="40"
                                                aria-valuemin="0" aria-valuemax="100" style="width:0%">
                                            </div>
                                            <h6 class="progress-text"></h6>
                                        </div>
                                    
                                    </div>
                                    <div class="modal-body">
                                    
                                        <div class="form-wrapper">
                                            <form action="#">
                                                <select class="toggle-identity" name="name-select">
                                                     <option value="select" selected disabled>Hide your identity?</option>
                                                     <option value="Yes">Yes</option>
                                                     <option value="No">No</option>
                                                </select>
                                                <input type="text" name="name" placeholder="Donor Name" class="donor-name" value="'.$username.'">
                                                <input type="number" name="amount" class="points-to-donate" placeholder="Amount">
                                                <!--<input type="submit" value="Send Donation" id="sendPoints"> -->
                                                <a href="javascript:void(0)" class="btn btn-primary" id="sendPoints"><span class="btn-loading"><i class="fa fa-spinner fa-spin"></i></span>Send Donation</a>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>'
            );

        }else{
            $result = array(
                'username'  => '',
                'success'   => 'error',
                'data'      => '',
                'button'    => '',
                'modal'     => $post_id
            );
        }
    }else if($_POST['query'] === 'insert'){
       
        $comm_tbl = $wpdb->prefix . 'pay_to_unlock';
        $donor_tbl = $wpdb->prefix . 'blck_payments';
        $manga_cointbl = $wpdb->prefix . 'manga_chapter_coin';
        $user_m = $wpdb->prefix . 'usermeta';
        $meta_key = 'mycred_default';
        $post_id = $_POST['post_id'];
        $chapter_id = $_POST['chapter_id'];
        $user = $_POST['user'];
        $amount = $_POST['amount'];
        $is_anonymous = $_POST['is_anonymous'];
        $user_id = $_POST['user_id'];
     
        $date = date('Y-m-d H:i:s');

     

        $user_meta = $wpdb->get_results("SELECT meta_value from $user_m where user_id = $user_id and meta_key = 'mycred_default'");


        if($is_anonymous !== 'false'){
            $user_id_a = 0;
            $anonymous = 'yes';
        }else{
            $user_id_a = $_POST['user_id'];
            $anonymous = 'no';
         
        }
        if(count($user_meta) > 0){
            $current_points = $user_meta[0]->meta_value;

            if($amount <= $current_points){
                $new_points = $current_points - $amount;


                $user_update_meta = $wpdb->update(
                    $user_m,
                    array(
                        'meta_value'    => $new_points
                    ),
                    array(
                        'user_id'   => $user_id,
                        'meta_key'  => $meta_key
                    )
                );

                if($user_update_meta){

                    $comm_result = $wpdb->get_results("SELECT total_payment , payment_to_unlock from $comm_tbl where post_id = $post_id and chapter_id = $chapter_id");

                    $total_payment = $comm_result[0]->total_payment;
                    $total = $total_payment + $amount;
                    $goal = $comm_result[0]->payment_to_unlock;
            

                    if($goal == $total){
                        $is_unlocked = 'yes';
            
                        $coinReset = $wpdb->update(
                            $manga_cointbl,
                            array(
                                'coin'  => 0
                            ),
                            array(
                                'chapter_id'    => $chapter_id
                            )
                        );
            
                    }else{
                        $is_unlocked = 'no';
                    }
            
                    $comm_r = $wpdb->update(
                        $comm_tbl,
                        array(
                            'total_payment' => $total,
                            'is_manga_unlocked' => $is_unlocked,
                            'time'  => $date
                        ),
                        array(
                            'chapter_id'    => $chapter_id,
                            'post_id'   => $post_id
                        )
                        );
                    if($comm_r){
                
                            $don_r = $wpdb->insert(
                                $donor_tbl,
                                array(
                                    'user_id'   => $user_id_a,
                                    'donor'     => $user,
                                    'amount'    => $amount,
                                    'chapter_id'    => $chapter_id,
                                    'post_id'       => $post_id,
                                    'is_anonymous'  => $anonymous,
                                    'time'          => $date
                                )
                            );
                
                            if($don_r){
                                $result = array(
                                    'status' => 'success',
                                    'text'  => 'Points donated! Refreshing the page'
                                );
                            }else{
                                $result = array(
                                    'status' => 'error',
                                );
                            }
                    }else{
                        $result = array(
                            'status' => 'error',
                            'text'  => $chapter_id . ' ' . $user_meta[0]->meta_value
                        );
                    }
                }else{
                    $result = array(
                        'status' => 'error',
                    );
                }
            }else{
                $result = array(
                    'status' => 'error_h',
                    'text'  => 'Insuficient points, buy more coins to donate in this chapter'
                );
            }
        }else{
            $result = array(
                'status' => 'error_h',
                'text'  => 'Insuficient points, buy coins to donate in this chapter',
                'sample'    => $user_meta[0]->meta_value . ' ' . $user_m . ' ' . $user_id . ' ' . $meta_key
            );
        }


    }
     wp_send_json( $result);
    // $wpdb->close();
    die;
}

function add_points_ajax_handler(){
   
    global $wpdb;
    
    $wp_user = $wpdb->prefix . 'usermeta';
    $user_id = $_POST['user_id'];
    $amount = $_POST['points'];
    $mycred_meta = 'mycred_default';
    $user_r = $wpdb->get_results("SELECT meta_value FROM $wp_user where user_id = $user_id and meta_key = 'mycred_default'");

    if ( ! wp_verify_nonce( $_POST['nonce'], 'ajax-buy-nonce' ) ) {
        die;
    }

    if(count($user_r) > 0){
        $user_points = $user_r[0]->meta_value;
        $user_added_points = $user_points + $amount;
        $user_u = $wpdb->update(
            $wp_user,
            array(
                'meta_value'    => $user_added_points
            ),
            array(
                'user_id'  => $user_id,
                'meta_key'  => 'mycred_default'
            )
        );
        if($user_u){
            $result = array(
                'status'    => 'success',
            );
        }else{
            $result = array(
                'status'    => 'error'
            ); 
        }
    }else{
        $user_i = $wpdb->insert(
            $wp_user,
            array(
                'user_id'   => $user_id,
                'meta_key'  => 'mycred_default',
                'meta_value'    => $amount
            )
        );

        if($user_i){
            $result = array(
                'status'    => 'success',
            );
        }else{
            $result = array(
                'status'    => 'error'
            ); 
        }
    }
  

 wp_send_json( $result);
// $wpdb->close();
die;
}   