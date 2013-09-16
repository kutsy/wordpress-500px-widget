<?php
/**
 * Plugin Name: 500px Widget
 * Plugin URI: http://romantelychko.com/downloads/wordpress/plugins/500px-widget.latest.zip
 * Description: 500px Widget works only as a sidebar widget and will retrieve images (based on a criteria) hosted on the 500px.com service. No 500px API key is required to use this plugin.
 * Version: 0.8.1
 * Author: Roman Telychko
 * Author URI: http://romantelychko.com
*/

///////////////////////////////////////////////////////////////////////////////

/**
 * 500px Widget.
 */
class Widget_500px extends WP_Widget 
{
    ///////////////////////////////////////////////////////////////////////////
        
    protected $defaults = array(
        'widget_id'                 => 'widget_500px',

        'title'                     => '500px',
        'consumer_key'              => '5JwOJabC89Cb5uvgHmCJgYDAGXG9TwJ5fjOEg9Pk',
        'feature'                   => 1,
        'feature_username'          => '',
        'feature_tag'               => '',
        'category'                  => -1,
        'sort_by'                   => 1,
        'count'                     => 6,
        'thumb_size'                => 1,
        'cache_lifetime'            => 3600,
        'one_element_html'          => "<span class=\"widget_500px_item entry-content\">\n<a href=\"{photo_url}\" target=\"_blank\" rel=\"nofollow\" title=\"{photo_title}\"><img src=\"{photo_image_url}\" width=\"{photo_width}\" height=\"{photo_height}\" alt=\"{photo_title}\" /></a>\n</span>",
        'categories'                => 
            array(
                '-1'    => 'Any',
                '0'     => 'Uncategorized',
                '10'    => 'Abstract',
                '11'    => 'Animals',
                '5'     => 'Black and White',
                '1'     => 'Celebrities',
                '9'     => 'City and Architecture',
                '15'    => 'Commercial',
                '16'    => 'Concert',
                '20'    => 'Family',
                '14'    => 'Fashion',
                '2'     => 'Film',
                '24'    => 'Fine Art',
                '23'    => 'Food',
                '3'     => 'Journalism',
                '8'     => 'Landscapes',
                '12'    => 'Macro',
                '18'    => 'Nature',
                '4'     => 'Nude',
                '7'     => 'People',
                '19'    => 'Performing Arts',
                '17'    => 'Sport',
                '6'     => 'Still Life',
                '21'    => 'Street',
                '26'    => 'Transporation',
                '13'    => 'Travel',
                '22'    => 'Underwater',
                '27'    => 'Urban Exploration',
                '25'    => 'Wedding',
            ),
        );

    ///////////////////////////////////////////////////////////////////////////

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() 
	{	
		parent::__construct(
	 		$this->defaults['widget_id'],
			'500px Widget',
			array(
			    'description'   => 'Displays photos from 500px.com', 
			    'classname'     => $this->defaults['widget_id'],
			    ),
		    array(
			    'width'     => 400,
			    'height'    => 700,
		    )
		);
	}
	
    ///////////////////////////////////////////////////////////////////////////

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param       array       $args               Widget arguments.
	 * @param       array       $instance           Saved values from database.
     * @return      html
	 */
	public function widget( $args, $instance ) 
	{
	    ///////////////////////////////////////////////////////////////////////

	    // args
	    $args = array_merge( $this->defaults, $args );
		
        // cache key
        $cache_key = $this->defaults['widget_id'].'_'.dechex(crc32( $args['widget_id'] ));

        // try to get cached data from transient cache
        $html = get_transient( $cache_key );

        if( empty($html) )
        {   
            $html =
                ( isset($instance['custom_css']) && strlen($instance['custom_css'])>5 ? '<style type="text/css">'.$instance['custom_css'].'</style>' : '' ).
                $args['before_widget'];

		    if( !empty( $instance['title'] ) )
		    {
			    $html .= $args['before_title'].$instance['title'].$args['after_title'];
		    }
		
		    $photos = $this->getPhotos( $instance );

		    if( empty($photos) )
		    {
		        return false;
		    }
		    
		    if( is_array($photos) )
		    {
		        $html .= $this->getHTML( $photos, $instance );
		    }
		    else
		    {
		        $html .= '<span class="error">'.$photos.'</span>';        // its error
		    }

		    $html .= $args['after_widget'];
	
		    if( is_array($photos) )     // if not error
		    {
                // store result to cache
                set_transient( $cache_key, $html, $instance['cache_lifetime'] );
            }
		}

		echo( $html );		
		
	    ///////////////////////////////////////////////////////////////////////
	}
	
