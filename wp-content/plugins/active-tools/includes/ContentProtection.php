<?php


namespace ActiveTools;

use Wa72\HtmlPageDom\HtmlPageCrawler;

class ContentProtection {
    
    private int $rand_num1;
    private int $rand_num2;
    private string $rand_num1_string;
    private string $rand_num2_string;
    private string $scramble_styles;
    
    private bool $bad_experience_enabled;
    private int $bad_experience_random_seed;
    private int $bad_experience_swap_paragraph_rate;
    private int $bad_experience_swap_words_rate;
    private array $bad_experience_swap_words;
    
    private function __construct() {
        $this->register_hooks();
        
        $this->rand_num1 = rand(0, 999999);
        $this->rand_num2 = rand(0, 999999);
        
        $this->rand_num1_string = md5( $this->rand_num1 );
        $this->rand_num2_string = md5( $this->rand_num2 );
        
        $this->scramble_styles = '';
        
        $this->bad_experience_enabled = false;
        $this->bad_experience_random_seed = 0;
        $this->bad_experience_swap_paragraph_rate = 0;
        $this->bad_experience_swap_words_rate = 0;
        $this->bad_experience_swap_words = [];
    }
    
    protected static ?ContentProtection $instance = null;
    
    public static function getInstance() {
        NULL === self::$instance and self::$instance = new self;
        return self::$instance;
    }
    
    private function register_hooks() {
    
        add_filter( 'the_content', array( $this, 'filter_content' ), PHP_INT_MAX - 1 );
    }

    public function wp_head() {
        global $post;
        ?>
        
        <?php
    }
    
    function filter_content( $content ) {
        
        global $post;
        
        $user_id = get_current_user_id();
        
        if ( $user_id == 0 ) {
            $user_id = 3333;
        }
        
        $content = str_replace('{{uid}}', strval( $user_id ), $content );
        
        $this->init_bad_experience();
        
        $obfuscation_types = [];
        
        if ( $post ) {
            $at_po_types = get_post_meta( $post->ID, '_at_po_t', true );
            
            if ( ! empty( $at_po_types ) ) {
                foreach( $at_po_types as $at_po_type ) {
                    $obfuscation_types[] = $at_po_type['value'];
                }
            }
        }
        
        if ( empty( $obfuscation_types ) || in_array( 'disabled', $obfuscation_types ) ) {
            return $content;
        }
        
        $ob_all = in_array( 'all', $obfuscation_types );
    
        $letter_arr = str_split('abcdefghijklmnopqrstuvwxyz');
        $base_class = $letter_arr[rand(0,25)] . $this->rand_num1_string;
        
        $c = new HtmlPageCrawler('<div class="' . $base_class . '">' . $content . '</div>');
        
        /** @var $node \Symfony\Component\CssSelector\Node\ElementNode */
        
        if ( $ob_all || in_array( 'scrambled_text', $obfuscation_types ) ) {
            $po_content = $c->filter( '.' . $base_class );
    
            $inner_tags_filter = [
                'em',
                'strong',
                'i',
                'a'
            ];
            
            $this->scramble_styles = '<style>';
            
            $po_content->each( function ( $po_ele, $i ) use ( $base_class ) {
                
                /** @var HtmlPageCrawler $paragraphs */
                $paragraphs = $po_ele->filter( '.' . $base_class . ' p' );
                $paragraphs_count = $paragraphs->count();
                
                // First process swapping of paragraphs
                if ( $this->bad_experience_swap_paragraph_rate > 0 && $paragraphs_count > 1 ) {
                    $paragraphs->each( function ( $element, $j ) use ( $paragraphs, $paragraphs_count ) {
                        
                        mt_srand( $this->bad_experience_random_seed + $j );
                        
                        if ( mt_rand( 0, 99 ) < $this->bad_experience_swap_paragraph_rate ) {
                            
                            $swap_paragraphs = $paragraphs->each( function( $node, $i ) use ( $paragraphs_count ) {
                                if ( mt_rand( 0, $paragraphs_count - 1 ) == $i ) {
                                    return $node;
                                }
                                return null;
                            });
                            
                            foreach ( $swap_paragraphs as $swap_paragraph ) {
                                if ( $swap_paragraph != null ) {
                                    $text = $element->text();
                                    
                                    $element->setInnerHtml( $swap_paragraph->text() );
                                    $swap_paragraph->setInnerHtml( $text );
                                    break;
                                }
                            }
                        }
                        
                        mt_srand();
                    } );
                }
                
                $paragraphs->each( function ( $outer_element, $j ) use ( $paragraphs ) {
                    
                    $text = $outer_element->text();
                    
                    $text_data = $this->process_string( $text );
                    
                    $inner_text = '';
                    
                    if ( is_array( $text_data ) ) {
                    
                        foreach( $text_data as $item ) {
        
                            $inner_text .= '<span class=" ' . $item['class'] . '"> ' . $item['inner_text'] . ' </span>';
                            ob_start();
                            ?>.<?php echo $item['class']; ?>::before {content: '<?php echo str_replace('\'', '\\\'', $item['before_text'] ); ?>';}.<?php echo $item['class']; ?>::after {content: '<?php echo str_replace('\'', '\\\'', $item['after_text'] ); ?>';}<?php
    
                            $this->scramble_styles .= ob_get_clean();
                        }
                    } else {
                        $inner_text = $text_data;
                    }
                    
                    $outer_element->setInnerHtml( $inner_text );
                    
                } );
            } );
            
            ob_start();
            
            ?>
            .<?php echo $base_class; ?> p {
                -moz-user-select: none;
                -ms-user-select: none;
                -webkit-user-select: none;
                user-select: none;
                position: relative;
            }
            .<?php echo $base_class; ?> p::before {
                -moz-user-select: none;
                -ms-user-select: none;
                -webkit-user-select: none;
                user-select: none;position: absolute;
                content: '';
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #000;
                opacity: 0;
                z-index: 99;
            }
            @media print {
                .<?php echo $base_class; ?> {
                    display: none;
                }
            }
            </style>
            <?php
            
            $this->scramble_styles .= ob_get_clean();
        }
        
        return $c->saveHTML() . $this->scramble_styles;
    }
    
