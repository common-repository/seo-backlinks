if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();

delete_option('loc_max');
delete_option('loc_sensitive');
delete_option('loc_target_blank');
delete_option('loc_back');
