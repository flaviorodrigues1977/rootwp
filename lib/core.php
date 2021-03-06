<?php

do_action( 'root_core' );

if ( !function_exists( 'get_simple_data' ) ) {

    /**
     *
     * Retrieves the content in list format to be used in form field of the select type
	 *
     * @global object $wpdb Class database of WordPress http://codex.wordpress.org/Class_Reference/wpdb
     * @param string $post_type Type of post to be recovered
     * @param string $where Additional search conditions
     * @return array|boolean List with the results, or false if there are no records in the database
     *
     */
    function get_simple_data( $post_type, $where='' )
    {
        global $wpdb;
        $data_fields = apply_filters( 'simple_data_fields', array( 'ID', 'post_title' ) );
        if ( is_array( $data_fields ) && ( count( $data_fields ) > 1 ) ) {
            $fields = implode( ', ', $data_fields );
            $orderby = apply_filters( 'simple_data_orderby', 'post_title' );

            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT {$fields} FROM {$wpdb->posts} WHERE post_type=%s AND post_status=%s " . $where . " ORDER BY {$orderby} ASC",
                    $post_type,
                    'publish'
                ), ARRAY_A
            );
            if ( is_array( $rows ) && count( $rows ) ) {
                $r = array();
                list( $k, $v ) = $data_fields;
                foreach( $rows as $row )
                    $r[ $row[ $k ] ] = $row[ $v ];

                return $r;
            }
        }
        return false;
    }

}


if ( !function_exists( 'get_simple_title' ) ) {

    /**
     *
     * Retrieves only the title from the post
	 *
     * @global object $wpdb Class database of WordPress http://codex.wordpress.org/Class_Reference/wpdb
     * @param string $post_id Post ID
     * @return string|boolean Post title
     *
     */
    function get_simple_title( $post_id )
    {
        global $wpdb;
        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_title FROM {$wpdb->posts} WHERE ID=%d",
                $post_id
            )
        );
    }

}

if ( !function_exists( 'wp_dropdown_posts' ) ) {

    /**
     *
     * Retrieve or display list of posts as a dropdown (select list).
     *
     * @param array|string $args Optional. Override default arguments.
     * @return string HTML content, if not displaying.
     *
     */
    function wp_dropdown_posts( $args='' )
    {
        $defaults = array(
            'post_type' => 'post',
            'selected'  => 0,
            'echo'      => 1,
            'name'      => 'post_id',
            'class'     => 'chosen',
            'required'  => 1
        );

        $r = wp_parse_args( $args, $defaults );
        extract( $r, EXTR_SKIP );

        $rows = get_simple_data( $post_type );
        $output = null;
        if ( is_array( $rows ) ) {
            $output = "<select name='" . esc_attr( $name ) . "' class='" . esc_attr( $class ) . "' data-placeholder='Escolha...'";

            if ( $required )
                $output .= ' required="true"';

            $output .= ">\n";
            $output .= "\t<option value=\"0\">Escolha...</option>\n";

            foreach ( $rows as $id => $label ) {
                $output .= sprintf( '<option value="%d"', $id );

                if ( $selected == $id )
                    $output .= ' selected="true"';

                $output .= sprintf( '>%s</option>', $label );
            }

            $output .= "</select>\n";
        }

        if ( !$echo )
            return $output;
        else
            echo $output;
    }

}


if ( !function_exists( 'get_term_data' ) ) {

    /**
     *
     * Retrieves the content in list format to be used in form field of the select type
	 *
     * @global object $wpdb Class database of WordPress http://codex.wordpress.org/Class_Reference/wpdb
     * @param string $post_type Type of post to be recovered
     * @param string $where Additional search conditions
     * @return array|boolean List with the results, or false if there are no records in the database
     *
     */
    function get_term_data( $tax, $args=array() )
    {
        $r = array();
        $rows = get_terms( $tax, $args );
        if ( is_array( $rows ) && count( $rows ) ) {
            foreach( $rows as $row )
                $r[ $row->term_id ] = $row->name;
        }
        return $r;
    }

}