    ///////////////////////////////////////////////////////////////////////////
	
	/**
	 * Returns Photos
	 *
	 * @param       array       $args
	 * @return      array
	 */
	public function getPhotos( $args = array() )
	{
	    ///////////////////////////////////////////////////////////////////////
	    
	    $url = 'https://api.500px.com/v1/photos';

	    ///////////////////////////////////////////////////////////////////////

	    switch( $args['sort_by'] )
	    {
	        case 1:             // Time of upload (Most recent first)
	        default:
        	    $url_sort = 'created_at';	            
	            break;
            
            case 2:             // Rating (Highest rated first)
        	    $url_sort = 'rating';	            
	            break;
         
            case 3:             // View count (Most viewed first)
        	    $url_sort = 'times_viewed';	            
	            break;
                    
            case 4:             // Votes count (Most voted first)
        	    $url_sort = 'votes_count';	            
	            break;
                                        
            case 5:             // Favorites count (Most favorited first)
        	    $url_sort = 'favorites_count';	            
	            break;
                    
            case 6:             // Comments count (Most commented first)
        	    $url_sort = 'comments_count';	            
	            break;
                                        
            case 7:             // Original date (Most recent first)
        	    $url_sort = 'taken_at';	            
	            break;
                                        
	    }
	    
	    ///////////////////////////////////////////////////////////////////////
	    
	    #$url_params .= '?consumer_key='.$args['consumer_key'].'&sort='.$url_sort.'&rpp='.$args['count'].'&image_size='.$args['thumb_size'];
	    $url_params = '?consumer_key='.$this->defaults['consumer_key'].'&sort='.$url_sort.'&rpp='.$args['count'].'&image_size='.$args['thumb_size'];	    
	    
	    ///////////////////////////////////////////////////////////////////////
	    
	    if( $args['feature']<10 && $args['category']>=0 && isset($this->defaults['categories'][$args['category']]) )
	    {
	        $url_params .= '&only='.urlencode( $this->defaults['categories'][$args['category']] );
	    }
	    
	    ///////////////////////////////////////////////////////////////////////

	    switch( $args['feature'] )
	    {
	        case 1:             // Popular Photos
	        default:
	            $url .= $url_params.'&feature=popular';
	            break;
	            
	        case 2:             // Upcoming Photos
	            $url .= $url_params.'&feature=upcoming';
	            break;
	            
	        case 3:             // Editors' Choice Photos
	            $url .= $url_params.'&feature=editors';
	            break;
	            
	        case 4:             // Fresh Today Photos
	            $url .= $url_params.'&feature=fresh_today';
	            break;
	            
	        case 5:             // Fresh Yesterday Photos
	            $url .= $url_params.'&feature=fresh_yesterday';
	            break;
	            
	        case 6:             // Fresh This Week Photos
	            $url .= $url_params.'&feature=fresh_week';
	            break;
	            
	        case 7:             // User Photos
	            $url .= $url_params.'&feature=user&username='.$args['feature_username'];
	            break;
	            
	        case 8:             // User Friends Photos
	            $url .= $url_params.'&feature=user_friends&username='.$args['feature_username'];
	            break;
	            
	        case 9:             // User Favorites Photos
	            $url .= $url_params.'&feature=user_favorites&username='.$args['feature_username'];
	            break;
	            
	        case 10:            // Tag Photos
	            $url .= '/search'.$url_params.'&tag='.urlencode($args['feature_tag']);
	            break;
	    }
	    
	    ///////////////////////////////////////////////////////////////////////

        $data = wp_remote_get( $url, array( 'timeout' => 2 ) );

        if( !empty($data) )
        {	
            if( is_object($data) )
            {
                return $data->get_error_message();
            }
            else if( isset($data['body']) && !empty($data['body']) )
            {
                return json_decode( $data['body'], true );
            }            
        }    
	    
	    return false;
	    
	    ///////////////////////////////////////////////////////////////////////
	}
	