    private function process_text_bad_experience( $text ) {
    
    }
    
    private function split_text_by_inner_elements( string $text, array $inner_elements ) {
        $string = [ $text ];
        $result = [];
    }
    
    private function mb_str_split( $string ) {
        return mb_split('/(?<!^)(?!$)/u', $string );
    }
    
    private function process_string( string $string, $wrap_element = 'span' ) {
        
        $letter_arr = str_split('abcdefghijklmnopqrstuvwxyz');
        $str_arr = mb_split( '\s', $string );
    
        $processed_content = [];
        
        $num_words = count( $str_arr );
        
        if ( $this->bad_experience_enabled ) {
            
            foreach( $str_arr as $key => $str ) {
                
                mt_srand( $this->bad_experience_random_seed + $key );
                
                foreach( $this->bad_experience_swap_words as $search => $replace ) {
                    
                    if ( strtolower( $search ) == strtolower( $str ) ) {
                        if ( mt_rand( 0, 99 ) < $this->bad_experience_swap_words_rate ) {
                            $str_arr[$key] = $replace;
                            break;
                        }
                    }
                    
                }
            }
            
            mt_srand();
            
        }
        
        if ( $num_words < 3 ) {
            return $string;
        }
        
        for ( $i = 0; $i < $num_words; ) {
            
            $before_len = min( rand( 1, 6 ), $num_words - $i - 1 );
            $inner_len = min( rand( 1, 6 ), $num_words - $i - $before_len - 1 );
            $after_len = min( rand( 1, 6 ), $num_words - $i - $before_len - $inner_len );
            
            $before_text = implode(' ', array_slice( $str_arr, $i, $before_len ) );
            $inner_text = implode(' ', array_slice( $str_arr, $i + $before_len, $inner_len ) );
            $after_text = implode(' ', array_slice( $str_arr, $i + $before_len + $inner_len, $after_len ) );
            
            $class = $letter_arr[rand(0,25)] . md5( rand( 0, 999999 ) );
    
            $processed_content[] = [
                'class' => $class,
                'before_text' => ( $i == 0 ? '' : '\a0 ') . $before_text,
                'inner_text' => $inner_text,
                'after_text' => $after_text,
            ];
            
            $i += $before_len + $inner_len + $after_len;
        }
        
        return $processed_content;
    }
    
    private function span_punctuation( $string ) {
        return preg_replace('[-\.,!]', "<span>$1</span>", $string );
    }
    
    private function string_spaces( int $num_spaces ) {
        $out = '';
        for( $i = 0; $i < $num_spaces; $i++ ) {
            $out .= '&nbsp;';
        }
        return $out;
    }
    
    private function mb_strrev($str){
        $r = '';
        for ($i = mb_strlen($str); $i>=0; $i--) {
            $r .= mb_substr($str, $i, 1);
        }
        return $r;
    }
    
    private function init_bad_experience() {
        
        $user = wp_get_current_user();
        
        $bad_experience_roles = get_option( '_at_cp_be_ur', [] );
        
        
        if ( get_user_meta( $user->ID, '_at_cp_be_e', true ) == 'yes' ) {
            $this->bad_experience_enabled = true;
        } else {
            foreach( $bad_experience_roles as $role ) {
                if ( in_array( $role['value'], $user->roles ) || $role['value'] == '_all' ) {
                    $this->bad_experience_enabled = true;
                }
            }
        }
        
        if ( ! $this->bad_experience_enabled ) {
            return;
        }
        
        $this->bad_experience_random_seed = get_option( '_at_cp_be_rs', 0 );
        
        if ( empty( $this->bad_experience_random_seed ) ) {
            $this->bad_experience_random_seed = get_current_user_id();
        }
        
        $this->bad_experience_swap_paragraph_rate = get_option( '_at_cp_be_s_pg_i_r', 0 );
        $this->bad_experience_swap_words_rate = get_option( '_at_cp_be_s_w_i_r', 0 );
        
        $words_to_replace = get_option( '_at_cp_be_s_w_i', 0 );
        
        if ( empty( $words_to_replace ) ) {
            return;
        }
        
        foreach( $words_to_replace as $terms ) {
            if ( ! empty( $terms['search'][0]['value'] ) && ! empty( $terms['replace'][0]['value'] ) ) {
                $this->bad_experience_swap_words[$terms['search'][0]['value']] = $terms['replace'][0]['value'];
            }
        }
    }
}