if ( !function_exists( 'run_once' ) ) {

    /**
     *
     * Executes the callback function only once, if no such option in the database
     *
     * @param string $option_name Name of the option being checked
     * @param function $function Function to be triggered if the option does not exist
     * @param mixed $value Value to be compared
     *
     */
    function run_once( $option_name, $function, $value=false )
    {
        $opt = get_option( $option_name );
        if ( !$opt || ( $opt !== $value ) ) {
            update_option( $option_name, $value );
            
            $call = false;
            if ( is_string( $function ) && function_exists( $function ) )
                $call = true;
            else if ( is_array( $function ) && method_exists( $function[0], $function[1] ) )
                $call = true;

            if ( $call )
                call_user_func( $function );
        }
    }

}

if ( !function_exists( 'get_term_meta' ) ) {

    /**
     *
     * Retrieves the metadata of a term
     *
     * @param int $term_id term ID.
     * @param string $key Key to be recovered, returns all by default
     * @param bool $single If you want (true) or not (false) return a single value
     * @return array|string List of items or just a value if $single is true
     *
     */
    function get_term_meta( $term_id, $key='', $single=false )
    {
        return get_metadata( 'term', $term_id, $key, $single );
    }

}

if ( !function_exists( 'add_term_meta' ) ) {

    /**
     *
     * Adds a meta data to a term
     *
     * @param int $term_id term ID
     * @param string $meta_key Key of meta data
     * @param mixed $meta_value Value to be added
     * @param bool $unique If is true, and if there is a term, it is not inserted
     * @return int|bool Meta ID or false
     *
     */
    function add_term_meta( $term_id, $meta_key, $meta_value, $unique=false )
    {
        return add_metadata( 'term', $term_id, $meta_key, $meta_value, $unique );
    }

}

if ( !function_exists( 'delete_term_meta' ) ) {

    /**
     *
     * Deletes the meta values ​​of the past term as parameter
     *
     * @param int $term_id term ID
     * @param string $meta_key Key of meta data
     * @param mixed $meta_value Field value
     * @return bool Sucess or Fail
     *
     */
    function delete_term_meta( $term_id, $meta_key, $meta_value='' )
    {
        return delete_metadata( 'term', $term_id, $meta_key, $meta_value );
    }

}

if ( !function_exists( 'update_term_meta' ) ) {

    /**
     *
     * Updates the meta values ​​of a term
     *
     * @param int $term_id term ID
     * @param string $meta_key Meta data key
     * @param mixed $meta_value Updated field value
     * @param mixed $prev_value If completed, this removes old value
     * @return bool Sucess or Fail
     *
     */
    function update_term_meta( $term_id, $meta_key, $meta_value, $prev_value='' )
    {
        return update_metadata( 'term', $term_id, $meta_key, $meta_value, $prev_value );
    }

}

if ( !function_exists( 'set_url' ) ) {

    /**
     *
     * Format a url with a protocol access
     *
     * @param string $url Url address to be formatted
     * @param string $protocol Protocol to be used
     * @return string Url with defined protocol
     *
     */
    function set_url( $url, $protocol='http' )
    {
       if ( !$url )
           return '#';

       $protocols = apply_filters(
           'url_protocols',
           array(
               'http',
               'https',
               'ftp'
           )
        );

       if ( preg_match( '/' . implode( '|', $protocols ) . '/', $url ) )
           return $url;
       else
           return $protocol . '://' . $url;
    }

}

if ( !function_exists( 'in_localhost' ) ) {

    /**
     *
     * Checks if application is running in local environment
     *
     * @return boolean Return True or False
     *
     */
    function in_localhost()
    {
       $domains = array( 'localhost', '127.0.0.1' );
       return in_array( $_SERVER[ 'HTTP_HOST' ], $domains );
    }

}