    ///////////////////////////////////////////////////////////////////////////
	
	/**
	 * Returns HTML of photos
	 *
	 * @param       array       $photos
	 * @param       array       $args
	 * @return      string
	 */
	public function getHTML( $photos = array(), $args = array() )
	{	
	    ///////////////////////////////////////////////////////////////////////
	
	    if( empty($photos) || !isset($photos['photos']) || empty($photos['photos']) )
	    {
	        return false;
	    }
	    
	    ///////////////////////////////////////////////////////////////////////
	    
	    // args
	    $args = array_merge( $this->defaults, $args );
	    
	    switch( $args['thumb_size'] )
	    {
	        case 1:
	        default:
	            $width  = $height = '70';
	            break;
	            
	        case 2:
	            $width  = $height = '140';
	            break;

	        case 3:
	            $width  = $height = '280';
	            break;
	            
	        case 4:
	            $width  = '';
	            $height = '';
	            break;
	    }
	    
	    ///////////////////////////////////////////////////////////////////////

	    $html = '';
	    
	    foreach( $photos['photos'] as $photo )
	    {
	        $html .= 
	            str_ireplace(
	                array(
	                    '{photo_title}',
	                    '{photo_url}',
	                    '{photo_image_url}',
	                    '{photo_width}',
	                    '{photo_height}',
	                    ),
	                array(
	                    $photo['name'],
	                    'http://500px.com/photo/'.$photo['id'],
	                    $photo['image_url'],
	                    $width,
	                    $height,
	                    ),
	                $args['one_element_html']
	                );
	        
	        /*
	        $html .= 
	            '<span class="'.$this->defaults['widget_id'].'_item">'.
	                '<a href="http://500px.com/photo/'.$photo['id'].'" target="_blank" rel="nofollow" title="'.esc_attr($photo['name']).'">'.
	                    '<img src="'.$photo['image_url'].'"'.( $width ? ' width="'.$width.'"' : '' ).( $height ? ' height="'.$height.'"' : '' ).' alt="'.esc_attr($photo['name']).'" />'.
                    '</a>'.
                '</span> ';
            */
	    }
	    
	    return $html;	
	    
	    ///////////////////////////////////////////////////////////////////////
	}
	
	///////////////////////////////////////////////////////////////////////////
	
	/**
	 * Clear transient widget cache
	 *
	 * @return      bool
	 */
	public function clearCache()
	{	
	    ///////////////////////////////////////////////////////////////////////
	
	    global $wpdb;
	
	    $q = '
	        SELECT
		        option_name     as name
	        FROM
		        '.$wpdb->options.'
	        WHERE	
	            option_name LIKE \'_transient_'.$this->defaults['widget_id'].'_%\'';

	    $transients = $wpdb->get_results($q);
	    
	    if( !empty($transients) )
	    {
	        foreach( $transients as $transient )
	        {
	            delete_transient( str_replace( '_transient_', '', $transient->name ) );
	        }
	    }
	    
	    return true;
	    
	    ///////////////////////////////////////////////////////////////////////
	}
	
