<?php if (!defined('FW')) die('Forbidden');

class FW_Ext_Backups_Task_Type_Remote_Download extends FW_Ext_Backups_Task_Type {
    public function get_type() {
        return 'remote-download';
    }

    public function get_title(array $args = array(), array $state = array()) {
        return __('Downloading Sample', 'fw');
    }

    /**
     * {@inheritdoc}
     * @param array $args
     * * dir - destination directory in which will be created `database.json.txt`
     */
    public function execute(array $args, array $state = array()) {
        $src_args = $args['src_args'];
        $download_link = $args['download_link'];

        $dst = $src_args['source'].'/demo.zip';
		$file = true;
		if(!file_exists($dst)){
			$file = file_put_contents( $dst , fopen( $download_link , 'r'));
		}
        if($file){
            $zip = new ZipArchive;
            $res = $zip->open( $dst );
            if ($res === TRUE) {
                $zip->extractTo( $src_args['source'] );
                $zip->close();
                return true;
            } else {
                return new WP_Error(
                    'no_dir', __('Can\'t unzip', 'fw')
                );
            }
        } else {
            // try using cURL
			$zipResource = fopen($dst, "w");

						// Get The Zip File From Server
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $download_link);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
			curl_setopt($ch, CURLOPT_FILE, $zipResource);
			$page = curl_exec($ch);
			if(!$page) {
				
			$error = new WP_Error(
								'no_dir', curl_error($ch)
							);
							curl_close($ch);
							return $error;
			} else {
				curl_close($ch);
				
				$zip = new ZipArchive;
						$res = $zip->open( $dst );
						if ($res === TRUE) {
							$zip->extractTo( $src_args['source'] );
							$zip->close();
							return true;
						} else {
							return new WP_Error(
								'no_dir', __('Can\'t unzip. Make sure gzip library is enabled', 'fw')
							);
						}
			}            
        }        
    }
}