if ( !function_exists( 'zerofill' ) ) {

    /**
     *
     * Fill with '0 '(zero) left side of the number inserted as a parameter
     *
     * @param integer $num Number to be formatted
     * @param integer $zerofill Amount of (zeros) to enter to left of the number
     * @return string Formatted number
     *
     */
    function zerofill( $num, $zerofill=2 )
    {
        return str_pad( $num, $zerofill, '0', STR_PAD_LEFT );
    }

}

if ( !function_exists( 'get_html_attribute' ) ) {

    /**
     *
     * Retrieves attributes of an HTML tag
     *
     * @version 1.0
     *
     * @param type $attr Required attribute
     * @param type $html_tag HTML Tag to be searched
     * @return string|boolean Attribute value or false
     */
    function get_html_attribute( $attr, $html_tag )
    {
        $re = '/' . preg_quote( $attr ) . '=([\'"])?((?(1).+?|[^\s>]+))(?(1)\1)/is';
        if ( preg_match( $re, $html_tag, $match ) )
            return urldecode( $match[2] );

        return false;
    }

}

if ( !function_exists( 'date_french_to_english' ) ) {

    /**
     *
     * Converts dates from the French (Brazilian) to the English standard default
     *
     * @param string $date_french Date formatted as dd/mm/YYYY
     * @return string|boolean Date under the new format, or false if invalid date
     *
     */
    function date_french_to_english( $date_french )
    {
        sscanf( $date_french, '%d/%d/%d', $d, $m, $y );

        $dt = sprintf( '%s-%s-%s', $y, zerofill( $m ), zerofill( $d ) );
        $date_check = date( 'd/m/Y', strtotime( $dt ) );
        if ( $date_french !== $date_check )
            $dt = false;

        return $dt;
    }

}

if ( !function_exists( 'datetime_br' ) ) {

    /**
     *
     * Format in long date Portuguese of Brazil
     *
     * @param string $f Format to be returned http://php.net/manual/pt_BR/function.strftime.php
     * @param timestamp $time Time to be formatted
     * @return string Formatted date
     *
     */
    function datetime_br( $f, $time )
    {
        setlocale( LC_ALL, "pt_BR", "pt_BR.iso-8859-1", "pt_BR.utf-8", "portuguese" );
        return strftime( $f, $time );
    }

}

if ( !function_exists( 'wp_list_posts' ) ) {

    /**
     *
     * Show on the screen a list of links about posts
     *
     * @param array $args Query settings
     *
     */
    function wp_list_posts( $args=array() )
    {
        $defaults = array(
            'post_type'         => 'post',
            'posts_per_page'    => 10
        );
        $args = wp_parse_args( $args, $defaults );
        $rows = get_posts( $args );
        if ( count( $rows ) ) {
            printf( '<ul%s>', ( isset( $args[ 'class' ] ) ) ? ' class="' . $args[ 'class' ] . '"' : '' );
            foreach( $rows as $row ) {
                printf(
                    '<li><a href="%1$s" title="%2$s">%2$s</a></li>',
                    get_permalink( $row->ID ),
                    get_the_title( $row->ID )
                );
            }
            echo '</ul>';
        } else {
            printf( '<p>%s</p>', $args[ 'not_found' ] );
        }
    }

}

if ( !function_exists( 'get_string_numeric' ) ) {

    /**
     * 
     * Display the language string for the number of items passed by param.
     * Works like comments_number(), WordPress function.
     *
     * @param integer $number     Required. Amount of items.
     * @param string  $zero       Optional. Text for no comments. Default false.
     * @param string  $one        Optional. Text for one comment. Default false.
     * @param string  $more       Optional. Text for more than one comment. Default false.
     * 
     **/
    function get_string_numeric( $number, $zero=false, $one=false, $more=false )
    {
        $number = (int) $number;
        if ( $number > 1 )
            $output = str_replace( '%', number_format_i18n( $number ), ( false === $more ) ? __r( '% items' ) : $more );
        elseif ( $number == 0 )
            $output = ( false === $zero ) ? __r( 'No items' ) : $zero;
        else // must be one
            $output = ( false === $one ) ? __r( 'One item') : $one;

        return $output;
    }

}

?>