    ///////////////////////////////////////////////////////////////////////////

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param       array       $new_instance       Values just sent to be saved.
	 * @param       array       $old_instance       Previously saved values from database.
	 *
	 * @return      array                           Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) 
	{
	    ///////////////////////////////////////////////////////////////////////
	
	    // drop cache
	    $this->clearCache();

	    ///////////////////////////////////////////////////////////////////////
	
	    // return sanitized data
		return 
		    array(
		        'title'                         => trim( strip_tags( $new_instance['title'], '<a><b><strong><i><em><span><div>' ) ),
		        #'consumer_key'                  => trim( preg_replace( '#[^0-9A-Za-z]#', '', strip_tags( $new_instance['consumer_key'] ) ) ),
                'feature'                       => intval( preg_replace( '#[^0-9]#', '', $new_instance['feature'] ) ),
                'feature_username'              => trim( strip_tags( $new_instance['feature_username'] ) ),
                'feature_tag'                   => trim( strip_tags( $new_instance['feature_tag'] ) ),
                'category'                      => intval( preg_replace( '#[^0-9\-]#', '', $new_instance['category'] ) ),
                'sort_by'                       => intval( preg_replace( '#[^0-9]#', '', $new_instance['sort_by'] ) ),
		        'count'                         => intval( preg_replace( '#[^0-9]#', '', $new_instance['count'] ) ),
		        'thumb_size'                    => intval( preg_replace( '#[^0-9]#', '', $new_instance['thumb_size'] ) ),
		        'cache_lifetime'                => intval( preg_replace( '#[^0-9]#', '', $new_instance['cache_lifetime'] ) ),
		        'one_element_html'              => trim( $new_instance['one_element_html'] ),
                'custom_css'                    => trim(
                                                       strip_tags(
                                                           str_ireplace(
                                                               '#'.$this->id_base.'-__i__',
                                                               '#'.$this->id_base.'-'.$this->number,
                                                               $new_instance['custom_css']
                                                           )
                                                       )
                                                   ),
		    );
		    
	    ///////////////////////////////////////////////////////////////////////
	}
	
    ///////////////////////////////////////////////////////////////////////////

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param       array       $instance           Previously saved values from database.
     * @return      html
	 */
	public function form( $instance ) 
	{
	    ///////////////////////////////////////////////////////////////////////
	
	    // defaults
	    $title                      = $this->defaults['title'];
	    #$consumer_key               = $this->defaults['consumer_key'];	    
	    $feature                    = $this->defaults['feature'];
	    $feature_username           = $this->defaults['feature_username'];
	    $feature_tag                = $this->defaults['feature_tag'];
	    $category                   = $this->defaults['category'];
	    $sort_by                    = $this->defaults['sort_by'];
	    $count                      = $this->defaults['count'];
	    $thumb_size                 = $this->defaults['thumb_size'];
	    $cache_lifetime             = $this->defaults['cache_lifetime'];
	    $one_element_html           = $this->defaults['one_element_html'];
        $custom_css                 = '';
	    
	    ///////////////////////////////////////////////////////////////////////

        // set values
		if( isset($instance['title']) && strlen($instance['title'])>1 ) 
		{
			$title = $instance['title'];
		}
		
    	#if( isset($instance['consumer_key']) && strlen($instance['consumer_key'])>1 ) 
		#{
			#$consumer_key = $instance['consumer_key'];
		#}
		
		if( isset($instance['feature']) && intval($instance['feature'])>0 ) 
		{
			$feature = intval($instance['feature']);
		}
		
		if( isset($instance['feature_username']) && strlen($instance['feature_username'])>1 ) 
		{
			$feature_username = $instance['feature_username'];
		}
		
		if( isset($instance['feature_tag']) && strlen($instance['feature_tag'])>1 ) 
		{
			$feature_tag = $instance['feature_tag'];
		}
		
		if( isset($instance['category']) ) 
		{
			$category = intval($instance['category']);
		}
		
		if( isset($instance['sort_by']) && intval($instance['sort_by'])>0 ) 
		{
			$sort_by = intval($instance['sort_by']);
		}
		
		if( isset($instance['count']) && intval($instance['count'])>0 ) 
		{
			$count = intval($instance['count']);
		}
		
		if( isset($instance['thumb_size']) && intval($instance['thumb_size'])>0 ) 
		{
			$thumb_size = intval($instance['thumb_size']);
		}
		
		if( isset($instance['cache_lifetime']) && intval($instance['cache_lifetime'])>0 ) 
		{
			$cache_lifetime = intval($instance['cache_lifetime']);
		}
		
		if( isset($instance['one_element_html']) && strlen($instance['one_element_html'])>1 ) 
		{
			$one_element_html = $instance['one_element_html'];
		}

        if( isset($instance['custom_css']) )
        {
            $custom_css = $instance['custom_css'];
        }
        else
        {
            $temp_widget_id = $this->id_base.'-'.$this->number;

            $custom_css =
                '#'.$temp_widget_id.' { /* block style */ }'."\n".
                '#'.$temp_widget_id.' .widget-title { /* widget title style */ }'."\n".
                '#'.$temp_widget_id.' .widget_500px_item { /* one item style */ }';
        }

	    ///////////////////////////////////////////////////////////////////////
	    
	    $temp_select_categories = '';
	    
	    foreach( $this->defaults['categories'] as $category_id => $category_title )
	    {
	        $temp_select_categories .= '<option value="'.$category_id.'"'.( $category_id==$category ? ' selected="selected"' : '' ).'>'.$category_title.'</option>';
	    }
	    
	    ///////////////////////////////////////////////////////////////////////
	
	    // html	
		echo(
		    '<script type="text/javascript">
		        jQuery(document).ready(
		            function()
		            {
		                jQuery(\'select.'.$this->defaults['widget_id'].'_feature_select\').live( 
		                    \'change\', 
		                    function()
		                    {		                    
                                if( jQuery(this).val()<7 )
                                {
                                    jQuery(this).closest(\'div.'.$this->defaults['widget_id'].'_div\').find(\'p.'.$this->defaults['widget_id'].'_feature_username_p\').hide();
                                    jQuery(this).closest(\'div.'.$this->defaults['widget_id'].'_div\').find(\'p.'.$this->defaults['widget_id'].'_feature_tag_p\').hide();
                                    jQuery(this).closest(\'div.'.$this->defaults['widget_id'].'_div\').find(\'p.'.$this->defaults['widget_id'].'_category_p\').show();
                                }
                                else if( jQuery(this).val()==10 )
                                {
                                    jQuery(this).closest(\'div.'.$this->defaults['widget_id'].'_div\').find(\'p.'.$this->defaults['widget_id'].'_feature_username_p\').hide();
                                    jQuery(this).closest(\'div.'.$this->defaults['widget_id'].'_div\').find(\'p.'.$this->defaults['widget_id'].'_feature_tag_p\').show();
                                    jQuery(this).closest(\'div.'.$this->defaults['widget_id'].'_div\').find(\'p.'.$this->defaults['widget_id'].'_category_p\').hide();
                                }
                                else
                                {
                                    jQuery(this).closest(\'div.'.$this->defaults['widget_id'].'_div\').find(\'p.'.$this->defaults['widget_id'].'_feature_username_p\').show();
                                    jQuery(this).closest(\'div.'.$this->defaults['widget_id'].'_div\').find(\'p.'.$this->defaults['widget_id'].'_feature_tag_p\').hide();
                                    jQuery(this).closest(\'div.'.$this->defaults['widget_id'].'_div\').find(\'p.'.$this->defaults['widget_id'].'_category_p\').show();
                                }                                       		        
                                
                                return true;
		                    });
	                });'.
		    '</script>'.
		
	        '<div class="'.$this->defaults['widget_id'].'_div">'.
		        '<p>'.
		            '<label for="'.$this->get_field_id('title').'">Widget title:</label>'.
		            '<input class="widefat" id="'.$this->get_field_id('title').'" name="'.$this->get_field_name('title').'" type="text" value="'.esc_attr($title).'" />'.
		        '</p>'.
		        #'<p>'.
		            #'<label for="'.$this->get_field_id('consumer_key').'">Consumer key (from <a href="http://500px.com/settings/applications" target="_BLANK">500px applications</a>):</label>'.
		            #'<input class="widefat" id="'.$this->get_field_id('consumer_key').'" name="'.$this->get_field_name('consumer_key').'" type="text" value="'.esc_attr($consumer_key).'" placeholder="&lt;register your 500px-application&gt;" />'.
		        #'</p>'.
		        '<p>'.
		            '<label for="'.$this->get_field_id('feature').'">What to display:</label>'.
		            '<select class="widefat '.$this->defaults['widget_id'].'_feature_select" id="'.$this->get_field_id('feature').'" name="'.$this->get_field_name('feature').'">'.
		                '<option value="1"'.( $feature<2 || $feature>10 ? ' selected="selected"' : '' ).'>Popular Photos</option>'.
		                '<option value="2"'.( $feature==2 ? ' selected="selected"' : '' ).'>Upcoming Photos</option>'.
		                '<option value="3"'.( $feature==3 ? ' selected="selected"' : '' ).'>Editors\' Choice Photos</option>'.
		                '<option value="4"'.( $feature==4 ? ' selected="selected"' : '' ).'>Fresh Today Photos</option>'.
		                '<option value="5"'.( $feature==5 ? ' selected="selected"' : '' ).'>Fresh Yesterday Photos</option>'.
		                '<option value="6"'.( $feature==6 ? ' selected="selected"' : '' ).'>Fresh This Week Photos</option>'.
		                
		                '<option value="7"'.( $feature==7 ? ' selected="selected"' : '' ).'>User Photos</option>'.
		                '<option value="8"'.( $feature==8 ? ' selected="selected"' : '' ).'>User Friends Photos</option>'.
		                '<option value="9"'.( $feature==9 ? ' selected="selected"' : '' ).'>User Favorites Photos</option>'.
		                
		                '<option value="10"'.( $feature==10 ? ' selected="selected"' : '' ).'>Tag Photos</option>'.
		            '</select>'.
		        '</p>'.
		        '<p class="'.$this->defaults['widget_id'].'_feature_username_p"'.( $feature>6 && $feature<10 ? ' style="display:block;"' : ' style="display:none;"' ).'>'.
		            '<label for="'.$this->get_field_id('feature_username').'">Username:</label>'.
		            '<input class="widefat" id="'.$this->get_field_id('feature_username').'" name="'.$this->get_field_name('feature_username').'" type="text" value="'.esc_attr($feature_username).'" />'.
		        '</p>'.
		        '<p class="'.$this->defaults['widget_id'].'_feature_tag_p"'.( $feature==10 ? ' style="display:block;"' : ' style="display:none;"' ).'>'.
		            '<label for="'.$this->get_field_id('feature_tag').'">Tag:</label>'.
		            '<input class="widefat" id="'.$this->get_field_id('feature_tag').'" name="'.$this->get_field_name('feature_tag').'" type="text" value="'.esc_attr($feature_tag).'" />'.
		        '</p>'.
		        '<p class="'.$this->defaults['widget_id'].'_category_p"'.( $feature<10 ? ' style="display:block;"' : ' style="display:none;"' ).'>'.
		            '<label for="'.$this->get_field_id('category').'">Category:</label>'.
		            '<select class="widefat" id="'.$this->get_field_id('category').'" name="'.$this->get_field_name('category').'">'.		            		        
		                $temp_select_categories.
		            '</select>'.
		        '</p>'.			        
		        '<p>'.
		            '<label for="'.$this->get_field_id('sort_by').'">Sort by:</label>'.
		            '<select class="widefat" id="'.$this->get_field_id('sort_by').'" name="'.$this->get_field_name('sort_by').'">'.		            
		                '<option value="1"'.( $sort_by<2 || $sort_by>7 ? ' selected="selected"' : '' ).'>Time of upload (Most recent first)</option>'.
		                '<option value="2"'.( $sort_by==2 ? ' selected="selected"' : '' ).'>Rating (Highest rated first)</option>'.
		                '<option value="3"'.( $sort_by==3 ? ' selected="selected"' : '' ).'>View count (Most viewed first)</option>'.
		                '<option value="4"'.( $sort_by==4 ? ' selected="selected"' : '' ).'>Votes count (Most voted first)</option>'.
		                '<option value="5"'.( $sort_by==5 ? ' selected="selected"' : '' ).'>Favorites count (Most favorited first)</option>'.
		                '<option value="6"'.( $sort_by==6 ? ' selected="selected"' : '' ).'>Comments count (Most commented first)</option>'.
		                '<option value="7"'.( $sort_by==7 ? ' selected="selected"' : '' ).'>Original date (Most recent first)</option>'.
		            '</select>'.
		        '</p>'.	
		        '<p>'.
		            '<label for="'.$this->get_field_id('count').'">Display count:</label>'.
		            '<input class="widefat" id="'.$this->get_field_id('count').'" name="'.$this->get_field_name('count').'" type="text" value="'.esc_attr($count).'" />'.
		        '</p>'.
		        '<p>'.
		            '<label for="'.$this->get_field_id('thumb_size').'">Thumb size (px):</label>'.
		            '<select class="widefat" id="'.$this->get_field_id('thumb_size').'" name="'.$this->get_field_name('thumb_size').'">'.		            
		                '<option value="1"'.( $thumb_size<2 || $thumb_size>4 ? ' selected="selected"' : '' ).'>70x70</option>'.
		                '<option value="2"'.( $thumb_size==2 ? ' selected="selected"' : '' ).'>140x140</option>'.
		                '<option value="3"'.( $thumb_size==3 ? ' selected="selected"' : '' ).'>280x280</option>'.
		                '<option value="4"'.( $thumb_size==4 ? ' selected="selected"' : '' ).'>900x / x900</option>'.
		            '</select>'.
		        '</p>'.		
		        '<p>'.
		            '<label for="'.$this->get_field_id('cache_lifetime').'">Cache lifetime (sec):</label>'.
		            '<input class="widefat" id="'.$this->get_field_id('cache_lifetime').'" name="'.$this->get_field_name('cache_lifetime').'" type="text" value="'.esc_attr($cache_lifetime).'" />'.
		        '</p>'.
		        '<p>'.
		            '<label for="'.$this->get_field_id('one_element_html').'">One element HTML (inside &lt;LI&gt;):</label>'.
		            '<textarea class="widefat" cols="20" rows="6" id="'.$this->get_field_id('one_element_html').'" name="'.$this->get_field_name('one_element_html').'">'.$one_element_html.'</textarea>'.
                    'You can use this placeholders:'.
                    '<ul>'.
		                '<li><code>{photo_title}</code> - Photo title on <a href="http://500px.com">500px.com</a></li>'.
		                '<li><code>{photo_url}</code> - URL to photo on <a href="http://500px.com">500px.com</a></li>'.
		                '<li><code>{photo_image_url}</code> - URL to photo image on <a href="http://500px.com">500px.com</a></li>'.
		                '<li><code>{photo_width}</code> - Thumbnail width (in px).<br />&nbsp;&nbsp;Recommended to remove attribute for Thumb Size: <code>900x/x900</code></li>'.
		                '<li><code>{photo_height}</code> - Thumbnail height (in px).<br />&nbsp;&nbsp;Recommended to remove attribute for Thumb Size: <code>900x/x900</code></li>'.
	                '</ul>'.
		        '</p>'.
                '<p>'.
                    '<label for="'.$this->get_field_id('custom_css').'">Custom CSS (remove if unneeded):</label>'.
                    '<textarea class="widefat" cols="20" rows="6" id="'.$this->get_field_id('custom_css').'" name="'.$this->get_field_name('custom_css').'">'.$custom_css.'</textarea>'.
                '</p>'.
		        '<p>'.
		            'I forgot something? <a href="http://wordpress.org/support/plugin/500px-widget" target="_BLANK">You can write to me!</a>'.
		        '</p>'.
	        '</div>'
		    );
		    
	    ///////////////////////////////////////////////////////////////////////
	}

    ///////////////////////////////////////////////////////////////////////////
}

///////////////////////////////////////////////////////////////////////////////

// register 500px Widget
add_action( 'widgets_init', create_function( '', 'register_widget( "Widget_500px" );' ) );

///////////////////////////////////////////////////////////////////////////////
